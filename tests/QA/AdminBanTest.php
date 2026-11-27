<?php

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\DebateBanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin Ban QA',
        'email' => 'admin-ban-qa@example.test',
        'slug' => 'admin-ban-qa',
        'is_admin' => true,
    ]);
    $this->member = User::factory()->create([
        'name' => 'Miembro Ban QA',
        'email' => 'miembro-ban-qa@example.test',
        'slug' => 'miembro-ban-qa',
    ]);
});

test('an admin can choose among several ban durations including permanent', function () {
    foreach ([30, 60, 360, 1440, 10080, 43200, 52560000] as $minutes) {
        $this->actingAs($this->admin)
            ->post("/admin/usuarios/{$this->member->id}/ban", ['minutes' => $minutes])
            ->assertStatus(303);

        $until = $this->member->refresh()->banned_until;
        expect($until)->not->toBeNull()
            ->and($until->greaterThan(now()->addMinutes($minutes - 1)))->toBeTrue()
            ->and($until->lessThan(now()->addMinutes($minutes + 1)))->toBeTrue();
    }
});

test('a banned member cannot log in until the ban is lifted or expires', function () {
    $this->member->banUntil(now()->addMinutes(30));

    $this->post(route('login.store'), [
        'email' => $this->member->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('suspendida');
    $this->assertGuest();

    $this->member->banUntil(now()->addYears(100));

    $this->post(route('login.store'), [
        'email' => $this->member->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    expect(session('errors')->first('email'))->toContain('permanentemente');

    $this->member->banUntil(now()->subMinute());

    $this->post(route('login.store'), [
        'email' => $this->member->email,
        'password' => 'password',
    ])->assertRedirect(route('community.feed', absolute: false));

    $this->assertAuthenticatedAs($this->member);
});

test('only an admin can ban and nobody can ban themselves', function () {
    $this->actingAs($this->member)
        ->post("/admin/usuarios/{$this->admin->id}/ban", ['minutes' => 30])
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->admin->id}/ban", ['minutes' => 30])
        ->assertStatus(422);

    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->member->id}/ban", ['minutes' => 7])
        ->assertSessionHasErrors('minutes');

    expect($this->member->refresh()->isBanned())->toBeFalse()
        ->and($this->admin->refresh()->isBanned())->toBeFalse();
});

test('the admin panel shows the ban and can lift it', function () {
    $this->member->banUntil(now()->addMinutes(30));

    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.banned', 1)
            ->where('users', fn ($users) => collect($users)->firstWhere('id', $this->member->id)['is_banned'] === true));

    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->member->id}/desbanear")
        ->assertStatus(303);

    expect($this->member->refresh()->isBanned())->toBeFalse();
});

test('members can report offensive posts but not their own or duplicates', function () {
    $post = Post::query()->create([
        'user_id' => $this->member->id,
        'kind' => 'reflexion',
        'body' => 'Publicación ofensiva',
    ]);
    $other = User::factory()->create(['email' => 'otro-ban-qa@example.test']);

    $this->actingAs($other)
        ->post("/posts/{$post->id}/reportar", ['reason' => 'Ofensas repetidas'])
        ->assertRedirect();

    expect(Report::query()->where('post_id', $post->id)->count())->toBe(1);

    $this->actingAs($other)
        ->post("/posts/{$post->id}/reportar", ['reason' => 'Otra queja'])
        ->assertRedirect();

    expect(Report::query()->where('post_id', $post->id)->count())->toBe(1);

    $this->actingAs($this->member)
        ->post("/posts/{$post->id}/reportar", ['reason' => 'Reporte propio'])
        ->assertStatus(422);
});

test('the admin reviews a report and can ban, delete or dismiss it', function () {
    $post = Post::query()->create([
        'user_id' => $this->member->id,
        'kind' => 'reflexion',
        'body' => 'Publicación a revisar',
    ]);
    $reporter = User::factory()->create(['email' => 'reportante-ban-qa@example.test']);
    $report = Report::query()->create([
        'reporter_id' => $reporter->id,
        'post_id' => $post->id,
        'reason' => 'Lenguaje ofensivo',
    ]);

    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.reports', 1)
            ->where('reports', fn ($reports) => collect($reports)->firstWhere('id', $report->id)['reason'] === 'Lenguaje ofensivo'));

    $this->actingAs($this->admin)
        ->post("/admin/reportes/{$report->id}/resolver", ['action' => 'ban', 'minutes' => 1440])
        ->assertStatus(303);

    expect($this->member->refresh()->isBanned())->toBeTrue()
        ->and($report->refresh()->status)->toBe('resolved')
        ->and($report->resolved_by_id)->toBe($this->admin->id);

    $secondReporter = User::factory()->create(['email' => 'otro-reportante-ban-qa@example.test']);
    $second = Report::query()->create([
        'reporter_id' => $secondReporter->id,
        'post_id' => $post->id,
        'reason' => 'Reincidencia',
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/reportes/{$second->id}/resolver", ['action' => 'delete'])
        ->assertStatus(303);

    expect(Post::find($post->id))->toBeNull()
        ->and(Report::find($second->id))->toBeNull()
        ->and($this->member->refresh()->isBanned())->toBeTrue();

    $thirdPost = Post::query()->create([
        'user_id' => $this->member->id,
        'kind' => 'reflexion',
        'body' => 'Otra publicación',
    ]);
    $thirdReporter = User::factory()->create(['email' => 'tercer-reportante-ban-qa@example.test']);
    $third = Report::query()->create([
        'reporter_id' => $thirdReporter->id,
        'post_id' => $thirdPost->id,
        'reason' => 'Queja sin fundamento',
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/reportes/{$third->id}/resolver", ['action' => 'dismiss'])
        ->assertStatus(303);

    expect($third->refresh()->status)->toBe('dismissed')
        ->and(Post::find($thirdPost->id))->not->toBeNull();
});

test('only an admin can resolve reports and nobody can ban themselves while reviewing', function () {
    $post = Post::query()->create([
        'user_id' => $this->admin->id,
        'kind' => 'reflexion',
        'body' => 'Publicación del admin',
    ]);
    $report = Report::query()->create([
        'reporter_id' => $this->member->id,
        'post_id' => $post->id,
        'reason' => 'Revisá esto',
    ]);

    $this->actingAs($this->member)
        ->post("/admin/reportes/{$report->id}/resolver", ['action' => 'dismiss'])
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->post("/admin/reportes/{$report->id}/resolver", ['action' => 'ban', 'minutes' => 30])
        ->assertStatus(422);

    expect($this->admin->refresh()->isBanned())->toBeFalse()
        ->and($report->refresh()->status)->toBe('pending');
});

test('the debate seeder creates the fight and a mutual report', function () {
    (new DebateBanSeeder)->run();

    $maria = User::where('email', DebateBanSeeder::MARIA_EMAIL)->firstOrFail();
    $jose = User::where('email', DebateBanSeeder::JOSE_EMAIL)->firstOrFail();

    expect($maria->name)->toBe('María Conchita')
        ->and($jose->name)->toBe('José López')
        ->and(Post::whereIn('user_id', [$maria->id, $jose->id])->count())->toBe(5)
        ->and(Report::whereIn('reporter_id', [$maria->id, $jose->id])->count())->toBe(2);

    $post = Post::where('user_id', $jose->id)->whereNotNull('parent_id')->firstOrFail();
    expect($post->body)->toContain('ignorancia');

    $this->actingAs($this->member)->get("/posts/{$post->id}")->assertOk();

    $this->actingAs($this->admin)
        ->delete("/admin/posts/{$post->id}")
        ->assertStatus(303);

    expect(Post::find($post->id))->toBeNull();
});

test('ban state cannot be assigned through mass assignment', function () {
    $user = User::create([
        'name' => 'Sin ban QA',
        'email' => 'sin-ban-qa@example.test',
        'password' => 'password',
        'banned_until' => now()->addDay(),
    ]);

    expect($user->isBanned())->toBeFalse();
});

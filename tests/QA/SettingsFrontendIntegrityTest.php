<?php

use App\Http\Middleware\HandleAppearance;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Ana QA',
        'slug' => 'ana-qa',
        'email' => 'ana@example.com',
    ]);
});

test('guests are sent to login from every settings page', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->get(route('appearance.edit'))->assertRedirect(route('login'));
    $this->get(route('security.edit'))->assertRedirect(route('login'));
});

test('the settings root redirects authenticated users to the profile page', function () {
    $this->actingAs($this->user)->get('/settings')->assertRedirect(route('profile.edit'));
});

test('profile and appearance pages render the expected inertia components', function () {
    $this->actingAs($this->user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Profile')
            ->where('mustVerifyEmail', true));

    $this->actingAs($this->user)
        ->get(route('appearance.edit'))
        ->assertInertia(fn (Assert $page) => $page->component('settings/Appearance'));
});

test('unverified users cannot open verified settings or delete their account', function () {
    $unverified = User::factory()->unverified()->create(['slug' => 'sin-verificar-qa']);

    $this->actingAs($unverified)
        ->get(route('appearance.edit'))
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($unverified)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('verification.notice'));

    expect($unverified->fresh())->not->toBeNull();
});

test('profile update rejects an email already used by another account', function () {
    $other = User::factory()->create(['email' => 'otra@example.com']);

    $this->actingAs($this->user)
        ->patch(route('profile.update'), [
            'name' => 'Ana QA',
            'email' => $other->email,
        ])
        ->assertSessionHasErrors('email');

    expect($this->user->refresh()->email)->toBe('ana@example.com');
});

test('password updates stop after six attempts in one minute', function () {
    foreach (range(1, 6) as $attempt) {
        $this->actingAs($this->user)->putJson(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-'.$attempt,
            'password_confirmation' => 'new-password-'.$attempt,
        ])->assertStatus(422);
    }

    $this->actingAs($this->user)->putJson(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'new-password-final',
        'password_confirmation' => 'new-password-final',
    ])->assertStatus(429);

    expect(Hash::check('password', $this->user->refresh()->password))->toBeTrue();
});

test('a weak or unconfirmed new password is rejected', function () {
    $this->actingAs($this->user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'abc',
            'password_confirmation' => 'abc',
        ])
        ->assertSessionHasErrors('password');

    $this->actingAs($this->user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-qa',
            'password_confirmation' => 'distinta',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('password', $this->user->refresh()->password))->toBeTrue();
});

test('the well known passkey endpoint publishes the enrollment URL', function () {
    $this->getJson('/.well-known/passkey-endpoints')
        ->assertOk()
        ->assertExactJson([
            'enroll' => route('security.edit'),
            'manage' => route('security.edit'),
        ]);
});

test('security props expose the passkey authenticator derived from its credential', function () {
    DB::table('passkeys')->insert([
        'user_id' => $this->user->id,
        'name' => 'Llave QA',
        'credential_id' => 'qa-credential-id',
        'credential' => json_encode([
            'aaguid' => '08987058-cadc-4b81-b6e1-30de50dcbe96',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Security')
            ->where('passkeys.0.name', 'Llave QA')
            ->where('passkeys.0.authenticator', 'Windows Hello'));
});

test('the appearance middleware prefers the theme cookie and falls back to getsemani', function () {
    $middleware = new HandleAppearance;

    $cases = [
        [['theme' => 'olivo', 'appearance' => 'patmos'], 'olivo'],
        [['appearance' => 'patmos'], 'patmos'],
        [[], 'getsemani'],
    ];

    foreach ($cases as [$cookies, $expected]) {
        $middleware->handle(Request::create('/', 'GET', [], $cookies), fn () => new Response);
        expect(View::shared('appearance'))->toBe($expected);
    }
});

test('integrity indexes for cross references and rebibles are present', function () {
    $referenceIndexes = collect(Schema::getIndexes('bible_cross_references'))
        ->pluck('name')
        ->all();
    $postIndexes = collect(Schema::getIndexes('posts'))
        ->pluck('name')
        ->all();

    expect($referenceIndexes)
        ->toContain('bible_cross_refs_to_reference_index')
        ->toContain('bible_cross_refs_unique_reference')
        ->and($postIndexes)->toContain('posts_user_rebible_unique');
});

test('duplicate cross references are rejected by the unique index', function () {
    $row = [
        'from_book' => 'GEN',
        'from_chapter' => 1,
        'from_verse' => 1,
        'to_book' => 'JHN',
        'to_chapter' => 3,
        'to_verse' => 16,
        'score' => 10,
    ];

    DB::table('bible_cross_references')->insert($row);

    expect(fn () => DB::table('bible_cross_references')->insert($row))
        ->toThrow(QueryException::class);

    expect(DB::table('bible_cross_references')->count())->toBe(1);
});

test('a user cannot rebible the same post twice', function () {
    $original = Post::query()->create([
        'user_id' => $this->user->id,
        'kind' => 'reflexion',
        'body' => 'Publicación original.',
    ]);

    $rebible = [
        'user_id' => $this->user->id,
        'rebible_of_id' => $original->id,
        'kind' => 'rebibleada',
        'body' => 'Rebibleo.',
    ];

    Post::query()->create($rebible);

    expect(fn () => Post::query()->create($rebible))->toThrow(QueryException::class);
    expect(Post::query()->count())->toBe(2);
});

test('deleting a user removes their passkeys by cascade', function () {
    $userId = $this->user->id;

    DB::table('passkeys')->insert([
        'user_id' => $userId,
        'name' => 'Llave a borrar',
        'credential_id' => 'qa-credential-cascade',
        'credential' => json_encode(['aaguid' => '00000000-0000-0000-0000-000000000000']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->user->delete();

    expect(DB::table('passkeys')->where('user_id', $userId)->count())->toBe(0);
});

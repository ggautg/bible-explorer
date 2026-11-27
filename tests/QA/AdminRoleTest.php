<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'Admin QA',
        'email' => 'admin-qa@example.test',
        'slug' => 'admin-qa',
        'is_admin' => true,
    ]);
    $this->member = User::factory()->create([
        'name' => 'Miembro QA',
        'email' => 'miembro-qa@example.test',
        'slug' => 'miembro-qa',
    ]);
    $this->post = Post::query()->create([
        'user_id' => $this->member->id,
        'kind' => 'reflexion',
        'body' => 'Publicación del miembro',
    ]);
});

test('the admin panel is restricted to admins', function () {
    $this->get('/admin')->assertRedirect('/login');
    $this->actingAs($this->member)->get('/admin')->assertForbidden();

    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.users', 2)
            ->where('stats.admins', 1)
            ->where('users', fn ($users) => count($users) === 2));
});

test('an admin can promote and demote other users but not demote themselves', function () {
    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->member->id}/rol")
        ->assertStatus(303);

    expect($this->member->refresh()->isAdmin())->toBeTrue();

    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->member->id}/rol")
        ->assertStatus(303);

    expect($this->member->refresh()->isAdmin())->toBeFalse();

    $this->actingAs($this->admin)
        ->post("/admin/usuarios/{$this->admin->id}/rol")
        ->assertStatus(422);

    expect($this->admin->refresh()->isAdmin())->toBeTrue();
});

test('an admin can delete somebody elses post', function () {
    $this->actingAs($this->admin)
        ->delete("/admin/posts/{$this->post->id}")
        ->assertStatus(303);

    expect(Post::find($this->post->id))->toBeNull();
});

test('a member cannot use admin superpowers', function () {
    $this->actingAs($this->member)
        ->post("/admin/usuarios/{$this->admin->id}/rol")
        ->assertForbidden();

    $this->actingAs($this->member)
        ->delete("/admin/posts/{$this->post->id}")
        ->assertForbidden();

    expect($this->admin->refresh()->isAdmin())->toBeTrue()
        ->and(Post::find($this->post->id))->not->toBeNull();
});

test('the role cannot be granted through a profile update', function () {
    $this->actingAs($this->member)->patch('/settings/profile', [
        'name' => 'Miembro QA',
        'email' => $this->member->email,
        'is_admin' => true,
        'role' => 'admin',
    ])->assertRedirect('/settings/profile');

    expect($this->member->refresh()->isAdmin())->toBeFalse()
        ->and($this->member->getAttributes())->not->toHaveKey('role');
});

test('the admin flag is shared with the frontend', function () {
    $this->actingAs($this->admin)->get('/comunidad')
        ->assertInertia(fn ($page) => $page->where('auth.user.is_admin', true));

    $this->actingAs($this->member)->get('/comunidad')
        ->assertInertia(fn ($page) => $page->where('auth.user.is_admin', false));
});

test('admins bypass write throttles while members keep their limits', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.99.0.1']);

    foreach (range(1, 20) as $index) {
        $this->actingAs($this->admin)->post('/posts', [
            'body' => "Post de admin {$index}",
        ])->assertRedirect();
    }

    expect(Post::query()->where('user_id', $this->admin->id)->count())->toBe(20);

    $this->withServerVariables(['REMOTE_ADDR' => '10.99.0.2']);

    foreach (range(1, 15) as $index) {
        $this->actingAs($this->member)->post('/posts', [
            'body' => "Post de miembro {$index}",
        ])->assertRedirect();
    }

    $this->actingAs($this->member)->post('/posts', ['body' => 'Fuera de límite'])
        ->assertStatus(429);
});

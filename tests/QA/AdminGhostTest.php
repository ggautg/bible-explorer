<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    $this->ghost = User::factory()->create([
        'name' => 'Admin fantasma',
        'email' => 'fantasma-qa@example.test',
        'slug' => 'fantasma-qa',
        'is_admin' => true,
    ]);
    $this->member = User::factory()->create([
        'name' => 'Miembro QA',
        'email' => 'miembro-fantasma-qa@example.test',
        'slug' => 'miembro-fantasma-qa',
    ]);
    $this->ghostPost = Post::query()->create([
        'user_id' => $this->ghost->id,
        'kind' => 'reflexion',
        'body' => 'Mensaje del fantasma',
    ]);
    $this->memberPost = Post::query()->create([
        'user_id' => $this->member->id,
        'kind' => 'reflexion',
        'body' => 'Mensaje visible',
    ]);
});

test('the ghost is invisible in the member feed but the admin sees everything', function () {
    $this->actingAs($this->member)->get('/comunidad')
        ->assertInertia(fn ($page) => $page
            ->where('posts', fn ($posts) => count($posts) === 1 && $posts[0]['body'] === 'Mensaje visible')
            ->where('people', []));

    $this->actingAs($this->ghost)->get('/comunidad')
        ->assertInertia(fn ($page) => $page
            ->where('posts', fn ($posts) => count($posts) === 2)
            ->where('people', fn ($people) => count($people) === 1 && $people[0]['id'] === $this->member->id));
});

test('members cannot open or interact with ghost content', function () {
    $this->actingAs($this->member)->get("/perfil/{$this->ghost->slug}")->assertNotFound();
    $this->actingAs($this->member)->get("/posts/{$this->ghostPost->id}")->assertNotFound();
    $this->actingAs($this->member)->post("/posts/{$this->ghostPost->id}/like")->assertNotFound();
    $this->actingAs($this->member)->post("/posts/{$this->ghostPost->id}/reply", ['body' => 'Respuesta'])->assertNotFound();

    $this->actingAs($this->ghost)->get("/perfil/{$this->ghost->slug}")->assertOk();
    $this->actingAs($this->ghost)->get("/posts/{$this->ghostPost->id}")->assertOk();
});

test('nobody follows the ghost and the ghost follows nobody', function () {
    expect($this->member->canFollow($this->ghost))->toBeFalse()
        ->and($this->ghost->canFollow($this->member))->toBeFalse();

    $this->actingAs($this->member)->post("/usuarios/{$this->ghost->slug}/seguir")->assertRedirect();
    $this->actingAs($this->ghost)->post("/usuarios/{$this->member->slug}/seguir")->assertRedirect();

    expect(DB::table('follows')->count())->toBe(0)
        ->and($this->member->refresh()->following()->count())->toBe(0)
        ->and($this->member->refresh()->followers()->count())->toBe(0)
        ->and($this->ghost->refresh()->following()->count())->toBe(0)
        ->and($this->ghost->refresh()->followers()->count())->toBe(0);
});

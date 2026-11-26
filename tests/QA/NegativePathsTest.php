<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    BibleBook::query()->create(['code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1]);
    BibleVerse::query()->create([
        'book_code' => 'GEN',
        'translation' => 'spa_rvg',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'En el principio creó Dios los cielos y la tierra.',
    ]);

    $this->viewer = User::factory()->create([
        'name' => 'Lector Negativo',
        'email' => 'lector-negativo@example.com',
        'slug' => 'lector-negativo',
    ]);
    $this->author = User::factory()->create([
        'name' => 'Autor Negativo',
        'email' => 'autor-negativo@example.com',
        'slug' => 'autor-negativo',
    ]);
});

function qaNegativeReference(): array
{
    return ['book_code' => 'GEN', 'chapter' => 1, 'verse' => 1];
}

function qaNegativePost(int $userId, ?int $parentId = null): Post
{
    return Post::query()->create([
        'user_id' => $userId,
        'parent_id' => $parentId,
        'body' => 'Publicación de prueba',
    ]);
}

test('underscore and backslash are literal in book suggestions', function () {
    $this->actingAs($this->viewer)
        ->getJson('/api/bible/books-search?q='.rawurlencode('_'))->assertOk()->assertExactJson([]);
    $this->actingAs($this->viewer)
        ->getJson('/api/bible/books-search?q='.rawurlencode('\\'))->assertOk()->assertExactJson([]);
});

test('underscore is literal in the sqlite text search fallback', function () {
    $this->actingAs($this->viewer)->get('/buscar?q=D_o')
        ->assertInertia(fn ($page) => $page->component('Bible/Search')->where('results', []));
});

test('zero chapter and verse are not found in free text references', function () {
    $this->actingAs($this->viewer)
        ->getJson('/api/bible/resolve?ref='.rawurlencode('Génesis 0:1'))->assertNotFound();
    $this->actingAs($this->viewer)
        ->getJson('/api/bible/resolve?ref='.rawurlencode('Génesis 1:0'))->assertNotFound();
});

test('wildcards are rejected in free text references', function () {
    foreach (['Génesis 1:%', 'Génesis 1:_'] as $reference) {
        $this->actingAs($this->viewer)
            ->getJson('/api/bible/resolve?ref='.rawurlencode($reference))
            ->assertStatus(422)->assertJsonPath('error', 'Formato no válido. Usa algo como "Juan 3:16".');
    }
});

test('a valid book with an empty chapter returns an empty verse list', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/structure?book=GEN&chapter=99')
        ->assertOk()
        ->assertJsonPath('chapters.0', 1)
        ->assertJsonPath('verses', []);
});

test('verses only present in the alternate translation are not readable or shareable', function () {
    BibleBook::query()->create(['code' => 'PDT', 'name' => 'Alterna', 'testament' => 'NT', 'order' => 2]);
    BibleVerse::query()->create([
        'book_code' => 'PDT',
        'translation' => 'spa_pdt',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'Sólo en la alterna.',
    ]);

    $this->actingAs($this->viewer)->get('/leer/PDT/1')->assertNotFound();
    $this->get('/compartido/PDT/1/1')->assertNotFound();
});

test('rebibbling a rebible points to the original post', function () {
    $original = qaNegativePost($this->author->id);
    $third = User::factory()->create(['email' => 'tercero-negativo@example.com']);

    $this->actingAs($this->viewer)->post(route('posts.rebible', $original));
    $firstRebible = Post::where('user_id', $this->viewer->id)->firstOrFail();

    $this->actingAs($third)->post(route('posts.rebible', $firstRebible));

    expect(Post::where('user_id', $third->id)->firstOrFail()->rebible_of_id)->toBe($original->id);
});

test('a rebible cannot be rebibled by its own author', function () {
    $original = qaNegativePost($this->author->id);

    $this->actingAs($this->viewer)->post(route('posts.rebible', $original));
    $this->actingAs($this->viewer)->post(route('posts.rebible', Post::where('user_id', $this->viewer->id)->firstOrFail()));

    expect(Post::where('rebible_of_id', $original->id)->count())->toBe(1);
});

test('unauthenticated deletes are rejected before validation', function () {
    $this->deleteJson('/api/notes', qaNegativeReference())->assertStatus(401);
    $this->deleteJson('/api/favorites', qaNegativeReference())->assertStatus(401);
});

test('deleting a profile without a password is rejected', function () {
    $this->actingAs($this->viewer)->delete(route('profile.destroy'))
        ->assertSessionHasErrors('password');

    expect(User::find($this->viewer->id))->not->toBeNull();
});

test('a password update without confirmation is rejected', function () {
    $this->actingAs($this->viewer)->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'nueva-clave-qa',
    ])->assertSessionHasErrors('password');

    expect(Hash::check('password', $this->viewer->refresh()->password))->toBeTrue();
});

test('an unverified user cannot update the password', function () {
    $unverified = User::factory()->unverified()->create(['email' => 'sin-verificar-negativo@example.com']);

    $this->actingAs($unverified)->put(route('user-password.update'), [
        'current_password' => 'password',
        'password' => 'nueva-clave-qa',
        'password_confirmation' => 'nueva-clave-qa',
    ])->assertRedirect(route('verification.notice'));

    expect(Hash::check('password', $unverified->refresh()->password))->toBeTrue();
});

test('social writes stop after fifteen requests in one minute', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.20.0.1']);
    $replyUser = User::factory()->create(['email' => 'respuestas-negativo@example.com']);
    $followUser = User::factory()->create(['email' => 'seguir-negativo@example.com']);
    $rebibleUser = User::factory()->create(['email' => 'rebible-negativo@example.com']);

    foreach (range(1, 15) as $index) {
        $this->actingAs($this->viewer)->post(route('posts.store'), [
            'body' => "Publicación {$index}",
        ])->assertStatus(302);
    }
    $this->actingAs($this->viewer)->post(route('posts.store'), ['body' => 'Fuera de límite'])
        ->assertStatus(429);

    $post = qaNegativePost($this->author->id);
    foreach (range(1, 15) as $index) {
        $this->actingAs($replyUser)->post(route('posts.reply', $post), [
            'body' => "Respuesta {$index}",
        ])->assertStatus(302);
    }
    $this->actingAs($replyUser)->post(route('posts.reply', $post), ['body' => 'Fuera de límite'])
        ->assertStatus(429);

    foreach (range(1, 15) as $index) {
        $this->actingAs($followUser)->post(route('users.follow.toggle', $this->author))
            ->assertStatus(302);
    }
    $this->actingAs($followUser)->post(route('users.follow.toggle', $this->author))
        ->assertStatus(429);

    $rebibleTarget = qaNegativePost($this->author->id);
    foreach (range(1, 15) as $index) {
        $this->actingAs($rebibleUser)->post(route('posts.rebible', $rebibleTarget))
            ->assertStatus(302);
    }
    $this->actingAs($rebibleUser)->post(route('posts.rebible', $rebibleTarget))
        ->assertStatus(429);
});

test('note and favorite writes stop after thirty requests in one minute', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '10.30.0.1']);
    $favoriteUser = User::factory()->create(['email' => 'favoritos-negativo@example.com']);
    $deleteNoteUser = User::factory()->create(['email' => 'borrar-nota-negativo@example.com']);
    $deleteFavoriteUser = User::factory()->create(['email' => 'borrar-favorito-negativo@example.com']);

    foreach (range(1, 30) as $index) {
        $this->actingAs($this->viewer)->putJson('/api/notes', [
            ...qaNegativeReference(),
            'body' => "Nota {$index}",
        ])->assertOk();
    }
    $this->actingAs($this->viewer)->putJson('/api/notes', [
        ...qaNegativeReference(),
        'body' => 'Fuera de límite',
    ])->assertStatus(429);

    foreach (range(1, 30) as $index) {
        $this->actingAs($favoriteUser)->postJson('/api/favorites', qaNegativeReference())->assertOk();
    }
    $this->actingAs($favoriteUser)->postJson('/api/favorites', qaNegativeReference())
        ->assertStatus(429);

    foreach (range(1, 30) as $index) {
        $this->actingAs($deleteNoteUser)->deleteJson('/api/notes', qaNegativeReference())->assertOk();
    }
    $this->actingAs($deleteNoteUser)->deleteJson('/api/notes', qaNegativeReference())
        ->assertStatus(429);

    foreach (range(1, 30) as $index) {
        $this->actingAs($deleteFavoriteUser)->deleteJson('/api/favorites', qaNegativeReference())->assertOk();
    }
    $this->actingAs($deleteFavoriteUser)->deleteJson('/api/favorites', qaNegativeReference())
        ->assertStatus(429);
});

<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use App\Services\BibleGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    qaLimitBook('GEN', 'Génesis');

    $this->viewer = User::factory()->create([
        'name' => 'Lector Límites',
        'slug' => 'lector-limites',
        'email' => 'lector-limites@example.com',
    ]);
    $this->author = User::factory()->create([
        'name' => 'Autor Límites',
        'slug' => 'autor-limites',
        'email' => 'autor-limites@example.com',
    ]);
});

function qaLimitBook(string $code, string $name): void
{
    BibleBook::query()->create([
        'code' => $code,
        'name' => $name,
        'testament' => 'OT',
        'order' => 1,
    ]);
}

function qaLimitVerse(string $book, int $chapter, int $verse, string $text, string $translation = 'spa_rvg'): void
{
    BibleVerse::query()->create([
        'book_code' => $book,
        'translation' => $translation,
        'chapter' => $chapter,
        'verse' => $verse,
        'text' => $text,
    ]);
}

function qaLimitNotePayload(array $overrides = []): array
{
    return array_merge([
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'body' => 'Nota de prueba',
    ], $overrides);
}

function qaLimitPost(): Post
{
    return Post::query()->create([
        'user_id' => qaLimitAuthorId(),
        'body' => 'Publicación de prueba',
    ]);
}

function qaLimitAuthorId(): int
{
    return (int) User::where('email', 'autor-limites@example.com')->value('id');
}

function qaLimitSeedPosts(int $amount): void
{
    foreach (range(1, $amount) as $index) {
        Post::query()->create([
            'user_id' => qaLimitAuthorId(),
            'body' => "Publicación {$index}",
        ]);
    }
}

test('a note body longer than five thousand characters is rejected', function () {
    qaLimitVerse('GEN', 1, 1, 'Dios');

    $this->actingAs($this->viewer)->putJson('/api/notes', qaLimitNotePayload([
        'body' => str_repeat('á', 5001),
    ]))->assertStatus(422)->assertJsonValidationErrors('body');

    expect($this->viewer->notes()->count())->toBe(0);
});

test('posts and replies longer than two thousand characters are rejected', function () {
    $this->actingAs($this->viewer)->post(route('posts.store'), [
        'body' => str_repeat('á', 2001),
    ])->assertSessionHasErrors('body');

    $post = qaLimitPost();
    $this->actingAs($this->viewer)->post(route('posts.reply', $post), [
        'body' => str_repeat('á', 2001),
    ])->assertSessionHasErrors('body');

    expect(Post::count())->toBe(1);
});

test('an eleven character book code is rejected in every write path', function () {
    qaLimitVerse('GEN', 1, 1, 'Dios');
    $longCode = str_repeat('X', 11);

    $this->actingAs($this->viewer)->putJson('/api/notes', qaLimitNotePayload([
        'book_code' => $longCode,
    ]))->assertStatus(422)->assertJsonValidationErrors('book_code');
    $this->actingAs($this->viewer)->postJson('/api/favorites', [
        'book_code' => $longCode,
        'chapter' => 1,
        'verse' => 1,
    ])->assertStatus(422)->assertJsonValidationErrors('book_code');
    $this->actingAs($this->viewer)->post(route('posts.store'), [
        'body' => 'Texto',
        'book_code' => $longCode,
        'chapter' => 1,
        'verse' => 1,
    ])->assertSessionHasErrors('book_code');
    $this->actingAs($this->viewer)->post(route('posts.reply', qaLimitPost()), [
        'body' => 'Texto',
        'book_code' => $longCode,
        'chapter' => 1,
        'verse' => 1,
    ])->assertSessionHasErrors('book_code');
});

test('zero chapter and verse are rejected when writing notes, favorites, posts and replies', function () {
    qaLimitVerse('GEN', 1, 1, 'Dios');

    foreach (['chapter', 'verse'] as $field) {
        $this->actingAs($this->viewer)->putJson('/api/notes', qaLimitNotePayload([
            $field => 0,
        ]))->assertStatus(422)->assertJsonValidationErrors($field);
        $this->actingAs($this->viewer)->postJson('/api/favorites', [
            'book_code' => 'GEN',
            'chapter' => $field === 'chapter' ? 0 : 1,
            'verse' => $field === 'verse' ? 0 : 1,
        ])->assertStatus(422)->assertJsonValidationErrors($field);
        $this->actingAs($this->viewer)->post(route('posts.store'), [
            'body' => 'Texto',
            'book_code' => 'GEN',
            'chapter' => $field === 'chapter' ? 0 : 1,
            'verse' => $field === 'verse' ? 0 : 1,
        ])->assertSessionHasErrors($field);
        $this->actingAs($this->viewer)->post(route('posts.reply', qaLimitPost()), [
            'body' => 'Texto',
            'book_code' => 'GEN',
            'chapter' => $field === 'chapter' ? 0 : 1,
            'verse' => $field === 'verse' ? 0 : 1,
        ])->assertSessionHasErrors($field);
    }
});

test('text search returns at most fifty matches', function () {
    foreach (range(1, 55) as $number) {
        qaLimitVerse('GEN', 3, $number, "Dios {$number}");
    }

    $this->actingAs($this->viewer)->get(route('bible.search', ['q' => 'Dios']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Search')
            ->where('results', fn (Collection $results) => $results->count() === 50));
});

test('the feed shows at most sixty posts', function () {
    qaLimitSeedPosts(61);

    $this->actingAs($this->viewer)->get(route('community.feed'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Community/Feed')
            ->where('posts', fn (Collection $posts) => $posts->count() === 60));
});

test('the feed suggests at most twenty people', function () {
    foreach (range(1, 21) as $index) {
        User::factory()->create([
            'name' => "Sugerido {$index}",
            'email' => "sugerido-{$index}@example.com",
        ]);
    }

    $this->actingAs($this->viewer)->get(route('community.feed'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('people', fn (Collection $people) => $people->count() === 20));
});

test('a thread serializes at most twenty levels of replies', function () {
    $root = qaLimitPost();
    $parent = $root;

    foreach (range(1, 22) as $level) {
        $parent = Post::query()->create([
            'user_id' => qaLimitAuthorId(),
            'parent_id' => $parent->id,
            'body' => "Nivel {$level}",
        ]);
    }

    $this->actingAs($this->viewer)->get(route('posts.show', $root))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Show')
            ->where('replies', fn (Collection $replies) => $replies->count() === 20));
});

test('a profile timeline shows at most sixty posts', function () {
    qaLimitSeedPosts(61);

    $this->actingAs($this->viewer)->get(route('profile.show', $this->author->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/Show')
            ->where('posts', fn (Collection $posts) => $posts->count() === 60));
});

test('profile names and emails respect their maximum length and format', function () {
    $this->actingAs($this->viewer)->patch(route('profile.update'), [
        'name' => str_repeat('a', 256),
        'email' => 'nombre-largo@example.com',
    ])->assertSessionHasErrors('name');

    $this->actingAs($this->viewer)->patch(route('profile.update'), [
        'name' => 'Nombre válido',
        'email' => str_repeat('a', 250).'@example.com',
    ])->assertSessionHasErrors('email');

    $this->actingAs($this->viewer)->patch(route('profile.update'), [
        'name' => 'Nombre válido',
        'email' => 'no-es-email',
    ])->assertSessionHasErrors('email');
});

test('the graph caps incoming connections at twenty five', function () {
    qaLimitVerse('GEN', 1, 1, 'Centro');

    foreach (range(1, 30) as $number) {
        qaLimitVerse('GEN', 2, $number, "Origen {$number}");
        DB::table('bible_cross_references')->insert([
            'from_book' => 'GEN',
            'from_chapter' => 2,
            'from_verse' => $number,
            'to_book' => 'GEN',
            'to_chapter' => 1,
            'to_verse' => 1,
        ]);
    }

    $graph = app(BibleGraphService::class)->buildGraph('GEN', 1, 1);

    expect($graph['edges'])->toHaveCount(25);
});

test('invalid tabs fall back silently in the feed and the profile', function () {
    $this->actingAs($this->viewer)->get('/comunidad?tab=basura')
        ->assertInertia(fn (Assert $page) => $page->where('tab', 'para_ti'));

    $this->actingAs($this->viewer)->get(route('profile.show', $this->author->slug).'?tab=basura')
        ->assertInertia(fn (Assert $page) => $page->where('tab', 'posts'));
});

test('the structure API validates a missing book and a non numeric chapter', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/structure')
        ->assertStatus(422)->assertJsonValidationErrors('book');

    $this->actingAs($this->viewer)->getJson('/api/bible/structure?book=GEN&chapter=abc')
        ->assertStatus(422)->assertJsonValidationErrors('chapter');
});

test('verses from another translation or with empty text are not usable references', function () {
    qaLimitBook('PDT', 'Traducción alterna');
    qaLimitVerse('PDT', 1, 1, 'Sólo en la alterna', 'spa_pdt');

    $this->actingAs($this->viewer)->putJson('/api/notes', qaLimitNotePayload([
        'book_code' => 'PDT',
    ]))->assertStatus(422);
    $this->actingAs($this->viewer)->postJson('/api/favorites', [
        'book_code' => 'PDT',
        'chapter' => 1,
        'verse' => 1,
    ])->assertStatus(422);
    $this->actingAs($this->viewer)->post(route('posts.store'), [
        'body' => 'Texto',
        'book_code' => 'PDT',
        'chapter' => 1,
        'verse' => 1,
    ])->assertSessionHasErrors('book_code');

    qaLimitBook('EMP', 'Versículo vacío');
    qaLimitVerse('EMP', 1, 1, '');

    $this->actingAs($this->viewer)->putJson('/api/notes', qaLimitNotePayload([
        'book_code' => 'EMP',
    ]))->assertStatus(422);
});

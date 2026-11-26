<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use App\Services\BibleGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    BibleBook::query()->create(['code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1]);

    $this->viewer = User::factory()->create([
        'name' => 'Lector Final',
        'email' => 'lector-final@example.com',
        'slug' => 'lector-final',
    ]);
    $this->author = User::factory()->create([
        'name' => 'Autor Final',
        'email' => 'autor-final@example.com',
        'slug' => 'autor-final',
    ]);
});

function qaBoundaryVerse(string $book, int $chapter, int $verse, string $text): void
{
    BibleVerse::query()->create([
        'book_code' => $book,
        'translation' => 'spa_rvg',
        'chapter' => $chapter,
        'verse' => $verse,
        'text' => $text,
    ]);
}

function qaBoundaryPost(int $userId, ?int $parentId = null): Post
{
    return Post::query()->create([
        'user_id' => $userId,
        'parent_id' => $parentId,
        'body' => 'Publicación de prueba',
    ]);
}

test('percent and backslash are literal in the sqlite text search', function () {
    qaBoundaryVerse('GEN', 1, 1, 'Dios 100% fiel');
    qaBoundaryVerse('GEN', 1, 2, 'Dios 1000 fiel');
    qaBoundaryVerse('GEN', 1, 3, 'Dios \\ fiel');
    qaBoundaryVerse('GEN', 1, 4, 'Dios X fiel');

    $this->actingAs($this->viewer)->get('/buscar?q='.rawurlencode('100%'))
        ->assertInertia(fn ($page) => $page
            ->component('Bible/Search')
            ->where('results', fn ($results) => count($results) === 1)
            ->where('results.0.text', 'Dios 100% fiel'));

    $this->actingAs($this->viewer)->get('/buscar?q='.rawurlencode('\\ fiel'))
        ->assertInertia(fn ($page) => $page
            ->component('Bible/Search')
            ->where('results', fn ($results) => count($results) === 1)
            ->where('results.0.text', 'Dios \\ fiel'));
});

test('partial verse references are rejected on posts and replies', function () {
    qaBoundaryVerse('GEN', 1, 1, 'Dios');
    $root = qaBoundaryPost($this->author->id);

    foreach ([
        ['book_code' => 'GEN', 'chapter' => 1],
        ['book_code' => 'GEN', 'verse' => 1],
    ] as $partial) {
        $this->actingAs($this->viewer)->post(route('posts.store'), [
            'body' => 'Texto',
            ...$partial,
        ])->assertSessionHasErrors('book_code');

        $this->actingAs($this->viewer)->post(route('posts.reply', $root), [
            'body' => 'Texto',
            ...$partial,
        ])->assertSessionHasErrors('book_code');
    }

    expect(Post::count())->toBe(1);
});

test('the graph cap keeps the strongest scored connections', function () {
    qaBoundaryVerse('GEN', 1, 1, 'Centro');
    BibleBook::query()->create(['code' => 'JHN', 'name' => 'Juan', 'testament' => 'NT', 'order' => 2]);

    foreach (range(1, 30) as $number) {
        qaBoundaryVerse('JHN', 1, $number, "Destino {$number}");
        DB::table('bible_cross_references')->insert([
            'from_book' => 'GEN',
            'from_chapter' => 1,
            'from_verse' => 1,
            'to_book' => 'JHN',
            'to_chapter' => 1,
            'to_verse' => $number,
            'score' => $number,
        ]);
    }

    $graph = app(BibleGraphService::class)->buildGraph('GEN', 1, 1);
    $scores = collect($graph['edges'])->pluck('score');

    expect($scores)->toHaveCount(25)
        ->and($scores->min())->toBe(6)
        ->and($scores->max())->toBe(30);
});

test('a missing or blank reference returns a format error', function () {
    $message = 'Formato no válido. Usa algo como "Juan 3:16".';

    $this->actingAs($this->viewer)->getJson('/api/bible/resolve')
        ->assertStatus(422)->assertJsonPath('error', $message);
    $this->actingAs($this->viewer)->getJson('/api/bible/resolve?ref='.rawurlencode('   '))
        ->assertStatus(422)->assertJsonPath('error', $message);
});

test('the welcome page degrades without corpus or book metadata', function () {
    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page->component('Welcome')->where('verseOfTheDay', null));

    DB::statement('PRAGMA defer_foreign_keys = ON');
    qaBoundaryVerse('XXX', 1, 1, 'Versículo huérfano');

    $this->get(route('home'))
        ->assertInertia(fn ($page) => $page
            ->where('verseOfTheDay.bookName', 'XXX')
            ->where('verseOfTheDay.text', 'Versículo huérfano'));

    BibleVerse::where('book_code', 'XXX')->delete();
    DB::statement('PRAGMA defer_foreign_keys = OFF');
});

test('mios and respuestas tabs keep their scopes', function () {
    $own = qaBoundaryPost($this->viewer->id);
    $foreign = qaBoundaryPost($this->author->id);
    $ownReply = qaBoundaryPost($this->viewer->id, $foreign->id);
    qaBoundaryPost($this->author->id, $foreign->id);

    $this->actingAs($this->viewer)->get('/comunidad?tab=mios')
        ->assertInertia(fn ($page) => $page
            ->where('posts', fn ($posts) => count($posts) === 1 && $posts[0]['id'] === $own->id));

    $this->actingAs($this->viewer)->get(route('profile.show', $this->author->slug).'?tab=respuestas')
        ->assertInertia(fn ($page) => $page
            ->where('posts', fn ($posts) => count($posts) === 1 && $posts[0]['id'] !== $ownReply->id)
            ->where('posts.0.author.id', $this->author->id));
});

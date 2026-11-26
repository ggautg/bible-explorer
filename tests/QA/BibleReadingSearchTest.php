<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\User;
use App\Services\BibleGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    BibleBook::query()->create(['code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1]);
    BibleBook::query()->create(['code' => 'EXO', 'name' => 'Éxodo', 'testament' => 'OT', 'order' => 2]);
    BibleBook::query()->create(['code' => 'JHN', 'name' => 'Juan', 'testament' => 'NT', 'order' => 3]);

    verse('GEN', 1, 1, 'En el principio creó Dios los cielos y la tierra.');
    verse('GEN', 1, 2, 'Y la tierra estaba desordenada y vacía.');
    verse('GEN', 2, 1, 'Así fueron acabados los cielos y la tierra.');
    verse('EXO', 1, 1, 'Estos son los nombres de los hijos de Israel.');
    verse('JHN', 3, 16, 'Porque de tal manera amó Dios al mundo.');
    verse('GEN', 1, 1, 'Al principio Dios creó el cielo y la tierra.', 'spa_pdt');

    $this->viewer = User::factory()->create(['slug' => 'lector-biblia-qa']);
});

function verse(string $book, int $chapter, int $verse, string $text, string $translation = 'spa_rvg'): void
{
    BibleVerse::query()->create([
        'book_code' => $book,
        'translation' => $translation,
        'chapter' => $chapter,
        'verse' => $verse,
        'text' => $text,
    ]);
}

function crossReference(string $fromBook, int $fromChapter, int $fromVerse, string $toBook, int $toChapter, int $toVerse, int $score = 10): void
{
    DB::table('bible_cross_references')->insert([
        'from_book' => $fromBook,
        'from_chapter' => $fromChapter,
        'from_verse' => $fromVerse,
        'to_book' => $toBook,
        'to_chapter' => $toChapter,
        'to_verse' => $toVerse,
        'score' => $score,
    ]);
}

test('books are listed in canonical order', function () {
    $this->actingAs($this->viewer)->get(route('bible.books'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Books')
            ->where('books.0.code', 'GEN')
            ->where('books.1.code', 'EXO')
            ->where('books.2.code', 'JHN')
            ->missing('books.3'));
});

test('a chapters page returns 404 for an unknown book', function () {
    $this->actingAs($this->viewer)->get(route('bible.chapters', 'XXX'))->assertNotFound();
});

test('a chapter page returns 404 when the chapter has no verses', function () {
    $this->actingAs($this->viewer)->get(route('bible.chapter', ['GEN', 99]))->assertNotFound();
});

test('a chapter page includes the alternate translation and navigation flags', function () {
    $this->actingAs($this->viewer)->get(route('bible.chapter', ['GEN', 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Chapter')
            ->where('verses.0.verse', 1)
            ->where('verses.1.verse', 2)
            ->where('alternateVerses.1', 'Al principio Dios creó el cielo y la tierra.')
            ->where('hasNext', true)
            ->where('hasPrev', false));
});

test('the last chapter has no next link', function () {
    $this->actingAs($this->viewer)->get(route('bible.chapter', ['GEN', 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('hasNext', false)
            ->where('hasPrev', true));
});

test('a blank book query returns no suggestions', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/books-search?q=%20%20')
        ->assertOk()
        ->assertExactJson([]);
});

test('book search finds partial names and caps results at eight', function () {
    foreach (range(1, 9) as $number) {
        BibleBook::query()->create([
            'code' => "PR{$number}",
            'name' => "Prueba {$number}",
            'testament' => 'OT',
            'order' => 10 + $number,
        ]);
    }

    $codes = $this->actingAs($this->viewer)->getJson('/api/bible/books-search?q=Prueba')
        ->assertOk()
        ->json('*.code');

    expect($codes)->toHaveCount(8)
        ->and($codes)->toBe(['PR1', 'PR2', 'PR3', 'PR4', 'PR5', 'PR6', 'PR7', 'PR8']);
});

test('a percent sign in the book query is treated as a literal character', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/books-search?q=%25')
        ->assertOk()
        ->assertExactJson([]);
});

test('structure validation rejects bad chapter and book values', function () {
    $this->actingAs($this->viewer)
        ->getJson('/api/bible/structure?book=GEN&chapter=0')
        ->assertStatus(422)
        ->assertJsonValidationErrors('chapter');

    $this->actingAs($this->viewer)
        ->getJson('/api/bible/structure?book='.str_repeat('X', 20))
        ->assertStatus(422)
        ->assertJsonValidationErrors('book');
});

test('references with accented book names are resolved', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/resolve?ref='.urlencode('Génesis 1:2'))
        ->assertOk()
        ->assertExactJson(['book' => 'GEN', 'chapter' => 1, 'verse' => 2]);
});

test('malformed references return 422', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/resolve?ref=Genesis%201')
        ->assertStatus(422)
        ->assertJsonPath('error', 'Formato no válido. Usa algo como "Juan 3:16".');
});

test('unknown books and verses return 404', function () {
    $this->actingAs($this->viewer)->getJson('/api/bible/resolve?ref=Marcos%201%3A1')->assertNotFound();
    $this->actingAs($this->viewer)->getJson('/api/bible/resolve?ref='.urlencode('Génesis 99:1'))->assertNotFound();
});

test('the graph shows the center and both directions', function () {
    crossReference('GEN', 1, 1, 'JHN', 3, 16, 20);
    crossReference('EXO', 1, 1, 'GEN', 1, 1, 30);

    $this->actingAs($this->viewer)->get('/bible/GEN/1/1')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Graph')
            ->where('center', 'GEN-1-1')
            ->where('nodes.0.id', 'GEN-1-1')
            ->where('nodes.0.isCenter', true)
            ->where('edges.0.source', 'GEN-1-1')
            ->where('edges.0.target', 'JHN-3-16')
            ->where('edges.1.source', 'EXO-1-1')
            ->where('edges.1.target', 'GEN-1-1'));
});

test('a graph without references still shows the center node', function () {
    $this->actingAs($this->viewer)->get('/bible/JHN/3/16')
        ->assertInertia(fn (Assert $page) => $page
            ->where('nodes.0.id', 'JHN-3-16')
            ->where('edges', []));
});

test('the graph returns 404 for an unknown center verse', function () {
    $this->actingAs($this->viewer)->get('/bible/GEN/99/99')->assertNotFound();
});

test('the graph caps connections at twenty five per direction', function () {
    foreach (range(1, 30) as $number) {
        verse('JHN', 1, $number, "Versículo {$number}");
        crossReference('GEN', 1, 1, 'JHN', 1, $number);
    }

    $graph = app(BibleGraphService::class)->buildGraph('GEN', 1, 1);

    expect($graph['edges'])->toHaveCount(25)
        ->and($graph['edges'][0]['target'])->toBe('JHN-1-1');
});

test('graph edges never point to a node that is missing', function () {
    crossReference('GEN', 1, 1, 'XXX', 9, 9);

    $graph = app(BibleGraphService::class)->buildGraph('GEN', 1, 1);
    $nodeIds = collect($graph['nodes'])->pluck('id')->all();
    $edgeEndpoints = collect($graph['edges'])
        ->flatMap(fn (array $edge) => [$edge['source'], $edge['target']])
        ->all();

    expect(array_diff($edgeEndpoints, $nodeIds))->toBe([]);
});

test('text searches shorter than three characters are skipped', function () {
    $this->actingAs($this->viewer)->get(route('bible.search', ['q' => 'ab']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Search')
            ->where('query', 'ab')
            ->where('results', []));
});

test('text search with three characters works through the sqlite fallback', function () {
    // MySQL usa MATCH ... AGAINST; SQLite usa LIKE con comodines escapados.
    $this->actingAs($this->viewer)->get(route('bible.search', ['q' => 'Dios']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('results.0.book', 'GEN')
            ->where('results.0.text', 'En el principio creó Dios los cielos y la tierra.'));
});

test('guests are redirected away from bible pages and APIs', function () {
    $this->get(route('bible.books'))->assertRedirect(route('login'));
    $this->get(route('bible.search'))->assertRedirect(route('login'));
    $this->getJson('/api/bible/structure?book=GEN')->assertUnauthorized();
    $this->getJson('/api/bible/resolve?ref=G%C3%A9nesis%201%3A1')->assertUnauthorized();
});

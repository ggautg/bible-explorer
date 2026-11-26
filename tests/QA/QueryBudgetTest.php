<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

function qaQueryBudget(callable $callback): array
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    $result = $callback();
    $count = count(DB::getQueryLog());

    DB::disableQueryLog();

    return [$result, $count];
}

function qaSeedPeople(int $amount, string $prefix): void
{
    foreach (range(1, $amount) as $index) {
        User::factory()->create([
            'name' => "{$prefix} {$index}",
            'email' => strtolower($prefix)."-{$index}@example.com",
        ]);
    }
}

function qaSeedPosts(User $author, int $amount): void
{
    foreach (range(1, $amount) as $index) {
        Post::query()->create([
            'user_id' => $author->id,
            'body' => "Publicación {$index}",
        ]);
    }
}

function qaSeedBookWithVerses(int $amount): void
{
    BibleBook::query()->create([
        'code' => 'GEN',
        'name' => 'Génesis',
        'testament' => 'OT',
        'order' => 1,
    ]);

    foreach (range(1, $amount) as $index) {
        BibleVerse::query()->create([
            'book_code' => 'GEN',
            'chapter' => 1,
            'verse' => $index,
            'text' => $index % 5 === 0 ? 'Dios dijo' : 'Texto sin coincidencia',
        ]);
    }
}

test('feed queries do not grow with the number of suggested people', function () {
    $viewer = User::factory()->create(['email' => 'lector@example.com']);
    qaSeedPeople(4, 'Persona');

    [, $few] = qaQueryBudget(fn () => $this->actingAs($viewer)->get(route('community.feed'))->assertOk());

    qaSeedPeople(20, 'Sugerido');

    [, $many] = qaQueryBudget(fn () => $this->actingAs($viewer)->get(route('community.feed'))->assertOk());

    expect($many)->toBeLessThanOrEqual($few + 2)
        ->and($few)->toBeGreaterThan(0);
});

test('the feed still flags followed people correctly after batching', function () {
    $viewer = User::factory()->create(['email' => 'lector-seguidos@example.com']);
    $followed = User::factory()->create(['name' => 'A Seguido', 'email' => 'seguido@example.com']);
    $other = User::factory()->create(['name' => 'B Otro', 'email' => 'otro@example.com']);

    DB::table('follows')->insert([
        'follower_id' => $viewer->id,
        'followed_id' => $followed->id,
    ]);

    $this->actingAs($viewer)->get(route('community.feed'))->assertInertia(fn (Assert $page) => $page
        ->component('Community/Feed')
        ->where('people', function (Collection $people) use ($followed, $other) {
            $flags = collect($people)->mapWithKeys(fn (array $person) => [$person['id'] => $person['following']]);

            return $flags->get($followed->id) === true && $flags->get($other->id) === false;
        }));
});

test('feed queries stay flat when posts grow to the display limit', function () {
    $viewer = User::factory()->create(['email' => 'lector-posts@example.com']);
    $author = User::factory()->create(['email' => 'autor-posts@example.com']);
    qaSeedPosts($author, 5);

    [, $few] = qaQueryBudget(fn () => $this->actingAs($viewer)->get(route('community.feed'))->assertOk());

    qaSeedPosts($author, 55);

    [, $many] = qaQueryBudget(fn () => $this->actingAs($viewer)->get(route('community.feed'))->assertOk());

    expect($many)->toBeLessThanOrEqual($few + 2);
});

test('notes and favorites APIs use a single query regardless of volume', function () {
    $user = User::factory()->create(['email' => 'volumen-notas@example.com']);

    foreach (range(1, 150) as $index) {
        DB::table('favorites')->insert([
            'user_id' => $user->id,
            'book_code' => 'GEN',
            'book_name' => 'Génesis',
            'chapter' => 1,
            'verse' => $index,
            'text' => 'Texto',
        ]);
        DB::table('notes')->insert([
            'user_id' => $user->id,
            'book_code' => 'GEN',
            'book_name' => 'Génesis',
            'chapter' => 1,
            'verse' => $index,
            'body' => 'Nota',
        ]);
    }

    [, $favorites] = qaQueryBudget(fn () => $this->actingAs($user)->getJson('/api/favorites')->assertOk()->assertJsonCount(150));
    [, $notes] = qaQueryBudget(fn () => $this->actingAs($user)->getJson('/api/notes')->assertOk()->assertJsonCount(150));

    expect($favorites)->toBe(1)->and($notes)->toBe(1);
});

test('the chapter page keeps its query count constant with many verses', function () {
    $user = User::factory()->create(['email' => 'capitulo@example.com']);
    qaSeedBookWithVerses(20);

    [, $few] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.chapter', ['GEN', 1]))->assertOk());

    foreach (range(21, 200) as $index) {
        BibleVerse::query()->create([
            'book_code' => 'GEN',
            'chapter' => 1,
            'verse' => $index,
            'text' => 'Texto',
        ]);
    }

    [, $many] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.chapter', ['GEN', 1]))->assertOk());

    expect($many)->toBeLessThanOrEqual($few + 1);
});

test('the graph keeps its query count constant with many cross references', function () {
    $user = User::factory()->create(['email' => 'grafo@example.com']);
    qaSeedBookWithVerses(2);

    [, $few] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.graph', ['GEN', 1, 1]))->assertOk());

    foreach (range(1, 60) as $index) {
        DB::table('bible_cross_references')->insert([
            'from_book' => 'GEN',
            'from_chapter' => 1,
            'from_verse' => 1,
            'to_book' => 'GEN',
            'to_chapter' => 1,
            'to_verse' => $index + 2,
        ]);
    }

    [, $many] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.graph', ['GEN', 1, 1]))->assertOk());

    expect($many)->toBeLessThanOrEqual($few + 1);
});

test('text search keeps its query count constant with a large corpus', function () {
    $user = User::factory()->create(['email' => 'busqueda@example.com']);
    qaSeedBookWithVerses(50);

    [, $few] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.search', ['q' => 'Dios']))->assertOk());

    foreach (range(51, 600) as $index) {
        BibleVerse::query()->create([
            'book_code' => 'GEN',
            'chapter' => 1,
            'verse' => $index,
            'text' => $index % 5 === 0 ? 'Dios dijo' : 'Texto sin coincidencia',
        ]);
    }

    [, $many] = qaQueryBudget(fn () => $this->actingAs($user)->get(route('bible.search', ['q' => 'Dios']))->assertOk());

    expect($many)->toBeLessThanOrEqual($few + 1);
});

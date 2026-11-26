<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    BibleBook::query()->create([
        'code' => 'GEN',
        'name' => 'Génesis',
        'testament' => 'OT',
        'order' => 1,
    ]);

    BibleVerse::query()->create([
        'book_code' => 'GEN',
        'translation' => 'spa_rvg',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'En el principio creó Dios los cielos y la tierra.',
    ]);

    $this->viewer = User::factory()->create(['slug' => 'notas-qa']);
    $this->other = User::factory()->create(['slug' => 'otro-qa']);
});

function notePayload(string $body = 'Mi nota personal'): array
{
    return [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'body' => $body,
    ];
}

function favoritePayload(): array
{
    return [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
    ];
}

test('notes are isolated per user', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload())->assertOk();

    $this->actingAs($this->other)->getJson('/api/notes')
        ->assertOk()
        ->assertJsonCount(0);

    $this->actingAs($this->other)->deleteJson('/api/notes', favoritePayload())->assertOk();

    expect($this->viewer->notes()->count())->toBe(1);
});

test('favorites are isolated per user', function () {
    $this->actingAs($this->viewer)->postJson('/api/favorites', favoritePayload())->assertOk();

    $this->actingAs($this->other)->getJson('/api/favorites')
        ->assertOk()
        ->assertJsonCount(0);

    $this->actingAs($this->other)->deleteJson('/api/favorites', favoritePayload())->assertOk();

    expect($this->viewer->favorites()->count())->toBe(1);
});

test('upserting a note updates the existing row instead of duplicating it', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload('Primera versión'))->assertOk();
    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload('Segunda versión'))->assertOk();

    expect($this->viewer->notes()->count())->toBe(1)
        ->and($this->viewer->notes()->first()->body)->toBe('Segunda versión');
});

test('adding the same favorite twice is idempotent', function () {
    $this->actingAs($this->viewer)->postJson('/api/favorites', favoritePayload())->assertOk();
    $this->actingAs($this->viewer)->postJson('/api/favorites', favoritePayload())->assertOk();

    expect($this->viewer->favorites()->count())->toBe(1);
});

test('a note with a blank body is rejected', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload("   \n"))
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');

    expect($this->viewer->notes()->count())->toBe(0);
});

test('a note for an unknown verse is rejected', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', array_merge(notePayload(), [
        'chapter' => 99,
    ]))->assertStatus(422);

    expect($this->viewer->notes()->count())->toBe(0);
});

test('note payloads with wrong types are rejected', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', array_merge(notePayload(), [
        'book_code' => ['GEN'],
    ]))->assertStatus(422)->assertJsonValidationErrors('book_code');

    $this->actingAs($this->viewer)->putJson('/api/notes', array_merge(notePayload(), [
        'chapter' => 'no-es-numero',
    ]))->assertStatus(422)->assertJsonValidationErrors('chapter');
});

test('deleting a note with a zero chapter is rejected', function () {
    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload())->assertOk();

    $this->actingAs($this->viewer)->deleteJson('/api/notes', array_merge(favoritePayload(), [
        'chapter' => 0,
    ]))->assertStatus(422)->assertJsonValidationErrors('chapter');

    expect($this->viewer->notes()->count())->toBe(1);
});

test('deleting a favorite with a zero verse is rejected', function () {
    $this->actingAs($this->viewer)->postJson('/api/favorites', favoritePayload())->assertOk();

    $this->actingAs($this->viewer)->deleteJson('/api/favorites', array_merge(favoritePayload(), [
        'verse' => 0,
    ]))->assertStatus(422)->assertJsonValidationErrors('verse');

    expect($this->viewer->favorites()->count())->toBe(1);
});

test('notes and favorites writes are rate limited', function () {
    $this->actingAs($this->viewer);

    for ($request = 0; $request < 30; $request++) {
        $this->putJson('/api/notes', notePayload())->assertOk();
    }

    $this->putJson('/api/notes', notePayload())->assertStatus(429);
});

test('guests receive an unauthorized response from the bible APIs', function () {
    $this->getJson('/api/notes')->assertUnauthorized();
    $this->getJson('/api/favorites')->assertUnauthorized();
    $this->putJson('/api/notes', notePayload())->assertUnauthorized();
    $this->postJson('/api/favorites', favoritePayload())->assertUnauthorized();
});

test('a shared verse page is public and shows the server text', function () {
    $this->get('/compartido/GEN/1/1')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bible/Shared')
            ->where('verse.bookName', 'Génesis')
            ->where('verse.text', 'En el principio creó Dios los cielos y la tierra.'));
});

test('a shared verse page returns 404 for unknown references', function () {
    $this->get('/compartido/GEN/99/99')->assertNotFound();
    $this->get('/compartido/XXX/1/1')->assertNotFound();
});

test('deleting one note leaves the other verses untouched', function () {
    BibleVerse::query()->create([
        'book_code' => 'GEN',
        'translation' => 'spa_rvg',
        'chapter' => 1,
        'verse' => 2,
        'text' => 'Y la tierra estaba desordenada y vacía.',
    ]);

    $this->actingAs($this->viewer)->putJson('/api/notes', notePayload())->assertOk();
    $this->actingAs($this->viewer)->putJson('/api/notes', array_merge(notePayload('Otra nota'), [
        'verse' => 2,
    ]))->assertOk();

    $this->actingAs($this->viewer)->deleteJson('/api/notes', favoritePayload())->assertOk();

    expect($this->viewer->notes()->count())->toBe(1)
        ->and($this->viewer->notes()->first()->verse)->toBe(2);
});

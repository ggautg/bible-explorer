<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->viewer = User::factory()->create();

    DB::table('bible_books')->insert([
        'code' => 'GEN',
        'name' => 'Génesis',
        'testament' => 'OT',
        'order' => 1,
    ]);

    DB::table('bible_verses')->insert([
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'translation' => 'spa_rvg',
        'text' => 'En el principio creó Dios los cielos y la tierra.',
    ]);
});

test('los favoritos derivan nombre y texto del versículo en el servidor', function () {
    $this->actingAs($this->viewer);

    $response = $this->postJson('/api/favorites', [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'book_name' => 'Nombre manipulado',
        'text' => 'Texto manipulado',
    ]);

    $response->assertOk()
        ->assertJsonPath('book_name', 'Génesis')
        ->assertJsonPath('text', 'En el principio creó Dios los cielos y la tierra.');

    $this->assertDatabaseHas('favorites', [
        'user_id' => $this->viewer->id,
        'book_name' => 'Génesis',
        'text' => 'En el principio creó Dios los cielos y la tierra.',
    ]);
});

test('las notas derivan el nombre del libro en el servidor', function () {
    $this->actingAs($this->viewer);

    $response = $this->putJson('/api/notes', [
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'book_name' => 'Nombre manipulado',
        'body' => 'Mi nota personal',
    ]);

    $response->assertOk()->assertJsonPath('book_name', 'Génesis');
    $this->assertDatabaseHas('notes', [
        'user_id' => $this->viewer->id,
        'book_name' => 'Génesis',
        'body' => 'Mi nota personal',
    ]);
});

test('el selector obtiene capítulos y versículos de un libro', function () {
    $this->actingAs($this->viewer);

    $this->getJson('/api/bible/structure?book=GEN')
        ->assertOk()
        ->assertJsonPath('chapters.0', 1);

    $this->getJson('/api/bible/structure?book=GEN&chapter=1')
        ->assertOk()
        ->assertJsonPath('verses.0', 1);

    $this->getJson('/api/bible/structure?book=XXX')->assertStatus(404);
});

test('las publicaciones derivan el nombre del libro del selector', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.store'), [
        'body' => 'Comparto el primer versículo.',
        'kind' => 'versiculo',
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'book_name' => 'Nombre manipulado',
    ])->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'user_id' => $this->viewer->id,
        'kind' => 'versiculo',
        'book_name' => 'Génesis',
    ]);
});

test('se rechazan publicaciones con una referencia incompleta', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.store'), [
        'body' => 'Referencia incompleta.',
        'kind' => 'versiculo',
        'book_code' => 'GEN',
    ])->assertSessionHasErrors('book_code');

    $this->assertDatabaseCount('posts', 0);
});

test('se rechazan referencias que no existen en la traducción actual', function () {
    $this->actingAs($this->viewer);

    $this->postJson('/api/favorites', [
        'book_code' => 'GEN',
        'chapter' => 99,
        'verse' => 99,
    ])->assertStatus(422);

    $this->assertDatabaseCount('favorites', 0);
});

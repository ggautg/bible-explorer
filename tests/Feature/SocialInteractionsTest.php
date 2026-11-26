<?php

use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->viewer = User::factory()->create(['slug' => 'lector-a']);
    $this->author = User::factory()->create(['slug' => 'autor-b']);
    $this->post = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Publicación de prueba',
    ]);
});

test('el me gusta alterna sin duplicar registros', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.like', $this->post))->assertRedirect();
    $this->assertDatabaseCount('post_likes', 1);

    $this->post(route('posts.like', $this->post))->assertRedirect();
    $this->assertDatabaseCount('post_likes', 0);
});

test('seguir alterna sin duplicar registros', function () {
    $this->actingAs($this->viewer);

    $this->post(route('users.follow.toggle', $this->author))->assertRedirect();
    $this->assertDatabaseCount('follows', 1);

    $this->post(route('users.follow.toggle', $this->author))->assertRedirect();
    $this->assertDatabaseCount('follows', 0);
});

test('el compositor guarda la opción de comunidad elegida', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.store'), [
        'body' => 'Acompañen a mi familia en oración.',
        'kind' => 'peticion_oracion',
    ])->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'user_id' => $this->viewer->id,
        'body' => 'Acompañen a mi familia en oración.',
        'kind' => 'peticion_oracion',
    ]);
});

test('el tipo de publicación se valida contra la lista de opciones', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.store'), [
        'body' => 'Publicación con tipo inválido.',
        'kind' => 'rebibleada',
    ])->assertSessionHasErrors('kind');

    $this->assertDatabaseMissing('posts', ['body' => 'Publicación con tipo inválido.']);
});

test('un rebibleo se etiqueta como rebibleada en el feed', function () {
    $this->actingAs($this->viewer);

    $this->post(route('posts.rebible', $this->post))->assertRedirect();

    $this->get(route('community.feed'))->assertInertia(fn ($page) => $page
        ->where('posts.0.kind', 'rebibleada')
        ->where('posts.0.original.id', $this->post->id));
});

test('la pestaña me gusta solo se expone al dueño del perfil', function () {
    $likedByAuthor = Post::query()->create([
        'user_id' => $this->viewer->id,
        'body' => 'Nota privada de prueba',
    ]);

    $this->actingAs($this->author);
    $this->post(route('posts.like', $likedByAuthor))->assertRedirect();

    $this->actingAs($this->viewer);
    $this->get(route('profile.show', ['user' => $this->author->slug]).'?tab=me-gusta')
        ->assertInertia(fn ($page) => $page
            ->where('tab', 'posts')
            ->where('posts.0.id', $this->post->id)
            ->missing('posts.1'));

    $this->actingAs($this->author);
    $this->get(route('profile.show', ['user' => $this->author->slug]).'?tab=me-gusta')
        ->assertInertia(fn ($page) => $page
            ->where('tab', 'me-gusta')
            ->where('posts.0.id', $likedByAuthor->id));
});

test('el feed no expone correos de otras personas', function () {
    $this->actingAs($this->viewer);

    $this->get(route('community.feed'))->assertInertia(fn ($page) => $page
        ->has('people.0')
        ->missing('people.0.email'));
});

test('las interacciones sociales tienen límite de frecuencia', function () {
    $this->actingAs($this->viewer);

    for ($i = 0; $i < 15; $i++) {
        $this->post(route('posts.like', $this->post))->assertRedirect();
    }

    $this->post(route('posts.like', $this->post))->assertStatus(429);
});

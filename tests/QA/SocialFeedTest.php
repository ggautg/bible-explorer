<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

beforeEach(function () {
    $this->viewer = User::factory()->create(['slug' => 'lector-qa']);
    $this->author = User::factory()->create(['slug' => 'autor-qa']);
});

test('a post containing only whitespace is rejected', function () {
    $this->actingAs($this->viewer)->post(route('posts.store'), [
        'body' => "   \n\t  ",
        'kind' => 'reflexion',
    ])->assertSessionHasErrors('body');

    expect(Post::count())->toBe(0);
});

test('a reply containing only whitespace is rejected', function () {
    $post = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Publicación original',
    ]);

    $this->actingAs($this->viewer)->post(route('posts.reply', $post), [
        'body' => '    ',
    ])->assertSessionHasErrors('body');

    expect(Post::where('parent_id', $post->id)->count())->toBe(0);
});

test('a post with exactly two thousand characters is accepted', function () {
    $this->actingAs($this->viewer)->post(route('posts.store'), [
        'body' => str_repeat('á', 2000),
    ])->assertRedirect();

    expect(Post::where('user_id', $this->viewer->id)->count())->toBe(1);
});

test('users cannot follow themselves', function () {
    $this->actingAs($this->viewer)
        ->post(route('users.follow.toggle', $this->viewer))
        ->assertRedirect();

    expect($this->viewer->following()->count())->toBe(0)
        ->and($this->viewer->followers()->count())->toBe(0);
});

test('users cannot rebible their own posts', function () {
    $post = Post::query()->create([
        'user_id' => $this->viewer->id,
        'body' => 'Publicación propia',
    ]);

    $this->actingAs($this->viewer)
        ->post(route('posts.rebible', $post))
        ->assertRedirect();

    expect(Post::where('rebible_of_id', $post->id)->count())->toBe(0);
});

test('a rebible toggles back to zero and never duplicates', function () {
    $post = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Publicación ajena',
    ]);

    $this->actingAs($this->viewer)->post(route('posts.rebible', $post))->assertRedirect();
    expect(Post::where('user_id', $this->viewer->id)->where('rebible_of_id', $post->id)->count())->toBe(1);

    $this->actingAs($this->viewer)->post(route('posts.rebible', $post))->assertRedirect();
    expect(Post::where('user_id', $this->viewer->id)->where('rebible_of_id', $post->id)->count())->toBe(0);
});

test('the feed only lists root posts', function () {
    $root = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Raíz',
    ]);
    Post::query()->create([
        'user_id' => $this->viewer->id,
        'parent_id' => $root->id,
        'body' => 'Respuesta',
    ]);

    $this->actingAs($this->viewer)->get(route('community.feed'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('posts.0.id', $root->id)
            ->missing('posts.1'));
});

test('the following tab only shows followed and own posts', function () {
    $stranger = User::factory()->create(['slug' => 'extraño-qa']);

    $own = Post::query()->create(['user_id' => $this->viewer->id, 'body' => 'Mío']);
    $followed = Post::query()->create(['user_id' => $this->author->id, 'body' => 'Seguido']);
    Post::query()->create(['user_id' => $stranger->id, 'body' => 'Ajeno']);

    $this->viewer->follow($this->author);

    $this->actingAs($this->viewer)->get(route('community.feed').'?tab=siguiendo')
        ->assertInertia(fn (Assert $page) => $page
            ->where('tab', 'siguiendo')
            ->where('posts.0.body', 'Seguido')
            ->where('posts.1.body', 'Mío')
            ->missing('posts.2'));

    expect($own->id)->not->toBe($followed->id);
});

test('guests are redirected away from social pages and actions', function () {
    $post = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Solo para autenticados',
    ]);

    $this->get(route('community.feed'))->assertRedirect(route('login'));
    $this->post(route('posts.store'), ['body' => 'Intento anónimo'])->assertRedirect(route('login'));
    $this->post(route('posts.like', $post))->assertRedirect(route('login'));

    expect(Post::where('body', 'Intento anónimo')->count())->toBe(0);
});

test('unicode bodies including emojis and script tags are stored verbatim', function () {
    $body = 'Gracias, Jesús 🙏 — <script>alert("x")</script> ñandú';

    $this->actingAs($this->viewer)->post(route('posts.store'), ['body' => $body])->assertRedirect();

    expect(Post::where('user_id', $this->viewer->id)->value('body'))->toBe($body);
});

test('a reply to a reply is visible in the thread', function () {
    $root = Post::query()->create([
        'user_id' => $this->author->id,
        'body' => 'Pregunta original',
    ]);
    $reply = Post::query()->create([
        'user_id' => $this->viewer->id,
        'parent_id' => $root->id,
        'body' => 'Primera respuesta',
    ]);
    $nested = Post::query()->create([
        'user_id' => $this->author->id,
        'parent_id' => $reply->id,
        'body' => 'Respuesta anidada',
    ]);

    $this->actingAs($this->viewer)->get(route('posts.show', $root))
        ->assertInertia(fn (Assert $page) => $page
            ->where('replies.0.id', $reply->id)
            ->where('replies.1.id', $nested->id));
});

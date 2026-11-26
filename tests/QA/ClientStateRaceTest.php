<?php

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    $this->viewer = User::factory()->create(['slug' => 'lector-carrera-qa']);
});

test('duplicate favorite submissions keep a single row', function () {
    $payload = ['book_code' => 'GEN', 'chapter' => 1, 'verse' => 1];

    $first = $this->actingAs($this->viewer)->postJson('/api/favorites', $payload);
    $second = $this->actingAs($this->viewer)->postJson('/api/favorites', $payload);

    expect($first->getStatusCode())->toBe(200)
        ->and($second->getStatusCode())->toBe(200)
        ->and(DB::table('favorites')->where('user_id', $this->viewer->id)->count())->toBe(1);
});

test('duplicate note submissions keep one row with the last body', function () {
    $base = ['book_code' => 'GEN', 'chapter' => 1, 'verse' => 1];

    $this->actingAs($this->viewer)->putJson('/api/notes', [...$base, 'body' => 'Primer borrador'])->assertOk();
    $this->actingAs($this->viewer)->putJson('/api/notes', [...$base, 'body' => 'Borrador final'])->assertOk();

    $notes = DB::table('notes')->where('user_id', $this->viewer->id)->get();

    expect($notes)->toHaveCount(1)
        ->and($notes->first()->body)->toBe('Borrador final');
});

test('two sequential likes and follows end in the original state', function () {
    $post = Post::query()->create(['user_id' => $this->viewer->id, 'body' => 'Publicación de prueba']);
    $other = User::factory()->create(['slug' => 'otro-lector-qa']);

    $this->actingAs($this->viewer)->post(route('posts.like', $post))->assertRedirect();
    $this->actingAs($this->viewer)->post(route('posts.like', $post))->assertRedirect();
    $this->actingAs($this->viewer)->post(route('users.follow.toggle', $other))->assertRedirect();
    $this->actingAs($this->viewer)->post(route('users.follow.toggle', $other))->assertRedirect();

    expect(DB::table('post_likes')->where('user_id', $this->viewer->id)->count())->toBe(0)
        ->and(DB::table('follows')->where('follower_id', $this->viewer->id)->count())->toBe(0);
});

test('the first inertia visit does not discard client state initialized during a full page load', function () {
    $source = (string) file_get_contents(base_path('resources/js/app.js'));

    expect($source)->toContain('currentUserId === null || nextUserId === currentUserId');
});

test('rapid favorite clicks do not duplicate entries in client state', function () {
    $source = (string) file_get_contents(base_path('resources/js/composables/useFavorites.js'));

    expect($source)->toMatch('/axios\.post\(\'\/api\/favorites\'.*?filter\(\(f\) => keyOf\(f\) !== keyOf\(payload\)\)/s');
});

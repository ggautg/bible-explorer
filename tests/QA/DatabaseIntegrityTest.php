<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

function qaDbUser(array $overrides = []): int
{
    return DB::table('users')->insertGetId(array_merge([
        'name' => 'Usuario BD',
        'email' => 'bd-'.Str::random(8).'@example.com',
        'password' => bcrypt('password'),
        'slug' => 'bd-'.Str::random(8),
    ], $overrides));
}

function qaDbPost(int $userId, array $overrides = []): int
{
    return DB::table('posts')->insertGetId(array_merge([
        'user_id' => $userId,
        'body' => 'Publicación de integridad',
    ], $overrides));
}

function qaDbComment(int $profileUserId, int $authorId, array $overrides = []): int
{
    return DB::table('profile_comments')->insertGetId(array_merge([
        'profile_user_id' => $profileUserId,
        'user_id' => $authorId,
        'body' => 'Comentario de integridad',
    ], $overrides));
}

test('all migrations roll back cleanly and can be reapplied', function () {
    $tables = fn () => collect(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"))
        ->pluck('name');

    expect($tables())->toContain('users')->toContain('bible_verses')->toContain('posts');

    expect(Artisan::call('migrate:rollback', ['--step' => 26, '--force' => true]))->toBe(0);

    expect($tables())->not->toContain('users')->not->toContain('bible_verses');

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    expect($tables())->toContain('users')->toContain('bible_verses')->toContain('posts');
});

test('user email and slug are unique at the database level', function () {
    $user = qaDbUser(['email' => 'unico@example.com', 'slug' => 'unico']);

    expect(fn () => qaDbUser(['email' => 'unico@example.com', 'slug' => 'otro']))
        ->toThrow(QueryException::class);

    expect(fn () => qaDbUser(['email' => 'otro@example.com', 'slug' => 'unico']))
        ->toThrow(QueryException::class);

    expect(DB::table('users')->count())->toBe(1)->and($user)->toBeGreaterThan(0);
});

test('favorites and notes reject a duplicate verse per user', function () {
    $user = qaDbUser();
    $favorite = [
        'user_id' => $user,
        'book_code' => 'GEN',
        'book_name' => 'Génesis',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'Texto',
    ];
    $note = [...$favorite, 'body' => 'Nota'];
    unset($note['text']);

    DB::table('favorites')->insert($favorite);
    DB::table('notes')->insert($note);

    expect(fn () => DB::table('favorites')->insert($favorite))->toThrow(QueryException::class);
    expect(fn () => DB::table('notes')->insert($note))->toThrow(QueryException::class);

    expect(DB::table('favorites')->count())->toBe(1)->and(DB::table('notes')->count())->toBe(1);
});

test('follows and post likes reject duplicate pairs', function () {
    $first = qaDbUser();
    $second = qaDbUser();
    $post = qaDbPost($first);

    DB::table('follows')->insert(['follower_id' => $first, 'followed_id' => $second]);
    DB::table('post_likes')->insert(['user_id' => $second, 'post_id' => $post]);

    expect(fn () => DB::table('follows')->insert(['follower_id' => $first, 'followed_id' => $second]))
        ->toThrow(QueryException::class);
    expect(fn () => DB::table('post_likes')->insert(['user_id' => $second, 'post_id' => $post]))
        ->toThrow(QueryException::class);

    expect(DB::table('follows')->count())->toBe(1)->and(DB::table('post_likes')->count())->toBe(1);
});

test('profile comment reactions reject a duplicate type per user and comment', function () {
    $author = qaDbUser();
    $comment = qaDbComment($author, $author);
    $reaction = ['profile_comment_id' => $comment, 'user_id' => $author, 'type' => 'like'];

    DB::table('profile_comment_reactions')->insert($reaction);

    expect(fn () => DB::table('profile_comment_reactions')->insert($reaction))
        ->toThrow(QueryException::class);

    expect(DB::table('profile_comment_reactions')->count())->toBe(1);
});

test('bible verses reject a duplicate reference within one translation', function () {
    DB::table('bible_books')->insert([
        'code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1,
    ]);
    $verse = [
        'translation' => 'spa_rvg',
        'book_code' => 'GEN',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'Texto',
    ];

    DB::table('bible_verses')->insert($verse);
    DB::table('bible_verses')->insert([...$verse, 'translation' => 'eng_kjv']);

    expect(fn () => DB::table('bible_verses')->insert($verse))->toThrow(QueryException::class);

    expect(DB::table('bible_verses')->count())->toBe(2);
});

test('cross references are unique across all six reference columns', function () {
    $reference = [
        'from_book' => 'GEN', 'from_chapter' => 1, 'from_verse' => 1,
        'to_book' => 'JHN', 'to_chapter' => 3, 'to_verse' => 16,
    ];

    DB::table('bible_cross_references')->insert($reference);

    expect(fn () => DB::table('bible_cross_references')->insert($reference))
        ->toThrow(QueryException::class);

    expect(DB::table('bible_cross_references')->count())->toBe(1);
});

test('deleting a user cascades to comments, reactions, follows and likes', function () {
    $first = qaDbUser();
    $second = qaDbUser();
    $post = qaDbPost($first);
    $comment = qaDbComment($first, $second);

    DB::table('profile_comment_reactions')->insert([
        'profile_comment_id' => $comment, 'user_id' => $second, 'type' => 'like',
    ]);
    DB::table('follows')->insert(['follower_id' => $second, 'followed_id' => $first]);
    DB::table('post_likes')->insert(['user_id' => $second, 'post_id' => $post]);

    DB::table('users')->where('id', $second)->delete();

    expect(DB::table('profile_comments')->where('user_id', $second)->count())->toBe(0)
        ->and(DB::table('profile_comment_reactions')->count())->toBe(0)
        ->and(DB::table('follows')->count())->toBe(0)
        ->and(DB::table('post_likes')->count())->toBe(0)
        ->and(DB::table('posts')->where('id', $post)->count())->toBe(1);
});

test('deleting a profile comment cascades to its replies and reactions', function () {
    $user = qaDbUser();
    $parent = qaDbComment($user, $user);
    $reply = qaDbComment($user, $user, ['parent_id' => $parent]);

    DB::table('profile_comment_reactions')->insert([
        'profile_comment_id' => $reply, 'user_id' => $user, 'type' => 'like',
    ]);

    DB::table('profile_comments')->where('id', $parent)->delete();

    expect(DB::table('profile_comments')->whereIn('id', [$parent, $reply])->count())->toBe(0)
        ->and(DB::table('profile_comment_reactions')->count())->toBe(0);
});

test('deleting a post cascades to replies, rebibles and likes', function () {
    $user = qaDbUser();
    $post = qaDbPost($user);
    $reply = qaDbPost($user, ['parent_id' => $post]);
    $rebible = qaDbPost($user, ['rebible_of_id' => $post]);

    DB::table('post_likes')->insert(['user_id' => $user, 'post_id' => $reply]);

    DB::table('posts')->where('id', $post)->delete();

    expect(DB::table('posts')->whereIn('id', [$post, $reply, $rebible])->count())->toBe(0)
        ->and(DB::table('post_likes')->count())->toBe(0);
});

test('a book with verses cannot be deleted while verses reference it', function () {
    DB::table('bible_books')->insert([
        'code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1,
    ]);
    DB::table('bible_verses')->insert([
        'book_code' => 'GEN', 'chapter' => 1, 'verse' => 1, 'text' => 'Texto',
    ]);

    expect(fn () => DB::table('bible_books')->where('code', 'GEN')->delete())
        ->toThrow(QueryException::class);

    expect(DB::table('bible_books')->count())->toBe(1)
        ->and(DB::table('bible_verses')->count())->toBe(1);
});

test('rolling back the last migration removes the post kind and reapplying restores its default', function () {
    $user = qaDbUser();
    $post = qaDbPost($user);

    expect(DB::table('posts')->where('id', $post)->value('kind'))->toBe('reflexion');

    expect(Artisan::call('migrate:rollback', ['--step' => 4, '--force' => true]))->toBe(0);

    expect(Schema::hasColumn('posts', 'kind'))->toBeFalse();

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    expect(DB::table('posts')->where('id', $post)->value('kind'))->toBe('reflexion');
});

test('rolling back the integrity migration removes the named indexes and reapplying restores them', function () {
    expect(Artisan::call('migrate:rollback', ['--step' => 5, '--force' => true]))->toBe(0);

    $postIndexes = collect(Schema::getIndexes('posts'))->pluck('name');
    $crossRefIndexes = collect(Schema::getIndexes('bible_cross_references'))->pluck('name');

    expect($postIndexes)->not->toContain('posts_user_rebible_unique')
        ->and($crossRefIndexes)->not->toContain('bible_cross_refs_unique_reference')
        ->not->toContain('bible_cross_refs_to_reference_index');

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    expect(collect(Schema::getIndexes('posts'))->pluck('name'))->toContain('posts_user_rebible_unique')
        ->and(collect(Schema::getIndexes('bible_cross_references'))->pluck('name'))
        ->toContain('bible_cross_refs_unique_reference', 'bible_cross_refs_to_reference_index');
});

test('the slug migration backfills existing users without duplicate slugs', function () {
    $first = qaDbUser(['name' => 'Juan Perez', 'slug' => null]);
    $second = qaDbUser(['name' => 'Juan Perez', 'slug' => null]);

    expect(Artisan::call('migrate:rollback', ['--step' => 12, '--force' => true]))->toBe(0);

    expect(Schema::hasColumn('users', 'slug'))->toBeFalse();

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    $slugs = DB::table('users')->whereIn('id', [$first, $second])->pluck('slug');

    expect($slugs)->toHaveCount(2)
        ->and($slugs->filter(fn ($slug) => str_starts_with($slug, 'juan-perez')))->toHaveCount(2)
        ->and($slugs->unique())->toHaveCount(2)
        ->and($slugs->contains(null))->toBeFalse();
});

test('the translation migration backfills verses created before the column existed', function () {
    expect(Artisan::call('migrate:rollback', ['--step' => 13, '--force' => true]))->toBe(0);

    expect(Schema::hasColumn('bible_verses', 'translation'))->toBeFalse();

    DB::table('bible_books')->insert([
        'code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1,
    ]);
    DB::table('bible_verses')->insert([
        'book_code' => 'GEN', 'chapter' => 1, 'verse' => 1, 'text' => 'Texto',
    ]);

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    expect(DB::table('bible_verses')->value('translation'))->toBe('spa_rvg');
});

test('legacy profile comments and reactions are migrated to posts and likes', function () {
    $user = qaDbUser();
    $other = qaDbUser();

    expect(Artisan::call('migrate:rollback', ['--step' => 7, '--force' => true]))->toBe(0);

    expect(Schema::hasTable('posts'))->toBeFalse();

    $root = qaDbComment($user, $user, ['created_at' => now(), 'updated_at' => now()]);
    $reply = qaDbComment($user, $other, [
        'parent_id' => $root,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('profile_comment_reactions')->insert([
        'profile_comment_id' => $root,
        'user_id' => $other,
        'type' => 'like',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(Artisan::call('migrate', ['--force' => true]))->toBe(0);

    expect(DB::table('posts')->count())->toBe(2)
        ->and(DB::table('posts')->whereNotNull('parent_id')->count())->toBe(1)
        ->and(DB::table('post_likes')->count())->toBe(1)
        ->and(DB::table('legacy_comment_post_map')->count())->toBe(2)
        ->and($reply)->toBeGreaterThan(0);
});

<?php

use Database\Seeders\SocialSimulationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

/**
 * Ejecuta la misma semilla que se usa para la BD persistente y verifica que
 * la red generada se pueda recorrer desde la aplicación.
 */
test('a fifty user network from different churches uses the whole social flow', function () {
    $seeder = new SocialSimulationSeeder;
    $seeder->run();

    $stats = $seeder->stats();

    $viewer = $seeder->users[7];
    $this->actingAs($viewer)->get(route('community.feed'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Community/Feed')->where('posts', fn ($posts) => count($posts) === 60));
    $this->actingAs($viewer)->get(route('profile.show', $seeder->users[3]->slug))->assertOk();
    $visiblePost = $seeder->rootPosts->first(fn ($post) => $post->user_id !== $seeder->users[0]->id);
    $this->actingAs($viewer)->get(route('posts.show', $visiblePost))->assertOk();

    expect($stats['usuarios'])->toBe(50)
        ->and($stats['iglesias'])->toBe(10)
        ->and($stats['posts_raiz'])->toBe(75)
        ->and($stats['respuestas'])->toBeGreaterThanOrEqual(80)
        ->and($stats['rebibleos'])->toBe(40)
        ->and($stats['me_gusta'])->toBeGreaterThan(50)
        ->and($stats['favoritos'])->toBe(30)
        ->and($stats['notas'])->toBe(30)
        ->and($stats['seguimientos'])->toBeGreaterThan(200)
        ->and($stats['rupturas'])->toBe(8)
        ->and($stats['reconciliaciones'])->toBe(3)
        ->and($stats['autoseguimientos'])->toBe(0)
        ->and($seeder->users[0]->following()->count())->toBe(0)
        ->and($seeder->users[0]->followers()->count())->toBe(0)
        ->and($stats['seguimientos'])->toBe(235);
});

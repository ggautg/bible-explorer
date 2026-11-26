<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

test('protected pages redirect guests to the login route', function () {
    $owner = User::factory()->create(['email' => 'dueno-rutas@example.com']);

    $protected = [
        route('community.feed'),
        route('bible.books'),
        route('bible.notes'),
        route('bible.favorites'),
        route('profile.show', $owner->slug),
        route('profile.edit'),
        route('devotional.index'),
    ];

    foreach ($protected as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});

test('settings require a verified account while the rest of the app does not', function () {
    $unverified = User::factory()->unverified()->create(['email' => 'sin-verificar-rutas@example.com']);
    $verified = User::factory()->create(['email' => 'verificado-rutas@example.com']);

    $this->actingAs($unverified)->get(route('security.edit'))
        ->assertRedirect(route('verification.notice'));
    $this->actingAs($unverified)->get(route('profile.edit'))->assertOk();
    $this->actingAs($unverified)->get(route('community.feed'))->assertOk();
    $this->actingAs($verified)->get(route('security.edit'))
        ->assertRedirect(route('password.confirm'));
    $this->actingAs($verified)->withSession(['auth.password_confirmed_at' => now()->unix()])
        ->get(route('security.edit'))->assertOk();
});

test('guest-only auth pages send authenticated users through the welcome redirect', function () {
    $user = User::factory()->create(['email' => 'autenticado-rutas@example.com']);

    foreach ([route('login'), route('register'), route('password.request')] as $url) {
        $this->actingAs($user)->get($url)->assertRedirect(route('home'));
    }

    $this->actingAs($user)->get(route('home'))->assertRedirect(route('community.feed'));
});

test('the declared throttles are present on the sensitive routes', function () {
    $expected = [
        'login.store' => 'throttle:login',
        'two-factor.login.store' => 'throttle:two-factor',
        'verification.send' => 'throttle:6,1',
        'passkey.login' => 'throttle:passkeys',
        'users.follow.toggle' => 'throttle:15,1',
        'posts.store' => 'throttle:15,1',
        'posts.reply' => 'throttle:15,1',
        'posts.like' => 'throttle:15,1',
        'posts.rebible' => 'throttle:15,1',
    ];

    foreach ($expected as $name => $middleware) {
        expect(Route::getRoutes()->getByName($name)->gatherMiddleware())->toContain($middleware);
    }
});

test('the welcome page is public and the devotional stays behind login', function () {
    $this->get(route('home'))->assertOk();
    $this->get(route('devotional.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['email' => 'devocionario@example.com']))
        ->get(route('devotional.index'))->assertOk();
});

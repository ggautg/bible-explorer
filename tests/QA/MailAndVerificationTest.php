<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

test('registration sends a verification notification to the new user', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Correo QA',
        'email' => 'correo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('community.feed', absolute: false));

    $user = User::where('email', 'correo@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('changing the email invalidates verification and sends a fresh notice', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'viejo@example.com',
        'slug' => 'correo-viejo',
    ]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'nuevo@example.com',
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();

    Notification::assertSentTo($user->fresh(), VerifyEmail::class);
});

test('the new email can be verified after a profile change', function () {
    Notification::fake();
    Event::fake();

    $user = User::factory()->create([
        'email' => 'viejo@example.com',
        'slug' => 'correo-verificable',
    ]);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => $user->name,
        'email' => 'nuevo@example.com',
    ]);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('nuevo@example.com')],
    );

    $this->actingAs($user->fresh())->get($url)
        ->assertRedirect(route('community.feed', absolute: false).'?verified=1');

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('resending the verification notice stops after six requests', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create(['slug' => 'sin-verificar-reintento']);

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($user)->post(route('verification.send'))
            ->assertRedirect('/');
    }

    $this->actingAs($user)->post(route('verification.send'))->assertTooManyRequests();

    Notification::assertSentToTimes($user, VerifyEmail::class, 6);
});

test('an expired verification link is rejected without verifying the account', function () {
    Event::fake();

    $user = User::factory()->unverified()->create(['slug' => 'enlace-vencido']);

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinute(),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($url)->assertForbidden();

    Event::assertNotDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a password reset request does not disclose whether the email exists', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'existente@example.com']);

    $known = $this->post(route('password.email'), ['email' => $user->email]);
    $unknown = $this->post(route('password.email'), ['email' => 'ausente@example.com']);

    $known->assertSessionHas('status');
    $unknown->assertSessionHas('status');
    expect($unknown->getSession()->get('status'))->toBe($known->getSession()->get('status'));

    Notification::assertSentTo($user, ResetPassword::class);
    Notification::assertCount(1);

    $this->get(route('password.request'))->assertInertia(fn (Assert $page) => $page
        ->component('auth/ForgotPassword')
        ->whereNotNull('status'));
});

test('an expired password reset token cannot change the password', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'token-vencido@example.com']);

    $this->post(route('password.email'), ['email' => $user->email]);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    DB::table('password_reset_tokens')
        ->where('email', $user->email)
        ->update(['created_at' => now()->subMinutes(70)]);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'contrasena-nueva-qa',
        'password_confirmation' => 'contrasena-nueva-qa',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('a password reset token cannot be reused after a successful reset', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'token-unico@example.com']);

    $this->post(route('password.email'), ['email' => $user->email]);

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
        $token = $notification->token;

        return true;
    });

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'contrasena-nueva-qa',
        'password_confirmation' => 'contrasena-nueva-qa',
    ])->assertRedirect(route('login'));

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'contrasena-segunda-qa',
        'password_confirmation' => 'contrasena-segunda-qa',
    ])->assertSessionHasErrors('email');

    expect(Hash::check('contrasena-nueva-qa', $user->fresh()->password))->toBeTrue();
});

test('a two factor recovery code is consumed after a successful login', function () {
    $user = User::factory()->withTwoFactor()->create(['email' => 'recuperacion@example.com']);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-1'])
        ->assertRedirect(route('community.feed', absolute: false));

    $this->assertAuthenticatedAs($user->fresh());
    expect($user->fresh()->recoveryCodes())->not->toContain('recovery-code-1');

    $this->post(route('logout'));
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-1'])
        ->assertSessionHasErrors('recovery_code');
});

test('passkey requests are throttled after ten attempts for one credential', function () {
    foreach (range(1, 10) as $attempt) {
        $response = $this->postJson(route('passkey.login'), [
            'credential' => ['id' => 'credencial-qa-estable'],
        ]);

        expect($response->status())->not->toBe(429);
    }

    $this->postJson(route('passkey.login'), [
        'credential' => ['id' => 'credencial-qa-estable'],
    ])->assertTooManyRequests();
});

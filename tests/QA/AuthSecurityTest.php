<?php

use App\Models\Favorite;
use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

function twoFactorUserWithSecret(string $secret): User
{
    return User::factory()->create([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode([])),
        'two_factor_confirmed_at' => now(),
    ]);
}

test('registered users receive a slug that matches their name', function () {
    $this->post(route('register.store'), [
        'name' => 'María José Ñandú',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('community.feed', absolute: false));

    $user = User::where('email', 'maria@example.com')->firstOrFail();

    expect($user->slug)->not->toBeNull()
        ->and($user->slug)->toBe('maria-jose-nandu');
});

test('a newly registered user can open the profile link built by the frontend', function () {
    $this->post(route('register.store'), [
        'name' => 'María José Ñandú',
        'email' => 'maria@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'maria@example.com')->firstOrFail();

    // NavHeader.vue builds the profile URL by concatenating the slug.
    $this->actingAs($user)->get('/perfil/'.$user->slug)->assertOk();
});

test('updating a name refreshes the profile slug', function () {
    $user = User::factory()->create(['slug' => 'juan-perez']);

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Ana María Ñandú',
        'email' => $user->email,
    ])->assertRedirect(route('profile.edit'));

    expect($user->fresh()->slug)->toBe('ana-maria-nandu');
});

test('registration rejects an email that only differs by case', function () {
    User::factory()->create(['email' => 'unico@example.com']);

    $this->post(route('register.store'), [
        'name' => 'Usuario Unico',
        'email' => 'UNICO@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    expect(User::count())->toBe(1)
        ->and(User::first()->email)->toBe('unico@example.com');
});

test('login is rate limited after five failed attempts', function () {
    $user = User::factory()->create();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(302);
    }

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertTooManyRequests();
});

test('a valid two factor code completes the challenge', function () {
    $secret = (new Google2FA)->generateSecretKey();
    $user = twoFactorUserWithSecret($secret);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    $this->post(route('two-factor.login.store'), [
        'code' => (new Google2FA)->getCurrentOtp($secret),
    ])->assertRedirect(route('community.feed', absolute: false));

    $this->assertAuthenticatedAs($user->fresh());
});

test('two factor challenge is rate limited after five invalid codes', function () {
    $user = twoFactorUserWithSecret((new Google2FA)->generateSecretKey());

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('two-factor.login'));

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
    }

    $this->post(route('two-factor.login.store'), ['code' => '000000'])
        ->assertTooManyRequests();
});

test('two factor can be enabled and disabled from settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'))
        ->assertStatus(302);

    expect($user->fresh()->two_factor_secret)->not->toBeNull();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('two-factor.disable'))
        ->assertStatus(302);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('deleting an account cascades to posts notes and favorites', function () {
    $user = User::factory()->create(['slug' => 'usuario-a-borrar']);
    $now = now();

    $postId = DB::table('posts')->insertGetId([
        'user_id' => $user->id,
        'body' => 'Contenido de prueba',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('notes')->insert([
        'user_id' => $user->id,
        'book_code' => 'GEN',
        'book_name' => 'Génesis',
        'chapter' => 1,
        'verse' => 1,
        'body' => 'Nota de prueba',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('favorites')->insert([
        'user_id' => $user->id,
        'book_code' => 'GEN',
        'book_name' => 'Génesis',
        'chapter' => 1,
        'verse' => 1,
        'text' => 'Texto de prueba',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    $this->actingAs($user)->delete(route('profile.destroy'), [
        'password' => 'password',
    ])->assertRedirect('/');

    expect(User::whereKey($user->id)->exists())->toBeFalse()
        ->and(Post::whereKey($postId)->exists())->toBeFalse()
        ->and(Note::where('user_id', $user->id)->exists())->toBeFalse()
        ->and(Favorite::where('user_id', $user->id)->exists())->toBeFalse();
});

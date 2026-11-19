<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Modelo de usuario de la aplicación.
 *
 * Representa a una persona autenticada que puede guardar favoritos, notas,
 * participar en muros/comunidad y gestionar su seguridad.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $church
 * @property string|null $slug
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'slug', 'church'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Obtiene los atributos que deben convertirse automáticamente.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Versículos favoritos guardados por el usuario.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Notas personales creadas por el usuario.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Comentarios publicados en el muro de este perfil.
     */
    public function profileComments(): HasMany
    {
        return $this->hasMany(ProfileComment::class, 'profile_user_id')->latest();
    }

    /**
     * Comentarios escritos por este usuario (en cualquier muro).
     */
    public function authoredProfileComments(): HasMany
    {
        return $this->hasMany(ProfileComment::class, 'user_id');
    }

    /**
     * Publicaciones creadas por el usuario (posts, respuestas y rebibleos).
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    /**
     * Publicaciones a las que el usuario dio "me gusta".
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * Usuarios que esta persona sigue.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    /**
     * Usuarios que siguen a esta persona.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Indica si este usuario sigue a otro.
     */
    public function isFollowing(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        return $this->following()->whereKey($user->id)->exists();
    }

    /**
     * Comienza a seguir a otro usuario.
     */
    public function follow(User $user): void
    {
        if ($this->id === $user->id) {
            return;
        }

        $this->following()->syncWithoutDetaching([$user->id]);
    }

    /**
     * Deja de seguir a otro usuario.
     */
    public function unfollow(User $user): void
    {
        $this->following()->detach($user->id);
    }
}

<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
class User extends Authenticatable implements MustVerifyEmail, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Mantiene el slug alineado con el nombre y garantiza que nunca quede vacío.
     */
    protected static function booted(): void
    {
        static::creating(function (self $user): void {
            if (empty($user->slug)) {
                $user->slug = $user->uniqueSlug();
            }
        });

        static::updating(function (self $user): void {
            if ($user->isDirty('name')) {
                $user->slug = $user->uniqueSlug();
            }
        });
    }

    /**
     * Construye un slug único derivado del nombre actual.
     */
    protected function uniqueSlug(): string
    {
        $base = Str::slug((string) $this->name) ?: 'usuario';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

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
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Notas personales creadas por el usuario.
     *
     * @return HasMany<Note, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Comentarios publicados en el muro de este perfil.
     *
     * @return HasMany<ProfileComment, $this>
     */
    public function profileComments(): HasMany
    {
        return $this->hasMany(ProfileComment::class, 'profile_user_id')->latest();
    }

    /**
     * Comentarios escritos por este usuario (en cualquier muro).
     *
     * @return HasMany<ProfileComment, $this>
     */
    public function authoredProfileComments(): HasMany
    {
        return $this->hasMany(ProfileComment::class, 'user_id');
    }

    /**
     * Publicaciones creadas por el usuario (posts, respuestas y rebibleos).
     *
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    /**
     * Publicaciones a las que el usuario dio "me gusta".
     *
     * @return BelongsToMany<Post, $this>
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes', 'user_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * Usuarios que esta persona sigue.
     *
     * @return BelongsToMany<User, $this>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')
            ->withTimestamps();
    }

    /**
     * Usuarios que siguen a esta persona.
     *
     * @return BelongsToMany<User, $this>
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

    /**
     * Devuelve la URL del avatar conocido para el usuario.
     *
     * Centraliza el mapeo para evitar duplicarlo en controladores.
     */
    public function avatarUrl(): ?string
    {
        $known = [
            'giovanni.arestivo@example.test' => '/images/testimonials/giovanni-arestivo.png',
            'piero.arestivo@example.test' => '/images/testimonials/piero-arestivo.png',
            'jose.britez@example.test' => '/images/testimonials/jose-britez.jpg',
            'giovanni-arestivo' => '/images/testimonials/giovanni-arestivo.png',
            'piero-arestivo' => '/images/testimonials/piero-arestivo.png',
            'jose-britez' => '/images/testimonials/jose-britez.jpg',
            'Giovanni Arestivo' => '/images/testimonials/giovanni-arestivo.png',
            'Piero Arestivo' => '/images/testimonials/piero-arestivo.png',
            'José Brítez' => '/images/testimonials/jose-britez.jpg',
        ];

        return $known[$this->email]
            ?? ($this->slug ? ($known[$this->slug] ?? null) : null)
            ?? ($known[$this->name] ?? null);
    }

    /**
     * Devuelve la iglesia del usuario, con fallback a datos conocidos.
     */
    public function churchName(): ?string
    {
        if (! empty($this->church)) {
            return $this->church;
        }

        $known = [
            'giovanni.arestivo@example.test' => 'Iglesia Menonita Concordia',
            'piero.arestivo@example.test' => 'Iglesia Menonita Concordia',
            'jose.britez@example.test' => 'Iglesia Pentecostal de Factorio',
        ];

        return $known[$this->email] ?? null;
    }
}

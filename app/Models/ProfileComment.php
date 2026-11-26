<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Comentario publicado en el muro de un perfil de usuario.
 *
 * Puede ser un comentario raíz (en el muro del perfil) o una respuesta a
 * otro comentario. Opcionalmente referencia un versículo bíblico citado.
 *
 * @property int $id
 * @property int $profile_user_id Usuario dueño del muro.
 * @property int $user_id Usuario autor del comentario.
 * @property int|null $parent_id Comentario padre (si es respuesta).
 * @property string|null $book_code Código del libro citado.
 * @property string|null $book_name Nombre del libro citado.
 * @property int|null $chapter Capítulo citado.
 * @property int|null $verse Versículo citado.
 * @property string $body Contenido del comentario.
 */
class ProfileComment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'profile_user_id',
        'user_id',
        'parent_id',
        'book_code',
        'book_name',
        'chapter',
        'verse',
        'body',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'chapter' => 'integer',
            'verse' => 'integer',
        ];
    }

    /**
     * Perfil (usuario) dueño del muro donde se publicó el comentario.
     *
     * @return BelongsTo<User, $this>
     */
    public function profileUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    /**
     * Usuario autor del comentario.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Comentario padre, si es una respuesta.
     *
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Respuestas directas a este comentario.
     *
     * @return HasMany<self, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    /**
     * Reacciones asociadas a este comentario.
     *
     * @return HasMany<ProfileCommentReaction, $this>
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(ProfileCommentReaction::class, 'profile_comment_id');
    }

    /**
     * Indica si el comentario cita un versículo.
     */
    public function hasVerse(): bool
    {
        return $this->book_code !== null && $this->chapter !== null && $this->verse !== null;
    }
}

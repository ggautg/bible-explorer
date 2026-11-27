<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Publicación de la red social bíblica.
 *
 * Puede ser un post normal, una respuesta (parent_id) o un "rebibleo"
 * (rebible_of_id) que republica otro post en el perfil del autor.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property int|null $rebible_of_id
 * @property string $kind Tipo de publicación de comunidad: reflexion, versiculo, peticion_oracion, testimonio, agradecimiento o rebibleada.
 * @property string|null $body
 * @property string|null $book_code
 * @property string|null $book_name
 * @property int|null $chapter
 * @property int|null $verse
 */
class Post extends Model
{
    /** Tipos permitidos en las opciones de comunidad. */
    public const KINDS = [
        'reflexion',
        'versiculo',
        'peticion_oracion',
        'testimonio',
        'agradecimiento',
        'rebibleada',
    ];

    /** Tipos que el usuario puede elegir al publicar (la rebibleada es automática). */
    public const SELECTABLE_KINDS = [
        'reflexion',
        'versiculo',
        'peticion_oracion',
        'testimonio',
        'agradecimiento',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        'rebible_of_id',
        'kind',
        'body',
        'book_code',
        'book_name',
        'chapter',
        'verse',
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
     * El admin ve todo; para los miembros sus publicaciones no existen.
     *
     * @param  Builder<Post>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): void
    {
        if ($viewer?->isAdmin()) {
            return;
        }

        $query->whereHas('author', fn (Builder $authorQuery) => $authorQuery->where('is_admin', false));
    }

    /**
     * Autor de la publicación.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Publicación padre si es una respuesta.
     *
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Respuestas directas a esta publicación.
     *
     * @return HasMany<self, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    /**
     * Publicación original si esto es un rebibleo.
     *
     * @return BelongsTo<self, $this>
     */
    public function rebibleOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rebible_of_id');
    }

    /**
     * Rebibleos que se hicieron de esta publicación.
     *
     * @return HasMany<self, $this>
     */
    public function rebibles(): HasMany
    {
        return $this->hasMany(self::class, 'rebible_of_id');
    }

    /**
     * Usuarios a los que les gusta esta publicación.
     *
     * @return BelongsToMany<User, $this>
     */
    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Indica si el post cita un versículo.
     */
    public function hasVerse(): bool
    {
        return $this->book_code !== null && $this->chapter !== null && $this->verse !== null;
    }

    /**
     * Indica si es un rebibleo (repost).
     */
    public function isRebible(): bool
    {
        return $this->rebible_of_id !== null;
    }

    /**
     * Indica si es una respuesta.
     */
    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }
}

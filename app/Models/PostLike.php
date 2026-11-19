<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de "me gusta" en una publicación.
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 */
class PostLike extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    /**
     * Usuario que dio me gusta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Publicación que recibió el me gusta.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
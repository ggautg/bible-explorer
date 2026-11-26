<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Reacción de un usuario a un comentario del muro de perfil.
 *
 * Tipos admitidos: amen, oracion, abstinencia, dislike.
 *
 * @property int $id
 * @property int $profile_comment_id
 * @property int $user_id
 * @property string $type
 */
class ProfileCommentReaction extends Model
{
    public const TYPES = ['amen', 'oracion', 'abstinencia', 'dislike'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'profile_comment_id',
        'user_id',
        'type',
    ];

    /**
     * Comentario al que pertenece la reacción.
     *
     * @return BelongsTo<ProfileComment, $this>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(ProfileComment::class, 'profile_comment_id');
    }

    /**
     * Usuario que realizó la reacción.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

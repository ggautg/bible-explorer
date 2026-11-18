<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de versículo favorito.
 *
 * Guarda la referencia y el texto de un versículo marcado como favorito por
 * un usuario autenticado.
 *
 * @property int         $id
 * @property int         $user_id    Usuario propietario del favorito.
 * @property string      $book_code  Código del libro.
 * @property string      $book_name  Nombre del libro (desnormalizado para mostrar rápido).
 * @property int         $chapter    Número de capítulo.
 * @property int         $verse      Número de versículo.
 * @property string|null $text       Texto del versículo guardado.
 */
class Favorite extends Model
{
    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_code',
        'book_name',
        'chapter',
        'verse',
        'text',
    ];

    /**
     * Usuario al que pertenece este favorito.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

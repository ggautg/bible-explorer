<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de nota personal sobre un versículo.
 *
 * Cada usuario puede tener una nota por versículo (identificado por libro,
 * capítulo y versículo). El contenido se almacena en el campo "body".
 *
 * @property int    $id
 * @property int    $user_id    Usuario propietario de la nota.
 * @property string $book_code  Código del libro.
 * @property string $book_name  Nombre del libro (desnormalizado para mostrar rápido).
 * @property int    $chapter    Número de capítulo.
 * @property int    $verse      Número de versículo.
 * @property string $body       Contenido de la nota.
 */
class Note extends Model
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
        'body',
    ];

    /**
     * Usuario al que pertenece esta nota.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

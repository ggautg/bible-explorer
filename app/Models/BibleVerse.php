<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de versículo bíblico.
 *
 * Almacena el texto de un versículo para una traducción, libro, capítulo y
 * número de versículo determinados.
 *
 * @property int         $id          Identificador autoincremental.
 * @property string      $translation Código de traducción (p. ej. "spa_rvg").
 * @property string      $book_code   Código del libro al que pertenece.
 * @property int         $chapter     Número de capítulo.
 * @property int         $verse       Número de versículo.
 * @property string|null $text        Texto del versículo.
 */
class BibleVerse extends Model
{
    /**
     * Nombre de la tabla asociada.
     *
     * @var string
     */
    protected $table = 'bible_verses';

    /**
     * El modelo no utiliza columnas de marcas de tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos asignables en masa.
     *
     * Se incluye "translation" para permitir operaciones de carga/actualización
     * masiva coherentes con el esquema de base de datos multitraducción.
     *
     * @var list<string>
     */
    protected $fillable = [
        'translation',
        'book_code',
        'chapter',
        'verse',
        'text',
    ];
}

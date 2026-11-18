<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de libro bíblico.
 *
 * Representa un libro de la Biblia (por ejemplo, Génesis o Juan) con su
 * identificador corto, nombre visible, testamento y orden canónico.
 *
 * @property string $code       Código corto del libro (clave primaria, p. ej. "GEN").
 * @property string $name       Nombre del libro para mostrar al usuario.
 * @property string $testament  Testamento al que pertenece: "OT" o "NT".
 * @property int    $order      Posición del libro en el orden canónico.
 */
class BibleBook extends Model
{
    /**
     * Nombre de la tabla asociada (convención por defecto de Laravel).
     *
     * @var string
     */
    protected $table = 'bible_books';

    /**
     * Clave primaria del modelo (código del libro, no un id numérico).
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Indica que la clave primaria no es autoincremental.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Tipo de la clave primaria.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * El modelo no utiliza columnas de marcas de tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'testament',
        'order',
    ];
}

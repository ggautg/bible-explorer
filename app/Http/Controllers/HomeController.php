<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use Inertia\Response;

/**
 * Controlador de la página de inicio.
 *
 * Muestra la bienvenida y selecciona un "versículo del día" determinístico
 * (el mismo para todos los usuarios durante la fecha actual).
 */
class HomeController extends Controller
{
    /**
     * Renderiza la página de bienvenida con el versículo del día.
     */
    public function index(): Response
    {
        $totalVerses = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->count();

        if ($totalVerses === 0) {
            return inertia('Welcome', [
                'verseOfTheDay' => null,
            ]);
        }

        // Semilla determinística basada en la fecha (YYYY-MM-DD) para que
        // todas las personas vean el mismo versículo durante el día.
        $seed = crc32(now()->format('Y-m-d'));
        $offset = $seed % $totalVerses;

        $verse = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->orderBy('id')
            ->skip($offset)
            ->first();

        if (! $verse) {
            return inertia('Welcome', [
                'verseOfTheDay' => null,
            ]);
        }

        $book = BibleBook::query()->find($verse->book_code);

        return inertia('Welcome', [
            'verseOfTheDay' => [
                'book' => $verse->book_code,
                'bookName' => $book?->name ?? $verse->book_code,
                'chapter' => $verse->chapter,
                'verse' => $verse->verse,
                'text' => $verse->text,
            ],
        ]);
    }
}

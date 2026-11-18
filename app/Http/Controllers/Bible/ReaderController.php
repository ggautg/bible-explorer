<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;
use Inertia\Response;

/**
 * Controlador de lectura de la Biblia.
 *
 * Expone las páginas para navegar por libros, capítulos, favoritos y notas.
 */
class ReaderController extends Controller
{
    /**
     * Muestra el listado de libros bíblicos ordenados canónicamente.
     */
    public function books(): Response
    {
        return inertia('Bible/Books', [
            'books' => BibleBook::query()->orderBy('order')->get(),
        ]);
    }

    /**
     * Muestra los capítulos disponibles de un libro.
     */
    public function chapters(string $book): Response
    {
        $bookModel = BibleBook::query()->findOrFail($book);

        $chapters = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->distinct()
            ->orderBy('chapter')
            ->pluck('chapter');

        return inertia('Bible/Book', [
            'book' => $bookModel,
            'chapters' => $chapters,
        ]);
    }

    /**
     * Muestra los versículos de un capítulo y metadatos de navegación.
     *
     * Incluye, cuando existen, los textos alternos de la traducción PDT para
     * comparación rápida en la interfaz de lectura.
     */
    public function chapter(string $book, int $chapter): Response
    {
        $bookModel = BibleBook::query()->findOrFail($book);

        $verses = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->orderBy('verse')
            ->get(['verse', 'text']);

        if ($verses->isEmpty()) {
            abort(404);
        }

        $alternateVerses = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_pdt')
            ->where('chapter', $chapter)
            ->orderBy('verse')
            ->pluck('text', 'verse');

        $maxChapter = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->max('chapter');

        return inertia('Bible/Chapter', [
            'book' => $bookModel,
            'chapter' => $chapter,
            'verses' => $verses,
            'alternateVerses' => $alternateVerses,
            'hasNext' => $chapter < (int) $maxChapter,
            'hasPrev' => $chapter > 1,
        ]);
    }

    /**
     * Muestra la página de favoritos del usuario.
     *
     * Los datos se cargan desde el cliente mediante la API de favoritos.
     */
    public function favorites(): Response
    {
        return inertia('Bible/Favorites');
    }

    /**
     * Muestra la página de notas personales del usuario.
     *
     * Los datos se cargan desde el cliente mediante la API de notas.
     */
    public function notes(): Response
    {
        return inertia('Bible/Notes');
    }
}

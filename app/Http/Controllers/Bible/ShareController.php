<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;
use Inertia\Response;

/**
 * Controlador de páginas públicas para compartir versículos.
 */
class ShareController extends Controller
{
    /**
     * Muestra una vista pública de un versículo compartido.
     *
     * @param  string  $book  Código del libro.
     * @param  int  $chapter  Número de capítulo.
     * @param  int  $verse  Número de versículo.
     * @return Response Página Inertia pública del versículo.
     */
    public function show(string $book, int $chapter, int $verse): Response
    {
        $verseModel = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->firstOrFail();

        $bookModel = BibleBook::query()->findOrFail($book);

        return inertia('Bible/Shared', [
            'verse' => [
                'book' => $book,
                'bookName' => $bookModel->name,
                'chapter' => $chapter,
                'verse' => $verse,
                'text' => $verseModel->text,
            ],
        ]);
    }
}

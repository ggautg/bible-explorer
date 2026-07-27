<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;

class ReaderController extends Controller
{
    public function books()
    {
        return inertia('Bible/Books', [
            'books' => BibleBook::orderBy('order')->get(),
        ]);
    }

    public function chapters(string $book)
    {
        $bookModel = BibleBook::findOrFail($book);

        $chapters = BibleVerse::where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->distinct()
            ->orderBy('chapter')
            ->pluck('chapter');

        return inertia('Bible/Book', [
            'book' => $bookModel,
            'chapters' => $chapters,
        ]);
    }

    public function chapter(string $book, int $chapter)
    {
        $bookModel = BibleBook::findOrFail($book);

        $verses = BibleVerse::where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->orderBy('verse')
            ->get(['verse', 'text']);

        if ($verses->isEmpty()) {
            abort(404);
        }

        $alternateVerses = BibleVerse::where('book_code', $book)
            ->where('translation', 'spa_pdt')
            ->where('chapter', $chapter)
            ->orderBy('verse')
            ->pluck('text', 'verse');

        $maxChapter = BibleVerse::where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->max('chapter');

        return inertia('Bible/Chapter', [
            'book' => $bookModel,
            'chapter' => $chapter,
            'verses' => $verses,
            'alternateVerses' => $alternateVerses,
            'hasNext' => $chapter < $maxChapter,
            'hasPrev' => $chapter > 1,
        ]);
    }

    public function favorites()
    {
        return inertia('Bible/Favorites');
    }

    public function notes()
    {
        return inertia('Bible/Notes');
    }
}

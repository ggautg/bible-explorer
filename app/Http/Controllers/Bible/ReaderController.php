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
            ->where('chapter', $chapter)
            ->orderBy('verse')
            ->get(['verse', 'text']);

        if ($verses->isEmpty()) {
            abort(404);
        }

        $maxChapter = BibleVerse::where('book_code', $book)->max('chapter');

        return inertia('Bible/Chapter', [
            'book' => $bookModel,
            'chapter' => $chapter,
            'verses' => $verses,
            'hasNext' => $chapter < $maxChapter,
            'hasPrev' => $chapter > 1,
        ]);
    }

    public function favorites()
{
    return inertia('Bible/Favorites');
}
}
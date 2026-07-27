<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;

class ShareController extends Controller
{
    public function show(string $book, int $chapter, int $verse)
    {
        $verseModel = BibleVerse::where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->firstOrFail();

        $bookModel = BibleBook::findOrFail($book);

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
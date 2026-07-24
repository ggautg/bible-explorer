<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleVerse;

class HomeController extends Controller
{
    public function index()
    {
        $totalVerses = BibleVerse::count();

        // Hash determinístico a partir de la fecha de hoy (YYYY-MM-DD).
        // El mismo día siempre da el mismo número, así todos ven el mismo versículo.
        $seed = crc32(now()->format('Y-m-d'));
        $offset = $seed % $totalVerses;

        $verse = BibleVerse::orderBy('id')->skip($offset)->first();
        $book = BibleBook::find($verse->book_code);

        return inertia('Welcome', [
            'verseOfTheDay' => [
                'book' => $verse->book_code,
                'bookName' => $book->name,
                'chapter' => $verse->chapter,
                'verse' => $verse->verse,
                'text' => $verse->text,
            ],
        ]);
    }
}
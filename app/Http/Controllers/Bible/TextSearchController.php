<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleVerse;
use Illuminate\Http\Request;

class TextSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (mb_strlen($query) < 3) {
            return inertia('Bible/Search', ['query' => $query, 'results' => []]);
        }

        $results = BibleVerse::query()
            ->select('book_code', 'chapter', 'verse', 'text')
            ->selectRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->orderByDesc('relevance')
            ->limit(50)
            ->get()
            ->map(function ($v) {
                $book = \App\Models\BibleBook::find($v->book_code);
                return [
                    'book' => $v->book_code,
                    'bookName' => $book?->name ?? $v->book_code,
                    'chapter' => $v->chapter,
                    'verse' => $v->verse,
                    'text' => $v->text,
                ];
            });

        return inertia('Bible/Search', ['query' => $query, 'results' => $results]);
    }
}
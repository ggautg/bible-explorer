<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador de búsqueda de texto en versículos.
 *
 * Permite buscar palabras o frases dentro del texto bíblico usando el índice
 * de texto completo de MySQL sobre la traducción principal.
 */
class TextSearchController extends Controller
{
    /**
     * Ejecuta una búsqueda de texto y muestra los resultados.
     *
     * Requiere al menos 3 caracteres para evitar consultas poco útiles.
     */
    public function search(Request $request): Response
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 3) {
            return inertia('Bible/Search', [
                'query' => $query,
                'results' => [],
            ]);
        }

        $bookNames = BibleBook::query()->pluck('name', 'code');

        $results = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->select('book_code', 'chapter', 'verse', 'text')
            ->selectRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
            ->whereRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
            ->orderByDesc('relevance')
            ->limit(50)
            ->get()
            ->map(fn ($v) => [
                'book' => $v->book_code,
                'bookName' => $bookNames[$v->book_code] ?? $v->book_code,
                'chapter' => $v->chapter,
                'verse' => $v->verse,
                'text' => $v->text,
            ]);

        return inertia('Bible/Search', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}

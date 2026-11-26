<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
     * Requiere al menos 3 caracteres para evitar consultas poco útiles. Usa
     * búsqueda de texto completo (MySQL) sobre la traducción principal.
     *
     * @param  Request  $request  Solicitud con el parámetro `q`.
     * @return Response Página Inertia con la consulta y resultados.
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

        $usesFulltext = DB::connection()->getDriverName() !== 'sqlite';

        $verses = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->select('book_code', 'chapter', 'verse', 'text')
            ->when(
                $usesFulltext,
                fn ($verses) => $verses
                    ->selectRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance', [$query])
                    ->whereRaw('MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE)', [$query])
                    ->orderByDesc('relevance'),
                function ($verses) use ($query) {
                    // SQLite no tiene FULLTEXT: los comodines del usuario son literales.
                    $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

                    return $verses
                        ->whereRaw('text LIKE ? ESCAPE ?', ['%'.$escaped.'%', '\\'])
                        ->orderBy('book_code')
                        ->orderBy('chapter')
                        ->orderBy('verse');
                }
            )
            ->limit(50)
            ->get();

        $results = $verses
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

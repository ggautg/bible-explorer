<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Services\BibleReferenceParser;
use App\Services\VerseMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

/**
 * Controlador de búsqueda y resolución de referencias bíblicas.
 *
 * Provee endpoints JSON para autocompletar libros y convertir una referencia
 * textual (por ejemplo, "Juan 3:16") en identificadores de libro/capítulo/versículo.
 */
class SearchController extends Controller
{
    /**
     * Crea una nueva instancia del controlador.
     *
     * @param  BibleReferenceParser  $parser  Servicio de parseo/resolución de referencias.
     */
    public function __construct(private BibleReferenceParser $parser) {}

    /**
     * Busca libros por nombre para el autocompletado.
     *
     * @param  Request  $request  Solicitud con el parámetro `q`.
     * @return JsonResponse Lista de libros coincidentes (máx. 8).
     */
    public function books(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        // Los caracteres comodín del usuario son literales, no patrones LIKE.
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        $books = BibleBook::query()
            ->whereRaw('name LIKE ? ESCAPE ?', ['%'.$escaped.'%', '\\'])
            ->orderBy('order')
            ->limit(8)
            ->get(['code', 'name']);

        return response()->json($books);
    }

    /**
     * Devuelve capítulos de un libro y versículos de un capítulo.
     *
     * Alimenta el selector del compositor sin enviar todo el texto bíblico:
     * con `book` devuelve los capítulos y con `book` + `chapter`, los versículos.
     *
     * @param  Request  $request  Solicitud con `book` y `chapter` opcional.
     * @return JsonResponse Estructura disponible en la traducción por defecto.
     */
    public function structure(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book' => 'required|string|max:10',
            'chapter' => 'nullable|integer|min:1',
        ]);

        $verses = BibleVerse::query()
            ->where('translation', VerseMetadata::TRANSLATION)
            ->where('book_code', $data['book']);

        $chapters = (clone $verses)
            ->distinct()
            ->orderBy('chapter')
            ->pluck('chapter')
            ->map(fn ($chapter) => (int) $chapter)
            ->values()
            ->all();

        if ($chapters === []) {
            return response()->json(['error' => 'No se encontró ese libro.'], 404);
        }

        $verseNumbers = [];
        if (isset($data['chapter'])) {
            $verseNumbers = (clone $verses)
                ->where('chapter', $data['chapter'])
                ->orderBy('verse')
                ->pluck('verse')
                ->map(fn ($verse) => (int) $verse)
                ->values()
                ->all();
        }

        return response()->json(['chapters' => $chapters, 'verses' => $verseNumbers]);
    }

    /**
     * Resuelve una referencia bíblica en formato texto.
     *
     * Devuelve 422 si el formato es inválido o 404 si el libro/versículo no existe.
     *
     * @param  Request  $request  Solicitud con el parámetro `ref`.
     * @return JsonResponse Referencia resuelta o error con código HTTP apropiado.
     */
    public function resolve(Request $request): JsonResponse
    {
        try {
            return response()->json($this->parser->parse((string) $request->input('ref', '')));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}

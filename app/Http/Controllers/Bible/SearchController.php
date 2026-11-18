<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Models\BibleBook;
use App\Services\BibleReferenceParser;
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
     */
    public function __construct(private BibleReferenceParser $parser) {}

    /**
     * Busca libros por nombre para el autocompletado.
     */
    public function books(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 1) {
            return response()->json([]);
        }

        $books = BibleBook::query()
            ->where('name', 'like', "%{$query}%")
            ->orderBy('order')
            ->limit(8)
            ->get(['code', 'name']);

        return response()->json($books);
    }

    /**
     * Resuelve una referencia bíblica en formato texto.
     *
     * Devuelve 422 si el formato es inválido o 404 si el libro/versículo no existe.
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

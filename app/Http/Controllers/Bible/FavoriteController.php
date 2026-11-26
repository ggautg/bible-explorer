<?php

namespace App\Http\Controllers\Bible;

use App\Concerns\VerseReferenceValidationRules;
use App\Http\Controllers\Controller;
use App\Services\VerseMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API de favoritos.
 *
 * Permite listar, agregar y quitar versículos favoritos del usuario
 * autenticado.
 */
class FavoriteController extends Controller
{
    use VerseReferenceValidationRules;

    /**
     * Devuelve todos los favoritos del usuario autenticado.
     *
     * @param  Request  $request  Solicitud HTTP autenticada.
     * @return JsonResponse Colección JSON de favoritos (más recientes primero).
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->favorites()->latest()->get()
        );
    }

    /**
     * Guarda un versículo como favorito.
     *
     * Si ya existe el mismo versículo para el usuario, no crea duplicados.
     *
     * El nombre del libro y el texto se derivan de la base de datos, no del cliente.
     *
     * @param  Request  $request  Solicitud con book_code, chapter y verse.
     * @return JsonResponse Favorito creado o existente.
     */
    public function store(Request $request, VerseMetadata $metadata): JsonResponse
    {
        $data = $request->validate($this->verseReferenceRules());

        $verse = $metadata->forReference($data['book_code'], (int) $data['chapter'], (int) $data['verse']);
        if (! $verse) {
            return response()->json(['message' => 'No encontramos ese versículo en la traducción actual.'], 422);
        }

        $favorite = $request->user()->favorites()->firstOrCreate([
            'book_code' => $data['book_code'],
            'chapter' => $data['chapter'],
            'verse' => $data['verse'],
        ], [
            'book_name' => $verse['book_name'],
            'text' => $verse['text'],
        ]);

        return response()->json($favorite);
    }

    /**
     * Elimina un versículo de favoritos.
     *
     * @param  Request  $request  Solicitud con book_code, chapter y verse.
     * @return JsonResponse Confirmación de eliminación.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate($this->verseReferenceRules());

        $request->user()->favorites()
            ->where($data)
            ->delete();

        return response()->json(['ok' => true]);
    }
}

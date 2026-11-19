<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
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
     * @param  Request  $request  Solicitud con book_code, book_name, chapter, verse y text.
     * @return JsonResponse Favorito creado o existente.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_code' => 'required|string|max:10',
            'book_name' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
            'text' => 'required|string',
        ]);

        $favorite = $request->user()->favorites()->firstOrCreate([
            'book_code' => $data['book_code'],
            'chapter' => $data['chapter'],
            'verse' => $data['verse'],
        ], $data);

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
        $data = $request->validate([
            'book_code' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
        ]);

        $request->user()->favorites()
            ->where($data)
            ->delete();

        return response()->json(['ok' => true]);
    }
}

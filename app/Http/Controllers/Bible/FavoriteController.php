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
     * Si ya existe el mismo versículo, no crea duplicados.
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

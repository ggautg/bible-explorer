<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API de notas personales.
 *
 * Permite listar, crear/actualizar y eliminar notas asociadas a versículos
 * para el usuario autenticado.
 */
class NoteController extends Controller
{
    /**
     * Devuelve todas las notas del usuario, ordenadas por última actualización.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->notes()->latest('updated_at')->get()
        );
    }

    /**
     * Crea o actualiza la nota de un versículo específico.
     */
    public function upsert(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_code' => 'required|string|max:10',
            'book_name' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
            'body' => 'required|string|max:5000',
        ]);

        $note = $request->user()->notes()->updateOrCreate(
            [
                'book_code' => $data['book_code'],
                'chapter' => $data['chapter'],
                'verse' => $data['verse'],
            ],
            $data
        );

        return response()->json($note);
    }

    /**
     * Elimina la nota asociada a un versículo.
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_code' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
        ]);

        $request->user()->notes()->where($data)->delete();

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use App\Services\VerseMetadata;
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
     *
     * @param  Request  $request  Solicitud HTTP autenticada.
     * @return JsonResponse Colección JSON de notas.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $request->user()->notes()->latest('updated_at')->get()
        );
    }

    /**
     * Crea o actualiza la nota de un versículo específico.
     *
     * El nombre del libro se deriva de la base de datos, no del cliente.
     *
     * @param  Request  $request  Solicitud con book_code, chapter, verse y body.
     * @return JsonResponse Nota creada o actualizada.
     */
    public function upsert(Request $request, VerseMetadata $metadata): JsonResponse
    {
        $data = $request->validate([
            'book_code' => 'required|string|max:10',
            'chapter' => 'required|integer|min:1',
            'verse' => 'required|integer|min:1',
            'body' => 'required|string|max:5000',
        ]);

        $verse = $metadata->forReference($data['book_code'], (int) $data['chapter'], (int) $data['verse']);
        if (! $verse) {
            return response()->json(['message' => 'No encontramos ese versículo en la traducción actual.'], 422);
        }

        $note = $request->user()->notes()->updateOrCreate(
            [
                'book_code' => $data['book_code'],
                'chapter' => $data['chapter'],
                'verse' => $data['verse'],
            ],
            [
                'book_name' => $verse['book_name'],
                'body' => $data['body'],
            ]
        );

        return response()->json($note);
    }

    /**
     * Elimina la nota asociada a un versículo.
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

        $request->user()->notes()->where($data)->delete();

        return response()->json(['ok' => true]);
    }
}

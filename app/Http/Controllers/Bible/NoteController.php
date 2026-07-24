<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Todas las notas del usuario logueado (para la página "Mis notas").
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->notes()->latest('updated_at')->get()
        );
    }

    /**
     * Guarda o actualiza la nota de un versículo puntual.
     */
    public function upsert(Request $request)
    {
        $data = $request->validate([
            'book_code' => 'required|string|max:10',
            'book_name' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
            'body' => 'required|string|max:5000',
        ]);

        $note = $request->user()->notes()->updateOrCreate(
            // condición para encontrar la nota existente:
            [
                'book_code' => $data['book_code'],
                'chapter' => $data['chapter'],
                'verse' => $data['verse'],
            ],
            // datos a guardar/actualizar:
            $data
        );

        return response()->json($note);
    }

    /**
     * Borra la nota de un versículo.
     */
    public function destroy(Request $request)
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
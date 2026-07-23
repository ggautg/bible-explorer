<?php

namespace App\Http\Controllers\Bible;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Devuelve todos los favoritos del usuario logueado.
     */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->favorites()->latest()->get()
        );
    }

    /**
     * Guarda un versículo como favorito. Si ya existe, no hace nada (evita duplicados).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'book_code' => 'required|string|max:10',
            'book_name' => 'required|string',
            'chapter' => 'required|integer',
            'verse' => 'required|integer',
            'text' => 'required|string',
        ]);

        $favorite = $request->user()->favorites()->firstOrCreate($data);

        return response()->json($favorite);
    }

    /**
     * Saca un versículo de favoritos.
     */
    public function destroy(Request $request)
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
<?php

namespace App\Http\Controllers;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador de la página de inicio.
 *
 * Muestra la bienvenida pública a visitantes y redirige a la comunidad a
 * las personas autenticadas.
 */
class HomeController extends Controller
{
    /**
     * Renderiza la bienvenida para visitantes o redirige a la comunidad.
     *
     * @param  Request  $request  Solicitud HTTP actual.
     * @return Response|RedirectResponse Página de bienvenida o redirección.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('community.feed');
        }

        $totalVerses = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->count();

        if ($totalVerses === 0) {
            return inertia('Welcome', [
                'verseOfTheDay' => null,
            ]);
        }

        // Semilla determinística basada en la fecha (YYYY-MM-DD) para que
        // todas las personas vean el mismo versículo durante el día.
        $seed = crc32(now()->format('Y-m-d'));
        $offset = $seed % $totalVerses;

        $verse = BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->orderBy('id')
            ->skip($offset)
            ->first();

        if (! $verse) {
            return inertia('Welcome', [
                'verseOfTheDay' => null,
            ]);
        }

        $book = BibleBook::query()->find($verse->book_code);

        return inertia('Welcome', [
            'verseOfTheDay' => [
                'book' => $verse->book_code,
                'bookName' => $book?->name ?? $verse->book_code,
                'chapter' => $verse->chapter,
                'verse' => $verse->verse,
                'text' => $verse->text,
            ],
        ]);
    }
}

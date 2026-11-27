<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de interacciones con publicaciones.
 *
 * Gestiona acciones sociales rápidas: me gusta y rebibleo (republicación).
 * Todas las acciones devuelven redirección atrás para que Inertia conserve
 * el scroll y actualice el estado con feedback.
 */
class PostInteractionController extends Controller
{
    /**
     * Alterna el "me gusta" del usuario autenticado en una publicación.
     *
     * @param  Request  $request  Solicitud autenticada.
     * @param  Post  $post  Publicación objetivo.
     * @return RedirectResponse Redirección atrás con notificación.
     */
    public function toggleLike(Request $request, Post $post): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($viewer->isAdmin() || ! $post->author->isAdmin(), 404);

        // La clave única (user_id, post_id) hace idempotente la escritura:
        // primero se intenta borrar y, si no había fila, se inserta ignorando
        // colisiones. Así dos clics concurrentes no duplican ni pierden el like.
        $removed = DB::table('post_likes')
            ->where('user_id', $viewer->id)
            ->where('post_id', $post->id)
            ->delete();

        if ($removed > 0) {
            $message = 'Quitaste tu me gusta.';
        } else {
            DB::table('post_likes')->insertOrIgnore([
                'user_id' => $viewer->id,
                'post_id' => $post->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = 'Te gustó esta publicación.';
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }

    /**
     * Alterna el rebibleo (repost) de una publicación.
     *
     * Si el post ya es un rebibleo, se toma su publicación original como objetivo.
     * No se permite rebiblear publicaciones propias.
     *
     * @param  Request  $request  Solicitud autenticada.
     * @param  Post  $post  Publicación a rebiblear.
     * @return RedirectResponse Redirección atrás con notificación.
     */
    public function toggleRebible(Request $request, Post $post): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($viewer->isAdmin() || ! $post->author->isAdmin(), 404);

        if ($post->user_id === $viewer->id) {
            return back()->with('toast', ['type' => 'info', 'message' => 'No podés rebiblear tu propia publicación.']);
        }

        $target = $post->isRebible() && $post->rebible_of_id ? $post->rebible_of_id : $post->id;

        $rebible = Post::query()
            ->where('user_id', $viewer->id)
            ->where('rebible_of_id', $target);

        if ($rebible->exists()) {
            $rebible->delete();
            $message = 'Quitaste el rebibleo.';
        } else {
            Post::query()->insertOrIgnore([[
                'user_id' => $viewer->id,
                'parent_id' => null,
                'rebible_of_id' => $target,
                'body' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]]);
            $message = 'Rebibleaste esta publicación.';
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }
}

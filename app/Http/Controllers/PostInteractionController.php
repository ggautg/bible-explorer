<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $existing = $post->likedBy()->where('users.id', $viewer->id)->exists();

        if ($existing) {
            $post->likedBy()->detach($viewer->id);
            $message = 'Quitaste tu me gusta.';
        } else {
            $post->likedBy()->attach($viewer->id);
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

        if ($post->user_id === $viewer->id) {
            return back()->with('toast', ['type' => 'info', 'message' => 'No podés rebiblear tu propia publicación.']);
        }

        $target = $post->isRebible() && $post->rebible_of_id ? $post->rebible_of_id : $post->id;

        $existing = Post::query()
            ->where('user_id', $viewer->id)
            ->where('rebible_of_id', $target)
            ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Quitaste el rebibleo.';
        } else {
            Post::query()->create([
                'user_id' => $viewer->id,
                'parent_id' => null,
                'rebible_of_id' => $target,
                'body' => null,
            ]);
            $message = 'Rebibleaste esta publicación.';
        }

        return back()->with('toast', ['type' => 'success', 'message' => $message]);
    }
}

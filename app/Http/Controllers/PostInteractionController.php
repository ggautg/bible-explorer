<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controlador de interacciones con publicaciones (like, rebibleo, etc.).
 */
class PostInteractionController extends Controller
{
    /**
     * Alterna "me gusta" en una publicación.
     */
    public function toggleLike(Request $request, Post $post): RedirectResponse
    {
        $viewer = $request->user();
        $existing = $post->likedBy()->where('users.id', $viewer->id)->exists();

        if ($existing) {
            $post->likedBy()->detach($viewer->id);
        } else {
            $post->likedBy()->attach($viewer->id);
        }

        return back();
    }

    /**
     * Alterna rebibleo (repost) de una publicación.
     */
    public function toggleRebible(Request $request, Post $post): RedirectResponse
    {
        $viewer = $request->user();

        // No se rebiblea un post propio en este flujo simple.
        if ($post->user_id === $viewer->id) {
            return back();
        }

        $target = $post->isRebible() && $post->rebible_of_id ? $post->rebible_of_id : $post->id;

        $existing = Post::query()
            ->where('user_id', $viewer->id)
            ->where('rebible_of_id', $target)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            Post::query()->create([
                'user_id' => $viewer->id,
                'parent_id' => null,
                'rebible_of_id' => $target,
                'body' => null,
            ]);
        }

        return back();
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Panel con métricas de la red y controles de moderación.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'admins' => User::query()->where('is_admin', true)->count(),
                'churches' => User::query()->distinct()->count('church'),
                'posts' => Post::count(),
                'follows' => DB::table('follows')->count(),
                'likes' => DB::table('post_likes')->count(),
            ],
            'users' => User::query()
                ->withCount(['posts', 'followers'])
                ->orderBy('id')
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'church' => $user->church,
                    'is_admin' => $user->isAdmin(),
                    'posts_count' => $user->posts_count,
                    'followers_count' => $user->followers_count,
                ]),
            'posts' => Post::query()
                ->with('author:id,name')
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'body' => $post->body,
                    'author' => $post->author->name ?? 'Usuario',
                ]),
        ]);
    }

    /**
     * Promueve o degrada a otra persona; el admin actual no puede degradarse.
     */
    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'No podés quitarte el rol a vos mismo.');

        $user->forceFill(['is_admin' => ! $user->isAdmin()])->save();

        return back(303);
    }

    /**
     * Borra cualquier publicación, incluso ajena.
     */
    public function destroyPost(Post $post): RedirectResponse
    {
        $post->delete();

        return back(303);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
                'banned' => User::query()->where('banned_until', '>', now())->count(),
                'reports' => Report::query()->where('status', 'pending')->count(),
                'churches' => User::query()->distinct()->count('church'),
                'posts' => Post::count(),
                'follows' => DB::table('follows')->count(),
                'likes' => DB::table('post_likes')->count(),
            ],
            'banDurations' => User::BAN_DURATIONS,
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
                    'is_banned' => $user->isBanned(),
                    'banned_until' => $user->banned_until?->toIso8601String(),
                    'posts_count' => $user->posts_count,
                    'followers_count' => $user->followers_count,
                ]),
            'reports' => Report::query()
                ->where('status', 'pending')
                ->with('reporter:id,name', 'post.author:id,name')
                ->latest()
                ->get()
                ->map(fn (Report $report) => [
                    'id' => $report->id,
                    'reporter' => $report->reporter->name,
                    'reported' => $report->post->author->name ?? 'Usuario',
                    'body' => $report->post->body,
                    'reason' => $report->reason,
                    'time' => $report->created_at?->diffForHumans(),
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
     * Suspende a otra persona; nadie puede banearse a sí mismo.
     */
    public function ban(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'No podés banearte a vos mismo.');

        $user->banUntil(now()->addMinutes($this->validatedMinutes($request)));

        return back(303);
    }

    /**
     * Levanta la suspensión de una cuenta.
     */
    public function unban(User $user): RedirectResponse
    {
        $user->unban();

        return back(303);
    }

    /**
     * El admin revisa un reporte: banea al autor, borra el post o descarta el caso.
     */
    public function resolveReport(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['ban', 'delete', 'dismiss'])],
            'minutes' => [
                'required_if:action,ban',
                'nullable',
                'integer',
                Rule::in(array_keys(User::BAN_DURATIONS)),
            ],
        ]);

        if ($data['action'] === 'ban') {
            $author = $report->post->author;
            abort_if($author->id === $request->user()->id, 422, 'No podés banearte a vos mismo.');
            $author->banUntil(now()->addMinutes((int) $data['minutes']));
        } elseif ($data['action'] === 'delete') {
            $report->post->delete();
        }

        $report->forceFill([
            'status' => $data['action'] === 'dismiss' ? 'dismissed' : 'resolved',
            'resolved_by_id' => $request->user()->id,
        ])->save();

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

    /**
     * Valida una duración de ban contra las opciones ofrecidas al admin.
     */
    private function validatedMinutes(Request $request): int
    {
        return (int) $request->validate([
            'minutes' => ['required', 'integer', Rule::in(array_keys(User::BAN_DURATIONS))],
        ])['minutes'];
    }
}

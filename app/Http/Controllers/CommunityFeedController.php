<?php

namespace App\Http\Controllers;

use App\Models\BibleVerse;
use App\Models\ProfileComment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador del feed general de la comunidad.
 *
 * Muestra publicaciones de muros con pestañas (Todo, Muro, Mis posts),
 * personas sugeridas y estado de seguimiento.
 */
class CommunityFeedController extends Controller
{
    /**
     * Muestra la vista de comunidad con el feed y pestañas.
     *
     * @param  Request  $request  Solicitud HTTP autenticada.
     * @return Response Página Inertia del feed.
     */
    public function index(Request $request): Response
    {
        $viewer = $request->user();
        $tab = (string) $request->query('tab', 'todo');
        if (! in_array($tab, ['todo', 'muro', 'mios'], true)) {
            $tab = 'todo';
        }

        $followingIds = $viewer->following()->pluck('users.id')->all();
        $feedIds = array_values(array_unique(array_merge($followingIds, [$viewer->id])));

        $query = ProfileComment::query()
            ->whereNull('parent_id')
            ->with([
                'author:id,name,email,slug,church',
                'profileUser:id,name,slug',
                'reactions:id,profile_comment_id,user_id,type',
            ])
            ->withCount('replies');

        if ($tab === 'mios') {
            $query->where('user_id', $viewer->id);
        } elseif ($tab === 'todo') {
            $query->whereIn('user_id', $feedIds);
        }
        // tab "muro": todas las publicaciones de muros

        $comments = $query->latest()->limit(50)->get();

        $posts = $comments->map(function (ProfileComment $comment) use ($viewer) {
            $author = $comment->author;
            $profile = $comment->profileUser;
            $reactionCount = $comment->relationLoaded('reactions') ? $comment->reactions->count() : 0;

            $verse = null;
            $verseText = $this->verseTextFor($comment);
            if ($comment->hasVerse() && $verseText) {
                $name = $comment->book_name ?: $comment->book_code;
                $verse = [
                    'reference' => "{$name} {$comment->chapter}:{$comment->verse}",
                    'text' => $verseText,
                ];
            }

            return [
                'id' => $comment->id,
                'body' => $comment->body,
                'time' => $comment->created_at?->diffForHumans(),
                'createdAt' => $comment->created_at?->format('Y-m-d H:i'),
                'wall' => $profile ? [
                    'name' => $profile->name,
                    'slug' => $profile->slug,
                ] : null,
                'author' => [
                    'id' => $author?->id,
                    'name' => $author?->name ?? 'Usuario',
                    'slug' => $author?->slug,
                    'avatar' => $this->avatarFor($author),
                    'church' => $this->churchFor($author),
                ],
                'verse' => $verse,
                'stats' => [
                    'replies' => (int) ($comment->replies_count ?? 0),
                    'reactions' => $reactionCount,
                ],
                'isMine' => $author && $author->id === $viewer->id,
            ];
        })->values()->all();

        $people = User::query()
            ->where('id', '!=', $viewer->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'slug', 'church'])
            ->map(function (User $user) use ($viewer) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'slug' => $user->slug,
                    'avatar' => $this->avatarFor($user),
                    'church' => $this->churchFor($user),
                    'following' => $viewer->isFollowing($user),
                ];
            })
            ->values()
            ->all();

        return inertia('Community/Feed', [
            'tab' => $tab,
            'people' => $people,
            'posts' => $posts,
        ]);
    }

    /**
     * Obtiene el texto del versículo citado por un comentario, si aplica.
     */
    private function verseTextFor(ProfileComment $comment): ?string
    {
        if (! $comment->hasVerse()) {
            return null;
        }

        return BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->where('book_code', $comment->book_code)
            ->where('chapter', $comment->chapter)
            ->where('verse', $comment->verse)
            ->value('text');
    }

    /**
     * Devuelve la URL de avatar conocida por usuario.
     */
    private function avatarFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $byEmail = [
            'giovanni.arestivo@example.test' => '/images/testimonials/giovanni-arestivo.png',
            'piero.arestivo@example.test' => '/images/testimonials/piero-arestivo.png',
            'jose.britez@example.test' => '/images/testimonials/jose-britez.jpg',
        ];

        if (isset($byEmail[$user->email])) {
            return $byEmail[$user->email];
        }

        $byName = [
            'Giovanni Arestivo' => '/images/testimonials/giovanni-arestivo.png',
            'Piero Arestivo' => '/images/testimonials/piero-arestivo.png',
            'José Brítez' => '/images/testimonials/jose-britez.jpg',
        ];

        return $byName[$user->name] ?? null;
    }

    /**
     * Devuelve la iglesia del usuario (DB o mapeo conocido).
     */
    private function churchFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if (! empty($user->church)) {
            return $user->church;
        }

        $isGiovanni = $user->slug === 'giovanni-arestivo'
            || $user->email === 'giovanni.arestivo@example.test'
            || $user->name === 'Giovanni Arestivo';
        if ($isGiovanni) {
            return 'Iglesia Menonita Concordia';
        }

        $isPiero = $user->slug === 'piero-arestivo'
            || $user->email === 'piero.arestivo@example.test'
            || $user->name === 'Piero Arestivo';
        if ($isPiero) {
            return 'Iglesia Menonita Concordia';
        }

        $isJose = $user->slug === 'jose-britez'
            || $user->email === 'jose.britez@example.test'
            || $user->name === 'José Brítez';
        if ($isJose) {
            return 'Iglesia Pentecostal de Factorio';
        }

        return null;
    }
}
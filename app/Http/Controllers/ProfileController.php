<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador de perfil público estilo red social.
 */
class ProfileController extends Controller
{
    public function __construct(private PostController $postPresenter) {}

    /**
     * Muestra el perfil de un usuario y su línea de tiempo.
     */
    public function show(Request $request, User $user): Response
    {
        $viewer = $request->user();
        $tab = (string) $request->query('tab', 'posts');
        if (! in_array($tab, ['posts', 'respuestas', 'me-gusta'], true)) {
            $tab = 'posts';
        }

        $query = Post::query()
            ->with($this->postPresenter->feedRelations())
            ->withCount(['replies', 'likedBy', 'rebibles']);

        if ($tab === 'posts') {
            $query->where('user_id', $user->id)->whereNull('parent_id');
        } elseif ($tab === 'respuestas') {
            $query->where('user_id', $user->id)->whereNotNull('parent_id');
        } else { // me-gusta
            $likedIds = $user->likedPosts()->pluck('posts.id');
            $query->whereIn('id', $likedIds);
        }

        $posts = $query->latest()->limit(60)->get();
        $viewerState = $this->postPresenter->viewerStateFor($posts, $viewer);

        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();
        $postsCount = Post::query()->where('user_id', $user->id)->whereNull('parent_id')->count();

        $profileGoals = null;
        $isGiovanni = $user->slug === 'giovanni-arestivo' || $user->email === 'giovanni.arestivo@example.test';
        if ($isGiovanni) {
            $supporters = User::query()
                ->whereIn('email', ['piero.arestivo@example.test', 'jose.britez@example.test'])
                ->get(['id', 'name', 'email', 'slug', 'church'])
                ->map(fn (User $s) => $this->postPresenter->serializePerson($s, $viewer))
                ->values()->all();

            $profileGoals = [
                'title' => 'Metas de Giovanni',
                'supporters' => $supporters,
                'items' => [
                    ['key' => 'ayuno', 'label' => 'Ayuno', 'description' => 'Racha de ayuno personal', 'current' => 1, 'total' => 7, 'unit' => 'días'],
                    ['key' => 'abstinencia-sexual', 'label' => 'Abstinencia sexual', 'description' => 'Compromiso de pureza y autocuidado', 'current' => 3, 'total' => 30, 'unit' => 'días'],
                    ['key' => 'maraton-biblico', 'label' => 'Maratón bíblico: Evangelio de Juan', 'description' => 'Lectura y meditación capítulo por capítulo', 'current' => 2, 'total' => 21, 'unit' => 'capítulos'],
                ],
            ];
        }

        return inertia('Profile/Show', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'avatar' => $user->avatarUrl(),
                'church' => $user->churchName(),
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
                'postsCount' => $postsCount,
                'isFollowing' => $viewer ? $viewer->isFollowing($user) : false,
                'isOwn' => $viewer?->id === $user->id,
            ],
            'tab' => $tab,
            'posts' => $posts->map(fn (Post $post) => $this->postPresenter->serializePost($post, $viewer, $viewerState))->values()->all(),
            'profileGoals' => $profileGoals,
        ]);
    }
}

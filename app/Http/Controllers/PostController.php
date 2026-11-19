<?php

namespace App\Http\Controllers;

use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador de publicaciones (posts) estilo red social.
 */
class PostController extends Controller
{
    /**
     * Muestra el feed principal (Comunidad / Inicio).
     *
     * @param  Request  $request  Solicitud autenticada.
     * @return Response Página Inertia del feed.
     */
    public function index(Request $request): Response
    {
        $viewer = $request->user();
        $tab = (string) $request->query('tab', 'para_ti');
        if (! in_array($tab, ['para_ti', 'siguiendo', 'mios'], true)) {
            $tab = 'para_ti';
        }

        $followingIds = $viewer->following()->pluck('users.id')->all();

        $query = Post::query()
            ->whereNull('parent_id')
            ->with($this->feedRelations())
            ->withCount(['replies', 'likedBy', 'rebibles']);

        if ($tab === 'siguiendo') {
            $ids = array_values(array_unique(array_merge($followingIds, [$viewer->id])));
            $query->whereIn('user_id', $ids);
        } elseif ($tab === 'mios') {
            $query->where('user_id', $viewer->id);
        }
        // para_ti: todas las publicaciones

        $posts = $query->latest()->limit(60)->get();

        $people = User::query()
            ->where('id', '!=', $viewer->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'slug', 'church'])
            ->map(fn (User $user) => $this->serializePerson($user, $viewer))
            ->values()
            ->all();

        return inertia('Community/Feed', [
            'tab' => $tab,
            'people' => $people,
            'posts' => $posts->map(fn (Post $post) => $this->serializePost($post, $viewer))->values()->all(),
        ]);
    }

    /**
     * Crea una nueva publicación.
     *
     * @param  Request  $request  Datos del post.
     * @return RedirectResponse Redirección al feed o atrás.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'book_code' => 'nullable|string|max:10',
            'book_name' => 'nullable|string|max:120',
            'chapter' => 'nullable|integer|min:1',
            'verse' => 'nullable|integer|min:1',
        ]);

        Post::query()->create([
            'user_id' => $request->user()->id,
            'parent_id' => null,
            'rebible_of_id' => null,
            'body' => $data['body'],
            'book_code' => $data['book_code'] ?? null,
            'book_name' => $data['book_name'] ?? null,
            'chapter' => $data['chapter'] ?? null,
            'verse' => $data['verse'] ?? null,
        ]);

        return back();
    }

    /**
     * Muestra una publicación y sus respuestas.
     *
     * @param  Request  $request  Solicitud actual.
     * @param  Post  $post  Publicación a mostrar.
     * @return Response Página Inertia del detalle.
     */
    public function show(Request $request, Post $post): Response
    {
        $viewer = $request->user();

        $post->load($this->feedRelations())->loadCount(['replies', 'likedBy', 'rebibles']);

        $replies = Post::query()
            ->where('parent_id', $post->id)
            ->with($this->feedRelations())
            ->withCount(['replies', 'likedBy', 'rebibles'])
            ->oldest()
            ->get();

        return inertia('Posts/Show', [
            'post' => $this->serializePost($post, $viewer),
            'replies' => $replies->map(fn (Post $reply) => $this->serializePost($reply, $viewer))->values()->all(),
        ]);
    }

    /**
     * Publica una respuesta a una publicación.
     *
     * @param  Request  $request  Datos de la respuesta.
     * @param  Post  $post  Publicación padre.
     * @return RedirectResponse Redirección atrás.
     */
    public function reply(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'book_code' => 'nullable|string|max:10',
            'book_name' => 'nullable|string|max:120',
            'chapter' => 'nullable|integer|min:1',
            'verse' => 'nullable|integer|min:1',
        ]);

        Post::query()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $post->id,
            'rebible_of_id' => null,
            'body' => $data['body'],
            'book_code' => $data['book_code'] ?? null,
            'book_name' => $data['book_name'] ?? null,
            'chapter' => $data['chapter'] ?? null,
            'verse' => $data['verse'] ?? null,
        ]);

        return back();
    }

    /**
     * Relaciones necesarias para serializar posts en feed.
     *
     * @return array<int, mixed>
     */
    public function feedRelations(): array
    {
        return [
            'author:id,name,email,slug,church',
            'rebibleOf' => function ($q): void {
                $q->with(['author:id,name,email,slug,church'])->withCount(['replies', 'likedBy', 'rebibles']);
            },
        ];
    }

    /**
     * Serializa una publicación para Inertia/Vue.
     *
     * @return array<string, mixed>
     */
    public function serializePost(Post $post, ?User $viewer): array
    {
        $viewerId = $viewer?->id;
        $likedByViewer = $viewerId ? $post->likedBy()->where('users.id', $viewerId)->exists() : false;
        $rebibledByViewer = $viewerId ? $post->rebibles()->where('user_id', $viewerId)->exists() : false;

        $original = null;
        if ($post->isRebible() && $post->relationLoaded('rebibleOf') && $post->rebibleOf) {
            $orig = $post->rebibleOf;
            $origLiked = $viewerId ? $orig->likedBy()->where('users.id', $viewerId)->exists() : false;
            $origRebibled = $viewerId ? $orig->rebibles()->where('user_id', $viewerId)->exists() : false;
            $original = $this->serializePostCore($orig, $viewer, $origLiked, $origRebibled);
        }

        $data = $this->serializePostCore($post, $viewer, $likedByViewer, $rebibledByViewer);
        $data['original'] = $original;
        $data['isRebible'] = $post->isRebible();

        return $data;
    }

    /**
     * Serializa campos base de un post.
     *
     * @return array<string, mixed>
     */
    private function serializePostCore(Post $post, ?User $viewer, bool $likedByViewer, bool $rebibledByViewer): array
    {
        $verse = null;
        if ($post->hasVerse()) {
            $text = BibleVerse::query()
                ->where('translation', 'spa_rvg')
                ->where('book_code', $post->book_code)
                ->where('chapter', $post->chapter)
                ->where('verse', $post->verse)
                ->value('text');
            $name = $post->book_name ?: $post->book_code;
            $verse = [
                'reference' => "{$name} {$post->chapter}:{$post->verse}",
                'text' => $text,
                'book' => $post->book_code,
                'chapter' => $post->chapter,
                'verse' => $post->verse,
            ];
        }

        return [
            'id' => $post->id,
            'body' => $post->body,
            'time' => $post->created_at?->diffForHumans(),
            'createdAt' => $post->created_at?->format('Y-m-d H:i'),
            'author' => $this->serializeAuthor($post->author),
            'verse' => $verse,
            'counts' => [
                'replies' => (int) ($post->replies_count ?? $post->replies()->count()),
                'likes' => (int) ($post->liked_by_count ?? $post->likedBy()->count()),
                'rebibles' => (int) ($post->rebibles_count ?? $post->rebibles()->count()),
            ],
            'viewer' => [
                'liked' => $likedByViewer,
                'rebibled' => $rebibledByViewer,
            ],
            'parentId' => $post->parent_id,
            'rebibleOfId' => $post->rebible_of_id,
        ];
    }

    /**
     * Serializa una persona para listados.
     *
     * @return array<string, mixed>
     */
    public function serializePerson(User $user, ?User $viewer): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'avatar' => $this->avatarFor($user),
            'church' => $this->churchFor($user),
            'following' => $viewer ? $viewer->isFollowing($user) : false,
        ];
    }

    /**
     * Serializa autor.
     *
     * @return array<string, mixed>
     */
    public function serializeAuthor(?User $user): array
    {
        if (! $user) {
            return ['id' => null, 'name' => 'Usuario', 'slug' => null, 'avatar' => null, 'church' => null];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'avatar' => $this->avatarFor($user),
            'church' => $this->churchFor($user),
        ];
    }

    public function avatarFor(?User $user): ?string
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

    public function churchFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }
        if (! empty($user->church)) {
            return $user->church;
        }

        $isGiovanni = $user->slug === 'giovanni-arestivo' || $user->email === 'giovanni.arestivo@example.test' || $user->name === 'Giovanni Arestivo';
        if ($isGiovanni) {
            return 'Iglesia Menonita Concordia';
        }
        $isPiero = $user->slug === 'piero-arestivo' || $user->email === 'piero.arestivo@example.test' || $user->name === 'Piero Arestivo';
        if ($isPiero) {
            return 'Iglesia Menonita Concordia';
        }
        $isJose = $user->slug === 'jose-britez' || $user->email === 'jose.britez@example.test' || $user->name === 'José Brítez';
        if ($isJose) {
            return 'Iglesia Pentecostal de Factorio';
        }

        return null;
    }
}
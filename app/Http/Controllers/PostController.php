<?php

namespace App\Http\Controllers;

use App\Models\BibleVerse;
use App\Models\Post;
use App\Models\User;
use App\Services\VerseMetadata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

/**
 * Controlador de publicaciones (posts) estilo red social.
 */
class PostController extends Controller
{
    /**
     * Muestra el feed principal (Comunidad / Inicio).
     *
     * Pestañas:
     * - para_ti: publicaciones de toda la comunidad.
     * - siguiendo: publicaciones de personas seguidas + las propias.
     * - mios: publicaciones propias.
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

        $posts = $query->latest()->limit(60)->get();
        $viewerState = $this->viewerStateFor($posts, $viewer);

        $people = User::query()
            ->where('id', '!=', $viewer->id)
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'slug', 'church'])
            ->map(fn (User $user) => $this->serializePerson($user, $viewer, $followingIds))
            ->values()
            ->all();

        return inertia('Community/Feed', [
            'tab' => $tab,
            'people' => $people,
            'posts' => $posts->map(fn (Post $post) => $this->serializePost($post, $viewer, $viewerState))->values()->all(),
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
        $data = $request->validate(
            [
                'body' => 'required|string|max:2000',
                'kind' => ['nullable', Rule::in(Post::SELECTABLE_KINDS)],
                'book_code' => 'nullable|string|max:10',
                'chapter' => 'nullable|integer|min:1',
                'verse' => 'nullable|integer|min:1',
            ],
            [
                'body.required' => 'Escribí algo antes de publicar.',
                'body.max' => 'La publicación no puede superar los 2000 caracteres.',
                'kind.in' => 'Elegí una opción válida de comunidad.',
            ],
        );

        $metadata = $this->referenceMetadata($data);

        Post::query()->create([
            'user_id' => $request->user()->id,
            'parent_id' => null,
            'rebible_of_id' => null,
            'kind' => $data['kind'] ?? 'reflexion',
            'body' => trim($data['body']),
            'book_code' => $data['book_code'] ?? null,
            'book_name' => $metadata['book_name'] ?? null,
            'chapter' => $data['chapter'] ?? null,
            'verse' => $data['verse'] ?? null,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Tu publicación se compartió.']);
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

        // El hilo es plano en la UI: incluimos todos los descendientes, no solo
        // las respuestas directas, para que ninguna respuesta quede invisible.
        $replies = collect();
        $parentIds = [$post->id];
        $depth = 0;

        while ($parentIds !== [] && $depth < 20) {
            $batch = Post::query()
                ->whereIn('parent_id', $parentIds)
                ->with($this->feedRelations())
                ->withCount(['replies', 'likedBy', 'rebibles'])
                ->oldest()
                ->get();

            if ($batch->isEmpty()) {
                break;
            }

            $replies = $replies->concat($batch);
            $parentIds = $batch->pluck('id')->all();
            $depth++;
        }

        $allPosts = collect([$post])->concat($replies);
        $viewerState = $this->viewerStateFor($allPosts, $viewer);

        return inertia('Posts/Show', [
            'post' => $this->serializePost($post, $viewer, $viewerState),
            'replies' => $replies->map(fn (Post $reply) => $this->serializePost($reply, $viewer, $viewerState))->values()->all(),
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
        $data = $request->validate(
            [
                'body' => 'required|string|max:2000',
                'book_code' => 'nullable|string|max:10',
                'chapter' => 'nullable|integer|min:1',
                'verse' => 'nullable|integer|min:1',
            ],
            [
                'body.required' => 'Escribí una respuesta antes de enviarla.',
                'body.max' => 'La respuesta no puede superar los 2000 caracteres.',
            ],
        );

        $metadata = $this->referenceMetadata($data);

        Post::query()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $post->id,
            'rebible_of_id' => null,
            'body' => trim($data['body']),
            'book_code' => $data['book_code'] ?? null,
            'book_name' => $metadata['book_name'] ?? null,
            'chapter' => $data['chapter'] ?? null,
            'verse' => $data['verse'] ?? null,
        ]);

        return back()->with('toast', ['type' => 'success', 'message' => 'Tu respuesta se publicó.']);
    }

    /**
     * Resuelve la referencia opcional de una publicación.
     *
     * El cliente envía sólamente códigos; el nombre del libro se deriva en el
     * servidor para que un payload manipulado no pueda guardar otro texto.
     *
     * @param  array<string, mixed>  $data  Datos ya validados.
     * @return array{book_name: string, text: string}|null Null si no hay referencia.
     */
    private function referenceMetadata(array $data): ?array
    {
        $bookCode = isset($data['book_code']) ? (string) $data['book_code'] : null;
        $chapter = isset($data['chapter']) ? (int) $data['chapter'] : null;
        $verse = isset($data['verse']) ? (int) $data['verse'] : null;

        if (! $bookCode && ! $chapter && ! $verse) {
            return null;
        }

        $metadata = (new VerseMetadata)->forReference($bookCode, $chapter, $verse);

        if (! $metadata) {
            throw ValidationException::withMessages([
                'book_code' => 'Elegí un libro, capítulo y versículo válidos.',
            ]);
        }

        return $metadata;
    }

    /**
     * Relaciones necesarias para serializar posts en feed.
     *
     * @return array<int|string, mixed>
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
     * @param  array{likedIds: array<int,bool>, rebibledIds: array<int,bool>, verseTexts: array<string,string>}|null  $viewerState  Estado precalculado del visor.
     * @return array<string, mixed>
     */
    public function serializePost(Post $post, ?User $viewer, ?array $viewerState = null): array
    {
        $viewerId = $viewer?->id;
        $likedByViewer = $viewerState
            ? isset($viewerState['likedIds'][$post->id])
            : ($viewerId ? $post->likedBy()->where('users.id', $viewerId)->exists() : false);
        $rebibledByViewer = $viewerState
            ? isset($viewerState['rebibledIds'][$post->id])
            : ($viewerId ? $post->rebibles()->where('user_id', $viewerId)->exists() : false);

        $original = null;
        if ($post->isRebible() && $post->relationLoaded('rebibleOf') && $post->rebibleOf) {
            $orig = $post->rebibleOf;
            $origLiked = $viewerState
                ? isset($viewerState['likedIds'][$orig->id])
                : ($viewerId ? $orig->likedBy()->where('users.id', $viewerId)->exists() : false);
            $origRebibled = $viewerState
                ? isset($viewerState['rebibledIds'][$orig->id])
                : ($viewerId ? $orig->rebibles()->where('user_id', $viewerId)->exists() : false);
            $original = $this->serializePostCore($orig, $viewer, $origLiked, $origRebibled, $viewerState['verseTexts'] ?? []);
        }

        $data = $this->serializePostCore($post, $viewer, $likedByViewer, $rebibledByViewer, $viewerState['verseTexts'] ?? []);
        $data['original'] = $original;
        $data['isRebible'] = $post->isRebible();

        return $data;
    }

    /**
     * Precálcula likes/rebibleos del visor para un conjunto de posts.
     * Evita consultas N+1 al serializar feeds.
     *
     * @param  iterable<Post>  $posts
     * @return array{likedIds: array<int,bool>, rebibledIds: array<int,bool>, verseTexts: array<string,string>}
     */
    public function viewerStateFor(iterable $posts, ?User $viewer): array
    {
        $ids = [];
        $references = [];
        foreach ($posts as $post) {
            $ids[] = $post->id;
            if ($post->hasVerse()) {
                $references[] = [
                    'book_code' => $post->book_code,
                    'chapter' => $post->chapter,
                    'verse' => $post->verse,
                ];
            }
            if ($post->relationLoaded('rebibleOf') && $post->rebibleOf) {
                $ids[] = $post->rebibleOf->id;
                if ($post->rebibleOf->hasVerse()) {
                    $references[] = [
                        'book_code' => $post->rebibleOf->book_code,
                        'chapter' => $post->rebibleOf->chapter,
                        'verse' => $post->rebibleOf->verse,
                    ];
                }
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        $verseTexts = (new VerseMetadata)->textsFor($references);

        if (! $viewer || empty($ids)) {
            return ['likedIds' => [], 'rebibledIds' => [], 'verseTexts' => $verseTexts];
        }

        $likedIds = $viewer->likedPosts()->whereIn('posts.id', $ids)->pluck('posts.id')->flip()->map(fn () => true)->all();
        $rebibledIds = Post::query()
            ->where('user_id', $viewer->id)
            ->whereIn('rebible_of_id', $ids)
            ->pluck('rebible_of_id')
            ->flip()
            ->map(fn () => true)
            ->all();

        return ['likedIds' => $likedIds, 'rebibledIds' => $rebibledIds, 'verseTexts' => $verseTexts];
    }

    /**
     * Serializa campos base de un post.
     *
     * @param  array<string, string>  $verseTexts  Textos precargados en lote (si faltan, se consulta uno).
     * @return array<string, mixed>
     */
    private function serializePostCore(Post $post, ?User $viewer, bool $likedByViewer, bool $rebibledByViewer, array $verseTexts = []): array
    {
        $verse = null;
        if ($post->hasVerse()) {
            $key = VerseMetadata::key((string) $post->book_code, (int) $post->chapter, (int) $post->verse);
            $text = array_key_exists($key, $verseTexts)
                ? $verseTexts[$key]
                : BibleVerse::query()
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
            'kind' => $post->isRebible() ? 'rebibleada' : (in_array($post->kind, Post::KINDS, true) ? $post->kind : 'reflexion'),
        ];
    }

    /**
     * Serializa una persona para listados (personas a seguir, seguidores, etc.).
     *
     * @param  array<int, int>  $followingIds  IDs que el visitante ya sigue.
     * @return array<string, mixed>
     */
    public function serializePerson(User $user, ?User $viewer, array $followingIds = []): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'slug' => $user->slug,
            'avatar' => $user->avatarUrl(),
            'church' => $user->churchName(),
            'following' => $viewer ? in_array($user->id, $followingIds, true) : false,
        ];
    }

    /**
     * Serializa el autor de una publicación.
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
            'avatar' => $user->avatarUrl(),
            'church' => $user->churchName(),
        ];
    }
}

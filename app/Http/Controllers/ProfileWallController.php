<?php

namespace App\Http\Controllers;

use App\Models\BibleVerse;
use App\Models\ProfileComment;
use App\Models\ProfileCommentReaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

/**
 * Controlador del muro de comentarios de un perfil.
 *
 * Muestra el "home" público del perfil con sus comentarios en hilo y
 * permite a usuarios autenticados publicar comentarios y respuestas.
 */
class ProfileWallController extends Controller
{
    /**
     * Muestra el muro de comentarios del perfil indicado.
     *
     * @param  Request  $request  Solicitud actual (para identificar al visor).
     * @param  User  $user  Usuario dueño del perfil/muro.
     * @return Response Página Inertia del muro.
     */
    public function show(Request $request, User $user): Response
    {
        $viewer = $request->user();
        $viewerId = $viewer?->id;

        $comments = ProfileComment::query()
            ->where('profile_user_id', $user->id)
            ->whereNull('parent_id')
            ->with([
                'author:id,name,email,slug,church',
                'reactions:id,profile_comment_id,user_id,type',
                'replies' => function ($query): void {
                    $query->with([
                        'author:id,name,email,slug,church',
                        'reactions:id,profile_comment_id,user_id,type',
                    ]);
                },
            ])
            ->oldest()
            ->get();

        $isFollowing = $viewer ? $viewer->isFollowing($user) : false;
        $followersCount = $user->followers()->count();
        $followingCount = $user->following()->count();

        return inertia('Profile/Show', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
                'avatar' => $this->avatarFor($user),
                'church' => $this->churchFor($user),
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
                'isFollowing' => $isFollowing,
                'isOwn' => $viewerId !== null && $viewerId === $user->id,
            ],
            'comments' => $comments->map(fn (ProfileComment $comment) => $this->serializeComment($comment, $viewerId)),
            'profileGoals' => $this->profileGoalsFor($user),
        ]);
    }

    /**
     * Publica un comentario o respuesta en el muro del perfil.
     *
     * @param  Request  $request  Datos del comentario/respuesta.
     * @param  User  $user  Usuario dueño del muro.
     * @return RedirectResponse Redirección de vuelta al muro.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|integer|exists:profile_comments,id',
            'book_code' => 'nullable|string|max:10',
            'book_name' => 'nullable|string|max:120',
            'chapter' => 'nullable|integer|min:1',
            'verse' => 'nullable|integer|min:1',
        ]);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null) {
            $belongs = ProfileComment::query()
                ->where('id', $parentId)
                ->where('profile_user_id', $user->id)
                ->exists();
            if (! $belongs) {
                abort(422, 'El comentario al que respondés no pertenece a este muro.');
            }
        }

        ProfileComment::query()->create([
            'profile_user_id' => $user->id,
            'user_id' => $request->user()->id,
            'parent_id' => $parentId,
            'body' => $data['body'],
            'book_code' => $data['book_code'] ?? null,
            'book_name' => $data['book_name'] ?? null,
            'chapter' => $data['chapter'] ?? null,
            'verse' => $data['verse'] ?? null,
        ]);

        return back();
    }

    /**
     * Alterna (activa/desactiva) una reacción del usuario en un comentario.
     *
     * @param  Request  $request  Solicitud con el tipo de reacción.
     * @param  User  $user  Usuario dueño del muro (validación de pertenencia).
     * @param  ProfileComment  $comment  Comentario objetivo.
     * @return RedirectResponse Redirección de vuelta al muro.
     */
    public function react(Request $request, User $user, ProfileComment $comment): RedirectResponse
    {
        if ($comment->profile_user_id !== $user->id) {
            abort(404);
        }

        $data = $request->validate([
            'type' => 'required|string|in:'.implode(',', ProfileCommentReaction::TYPES),
        ]);

        $viewerId = $request->user()->id;
        $type = $data['type'];

        $existing = ProfileCommentReaction::query()
            ->where('profile_comment_id', $comment->id)
            ->where('user_id', $viewerId)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ProfileCommentReaction::query()->create([
                'profile_comment_id' => $comment->id,
                'user_id' => $viewerId,
                'type' => $type,
            ]);
        }

        return back();
    }

    /**
     * Serializa un comentario (y sus respuestas) para Inertia.
     *
     * @return array<string, mixed>
     */
    private function serializeComment(ProfileComment $comment, ?int $viewerId = null): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'bookCode' => $comment->book_code,
            'bookName' => $comment->book_name,
            'chapter' => $comment->chapter,
            'verse' => $comment->verse,
            'verseText' => $this->verseTextFor($comment),
            'createdAt' => $comment->created_at?->format('Y-m-d H:i'),
            'author' => $this->serializeAuthor($comment->author),
            'reactions' => $this->serializeReactions($comment, $viewerId),
            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn (ProfileComment $reply) => [
                    'id' => $reply->id,
                    'body' => $reply->body,
                    'bookCode' => $reply->book_code,
                    'bookName' => $reply->book_name,
                    'chapter' => $reply->chapter,
                    'verse' => $reply->verse,
                    'verseText' => $this->verseTextFor($reply),
                    'createdAt' => $reply->created_at?->format('Y-m-d H:i'),
                    'author' => $this->serializeAuthor($reply->author),
                    'reactions' => $this->serializeReactions($reply, $viewerId),
                ])->values()->all()
                : [],
        ];
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
     * Resume las reacciones de un comentario (conteos y estado del visor).
     *
     * @return array<string, array{count:int, reacted:bool}>
     */
    private function serializeReactions(ProfileComment $comment, ?int $viewerId): array
    {
        $reactions = $comment->relationLoaded('reactions') ? $comment->reactions : collect();
        $summary = [];
        foreach (ProfileCommentReaction::TYPES as $type) {
            $items = $reactions->where('type', $type);
            $summary[$type] = [
                'count' => $items->count(),
                'reacted' => $viewerId !== null && $items->contains(fn ($r) => (int) $r->user_id === $viewerId),
            ];
        }

        return $summary;
    }

    /**
     * Serializa el autor de un comentario para la vista.
     *
     * @return array<string, mixed>
     */
    private function serializeAuthor(?User $user): array
    {
        if (! $user) {
            return ['id' => null, 'name' => 'Usuario', 'avatar' => null];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $this->avatarFor($user),
        ];
    }

    /**
     * Devuelve la URL de la foto de perfil conocida para testimonios, si existe.
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
     * Devuelve la iglesia del perfil, priorizando el dato en base y usando
     * mapeos conocidos como respaldo.
     *
     * @param  User  $user  Usuario del perfil.
     * @return string|null Nombre de la iglesia o null si no aplica.
     */
    private function churchFor(User $user): ?string
    {
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

    /**
     * Devuelve las metas activas del perfil, si corresponde.
     *
     * Giovanni lleva metas personales con el apoyo de los chicos.
     *
     * @return array<string, mixed>|null
     */
    private function profileGoalsFor(User $user): ?array
    {
        $isGiovanni = $user->slug === 'giovanni-arestivo'
            || $user->email === 'giovanni.arestivo@example.test'
            || $user->name === 'Giovanni Arestivo';

        if (! $isGiovanni) {
            return null;
        }

        $supporters = User::query()
            ->whereIn('email', [
                'piero.arestivo@example.test',
                'jose.britez@example.test',
            ])
            ->get(['id', 'name', 'email', 'slug'])
            ->map(fn (User $supporter) => [
                'id' => $supporter->id,
                'name' => $supporter->name,
                'slug' => $supporter->slug,
                'avatar' => $this->avatarFor($supporter),
            ])
            ->values()
            ->all();

        return [
            'title' => 'Metas de Giovanni',
            'supporters' => $supporters,
            'items' => [
                [
                    'key' => 'ayuno',
                    'label' => 'Ayuno',
                    'description' => 'Racha de ayuno personal',
                    'current' => 1,
                    'total' => 7,
                    'unit' => 'días',
                ],
                [
                    'key' => 'abstinencia-sexual',
                    'label' => 'Abstinencia sexual',
                    'description' => 'Compromiso de pureza y autocuidado',
                    'current' => 3,
                    'total' => 30,
                    'unit' => 'días',
                ],
                [
                    'key' => 'maraton-biblico',
                    'label' => 'Maratón bíblico: Evangelio de Juan',
                    'description' => 'Lectura y meditación capítulo por capítulo',
                    'current' => 2,
                    'total' => 21,
                    'unit' => 'capítulos',
                ],
            ],
        ];
    }
}

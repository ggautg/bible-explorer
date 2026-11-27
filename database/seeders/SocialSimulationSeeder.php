<?php

namespace Database\Seeders;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use App\Models\Favorite;
use App\Models\Note;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Red determinística de 50 personas de 10 iglesias: se siguen, publican,
 * comentan, rebiblean, guardan versículos, discuten, se dejan de seguir y
 * se reconcilian. La misma semilla reproduce exactamente los mismos datos.
 */
class SocialSimulationSeeder extends Seeder
{
    public const SEED = 20261126;

    public const ADMIN_EMAIL = 'admin@red.test';

    public const ADMIN_PASSWORD = 'password';

    /** @var Collection<int, User> */
    public Collection $users;

    /** @var Collection<int, Post> */
    public Collection $rootPosts;

    /** @var array<int, array<string, mixed>> */
    public array $conflicts = [];

    public int $breakups = 0;

    public int $reconciliations = 0;

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $postsPerChurch = DB::table('posts')
            ->join('users', 'posts.user_id', '=', 'users.id')
            ->whereNull('posts.parent_id')
            ->whereNull('posts.rebible_of_id')
            ->select('users.church', DB::raw('count(*) as total'))
            ->groupBy('users.church')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->church => (int) $row->total])
            ->all();

        $topFollowed = DB::table('follows')
            ->join('users', 'follows.followed_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => "{$row->name} ({$row->total})")
            ->all();

        return [
            'semilla' => self::SEED,
            'admin' => self::ADMIN_EMAIL.' / '.self::ADMIN_PASSWORD,
            'usuarios' => User::count(),
            'iglesias' => User::query()->distinct()->count('church'),
            'posts_raiz' => Post::query()->whereNull('parent_id')->whereNull('rebible_of_id')->count(),
            'respuestas' => Post::query()->whereNotNull('parent_id')->count(),
            'rebibleos' => Post::query()->whereNotNull('rebible_of_id')->count(),
            'me_gusta' => DB::table('post_likes')->count(),
            'favoritos' => Favorite::count(),
            'notas' => Note::count(),
            'seguimientos' => DB::table('follows')->count(),
            'rupturas' => $this->breakups,
            'reconciliaciones' => $this->reconciliations,
            'autoseguimientos' => DB::table('follows')->whereColumn('follower_id', 'followed_id')->count(),
            'posts_por_iglesia' => $postsPerChurch,
            'mas_seguidos' => $topFollowed,
            'conflictos' => $this->conflicts,
        ];
    }

    public function run(): void
    {
        mt_srand(self::SEED);

        $churches = [
            'Iglesia Menonita Concordia',
            'Pentecostal de Factorio',
            'Bautista Gracia',
            'Católica San José',
            'Evangélica Maranatha',
            'Metodista Bethel',
            'Presbiteriana Emaús',
            'Adventista Esperanza',
            'Nazarena Carmel',
            'Anglicana San Pedro',
        ];

        $firstNames = [
            'Ana', 'Lucas', 'Marta', 'Juan', 'Sofía', 'Pedro', 'Camila', 'Diego', 'Valeria', 'Mateo',
            'Laura', 'Nicolás', 'Florencia', 'Andrés', 'Julieta', 'Gustavo', 'Noelia', 'Emilio', 'Carolina', 'Iván',
            'Mariana', 'Rodrigo', 'Paula', 'Sebastián', 'Lucía', 'Federico', 'Antonella', 'Maximiliano', 'Agustina', 'Emiliano',
            'Victoria', 'Franco', 'Natalia', 'Cristian', 'Soledad', 'Ezequiel', 'Melisa', 'Gabriel', 'Verónica', 'Hernán',
            'Claudina', 'Omar', 'Silvina', 'Raúl', 'Eugenia', 'Marcos', 'Josefina', 'Damián', 'Teresa', 'Ángel',
        ];

        $lastNames = [
            'García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez', 'Sánchez', 'Pérez', 'Gómez', 'Martín',
            'Jiménez', 'Ruiz', 'Hernández', 'Díaz', 'Moreno', 'Álvarez', 'Romero', 'Navarro', 'Torres', 'Domínguez',
            'Vázquez', 'Ríos', 'Gil', 'Ramírez', 'Serrano', 'Blanco', 'Molina', 'Morales', 'Suárez', 'Ortega',
            'Delgado', 'Castro', 'Ortiz', 'Rubio', 'Núñez', 'Medina', 'Garrido', 'Cortés', 'Castillo', 'Santos',
            'Lozano', 'Guerrero', 'Cano', 'Prieto', 'Méndez', 'Cruz', 'Calvo', 'Gallego', 'Vidal', 'León',
        ];

        $books = [
            ['code' => 'GEN', 'name' => 'Génesis', 'testament' => 'OT', 'order' => 1],
            ['code' => 'JHN', 'name' => 'Juan', 'testament' => 'NT', 'order' => 2],
            ['code' => 'PSA', 'name' => 'Salmos', 'testament' => 'OT', 'order' => 3],
        ];
        foreach ($books as $book) {
            BibleBook::query()->firstOrCreate(['code' => $book['code']], $book);
        }

        $references = [
            ['book_code' => 'GEN', 'book_name' => 'Génesis', 'chapter' => 1, 'verse' => 1, 'text' => 'En el principio creó Dios los cielos y la tierra.'],
            ['book_code' => 'JHN', 'book_name' => 'Juan', 'chapter' => 3, 'verse' => 16, 'text' => 'Porque de tal manera amó Dios al mundo.'],
            ['book_code' => 'PSA', 'book_name' => 'Salmos', 'chapter' => 23, 'verse' => 1, 'text' => 'Jehová es mi pastor, nada me faltará.'],
        ];
        foreach ($references as $reference) {
            BibleVerse::query()->firstOrCreate(
                [
                    'book_code' => $reference['book_code'],
                    'translation' => 'spa_rvg',
                    'chapter' => $reference['chapter'],
                    'verse' => $reference['verse'],
                ],
                ['text' => $reference['text']],
            );
        }

        $this->users = collect(range(0, 49))->map(function (int $index) use ($firstNames, $lastNames, $churches): User {
            $isAdmin = $index === 0;

            return User::factory()->create([
                'name' => $isAdmin ? 'Super Admin' : ($firstNames[$index] ?? 'Usuario').' '.($lastNames[$index] ?? 'Red'),
                'email' => $isAdmin ? self::ADMIN_EMAIL : sprintf('red-%02d@example.test', $index + 1),
                'slug' => $isAdmin ? 'super-admin' : 'red-'.($index + 1),
                'church' => $churches[intdiv($index, 5)],
                'is_admin' => $isAdmin,
            ]);
        });

        $pick = function (Collection $collection) {
            return $collection->values()->all()[mt_rand(0, $collection->count() - 1)];
        };

        foreach ($this->users as $user) {
            $sameChurch = $this->users
                ->filter(fn (User $candidate) => $candidate->id !== $user->id && $candidate->church === $user->church)
                ->sortBy(fn () => mt_rand())
                ->take(3);
            $otherChurches = $this->users
                ->filter(fn (User $candidate) => $candidate->church !== $user->church)
                ->sortBy(fn () => mt_rand())
                ->take(2);

            foreach ($sameChurch->concat($otherChurches) as $target) {
                $user->follow($target);
            }
        }

        $kinds = Post::SELECTABLE_KINDS;
        $this->rootPosts = collect();
        foreach ($this->users as $index => $user) {
            $amount = 1 + ($index % 2);
            for ($postNumber = 0; $postNumber < $amount; $postNumber++) {
                $attributes = [
                    'user_id' => $user->id,
                    'kind' => $kinds[($index + $postNumber) % count($kinds)],
                    'body' => "Mensaje {$postNumber} de {$user->name}: gracias por compartir la Palabra.",
                ];

                if (($index + $postNumber) % 3 === 0) {
                    $reference = $references[($index + $postNumber) % count($references)];
                    $attributes = array_merge($attributes, array_intersect_key($reference, array_flip(['book_code', 'book_name', 'chapter', 'verse'])));
                }

                $this->rootPosts->push(Post::query()->create($attributes));
            }
        }

        foreach (range(1, 140) as $attempt) {
            $liker = $pick($this->users);
            $post = $pick($this->rootPosts);
            if ($post->user_id === $liker->id) {
                continue;
            }
            $liker->likedPosts()->syncWithoutDetaching([$post->id]);
        }

        $replyBodies = [
            'Amén, también lo viví así en mi congregación.',
            'Gracias por compartirlo, me sirvió para hoy.',
            '¿Lo podemos leer también en el capítulo siguiente?',
            'No comparto esa lectura; bajemos un cambio el tono, por favor.',
            'Pensá lo que escribís, esto se puede decir con respeto.',
        ];
        foreach (range(1, 80) as $index) {
            $parent = $pick($this->rootPosts);
            $author = $pick($this->users->filter(fn (User $candidate) => $candidate->id !== $parent->user_id));
            Post::query()->create([
                'user_id' => $author->id,
                'parent_id' => $parent->id,
                'kind' => 'reflexion',
                'body' => $replyBodies[$index % count($replyBodies)],
            ]);
        }

        foreach (range(1, 40) as $attempt) {
            $rebibler = $pick($this->users);
            $original = $pick($this->rootPosts->filter(fn (Post $post) => $post->user_id !== $rebibler->id));
            Post::query()->create([
                'user_id' => $rebibler->id,
                'kind' => 'rebibleada',
                'rebible_of_id' => $original->id,
                'body' => null,
            ]);
        }

        foreach ($this->users->take(30) as $user) {
            $reference = $pick(collect($references));
            Favorite::query()->create(array_merge(['user_id' => $user->id], array_intersect_key($reference, array_flip(['book_code', 'book_name', 'chapter', 'verse', 'text']))));
            Note::query()->create(array_merge([
                'user_id' => $user->id,
                'body' => 'Nota personal de '.$user->name.' sobre este versículo.',
            ], array_intersect_key($reference, array_flip(['book_code', 'book_name', 'chapter', 'verse']))));
        }

        foreach (range(0, 7) as $pair) {
            $first = $this->users[$pair * 3 + 1];
            $second = $this->users[$pair * 3 + 2];
            $first->follow($second);
            $second->follow($first);

            $root = $this->rootPosts->first(fn (Post $post) => $post->user_id === $first->id)
                ?? $this->rootPosts->first(fn (Post $post) => $post->user_id === $second->id);
            $attack = Post::query()->create([
                'user_id' => $second->id,
                'parent_id' => $root->id,
                'kind' => 'reflexion',
                'body' => 'No estoy de acuerdo con esa interpretación y la forma en que lo escribiste.',
            ]);
            Post::query()->create([
                'user_id' => $first->id,
                'parent_id' => $attack->id,
                'kind' => 'reflexion',
                'body' => 'Puedo discutir el texto, pero no voy a aceptar ese tono.',
            ]);

            $first->unfollow($second);
            $second->unfollow($first);
            $this->breakups++;

            $reconciled = $pair < 3;
            if ($reconciled) {
                $first->follow($second);
                $second->follow($first);
                $this->reconciliations++;
            }

            $this->conflicts[] = [
                'pareja' => $first->name.' ↔ '.$second->name,
                'iglesias' => $first->church.' / '.$second->church,
                'resultado' => $reconciled ? 'ruptura y reconciliación' : 'ruptura',
                'hilo' => [
                    $second->name.': '.$attack->body,
                    $first->name.': Puedo discutir el texto, pero no voy a aceptar ese tono.',
                ],
            ];
        }

        $stats = $this->stats();
        $snapshot = (string) getenv('LOCALAPPDATA').'\Temp\opencode\social-network-simulation.json';
        if (is_dir(dirname($snapshot))) {
            file_put_contents($snapshot, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $this->command?->info('Simulación de red social: '.json_encode($stats, JSON_UNESCAPED_UNICODE));
    }
}

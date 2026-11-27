<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Semilla del debate entre María Conchita y José López.
 *
 * Deja dos cuentas nuevas con un hilo de ofensas y un reporte cruzado para que
 * el admin pueda revisar cada caso desde el panel.
 */
class DebateBanSeeder extends Seeder
{
    public const MARIA_EMAIL = 'maria.conchita@example.test';

    public const JOSE_EMAIL = 'jose.lopez@example.test';

    /**
     * Carga las cuentas y reconstruye el hilo de forma idempotente.
     */
    public function run(): void
    {
        $maria = User::firstOrCreate(
            ['email' => self::MARIA_EMAIL],
            [
                'name' => 'María Conchita',
                'slug' => 'maria-conchita',
                'church' => 'Bautista Gracia',
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $jose = User::firstOrCreate(
            ['email' => self::JOSE_EMAIL],
            [
                'name' => 'José López',
                'slug' => 'jose-lopez',
                'church' => 'Presbiteriana Emaús',
                'email_verified_at' => now(),
                'password' => 'password',
            ],
        );

        $bodies = [
            'José, tu interpretación de Juan 3:16 es una simple tontería: no sabés leer un texto sin meterle tu propia idea.',
            'María, dejá de escribir disparates. Tu comentario es ignorancia pura y una pérdida de tiempo para todos.',
            '¿Ignorancia? Al menos no repito frases aprendidas de memoria como vos, que ni sabés defender lo que escribís.',
            'Qué ridiculez decir eso en público. Conchita, andá a estudiar antes de dar lecciones de fe a nadie.',
            'Sos un soberano payaso, José. Tu orgullo no te deja aceptar que estás equivocado.',
        ];

        Post::query()
            ->whereIn('user_id', [$maria->id, $jose->id])
            ->whereIn('body', $bodies)
            ->delete();

        $root = Post::query()->create([
            'user_id' => $maria->id,
            'kind' => 'reflexion',
            'body' => $bodies[0],
        ]);

        $reply = Post::query()->create([
            'user_id' => $jose->id,
            'parent_id' => $root->id,
            'kind' => 'reflexion',
            'body' => $bodies[1],
        ]);

        $counterReply = Post::query()->create([
            'user_id' => $maria->id,
            'parent_id' => $reply->id,
            'kind' => 'reflexion',
            'body' => $bodies[2],
        ]);

        $otherRoot = Post::query()->create([
            'user_id' => $jose->id,
            'kind' => 'reflexion',
            'body' => $bodies[3],
        ]);

        $insult = Post::query()->create([
            'user_id' => $maria->id,
            'parent_id' => $otherRoot->id,
            'kind' => 'reflexion',
            'body' => $bodies[4],
        ]);

        Report::query()->firstOrCreate(
            ['reporter_id' => $maria->id, 'post_id' => $reply->id],
            ['reason' => 'Me llamó ignorante y se rió de mi fe en público.'],
        );

        Report::query()->firstOrCreate(
            ['reporter_id' => $jose->id, 'post_id' => $insult->id],
            ['reason' => 'Me dijo payaso y atacó a mi iglesia.'],
        );
    }
}

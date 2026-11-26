<?php

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

/**
 * Respuesta mínima con la forma que espera bible:seed-text.
 *
 * @return array<string, mixed>
 */
function fakeTranslationPayload(): array
{
    return [
        'books' => [[
            'id' => 'GEN',
            'commonName' => 'Génesis',
            'order' => 1,
            'chapters' => [[
                'chapter' => [
                    'number' => 1,
                    'content' => [
                        [
                            'type' => 'verse',
                            'number' => 1,
                            'content' => [
                                'En el principio ',
                                ['text' => 'creó Dios'],
                                ['type' => 'heading'],
                                ' los cielos.',
                            ],
                        ],
                    ],
                ],
            ]],
        ]],
    ];
}

/**
 * Respuesta mínima con la forma que espera bible:seed-crossrefs.
 *
 * @return array<string, mixed>
 */
function fakeCrossRefPayload(): array
{
    return [
        'chapter' => ['content' => [[
            'verse' => 1,
            'references' => [[
                'book' => 'JHN',
                'chapter' => 1,
                'verse' => 1,
                'endVerse' => null,
                'score' => 12,
            ]],
        ]]],
    ];
}

test('bible:seed-text retries a transient server error and imports the data', function () {
    Http::fake([
        '*' => Http::sequence()
            ->pushStatus(500)
            ->push(fakeTranslationPayload()),
    ]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_rvg']);

    expect($exit)->toBe(Command::SUCCESS)
        ->and(DB::table('bible_books')->count())->toBe(1)
        ->and(DB::table('bible_verses')->count())->toBe(1)
        ->and(DB::table('bible_verses')->first()->text)->toBe('En el principio creó Dios los cielos.');

    Http::assertSentCount(2);
});

test('bible:seed-text retries a connection timeout and imports the data', function () {
    $attempts = 0;
    Http::fake([
        '*' => function () use (&$attempts) {
            $attempts++;

            if ($attempts === 1) {
                throw new ConnectionException('timeout simulado');
            }

            return Http::response(fakeTranslationPayload());
        },
    ]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_rvg']);

    expect($exit)->toBe(Command::SUCCESS)
        ->and($attempts)->toBe(2)
        ->and(DB::table('bible_verses')->count())->toBe(1);
});

test('bible:seed-text fails cleanly when the api keeps returning 500', function () {
    Http::fake(['*' => Http::sequence()
        ->pushStatus(500)
        ->pushStatus(500)
        ->pushStatus(500)]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_rvg']);

    expect($exit)->toBe(Command::FAILURE)
        ->and(DB::table('bible_books')->count())->toBe(0)
        ->and(DB::table('bible_verses')->count())->toBe(0)
        ->and(Artisan::output())->toContain('No se pudo descargar la traducción');

    // retry(2) = intento inicial + 1 reintento.
    Http::assertSentCount(2);
});

test('bible:seed-text fails cleanly when the payload has no books', function () {
    Http::fake(['*' => Http::response(['books' => []])]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_rvg']);

    expect($exit)->toBe(Command::FAILURE)
        ->and(DB::table('bible_verses')->count())->toBe(0);
});

test('bible:list-spanish fails cleanly on a server error', function () {
    Http::fake(['*' => Http::sequence()
        ->pushStatus(500)
        ->pushStatus(500)]);

    $exit = Artisan::call('bible:list-spanish');

    expect($exit)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('No se pudo contactar la API.');
});

test('bible:seed-crossrefs fails cleanly when the dataset index is unavailable', function () {
    Http::fake(['*' => Http::sequence()
        ->pushStatus(500)
        ->pushStatus(500)
        ->pushStatus(500)]);

    $exit = Artisan::call('bible:seed-crossrefs');

    expect($exit)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('No se pudo consultar el conjunto de datos')
        ->and(DB::table('bible_cross_references')->count())->toBe(0);
});

test('bible:seed-crossrefs reports failed chapters instead of claiming success', function () {
    Http::fake([
        '*/open-cross-ref/books.json' => Http::response([
            'books' => [['id' => 'GEN', 'numberOfChapters' => 1]],
        ]),
        '*/open-cross-ref/GEN/1.json' => Http::sequence()
            ->pushStatus(500)
            ->pushStatus(500)
            ->pushStatus(500)
            ->pushStatus(500),
    ]);

    $exit = Artisan::call('bible:seed-crossrefs');

    expect($exit)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('No se pudieron importar')
        ->and(DB::table('bible_cross_references')->count())->toBe(0);
});

test('bible:seed-crossrefs is idempotent when the command runs twice', function () {
    Http::fake([
        '*/open-cross-ref/books.json' => Http::response([
            'books' => [['id' => 'GEN', 'numberOfChapters' => 1]],
        ]),
        '*/open-cross-ref/GEN/1.json' => Http::response(fakeCrossRefPayload()),
    ]);

    expect(Artisan::call('bible:seed-crossrefs'))->toBe(Command::SUCCESS)
        ->and(DB::table('bible_cross_references')->count())->toBe(1);

    expect(Artisan::call('bible:seed-crossrefs'))->toBe(Command::SUCCESS)
        ->and(DB::table('bible_cross_references')->count())->toBe(1);
});

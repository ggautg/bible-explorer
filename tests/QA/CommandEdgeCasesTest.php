<?php

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class)->use(RefreshDatabase::class);

function qaEdgeTranslationPayload(bool $withVerseNumber = true): array
{
    $verse = ['type' => 'verse', 'content' => ['Texto del versículo.']];
    if ($withVerseNumber) {
        $verse['number'] = 1;
    }

    return [
        'books' => [[
            'id' => 'GEN',
            'commonName' => 'Génesis',
            'order' => 1,
            'chapters' => [[
                'chapter' => ['number' => 1, 'content' => [$verse]],
            ]],
        ]],
    ];
}

function qaEdgeCrossRefPayload(int $score): array
{
    return [
        'chapter' => ['content' => [[
            'verse' => 1,
            'references' => [[
                'book' => 'JHN',
                'chapter' => 1,
                'verse' => 1,
                'endVerse' => null,
                'score' => $score,
            ]],
        ]]],
    ];
}

function qaEdgeFakeCrossRefIndex(): array
{
    return ['books' => [['id' => 'GEN', 'numberOfChapters' => 1]]];
}

test('bible:seed-text skips a malformed verse instead of crashing', function () {
    Http::fake(['*' => Http::response(qaEdgeTranslationPayload(false))]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_rvg']);

    expect($exit)->toBe(Command::SUCCESS)
        ->and(DB::table('bible_verses')->count())->toBe(0);
});

test('bible:seed-text stores the translation passed as an argument', function () {
    Http::fake(['*' => Http::response(qaEdgeTranslationPayload())]);

    $exit = Artisan::call('bible:seed-text', ['translation' => 'spa_pdt']);

    expect($exit)->toBe(Command::SUCCESS)
        ->and(DB::table('bible_verses')->first()->translation)->toBe('spa_pdt');
});

test('bible:seed-crossrefs fails cleanly when the dataset has no books', function () {
    Http::fake(['*' => Http::response(['books' => []])]);

    $exit = Artisan::call('bible:seed-crossrefs');

    expect($exit)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('no incluyó libros')
        ->and(DB::table('bible_cross_references')->count())->toBe(0);
});

test('bible:seed-crossrefs counts a connection failure on a chapter as a failed import', function () {
    Http::fake([
        '*/open-cross-ref/books.json' => Http::response(qaEdgeFakeCrossRefIndex()),
        '*/open-cross-ref/GEN/1.json' => function () {
            throw new ConnectionException('timeout simulado');
        },
    ]);

    $exit = Artisan::call('bible:seed-crossrefs');

    expect($exit)->toBe(Command::FAILURE)
        ->and(Artisan::output())->toContain('No se pudieron importar')
        ->and(DB::table('bible_cross_references')->count())->toBe(0);
});

test('bible:seed-crossrefs skips entries without a source verse', function () {
    Http::fake([
        '*/open-cross-ref/books.json' => Http::response(qaEdgeFakeCrossRefIndex()),
        '*/open-cross-ref/GEN/1.json' => Http::response([
            'chapter' => ['content' => [[
                'references' => [['book' => 'JHN', 'chapter' => 1, 'verse' => 1]],
            ]]],
        ]),
    ]);

    $exit = Artisan::call('bible:seed-crossrefs');

    expect($exit)->toBe(Command::SUCCESS)
        ->and(DB::table('bible_cross_references')->count())->toBe(0);
});

test('bible:seed-crossrefs updates the score when a reference is imported again', function () {
    Http::fake([
        '*/open-cross-ref/books.json' => Http::response(qaEdgeFakeCrossRefIndex()),
        '*/open-cross-ref/GEN/1.json' => Http::sequence()
            ->push(qaEdgeCrossRefPayload(5))
            ->push(qaEdgeCrossRefPayload(20)),
    ]);

    expect(Artisan::call('bible:seed-crossrefs'))->toBe(Command::SUCCESS)
        ->and(DB::table('bible_cross_references')->first()->score)->toBe(5);

    expect(Artisan::call('bible:seed-crossrefs'))->toBe(Command::SUCCESS)
        ->and(DB::table('bible_cross_references')->count())->toBe(1)
        ->and(DB::table('bible_cross_references')->first()->score)->toBe(20);
});

test('bible:list-spanish only lists spanish translations', function () {
    Http::fake(['*' => Http::response(['translations' => [
        [
            'id' => 'eng-kjv',
            'language' => 'eng',
            'name' => 'King James',
            'englishName' => 'King James',
            'numberOfBooks' => 66,
            'totalNumberOfVerses' => 31102,
        ],
        [
            'id' => 'spa-rvg',
            'language' => 'spa',
            'name' => 'Reina-Valera',
            'englishName' => 'Reina Valera',
            'numberOfBooks' => 66,
            'totalNumberOfVerses' => 31102,
        ],
    ]])]);

    $exit = Artisan::call('bible:list-spanish');

    expect($exit)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('spa-rvg')->not->toContain('eng-kjv');
});

test('bible:list-spanish warns when no spanish translation is available', function () {
    Http::fake(['*' => Http::response(['translations' => [
        [
            'id' => 'eng-kjv',
            'language' => 'eng',
            'name' => 'King James',
            'englishName' => 'King James',
            'numberOfBooks' => 66,
            'totalNumberOfVerses' => 31102,
        ],
    ]])]);

    $exit = Artisan::call('bible:list-spanish');

    expect($exit)->toBe(Command::SUCCESS)
        ->and(Artisan::output())->toContain('No se encontraron traducciones en español.');
});

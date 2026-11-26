<?php

namespace App\Console\Commands;

use App\Models\BibleBook;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Comando para importar referencias cruzadas entre versículos.
 *
 * Descarga el conjunto de datos "open-cross-ref" y almacena las relaciones
 * origen/destino para alimentar el grafo de conexiones bíblicas.
 */
class SeedBibleCrossReferences extends Command
{
    /**
     * Nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'bible:seed-crossrefs';

    /**
     * Descripción del comando mostrada en Artisan.
     *
     * @var string
     */
    protected $description = 'Descarga y guarda las referencias cruzadas entre versículos';

    /**
     * Ejecuta la importación de referencias cruzadas.
     */
    public function handle(): int
    {
        // Asegura que existan libros cargados para contextualizar el proceso.
        BibleBook::query()->count();

        $this->info('Consultando estructura del conjunto de datos...');

        try {
            $indexResponse = $this->httpClient()
                ->get('https://bible.helloao.org/api/d/open-cross-ref/books.json');
        } catch (ConnectionException) {
            $this->error('No se pudo consultar el conjunto de datos de referencias cruzadas.');

            return self::FAILURE;
        }

        if ($indexResponse->failed()) {
            $this->error('No se pudo consultar el conjunto de datos de referencias cruzadas.');

            return self::FAILURE;
        }

        $datasetBooks = Arr::wrap($indexResponse->json('books', []));
        if ($datasetBooks === []) {
            $this->error('El conjunto de datos de referencias cruzadas no incluyó libros.');

            return self::FAILURE;
        }

        $totalChapters = collect($datasetBooks)->sum('numberOfChapters');
        $bar = $this->output->createProgressBar((int) $totalChapters);
        $failedChapters = 0;

        foreach ($datasetBooks as $datasetBook) {
            $bookCode = $datasetBook['id'];
            $numberOfChapters = (int) ($datasetBook['numberOfChapters'] ?? 0);

            for ($chapter = 1; $chapter <= $numberOfChapters; $chapter++) {
                try {
                    $response = $this->httpClient()->retry(3, 200, null, false)->get(
                        "https://bible.helloao.org/api/d/open-cross-ref/{$bookCode}/{$chapter}.json"
                    );
                } catch (ConnectionException) {
                    $failedChapters++;
                    $bar->advance();

                    continue;
                }

                if ($response->successful()) {
                    $this->storeChapterReferences($bookCode, $chapter, $response->json('chapter.content', []));
                } else {
                    $failedChapters++;
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        if ($failedChapters > 0) {
            $this->error("No se pudieron importar {$failedChapters} capítulos de referencias cruzadas.");

            return self::FAILURE;
        }

        $this->info('Referencias cruzadas importadas correctamente.');

        return self::SUCCESS;
    }

    /**
     * Crea el cliente HTTP para la descarga.
     */
    private function httpClient(): PendingRequest
    {
        // La última respuesta 500 se devuelve al llamador en lugar de lanzarse,
        // para que handle() pueda reportar el fallo sin stack trace.
        $client = Http::timeout(60)->retry(2, 300, null, false);

        if (app()->environment('local') || config('app.debug')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Guarda en base de datos las referencias encontradas en un capítulo.
     *
     * @param  array<int, array<string, mixed>>  $verses
     */
    private function storeChapterReferences(string $bookCode, int $chapter, array $verses): void
    {
        $rows = [];

        foreach ($verses as $verseData) {
            $fromVerse = $verseData['verse'] ?? null;

            if ($fromVerse === null) {
                continue;
            }

            foreach ($verseData['references'] ?? [] as $ref) {
                $rows[] = [
                    'from_book' => $bookCode,
                    'from_chapter' => $chapter,
                    'from_verse' => $fromVerse,
                    'to_book' => $ref['book'] ?? null,
                    'to_chapter' => $ref['chapter'] ?? null,
                    'to_verse' => $ref['verse'] ?? null,
                    'to_verse_end' => $ref['endVerse'] ?? null,
                    'score' => $ref['score'] ?? null,
                ];
            }
        }

        if ($rows !== []) {
            // El índice único evita duplicados si el comando se ejecuta dos veces.
            DB::table('bible_cross_references')->upsert(
                $rows,
                ['from_book', 'from_chapter', 'from_verse', 'to_book', 'to_chapter', 'to_verse'],
                ['score', 'to_verse_end']
            );
        }
    }
}

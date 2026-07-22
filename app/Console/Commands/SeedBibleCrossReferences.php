<?php

namespace App\Console\Commands;

use App\Models\BibleBook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SeedBibleCrossReferences extends Command
{
    protected $signature = 'bible:seed-crossrefs';
    protected $description = 'Descarga y guarda las referencias cruzadas entre versículos (dataset open-cross-ref)';

    public function handle(): int
    {
        $books = BibleBook::orderBy('order')->get();

        // Necesitamos el número de capítulos de cada libro según el propio dataset,
        // así que primero pedimos su books.json
        $this->info('Consultando estructura del dataset...');
        $datasetBooks = Http::get('https://bible.helloao.org/api/d/open-cross-ref/books.json')
            ->json('books');

        $totalChapters = collect($datasetBooks)->sum('numberOfChapters');
        $bar = $this->output->createProgressBar($totalChapters);

        foreach ($datasetBooks as $datasetBook) {
            $bookCode = $datasetBook['id'];

            for ($chapter = 1; $chapter <= $datasetBook['numberOfChapters']; $chapter++) {
                $response = Http::retry(3, 200)->get(
                    "https://bible.helloao.org/api/d/open-cross-ref/{$bookCode}/{$chapter}.json"
                );

                if ($response->successful()) {
                    $this->storeChapterReferences($bookCode, $chapter, $response->json('chapter.content', []));
                }

                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }

    private function storeChapterReferences(string $bookCode, int $chapter, array $verses): void
    {
        $rows = [];

        foreach ($verses as $verseData) {
            foreach ($verseData['references'] as $ref) {
                $rows[] = [
                    'from_book' => $bookCode,
                    'from_chapter' => $chapter,
                    'from_verse' => $verseData['verse'],
                    'to_book' => $ref['book'],
                    'to_chapter' => $ref['chapter'],
                    'to_verse' => $ref['verse'],
                    'to_verse_end' => $ref['endVerse'] ?? null,
                    'score' => $ref['score'] ?? null,
                ];
            }
        }

        if (! empty($rows)) {
            DB::table('bible_cross_references')->insert($rows);
        }
    }
}
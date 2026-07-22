<?php

namespace App\Console\Commands;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SeedBibleText extends Command
{
    protected $signature = 'bible:seed-text {translation=spa_rvg}';
    protected $description = 'Descarga y guarda el texto completo de una traducción bíblica';

    public function handle(): int
    {
        $translation = $this->argument('translation');

        $this->info("Descargando {$translation}...");
        $response = Http::timeout(120)->get("https://bible.helloao.org/api/{$translation}/complete.json");

        if ($response->failed()) {
            $this->error('No se pudo descargar la traducción.');
            return self::FAILURE;
        }

        $books = $response->json('books');
        $bar = $this->output->createProgressBar(count($books));

        foreach ($books as $book) {
            // Los primeros 39 libros del canon son del Antiguo Testamento
            $testament = $book['order'] <= 39 ? 'OT' : 'NT';

            BibleBook::updateOrCreate(
                ['code' => $book['id']],
                [
                    'name' => $book['commonName'] ?? $book['name'],
                    'testament' => $testament,
                    'order' => $book['order'],
                ]
            );

            $verseRows = [];

            foreach ($book['chapters'] as $chapterData) {
                $chapterNumber = $chapterData['chapter']['number'];

                foreach ($chapterData['chapter']['content'] as $item) {
                    if ($item['type'] !== 'verse') {
                        continue;
                    }

                    $verseRows[] = [
                        'book_code' => $book['id'],
                        'chapter' => $chapterNumber,
                        'verse' => $item['number'],
                        'text' => $this->extractText($item['content']),
                    ];
                }
            }

            // Insertamos de a lotes para no saturar memoria/queries
            foreach (array_chunk($verseRows, 500) as $chunk) {
                BibleVerse::upsert(
                    $chunk,
                    ['book_code', 'chapter', 'verse'],
                    ['text']
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }

    /**
     * El contenido de un versículo puede mezclar strings simples,
     * texto formateado ({text: ...}), referencias a notas ({noteId: ...})
     * y saltos de línea ({lineBreak: true}). Nos quedamos solo con el texto.
     */
    private function extractText(array $content): string
    {
        $parts = [];

        foreach ($content as $piece) {
            if (is_string($piece)) {
                $parts[] = $piece;
            } elseif (isset($piece['text'])) {
                $parts[] = $piece['text'];
            }
            // los noteId y lineBreak se ignoran para el texto plano
        }

        return trim(implode(' ', $parts));
    }
}
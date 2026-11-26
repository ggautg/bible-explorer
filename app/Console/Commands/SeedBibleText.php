<?php

namespace App\Console\Commands;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Comando para descargar y sembrar el texto completo de una traducción bíblica.
 *
 * Obtiene el JSON público desde bible.helloao.org y guarda libros y versículos
 * en las tablas locales para su consulta por la aplicación.
 */
class SeedBibleText extends Command
{
    /**
     * Nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'bible:seed-text {translation=spa_rvg}';

    /**
     * Descripción del comando mostrada en Artisan.
     *
     * @var string
     */
    protected $description = 'Descarga y guarda el texto completo de una traducción bíblica';

    /**
     * Ejecuta la importación de la traducción indicada.
     */
    public function handle(): int
    {
        $translation = (string) $this->argument('translation');

        $this->info("Descargando {$translation}...");

        try {
            $response = $this->httpClient()->get("https://bible.helloao.org/api/{$translation}/complete.json");
        } catch (ConnectionException) {
            $this->error('No se pudo descargar la traducción. Verifica la conexión o el código de traducción.');

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->error('No se pudo descargar la traducción. Verifica la conexión o el código de traducción.');

            return self::FAILURE;
        }

        $books = $response->json('books', []);
        if ($books === []) {
            $this->error('La respuesta de la API no incluyó libros para importar.');

            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar(count($books));

        foreach ($books as $book) {
            // Los primeros 39 libros del canon corresponden al Antiguo Testamento.
            $testament = ($book['order'] ?? 0) <= 39 ? 'OT' : 'NT';

            BibleBook::updateOrCreate(
                ['code' => $book['id']],
                [
                    'name' => $book['commonName'] ?? $book['name'] ?? $book['id'],
                    'testament' => $testament,
                    'order' => $book['order'] ?? 0,
                ]
            );

            $verseRows = [];

            foreach ($book['chapters'] ?? [] as $chapterData) {
                $chapterNumber = $chapterData['chapter']['number'] ?? null;

                if ($chapterNumber === null) {
                    continue;
                }

                foreach ($chapterData['chapter']['content'] ?? [] as $item) {
                    if (($item['type'] ?? null) !== 'verse') {
                        continue;
                    }

                    $verseNumber = $item['number'] ?? null;
                    if ($verseNumber === null) {
                        continue;
                    }

                    $verseRows[] = [
                        'translation' => $translation,
                        'book_code' => $book['id'],
                        'chapter' => $chapterNumber,
                        'verse' => $verseNumber,
                        'text' => $this->extractText($item['content'] ?? []),
                    ];
                }
            }

            // Inserción por lotes para reducir consumo de memoria y consultas.
            foreach (array_chunk($verseRows, 500) as $chunk) {
                BibleVerse::upsert(
                    $chunk,
                    ['translation', 'book_code', 'chapter', 'verse'],
                    ['text']
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Texto bíblico importado correctamente.');

        return self::SUCCESS;
    }

    /**
     * Crea el cliente HTTP para la descarga.
     *
     * En entorno local o con depuración activa se deshabilita la verificación
     * SSL para evitar fallos por certificados del sistema; en producción se
     * mantiene la verificación por defecto.
     */
    private function httpClient(): PendingRequest
    {
        // El cuarto argumento evita que la última respuesta 500 se convierta
        // en RequestException: el manejo de errores se hace en handle().
        $client = Http::timeout(120)->retry(2, 500, null, false);

        if (app()->environment('local') || config('app.debug')) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Extrae texto plano del contenido mixto de un versículo.
     *
     * El contenido puede incluir cadenas, objetos con clave "text", notas y
     * saltos de línea. Solo se conservan los fragmentos textuales.
     *
     * @param  array<int, mixed>  $content
     */
    private function extractText(array $content): string
    {
        $parts = [];

        foreach ($content as $piece) {
            if (is_string($piece)) {
                $parts[] = $piece;
            } elseif (is_array($piece) && isset($piece['text'])) {
                $parts[] = $piece['text'];
            }
        }

        return trim(preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '');
    }
}

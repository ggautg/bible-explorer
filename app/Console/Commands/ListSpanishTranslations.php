<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ListSpanishTranslations extends Command
{
    protected $signature = 'bible:list-spanish';

    protected $description = 'Lista las traducciones bíblicas disponibles en español desde la API de helloao';

    public function handle(): int
    {
        $response = Http::get('https://bible.helloao.org/api/available_translations.json');

        if ($response->failed()) {
            $this->error('No se pudo contactar la API.');

            return self::FAILURE;
        }

        $spanish = collect(Arr::wrap($response->json('translations')))
            ->filter(fn ($t) => $t['language'] === 'spa')
            ->sortBy('id')
            ->values();

        if ($spanish->isEmpty()) {
            $this->warn('No se encontraron traducciones en español.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Nombre', 'Nombre en inglés', 'Libros', 'Versículos'],
            $spanish->map(fn ($t) => [
                $t['id'],
                $t['name'],
                $t['englishName'],
                $t['numberOfBooks'],
                $t['totalNumberOfVerses'],
            ])
        );

        return self::SUCCESS;
    }
}

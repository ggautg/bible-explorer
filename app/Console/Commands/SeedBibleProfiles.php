<?php

namespace App\Console\Commands;

use App\Models\BibleProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SeedBibleProfiles extends Command
{
    protected $signature = 'bible:seed-profiles';
    protected $description = 'Descarga y guarda los perfiles de personajes del commentary Tyndale';

    public function handle(): int
    {
        $this->info('Descargando lista de perfiles...');
        $list = Http::get('https://bible.helloao.org/api/c/tyndale/profiles.json')->json('profiles');

        $bar = $this->output->createProgressBar(count($list));

        foreach ($list as $item) {
            if (! $item['reference']) {
                $bar->advance();
                continue; // algunos perfiles no tienen referencia bíblica asociada
            }

            $detail = Http::get("https://bible.helloao.org{$item['thisProfileLink']}")->json();

            BibleProfile::updateOrCreate(
                ['id' => $item['id']],
                [
                    'subject' => $item['subject'],
                    'book' => $item['reference']['book'],
                    'chapter' => $item['reference']['chapter'],
                    'verse' => $item['reference']['verse'],
                    'end_verse' => $item['reference']['endVerse'] ?? null,
                    'content' => $detail['content'] ?? [],
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Listo.');

        return self::SUCCESS;
    }
}
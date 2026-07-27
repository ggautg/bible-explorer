<?php

namespace App\Services;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use InvalidArgumentException;
use RuntimeException;

class BibleReferenceParser
{
    /**
     * Convierte una referencia tipo "Juan 3:16" en libro/capítulo/versículo.
     *
     * @throws InvalidArgumentException si el formato de texto no matchea
     * @throws RuntimeException si el libro o el versículo no existen
     */
    public function parse(string $reference): array
    {
        $reference = trim($reference);

        if (! preg_match('/^(\d?\s?[\p{L}\s]+?)\s+(\d+):(\d+)$/u', $reference, $matches)) {
            throw new InvalidArgumentException('Formato inválido. Usá algo como "Juan 3:16".');
        }

        [, $bookName, $chapter, $verse] = $matches;
        $bookName = trim($bookName);

        $book = BibleBook::where('name', 'like', "{$bookName}%")->first();

        if (! $book) {
            throw new RuntimeException("No se encontró el libro \"{$bookName}\".");
        }

        $exists = BibleVerse::where('book_code', $book->code)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->exists();
            
        if (! $exists) {
            throw new RuntimeException("No existe {$book->name} {$chapter}:{$verse}.");
        }

        return [
            'book' => $book->code,
            'chapter' => (int) $chapter,
            'verse' => (int) $verse,
        ];
    }
}

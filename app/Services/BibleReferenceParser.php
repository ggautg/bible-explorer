<?php

namespace App\Services;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use InvalidArgumentException;
use RuntimeException;

/**
 * Servicio para interpretar referencias bíblicas en texto.
 *
 * Convierte cadenas como "Juan 3:16" en códigos de libro, capítulo y versículo
 * verificando que existan en la base de datos.
 */
class BibleReferenceParser
{
    /**
     * Interpreta una referencia bíblica textual.
     *
     * @return array{book: string, chapter: int, verse: int}
     *
     * @throws InvalidArgumentException Si el formato del texto no es válido.
     * @throws RuntimeException Si el libro o el versículo no existen.
     */
    public function parse(string $reference): array
    {
        $reference = trim($reference);

        if (! preg_match('/^(\d?\s?[\p{L}\s]+?)\s+(\d+):(\d+)$/u', $reference, $matches)) {
            throw new InvalidArgumentException('Formato no válido. Usa algo como "Juan 3:16".');
        }

        [, $bookName, $chapter, $verse] = $matches;
        $bookName = trim($bookName);

        $book = BibleBook::query()
            ->where('name', 'like', "{$bookName}%")
            ->first();

        if (! $book) {
            throw new RuntimeException("No se encontró el libro \"{$bookName}\".");
        }

        $exists = BibleVerse::query()
            ->where('book_code', $book->code)
            ->where('translation', 'spa_rvg')
            ->where('chapter', (int) $chapter)
            ->where('verse', (int) $verse)
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

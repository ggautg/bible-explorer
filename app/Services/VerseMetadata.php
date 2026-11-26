<?php

namespace App\Services;

use App\Models\BibleBook;
use App\Models\BibleVerse;

/**
 * Resuelve metadatos de versículos desde la base de datos.
 *
 * Los clientes pueden enviar referencias, pero nunca el nombre del libro ni
 * el texto: esos datos se derivan en el servidor para evitar que un payload
 * manipulado guarde contenido ajeno al versículo solicitado.
 */
class VerseMetadata
{
    /** Traducción usada por defecto en lectura y herramientas. */
    public const TRANSLATION = 'spa_rvg';

    /**
     * Devuelve el nombre del libro y el texto de una referencia.
     *
     * @return array{book_name: string, text: string}|null Null si la referencia no existe.
     */
    public function forReference(?string $bookCode, ?int $chapter, ?int $verse): ?array
    {
        if (! $bookCode || ! $chapter || ! $verse) {
            return null;
        }

        $verseRow = BibleVerse::query()
            ->where('translation', self::TRANSLATION)
            ->where('book_code', $bookCode)
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->first(['text']);

        if (! $verseRow || ! $verseRow->text) {
            return null;
        }

        $bookName = BibleBook::query()->where('code', $bookCode)->value('name');

        return [
            'book_name' => $bookName ?: $bookCode,
            'text' => $verseRow->text,
        ];
    }

    /**
     * Resuelve varios textos en una sola consulta (evita N+1 en feeds).
     *
     * @param  iterable<array{book_code: string|null, chapter: int|null, verse: int|null}>  $references
     * @return array<string, string> Clave "libro|capítulo|versículo" (VerseMetadata::key()).
     */
    public function textsFor(iterable $references): array
    {
        $keys = [];
        foreach ($references as $reference) {
            if (! $reference['book_code'] || ! $reference['chapter'] || ! $reference['verse']) {
                continue;
            }
            $keys[self::key($reference['book_code'], $reference['chapter'], $reference['verse'])] = $reference;
        }

        if ($keys === []) {
            return [];
        }

        $verses = BibleVerse::query()
            ->where('translation', self::TRANSLATION)
            ->where(function ($query) use ($keys): void {
                foreach (array_values($keys) as $reference) {
                    $query->orWhere(function ($query) use ($reference): void {
                        $query->where('book_code', $reference['book_code'])
                            ->where('chapter', $reference['chapter'])
                            ->where('verse', $reference['verse']);
                    });
                }
            })
            ->get(['book_code', 'chapter', 'verse', 'text']);

        $texts = [];
        foreach ($verses as $verse) {
            $texts[self::key($verse->book_code, (int) $verse->chapter, (int) $verse->verse)] = $verse->text;
        }

        return $texts;
    }

    /**
     * Clave estable para una referencia bíblica.
     */
    public static function key(string $bookCode, int $chapter, int $verse): string
    {
        return "{$bookCode}|{$chapter}|{$verse}";
    }
}

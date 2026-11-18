<?php

namespace App\Services;

use App\Models\BibleBook;
use App\Models\BibleVerse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Servicio para construir el grafo de referencias cruzadas de un versículo.
 *
 * Reúne el versículo central, las referencias entrantes/salientes y los
 * nodos/aristas necesarios para la visualización en el frontend.
 */
class BibleGraphService
{
    /**
     * Máximo de conexiones a recuperar por dirección (entrantes/salientes).
     */
    private const MAX_CONNECTIONS_PER_DIRECTION = 25;

    /**
     * Construye la estructura de grafo para un versículo dado.
     *
     * @return array{nodes: array<int, array<string, mixed>>, edges: array<int, array<string, mixed>>, center: string}
     */
    public function buildGraph(string $book, int $chapter, int $verse): array
    {
        $centerVerse = BibleVerse::query()
            ->where('book_code', $book)
            ->where('translation', 'spa_rvg')
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->firstOrFail();

        $outgoing = $this->fetchReferences('from', $book, $chapter, $verse);
        $incoming = $this->fetchReferences('to', $book, $chapter, $verse);

        $connectedVerses = $this->fetchConnectedVerses($outgoing, $incoming);

        return [
            'nodes' => $this->buildNodes($centerVerse, $connectedVerses),
            'edges' => $this->buildEdges($outgoing, $incoming),
            'center' => "{$book}-{$chapter}-{$verse}",
        ];
    }

    /**
     * Obtiene referencias cruzadas en una dirección específica.
     */
    private function fetchReferences(string $direction, string $book, int $chapter, int $verse): Collection
    {
        return DB::table('bible_cross_references')
            ->where("{$direction}_book", $book)
            ->where("{$direction}_chapter", $chapter)
            ->where("{$direction}_verse", $verse)
            ->orderByDesc('score')
            ->limit(self::MAX_CONNECTIONS_PER_DIRECTION)
            ->get();
    }

    /**
     * Carga los versículos conectados al nodo central.
     */
    private function fetchConnectedVerses(Collection $outgoing, Collection $incoming): Collection
    {
        $keys = $outgoing
            ->map(fn ($r) => [$r->to_book, $r->to_chapter, $r->to_verse])
            ->concat($incoming->map(fn ($r) => [$r->from_book, $r->from_chapter, $r->from_verse]))
            ->unique(fn ($k) => implode('-', $k))
            ->values();

        if ($keys->isEmpty()) {
            return collect();
        }

        return BibleVerse::query()
            ->where('translation', 'spa_rvg')
            ->where(function ($query) use ($keys): void {
                foreach ($keys as [$b, $c, $v]) {
                    $query->orWhere(fn ($q) => $q
                        ->where('book_code', $b)
                        ->where('chapter', $c)
                        ->where('verse', $v));
                }
            })
            ->get();
    }

    /**
     * Construye la lista de nodos del grafo.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildNodes(BibleVerse $centerVerse, Collection $connectedVerses): array
    {
        $bookNames = BibleBook::query()->pluck('name', 'code');

        return collect([$centerVerse])
            ->concat($connectedVerses)
            ->map(fn (BibleVerse $v) => [
                'id' => "{$v->book_code}-{$v->chapter}-{$v->verse}",
                'book' => $v->book_code,
                'bookName' => $bookNames[$v->book_code] ?? $v->book_code,
                'chapter' => $v->chapter,
                'verse' => $v->verse,
                'text' => $v->text,
                'isCenter' => $v->is($centerVerse),
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * Construye la lista de aristas del grafo.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildEdges(Collection $outgoing, Collection $incoming): array
    {
        $mapEdge = fn ($r): array => [
            'source' => "{$r->from_book}-{$r->from_chapter}-{$r->from_verse}",
            'target' => "{$r->to_book}-{$r->to_chapter}-{$r->to_verse}",
            'score' => $r->score,
        ];

        return $outgoing
            ->map($mapEdge)
            ->concat($incoming->map($mapEdge))
            ->unique(fn ($e) => $e['source'].'>'.$e['target'])
            ->values()
            ->all();
    }
}

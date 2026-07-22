<?php

namespace App\Services;

use App\Models\BibleVerse;
use Illuminate\Support\Facades\DB;

class BibleGraphService
{
    private const MAX_CONNECTIONS_PER_DIRECTION = 25;

    public function buildGraph(string $book, int $chapter, int $verse): array
    {
        $centerVerse = BibleVerse::where('book_code', $book)
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

    private function fetchReferences(string $direction, string $book, int $chapter, int $verse)
    {
        return DB::table('bible_cross_references')
            ->where("{$direction}_book", $book)
            ->where("{$direction}_chapter", $chapter)
            ->where("{$direction}_verse", $verse)
            ->orderByDesc('score')
            ->limit(self::MAX_CONNECTIONS_PER_DIRECTION)
            ->get();
    }

    private function fetchConnectedVerses($outgoing, $incoming)
    {
        $keys = $outgoing->map(fn ($r) => [$r->to_book, $r->to_chapter, $r->to_verse])
            ->concat($incoming->map(fn ($r) => [$r->from_book, $r->from_chapter, $r->from_verse]))
            ->unique(fn ($k) => implode('-', $k));

        return BibleVerse::query()
            ->where(function ($query) use ($keys) {
                foreach ($keys as [$b, $c, $v]) {
                    $query->orWhere(fn ($q) => $q->where('book_code', $b)->where('chapter', $c)->where('verse', $v));
                }
            })
            ->get();
    }

    private function buildNodes($centerVerse, $connectedVerses)
    {
        return collect([$centerVerse])
            ->concat($connectedVerses)
            ->map(fn ($v) => [
                'id' => "{$v->book_code}-{$v->chapter}-{$v->verse}",
                'book' => $v->book_code,
                'chapter' => $v->chapter,
                'verse' => $v->verse,
                'text' => $v->text,
                'isCenter' => $v->is($centerVerse),
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function buildEdges($outgoing, $incoming)
    {
        $mapEdge = fn ($r) => [
            'source' => "{$r->from_book}-{$r->from_chapter}-{$r->from_verse}",
            'target' => "{$r->to_book}-{$r->to_chapter}-{$r->to_verse}",
            'score' => $r->score,
        ];

        return $outgoing->map($mapEdge)
            ->concat($incoming->map($mapEdge))
            ->unique(fn ($e) => $e['source'] . '>' . $e['target'])
            ->values()
            ->all();
    }
}
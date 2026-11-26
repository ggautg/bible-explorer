<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega los índices de integridad pendientes: referencias entrantes del
 * grafo y unicidad de rebibleos. Deduplica antes de crear la restricción para
 * no fallar sobre instalaciones con datos históricos.
 */
return new class extends Migration
{
    /**
     * @return array<int, string>
     */
    private function indexNames(string $table): array
    {
        return collect(Schema::getIndexes($table))
            ->pluck('name')
            ->filter()
            ->map(fn ($name) => (string) $name)
            ->all();
    }

    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        $referenceIndexes = $this->indexNames('bible_cross_references');

        if (! in_array('bible_cross_refs_to_reference_index', $referenceIndexes, true)) {
            Schema::table('bible_cross_references', function (Blueprint $table): void {
                $table->index(['to_book', 'to_chapter', 'to_verse'], 'bible_cross_refs_to_reference_index');
            });
        }

        // Conserva la fila de menor id y elimina duplicados exactos.
        // MySQL no permite UPDATE/DELETE con subconsulta sobre la misma tabla,
        // por eso usa un derived table en ese driver.
        if ($driver === 'mysql') {
            DB::statement(<<<'SQL'
                DELETE bcr
                FROM bible_cross_references bcr
                LEFT JOIN (
                    SELECT MIN(id) AS keep_id
                    FROM bible_cross_references
                    GROUP BY from_book, from_chapter, from_verse, to_book, to_chapter, to_verse
                ) keep ON keep.keep_id = bcr.id
                WHERE keep.keep_id IS NULL
                SQL);
        } else {
            DB::statement(<<<'SQL'
                DELETE FROM bible_cross_references
                WHERE id NOT IN (
                    SELECT MIN(id) FROM bible_cross_references
                    GROUP BY from_book, from_chapter, from_verse, to_book, to_chapter, to_verse
                )
                SQL);
        }

        if (! in_array('bible_cross_refs_unique_reference', $referenceIndexes, true)) {
            Schema::table('bible_cross_references', function (Blueprint $table): void {
                $table->unique(
                    ['from_book', 'from_chapter', 'from_verse', 'to_book', 'to_chapter', 'to_verse'],
                    'bible_cross_refs_unique_reference',
                );
            });
        }

        // Un usuario puede rebiblear una publicación una sola vez.
        DB::table('posts')
            ->select('user_id', 'rebible_of_id')
            ->whereNotNull('rebible_of_id')
            ->groupBy('user_id', 'rebible_of_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('user_id')
            ->each(function ($row): void {
                $ids = DB::table('posts')
                    ->where('user_id', $row->user_id)
                    ->where('rebible_of_id', $row->rebible_of_id)
                    ->orderBy('id')
                    ->pluck('id');

                DB::table('posts')->whereIn('id', $ids->slice(1))->delete();
            });

        if (! in_array('posts_user_rebible_unique', $this->indexNames('posts'), true)) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->unique(['user_id', 'rebible_of_id'], 'posts_user_rebible_unique');
            });
        }
    }

    public function down(): void
    {
        if (in_array('posts_user_rebible_unique', $this->indexNames('posts'), true)) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropUnique('posts_user_rebible_unique');
            });
        }

        $referenceIndexes = $this->indexNames('bible_cross_references');

        Schema::table('bible_cross_references', function (Blueprint $table) use ($referenceIndexes): void {
            if (in_array('bible_cross_refs_unique_reference', $referenceIndexes, true)) {
                $table->dropUnique('bible_cross_refs_unique_reference');
            }

            if (in_array('bible_cross_refs_to_reference_index', $referenceIndexes, true)) {
                $table->dropIndex('bible_cross_refs_to_reference_index');
            }
        });
    }
};

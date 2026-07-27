<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bible_verses', function (Blueprint $table) {
            $table->string('translation', 15)->default('spa_rvg')->after('book_code');
        });

        DB::table('bible_verses')->update(['translation' => 'spa_rvg']);

        Schema::table('bible_verses', function (Blueprint $table) {
            $table->dropUnique(['book_code', 'chapter', 'verse']);
            $table->unique(['translation', 'book_code', 'chapter', 'verse']);

            // No borramos el índice viejo ['book_code', 'chapter'] — MySQL lo necesita
            // para sostener la foreign key hacia bible_books. Solo agregamos el nuevo al lado.
            $table->index(['translation', 'book_code', 'chapter']);
        });
    }

    public function down(): void
    {
        Schema::table('bible_verses', function (Blueprint $table) {
            $table->dropUnique(['translation', 'book_code', 'chapter', 'verse']);
            $table->dropIndex(['translation', 'book_code', 'chapter']);
            $table->unique(['book_code', 'chapter', 'verse']);
            $table->dropColumn('translation');
        });
    }
};

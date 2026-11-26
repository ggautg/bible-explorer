<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FULLTEXT es exclusivo de MySQL; la suite de pruebas usa SQLite en memoria.
     * En otros drivers la búsqueda de texto sigue funcionando con LIKE.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('bible_verses', function (Blueprint $table) {
            $table->fullText('text');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('bible_verses', function (Blueprint $table) {
            $table->dropFullText(['text']);
        });
    }
};

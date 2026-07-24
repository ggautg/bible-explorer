<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bible_verses', function (Blueprint $table) {
            $table->fullText('text');
        });
    }

    public function down(): void
    {
        Schema::table('bible_verses', function (Blueprint $table) {
            $table->dropFullText(['text']);
        });
    }
};

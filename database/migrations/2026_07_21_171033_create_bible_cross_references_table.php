<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('bible_cross_references', function (Blueprint $table) {
        $table->id();
        $table->string('from_book', 10);
        $table->unsignedSmallInteger('from_chapter');
        $table->unsignedSmallInteger('from_verse');
        $table->string('to_book', 10);
        $table->unsignedSmallInteger('to_chapter');
        $table->unsignedSmallInteger('to_verse');

        // índice compuesto: la consulta más frecuente va a ser
        // "dame todas las referencias que salen de este versículo"
        $table->index(['from_book', 'from_chapter', 'from_verse']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_cross_references');
    }
};

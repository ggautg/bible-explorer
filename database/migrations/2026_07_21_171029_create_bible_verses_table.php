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
    Schema::create('bible_verses', function (Blueprint $table) {
        $table->id();
        $table->string('book_code', 10);
        $table->unsignedSmallInteger('chapter');
        $table->unsignedSmallInteger('verse');
        $table->text('text');

        $table->foreign('book_code')->references('code')->on('bible_books');
        $table->unique(['book_code', 'chapter', 'verse']);
        $table->index(['book_code', 'chapter']); // para cargar un capítulo entero rápido
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_verses');
    }
};

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
    Schema::create('bible_books', function (Blueprint $table) {
        $table->string('code', 10)->primary(); // GEN, EXO, MAT, etc.
        $table->string('name');                // Nombre en español
        $table->enum('testament', ['OT', 'NT']);
        $table->unsignedSmallInteger('order');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_books');
    }
};

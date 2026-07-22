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
    Schema::create('bible_profiles', function (Blueprint $table) {
        $table->string('id')->primary(); // slug, ej: "aaron"
        $table->string('subject');       // "Aaron"
        $table->string('book', 10);
        $table->unsignedSmallInteger('chapter');
        $table->unsignedSmallInteger('verse');
        $table->unsignedSmallInteger('end_verse')->nullable();
        $table->json('content'); // array de párrafos

        $table->index(['book', 'chapter']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_profiles');
    }
};

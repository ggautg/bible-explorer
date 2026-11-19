<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración: crea el muro de comentarios por perfil.
     */
    public function up(): void
    {
        Schema::create('profile_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('profile_comments')->cascadeOnDelete();
            $table->string('book_code', 10)->nullable();
            $table->string('book_name')->nullable();
            $table->unsignedSmallInteger('chapter')->nullable();
            $table->unsignedSmallInteger('verse')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index(['profile_user_id', 'created_at']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_comments');
    }
};

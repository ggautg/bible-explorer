<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de reacciones a comentarios del muro de perfil.
     */
    public function up(): void
    {
        Schema::create('profile_comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_comment_id')->constrained('profile_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->timestamps();

            $table->unique(['profile_comment_id', 'user_id', 'type'], 'profile_comment_reaction_unique');
            $table->index(['profile_comment_id', 'type']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_comment_reactions');
    }
};

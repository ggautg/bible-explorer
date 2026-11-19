<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de "me gusta" de publicaciones y migra reacciones previas.
     */
    public function up(): void
    {
        Schema::create('post_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'post_id']);
            $table->index('post_id');
        });

        // Migrar reacciones antiguas si existen tabla de mapeo y de reacciones.
        if (Schema::hasTable('legacy_comment_post_map') && Schema::hasTable('profile_comment_reactions')) {
            $map = DB::table('legacy_comment_post_map')->pluck('post_id', 'comment_id');
            foreach (DB::table('profile_comment_reactions')->orderBy('id')->get() as $reaction) {
                $postId = $map[$reaction->profile_comment_id] ?? null;
                if (! $postId) {
                    continue;
                }
                $exists = DB::table('post_likes')
                    ->where('user_id', $reaction->user_id)
                    ->where('post_id', $postId)
                    ->exists();
                if (! $exists) {
                    DB::table('post_likes')->insert([
                        'user_id' => $reaction->user_id,
                        'post_id' => $postId,
                        'created_at' => $reaction->created_at,
                        'updated_at' => $reaction->updated_at,
                    ]);
                }
            }
        }
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_likes');
    }
};
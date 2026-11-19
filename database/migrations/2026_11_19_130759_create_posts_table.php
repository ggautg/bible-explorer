<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de publicaciones (posts) estilo red social y migra
     * comentarios de muros existentes cuando corresponde.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->foreignId('rebible_of_id')->nullable()->constrained('posts')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('book_code', 10)->nullable();
            $table->string('book_name', 120)->nullable();
            $table->unsignedSmallInteger('chapter')->nullable();
            $table->unsignedSmallInteger('verse')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['parent_id', 'created_at']);
            $table->index('rebible_of_id');
        });

        // Migración de datos desde profile_comments (si existe).
        if (! Schema::hasTable('profile_comments')) {
            return;
        }

        $map = [];

        // Primero publicaciones raíz, luego respuestas (por nivel simple: parent_id null primero).
        $roots = DB::table('profile_comments')->whereNull('parent_id')->orderBy('id')->get();
        foreach ($roots as $row) {
            $newId = DB::table('posts')->insertGetId([
                'user_id' => $row->user_id,
                'parent_id' => null,
                'rebible_of_id' => null,
                'body' => $row->body,
                'book_code' => $row->book_code,
                'book_name' => $row->book_name,
                'chapter' => $row->chapter,
                'verse' => $row->verse,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
            $map[$row->id] = $newId;
        }

        $replies = DB::table('profile_comments')->whereNotNull('parent_id')->orderBy('id')->get();
        foreach ($replies as $row) {
            $parentNew = $map[$row->parent_id] ?? null;
            if (! $parentNew) {
                continue;
            }
            $newId = DB::table('posts')->insertGetId([
                'user_id' => $row->user_id,
                'parent_id' => $parentNew,
                'rebible_of_id' => null,
                'body' => $row->body,
                'book_code' => $row->book_code,
                'book_name' => $row->book_name,
                'chapter' => $row->chapter,
                'verse' => $row->verse,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
            $map[$row->id] = $newId;
        }

        // Guardar mapa temporalmente no es necesario; likes se migran en su migración.
        // Para que la migración de likes pueda mapear, almacenamos en una tabla temporal simple si existe.
        // En su lugar, la migración de likes hará el mapeo buscando por contenido/tiempo no fiable;
        // mejor: crear tabla de mapeo efímera no. Alternativa: migrar likes aquí también si la tabla existe.
        if (Schema::hasTable('profile_comment_reactions') && Schema::hasTable('post_likes')) {
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

        // Guardar mapa en cache de app no disponible; pero likes migration corre después y tabla post_likes
        // puede no existir aún. Por eso arriba chequeamos hasTable('post_likes') que será false.
        // Solución: persistir mapa en una tabla temporal en memoria no; usaremos una segunda pasada:
        // almacenamos el mapa serializado en una tabla auxiliar temporal dentro de esta migración.
        if (! Schema::hasTable('legacy_comment_post_map')) {
            Schema::create('legacy_comment_post_map', function (Blueprint $table): void {
                $table->unsignedBigInteger('comment_id')->primary();
                $table->unsignedBigInteger('post_id');
            });
        } else {
            DB::table('legacy_comment_post_map')->truncate();
        }
        foreach ($map as $commentId => $postId) {
            DB::table('legacy_comment_post_map')->insert([
                'comment_id' => $commentId,
                'post_id' => $postId,
            ]);
        }
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_comment_post_map');
        Schema::dropIfExists('posts');
    }
};
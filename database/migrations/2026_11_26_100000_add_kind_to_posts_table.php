<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega el tipo de publicación para las opciones de comunidad.
 *
 * El valor `rebibleada` se deriva de `rebible_of_id`, así que no se escribe
 * en el compositor; el resto de publicaciones nouvelles nace como `reflexion`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('kind', 24)->default('reflexion')->after('rebible_of_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('kind');
        });
    }
};

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
    Schema::table('bible_cross_references', function (Blueprint $table) {
        $table->unsignedSmallInteger('to_verse_end')->nullable()->after('to_verse');
        $table->unsignedTinyInteger('score')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bible_cross_references', function (Blueprint $table) {
            //
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bible_cross_references', function (Blueprint $table) {
            $table->smallInteger('score')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bible_cross_references', function (Blueprint $table) {
            $table->unsignedTinyInteger('score')->nullable()->change();
        });
    }
};

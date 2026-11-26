<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Agrega la columna slug a usuarios para rutas de perfil legibles.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Genera slugs para los usuarios existentes.
        User::query()->each(function (User $user): void {
            $base = Str::slug($user->name);
            $slug = $base !== '' ? $base : 'usuario-'.$user->id;
            $suffix = 1;

            while (User::query()->where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }

            $user->forceFill(['slug' => $slug])->save();
        });
    }

    /**
     * Revierte la columna slug.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

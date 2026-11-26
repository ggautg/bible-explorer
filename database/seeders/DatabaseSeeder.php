<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // idempotente: volver a sembrar no debe duplicar el usuario de prueba
        $attributes = User::factory()->make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ])->getAttributes();

        User::firstOrCreate(['email' => 'test@example.com'], $attributes);
    }
}

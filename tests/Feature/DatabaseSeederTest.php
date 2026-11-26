<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;

test('sembrar dos veces no duplica el usuario de prueba', function () {
    Artisan::call('db:seed', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);

    expect(User::query()->where('email', 'test@example.com')->count())->toBe(1);
});

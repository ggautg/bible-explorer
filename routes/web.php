<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Bible\GraphController;
use App\Http\Controllers\Bible\SearchController;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('/bible/{book}/{chapter}/{verse}', [GraphController::class, 'show'])
    ->name('bible.graph');

Route::get('/api/bible/books-search', [SearchController::class, 'books']);
Route::get('/api/bible/resolve', [SearchController::class, 'resolve']);

use App\Http\Controllers\Bible\ReaderController;

Route::prefix('leer')->group(function () {
    Route::get('/', [ReaderController::class, 'books'])->name('bible.books');
    Route::get('/{book}', [ReaderController::class, 'chapters'])->name('bible.chapters');
    Route::get('/{book}/{chapter}', [ReaderController::class, 'chapter'])->name('bible.chapter');
});

Route::get('/favoritos', [ReaderController::class, 'favorites'])->name('bible.favorites');

require __DIR__.'/settings.php';

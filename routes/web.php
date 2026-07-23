<?php

use App\Http\Controllers\Bible\FavoriteController;
use App\Http\Controllers\Bible\GraphController;
use App\Http\Controllers\Bible\ReaderController;
use App\Http\Controllers\Bible\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    // Grafo de constelaciones
    Route::get('/bible/{book}/{chapter}/{verse}', [GraphController::class, 'show'])->name('bible.graph');

    // Lector
    Route::prefix('leer')->group(function () {
        Route::get('/', [ReaderController::class, 'books'])->name('bible.books');
        Route::get('/{book}', [ReaderController::class, 'chapters'])->name('bible.chapters');
        Route::get('/{book}/{chapter}', [ReaderController::class, 'chapter'])->name('bible.chapter');
    });

    // Búsqueda (usada por el grafo)
    Route::prefix('api/bible')->group(function () {
        Route::get('/books-search', [SearchController::class, 'books']);
        Route::get('/resolve', [SearchController::class, 'resolve']);
    });

    // Favoritos
    Route::get('/favoritos', [ReaderController::class, 'favorites'])->name('bible.favorites');
    Route::get('/api/favorites', [FavoriteController::class, 'index']);
    Route::post('/api/favorites', [FavoriteController::class, 'store']);
    Route::delete('/api/favorites', [FavoriteController::class, 'destroy']);
});

require __DIR__.'/settings.php';
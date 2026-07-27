<?php

use App\Http\Controllers\Bible\FavoriteController;
use App\Http\Controllers\Bible\GraphController;
use App\Http\Controllers\Bible\NoteController;
use App\Http\Controllers\Bible\ReaderController;
use App\Http\Controllers\Bible\SearchController;
use App\Http\Controllers\Bible\ShareController;
use App\Http\Controllers\Bible\TextSearchController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/',[HomeController::class,'index'])->name('home');

Route::get('/compartido/{book}/{chapter}/{verse}', [ShareController::class, 'show']);

Route::middleware('auth')->group(function () {

    Route::get('/bible/{book}/{chapter}/{verse}', [GraphController::class, 'show'])->name('bible.graph');

    Route::prefix('leer')->group(function () {
        Route::get('/', [ReaderController::class, 'books'])->name('bible.books');
        Route::get('/{book}', [ReaderController::class, 'chapters'])->name('bible.chapters');
        Route::get('/{book}/{chapter}', [ReaderController::class, 'chapter'])->name('bible.chapter');
    });

    Route::prefix('api/bible')->group(function () {
        Route::get('/books-search', [SearchController::class, 'books']);
        Route::get('/resolve', [SearchController::class, 'resolve']);
    });

    Route::get('/favoritos', [ReaderController::class, 'favorites'])->name('bible.favorites');
    Route::get('/api/favorites', [FavoriteController::class, 'index']);
    Route::post('/api/favorites', [FavoriteController::class, 'store']);
    Route::delete('/api/favorites', [FavoriteController::class, 'destroy']);

    Route::get('/api/notes', [NoteController::class, 'index']);
    Route::put('/api/notes', [NoteController::class, 'upsert']);
    Route::delete('/api/notes', [NoteController::class, 'destroy']);

    Route::get('/notas', [ReaderController::class, 'notes'])->name('bible.notes');
    Route::get('/buscar', [TextSearchController::class, 'search'])->name('bible.search');
});

require __DIR__.'/settings.php';

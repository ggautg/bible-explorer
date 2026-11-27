<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Bible\FavoriteController;
use App\Http\Controllers\Bible\GraphController;
use App\Http\Controllers\Bible\NoteController;
use App\Http\Controllers\Bible\ReaderController;
use App\Http\Controllers\Bible\SearchController;
use App\Http\Controllers\Bible\ShareController;
use App\Http\Controllers\Bible\TextSearchController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostInteractionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/compartido/{book}/{chapter}/{verse}', [ShareController::class, 'show']);

/**
 * Área social (Twitter-like): feed, perfiles, posts e interacciones.
 */
Route::middleware('auth')->group(function () {
    // Feed principal
    Route::get('/comunidad', [PostController::class, 'index'])->name('community.feed');
    Route::get('/inicio', fn () => redirect()->route('community.feed'));

    // Perfil
    Route::get('/perfil/{user:slug}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/usuarios/{user:slug}/seguir', [FollowController::class, 'toggle'])
        ->middleware('throttle:social')->name('users.follow.toggle');

    // Publicaciones
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:social')->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::post('/posts/{post}/responder', [PostController::class, 'reply'])
        ->middleware('throttle:social')->name('posts.reply');
    Route::post('/posts/{post}/reportar', [ReportController::class, 'store'])
        ->middleware('throttle:social')->name('posts.report');
    Route::post('/posts/{post}/like', [PostInteractionController::class, 'toggleLike'])
        ->middleware('throttle:social')->name('posts.like');
    Route::post('/posts/{post}/rebiblear', [PostInteractionController::class, 'toggleRebible'])
        ->middleware('throttle:social')->name('posts.rebible');

    // Herramientas bíblicas (aparte del foco social)
    Route::get('/devocionario', fn () => inertia('Devotional/Index'))->name('devotional.index');

    Route::get('/bible/{book}/{chapter}/{verse}', [GraphController::class, 'show'])->name('bible.graph');

    Route::prefix('leer')->group(function () {
        Route::get('/', [ReaderController::class, 'books'])->name('bible.books');
        Route::get('/{book}', [ReaderController::class, 'chapters'])->name('bible.chapters');
        Route::get('/{book}/{chapter}', [ReaderController::class, 'chapter'])->name('bible.chapter');
    });

    Route::prefix('api/bible')->group(function () {
        Route::get('/books-search', [SearchController::class, 'books']);
        Route::get('/structure', [SearchController::class, 'structure']);
        Route::get('/resolve', [SearchController::class, 'resolve']);
    });

    Route::get('/favoritos', [ReaderController::class, 'favorites'])->name('bible.favorites');
    Route::get('/api/favorites', [FavoriteController::class, 'index']);
    Route::post('/api/favorites', [FavoriteController::class, 'store'])->middleware('throttle:bible-write');
    Route::delete('/api/favorites', [FavoriteController::class, 'destroy'])->middleware('throttle:bible-write');

    Route::get('/api/notes', [NoteController::class, 'index']);
    Route::put('/api/notes', [NoteController::class, 'upsert'])->middleware('throttle:bible-write');
    Route::delete('/api/notes', [NoteController::class, 'destroy'])->middleware('throttle:bible-write');

    Route::get('/notas', [ReaderController::class, 'notes'])->name('bible.notes');
    Route::get('/buscar', [TextSearchController::class, 'search'])->name('bible.search');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/usuarios/{user}/rol', [AdminController::class, 'toggleRole'])->name('users.role');
    Route::post('/usuarios/{user}/ban', [AdminController::class, 'ban'])->name('users.ban');
    Route::post('/usuarios/{user}/desbanear', [AdminController::class, 'unban'])->name('users.unban');
    Route::post('/reportes/{report}/resolver', [AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::delete('/posts/{post}', [AdminController::class, 'destroyPost'])->name('posts.destroy');
});

require __DIR__.'/settings.php';

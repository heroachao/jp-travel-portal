<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, '__invoke'])->name('home');
Route::get('/articles', [\App\Http\Controllers\Public\ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [\App\Http\Controllers\Public\ArticleController::class, 'show'])->name('articles.show');
Route::get('/destinations', [\App\Http\Controllers\Public\DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [\App\Http\Controllers\Public\DestinationController::class, 'show'])->name('destinations.show');
Route::get('/topics/{topic:slug}', [\App\Http\Controllers\Public\TopicController::class, 'show'])->name('topics.show');
Route::get('/tags/{tag:slug}', [\App\Http\Controllers\Public\TagController::class, 'show'])->name('tags.show');
Route::get('/search', \App\Http\Controllers\Public\SearchController::class)->name('search');

Route::view('/health', 'health')->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [\App\Http\Controllers\Auth\SessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\SessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [\App\Http\Controllers\Auth\SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::get('/articles', \App\Livewire\Admin\Articles\ArticleIndex::class)->name('articles.index');
        Route::get('/articles/create', \App\Livewire\Admin\Articles\ArticleForm::class)->name('articles.create');
        Route::get('/articles/{article}/edit', \App\Livewire\Admin\Articles\ArticleForm::class)->name('articles.edit');
        Route::get('/articles/{article}/review', \App\Livewire\Admin\Articles\ReviewPanel::class)->name('articles.review');
        Route::get('/destinations', \App\Livewire\Admin\Destinations\DestinationIndex::class)->name('destinations.index');
        Route::get('/topics', \App\Livewire\Admin\Topics\TopicIndex::class)->name('topics.index');
        Route::get('/tags', \App\Livewire\Admin\Tags\TagIndex::class)->name('tags.index');
        Route::get('/ads', \App\Livewire\Admin\Ads\AdPlacementIndex::class)->name('ads.index');
    });

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
    });

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, '__invoke'])->name('home');
Route::get('/about', [\App\Http\Controllers\Public\LegalPageController::class, 'about'])->name('pages.about');
Route::get('/contact', [\App\Http\Controllers\Public\LegalPageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy-policy', [\App\Http\Controllers\Public\LegalPageController::class, 'privacy'])->name('pages.privacy');
Route::get('/privacy', [\App\Http\Controllers\Public\LegalPageController::class, 'privacy'])->name('pages.privacy-short');
Route::get('/terms', [\App\Http\Controllers\Public\LegalPageController::class, 'terms'])->name('pages.terms');
Route::get('/disclaimer', [\App\Http\Controllers\Public\LegalPageController::class, 'disclaimer'])->name('pages.disclaimer');
Route::get('/articles', [\App\Http\Controllers\Public\ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [\App\Http\Controllers\Public\ArticleController::class, 'show'])->name('articles.show');
Route::get('/tools', [\App\Http\Controllers\Public\TravelToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{tool}', [\App\Http\Controllers\Public\TravelToolController::class, 'show'])->name('tools.show');
Route::get('/destinations', [\App\Http\Controllers\Public\DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [\App\Http\Controllers\Public\DestinationController::class, 'show'])->name('destinations.show');
Route::get('/regions', [\App\Http\Controllers\Public\DestinationController::class, 'regions'])->name('regions.index');
Route::get('/regions/{destination:slug}', [\App\Http\Controllers\Public\DestinationController::class, 'region'])->name('regions.show');
Route::get('/categories/{category:slug}', [\App\Http\Controllers\Public\TravelCategoryController::class, 'show'])->name('categories.show');
Route::get('/topics/{topic:slug}', [\App\Http\Controllers\Public\TopicController::class, 'show'])->name('topics.show');
Route::get('/tags/{tag:slug}', [\App\Http\Controllers\Public\TagController::class, 'show'])->name('tags.show');
Route::get('/image-credits', \App\Http\Controllers\Public\ImageCreditController::class)->name('image-credits');
Route::get('/search', \App\Http\Controllers\Public\SearchController::class)->name('search');
Route::get('/sitemap.xml', \App\Http\Controllers\Public\SitemapController::class)->name('sitemap');
Route::get('/robots.txt', \App\Http\Controllers\Public\RobotsController::class)->name('robots');
Route::get('/ads.txt', static fn () => response(
    "google.com, pub-3754179629894278, DIRECT, f08c47fec0942fa0\n",
    200,
    ['Content-Type' => 'text/plain'],
))->name('ads-txt');

Route::view('/health', 'health')->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [\App\Http\Controllers\Auth\SessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::post('/logout', [\App\Http\Controllers\Auth\SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin', 'admin.log'])
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
        Route::get('/travel-categories', \App\Livewire\Admin\TravelCategories\TravelCategoryIndex::class)->name('travel-categories.index');
        Route::get('/service-links', \App\Livewire\Admin\ServiceLinks\ServiceLinkIndex::class)->name('service-links.index');
        Route::get('/homepage-modules', \App\Livewire\Admin\HomepageModules\HomepageModuleIndex::class)->name('homepage-modules.index');
        Route::get('/ads', \App\Livewire\Admin\Ads\AdPlacementIndex::class)->name('ads.index');
        Route::get('/media', \App\Livewire\Admin\Media\MediaAssetIndex::class)->name('media.index');
        Route::get('/settings', \App\Livewire\Admin\Settings\SiteSettingsForm::class)->name('settings.index');
    });

<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\DestinationController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ImageCreditController;
use App\Http\Controllers\Public\LegalPageController;
use App\Http\Controllers\Public\RobotsController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TagController;
use App\Http\Controllers\Public\TopicController;
use App\Http\Controllers\Public\TravelCategoryController;
use App\Http\Controllers\Public\TravelToolController;
use App\Livewire\Admin\Ads\AdPlacementIndex;
use App\Livewire\Admin\Articles\ArticleForm;
use App\Livewire\Admin\Articles\ArticleIndex;
use App\Livewire\Admin\Articles\ReviewPanel;
use App\Livewire\Admin\Destinations\DestinationIndex;
use App\Livewire\Admin\HomepageModules\HomepageModuleIndex;
use App\Livewire\Admin\Media\MediaAssetIndex;
use App\Livewire\Admin\ServiceLinks\ServiceLinkIndex;
use App\Livewire\Admin\Settings\SiteSettingsForm;
use App\Livewire\Admin\Tags\TagIndex;
use App\Livewire\Admin\Topics\TopicIndex;
use App\Livewire\Admin\TravelCategories\TravelCategoryIndex;
use App\Support\PublicUrl;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, '__invoke'])->name('home');
Route::get('/about', [LegalPageController::class, 'about'])->name('pages.about');
Route::get('/contact', [LegalPageController::class, 'contact'])->name('pages.contact');
Route::get('/privacy-policy', [LegalPageController::class, 'privacy'])->name('pages.privacy');
Route::get('/privacy', [LegalPageController::class, 'privacy'])->name('pages.privacy-short');
Route::get('/terms', [LegalPageController::class, 'terms'])->name('pages.terms');
Route::get('/disclaimer', [LegalPageController::class, 'disclaimer'])->name('pages.disclaimer');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/tools', [TravelToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{tool}', [TravelToolController::class, 'show'])->name('tools.show');
Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/regions', [DestinationController::class, 'regions'])->name('regions.index');
Route::get('/regions/{destination:slug}', [DestinationController::class, 'region'])->name('regions.show');
Route::get('/categories/{category:slug}', [TravelCategoryController::class, 'show'])->name('categories.show');
Route::get('/topics/{topic:slug}', [TopicController::class, 'show'])->name('topics.show');
Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
Route::get('/image-credits', ImageCreditController::class)->name('image-credits');
Route::get('/search', SearchController::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::get('/llms.txt', static fn () => response(
    implode("\n", [
        '# Japan Trip Tools',
        '',
        'Japan Trip Tools is an independent English-language Japan travel planning site with practical guides, regional hubs, and browser-based travel tools.',
        '',
        'Sitemap: '.PublicUrl::route('sitemap'),
        'Home: '.PublicUrl::route('home'),
        'Articles: '.PublicUrl::route('articles.index'),
        'Regions: '.PublicUrl::route('regions.index'),
        'Tools: '.PublicUrl::route('tools.index'),
        'Image credits: '.PublicUrl::route('image-credits'),
        '',
        'Important tools:',
        '- Trip planner: '.PublicUrl::route('tools.show', 'trip-planner'),
        '- JR Pass checker: '.PublicUrl::route('tools.show', 'jr-pass-calculator'),
        '- Airport transfer planner: '.PublicUrl::route('tools.show', 'airport-transfer'),
        '- Budget calculator: '.PublicUrl::route('tools.show', 'budget-calculator'),
        '- Luggage planner: '.PublicUrl::route('tools.show', 'luggage-planner'),
        '',
        'Editorial note: pages are written in original professional English for international travelers. Readers should verify current prices, schedules, closures, safety alerts, and booking rules with official providers before travel.',
        '',
    ]),
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('llms-txt');
Route::get('/ads.txt', static fn () => response(
    "google.com, pub-3754179629894278, DIRECT, f08c47fec0942fa0\n",
    200,
    ['Content-Type' => 'text/plain'],
))->name('ads-txt');

Route::view('/health', 'health')->name('health');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'admin', 'admin.log'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'admin.dashboard')->name('dashboard');
        Route::get('/articles', ArticleIndex::class)->name('articles.index');
        Route::get('/articles/create', ArticleForm::class)->name('articles.create');
        Route::get('/articles/{article}/edit', ArticleForm::class)->name('articles.edit');
        Route::get('/articles/{article}/review', ReviewPanel::class)->name('articles.review');
        Route::get('/destinations', DestinationIndex::class)->name('destinations.index');
        Route::get('/topics', TopicIndex::class)->name('topics.index');
        Route::get('/tags', TagIndex::class)->name('tags.index');
        Route::get('/travel-categories', TravelCategoryIndex::class)->name('travel-categories.index');
        Route::get('/service-links', ServiceLinkIndex::class)->name('service-links.index');
        Route::get('/homepage-modules', HomepageModuleIndex::class)->name('homepage-modules.index');
        Route::get('/ads', AdPlacementIndex::class)->name('ads.index');
        Route::get('/media', MediaAssetIndex::class)->name('media.index');
        Route::get('/settings', SiteSettingsForm::class)->name('settings.index');
    });

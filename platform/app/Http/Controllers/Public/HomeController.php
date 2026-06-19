<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Services\Seo\MetaPayload;
use App\Support\TravelTools;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $publishedArticleCount = Article::published()->count();

        return view('public.home', [
            'meta' => new MetaPayload(
                'Japan Travel Guide | Practical Itineraries, Destinations, and Tips',
                'Independent Japan travel guides, destination hubs, itineraries, and practical planning notes.',
                url()->current(),
            ),
            'articleCount' => $publishedArticleCount,
            'articles' => Article::published()->latest('published_at')->limit(12)->get(),
            'serviceLinks' => ServiceLink::query()->enabled()->placement('header')->ordered()->get(),
            'tools' => collect(TravelTools::featured()),
            'toolCount' => count(TravelTools::all()),
            'regionChannels' => Destination::query()
                ->channel()
                ->where('is_indexable', true)
                ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
                ->ordered()
                ->limit(12)
                ->get(),
            'travelCategories' => TravelCategory::query()
                ->visible()
                ->where('is_indexable', true)
                ->withCount(['articles as published_articles_count' => fn ($query) => $query->published()])
                ->ordered()
                ->limit(12)
                ->get(),
            'homepageModules' => HomepageModule::query()
                ->enabled()
                ->ordered()
                ->with([
                    'items' => fn ($query) => $query->enabled()->ordered(),
                    'items.item',
                ])
                ->get(),
            'popularArticles' => Article::published()
                ->orderByDesc('popularity_score')
                ->latest('published_at')
                ->limit(6)
                ->get(),
        ]);
    }
}

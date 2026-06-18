<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\TravelCategory;
use App\Services\Seo\MetaPayload;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $region = trim((string) $request->query('region', ''));
        $category = trim((string) $request->query('category', ''));
        $tag = trim((string) $request->query('tag', ''));
        $coupon = $request->boolean('coupon');
        $requestedSort = (string) $request->query('sort', 'newest');
        $sort = in_array($requestedSort, ['newest', 'updated', 'popular', 'recommended'], true)
            ? $requestedSort
            : 'newest';

        $regions = Destination::query()
            ->channel()
            ->where('is_indexable', true)
            ->ordered()
            ->get();
        $categories = TravelCategory::query()
            ->visible()
            ->where('is_indexable', true)
            ->ordered()
            ->get();
        $tags = Tag::query()->orderBy('name')->get();

        if (! $regions->contains('slug', $region)) {
            $region = '';
        }

        if (! $tags->contains('slug', $tag)) {
            $tag = '';
        }

        $articles = Article::published()
            ->when($query !== '', fn ($builder) => $builder->where(function ($inner) use ($query): void {
                $inner->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%");
            }))
            ->when($region !== '', fn ($builder) => $builder->whereHas('destinations', fn ($inner) => $inner
                ->where('slug', $region)
                ->where('is_channel', true)
                ->where('is_indexable', true)))
            ->when($category !== '', fn ($builder) => $builder->whereHas('travelCategories', fn ($inner) => $inner
                ->where('slug', $category)
                ->where('is_visible', true)
                ->where('is_indexable', true)))
            ->when($tag !== '', fn ($builder) => $builder->whereHas('tags', fn ($inner) => $inner->where('slug', $tag)))
            ->when($coupon, fn ($builder) => $builder->where('has_coupon', true));

        match ($sort) {
            'updated' => $articles
                ->orderByRaw('COALESCE(display_updated_at, published_at) DESC')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
            'popular' => $articles
                ->orderByDesc('popularity_score')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
            'recommended' => $articles
                ->orderByDesc('has_coupon')
                ->orderByDesc('popularity_score')
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
            default => $articles
                ->orderByDesc('published_at')
                ->orderByDesc('id'),
        };

        return view('public.search', [
            'meta' => new MetaPayload('Search Japan Travel Guides', 'Search published Japan travel articles.', route('search')),
            'q' => $query,
            'region' => $region,
            'category' => $category,
            'tag' => $tag,
            'coupon' => $coupon,
            'sort' => $sort,
            'regions' => $regions,
            'categories' => $categories,
            'tags' => $tags,
            'articles' => $articles->paginate(12)->withQueryString(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\TravelCategory;
use App\Services\Seo\MetaPayload;
use App\Support\PublicUrl;
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

        if (! $categories->contains('slug', $category)) {
            $category = '';
        }

        if (! $tags->contains('slug', $tag)) {
            $tag = '';
        }

        $articles = Article::published()
            ->with([
                'destinations' => fn ($builder) => $builder->where('is_indexable', true)->ordered(),
                'travelCategories' => fn ($builder) => $builder->visible()->ordered(),
                'tags' => fn ($builder) => $builder->orderBy('name'),
            ])
            ->when($query !== '', fn ($builder) => $builder->where(function ($inner) use ($query): void {
                $inner->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('body', 'like', "%{$query}%")
                    ->orWhereHas('destinations', fn ($relation) => $relation
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('display_name', 'like', "%{$query}%"))
                    ->orWhereHas('travelCategories', fn ($relation) => $relation
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('display_name', 'like', "%{$query}%"))
                    ->orWhereHas('tags', fn ($relation) => $relation
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('slug', 'like', "%{$query}%"));
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

        if ($query !== '') {
            $like = '%'.mb_strtolower($query).'%';

            $articles->orderByRaw(
                "CASE
                    WHEN LOWER(title) LIKE ? THEN 0
                    WHEN EXISTS (
                        SELECT 1
                        FROM article_destination
                        INNER JOIN destinations ON destinations.id = article_destination.destination_id
                        WHERE article_destination.article_id = articles.id
                        AND (
                            LOWER(destinations.name) LIKE ?
                            OR LOWER(COALESCE(destinations.display_name, '')) LIKE ?
                            OR LOWER(destinations.slug) LIKE ?
                        )
                    ) THEN 1
                    WHEN LOWER(excerpt) LIKE ? THEN 2
                    WHEN EXISTS (
                        SELECT 1
                        FROM article_travel_category
                        INNER JOIN travel_categories ON travel_categories.id = article_travel_category.travel_category_id
                        WHERE article_travel_category.article_id = articles.id
                        AND (
                            LOWER(travel_categories.title) LIKE ?
                            OR LOWER(COALESCE(travel_categories.display_name, '')) LIKE ?
                            OR LOWER(travel_categories.slug) LIKE ?
                        )
                    ) THEN 3
                    WHEN EXISTS (
                        SELECT 1
                        FROM article_tag
                        INNER JOIN tags ON tags.id = article_tag.tag_id
                        WHERE article_tag.article_id = articles.id
                        AND (
                            LOWER(tags.name) LIKE ?
                            OR LOWER(tags.slug) LIKE ?
                        )
                    ) THEN 4
                    WHEN LOWER(body) LIKE ? THEN 5
                    ELSE 6
                END",
                [$like, $like, $like, $like, $like, $like, $like, $like, $like, $like, $like],
            );
        }

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
            'meta' => new MetaPayload('Search Japan Travel Guides', 'Search published Japan travel articles.', PublicUrl::route('search')),
            'q' => $query,
            'region' => $region,
            'category' => $category,
            'tag' => $tag,
            'coupon' => $coupon,
            'sort' => $sort,
            'regions' => $regions,
            'categories' => $categories,
            'tags' => $tags,
            'articles' => $articles->paginate(240)->withQueryString(),
        ]);
    }
}

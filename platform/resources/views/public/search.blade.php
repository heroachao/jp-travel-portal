@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card p-6">
            <p class="public-kicker">Travel Tools</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">Search Japan Travel Guides</h1>
            <p class="mt-3 max-w-2xl text-slate-600">Filter by region, category, tag, popularity, and service availability.</p>
        </div>
        <form method="get" action="{{ \App\Support\PublicUrl::route('search') }}" class="public-card public-search-page-form mt-5 grid gap-3 p-4 md:grid-cols-6" data-search-page-form>
            <input name="q" value="{{ $q }}" class="rounded border px-3 py-2 md:col-span-2" placeholder="Keyword">
            <select name="region" class="rounded border px-3 py-2">
                <option value="">All regions</option>
                @foreach($regions as $option)
                    <option value="{{ $option->slug }}" @selected($region === $option->slug)>{{ $option->display_name ?: $option->name }}</option>
                @endforeach
            </select>
            <select name="category" class="rounded border px-3 py-2">
                <option value="">All categories</option>
                @foreach($categories as $option)
                    <option value="{{ $option->slug }}" @selected($category === $option->slug)>{{ $option->display_name ?: $option->title }}</option>
                @endforeach
            </select>
            <select name="tag" class="rounded border px-3 py-2">
                <option value="">All tags</option>
                @foreach($tags as $option)
                    <option value="{{ $option->slug }}" @selected($tag === $option->slug)>#{{ $option->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded border px-3 py-2">
                <option value="newest" @selected($sort === 'newest')>Newest</option>
                <option value="updated" @selected($sort === 'updated')>Updated</option>
                <option value="popular" @selected($sort === 'popular')>Popular</option>
                <option value="recommended" @selected($sort === 'recommended')>Recommended</option>
            </select>
            <label class="flex items-center gap-2 text-sm md:col-span-2">
                <input type="checkbox" name="coupon" value="1" @checked($coupon)>
                Coupon/service available
            </label>
            <button class="rounded bg-slate-950 px-4 py-2 font-semibold text-white md:col-span-1">Search</button>
        </form>
        @ad('content-mid-rectangle')
        <div class="public-card mt-5 flex flex-wrap items-center justify-between gap-3 p-4">
            <p class="text-sm font-semibold text-slate-600">
                Showing <span data-search-result-count>{{ $articles->count() }}</span> guides
            </p>
            <p class="text-sm text-slate-500" data-search-active-label>
                Use keywords, regions, categories, tags, and sorting to narrow the guide feed.
            </p>
        </div>
        <div class="mt-5 grid gap-3" data-search-results>
            @foreach($articles as $article)
                @php
                    $articleDestinations = $article->destinations->pluck('slug')->implode(' ');
                    $articleDestinationNames = $article->destinations->map(fn ($destination) => $destination->display_name ?: $destination->name)->implode(' ');
                    $articleCategories = $article->travelCategories->pluck('slug')->implode(' ');
                    $articleCategoryNames = $article->travelCategories->map(fn ($category) => $category->display_name ?: $category->title)->implode(' ');
                    $articleTags = $article->tags->pluck('slug')->implode(' ');
                    $articleTagNames = $article->tags->pluck('name')->implode(' ');
                    $articleSearchText = implode(' ', [
                        $article->title,
                        $article->excerpt,
                        strip_tags((string) $article->body),
                        $articleDestinationNames,
                        $articleCategoryNames,
                        $articleTagNames,
                    ]);
                @endphp
                <a
                    class="public-row-story public-card public-search-result"
                    href="{{ \App\Support\PublicUrl::route('articles.show', $article) }}"
                    data-title="{{ e($article->title) }}"
                    data-index="{{ e(\Illuminate\Support\Str::lower($articleSearchText)) }}"
                    data-region="{{ e($articleDestinations) }}"
                    data-category="{{ e($articleCategories) }}"
                    data-tag="{{ e($articleTags) }}"
                    data-coupon="{{ $article->has_coupon ? '1' : '0' }}"
                    data-popularity="{{ $article->popularity_score }}"
                    data-published="{{ $article->published_at?->timestamp ?: 0 }}"
                    data-updated="{{ ($article->display_updated_at ?: $article->updated_at)?->timestamp ?: 0 }}"
                >
                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h2>{{ $article->title }}</h2>
                        @if($article->excerpt)
                            <p>{{ $article->excerpt }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="public-card mt-5 hidden p-6 text-slate-600" data-search-empty>
            <h2 class="text-xl font-black text-slate-950">No matching guides yet</h2>
            <p class="mt-2">Try a broader region, remove one tag, or search for Tokyo, Kyoto, JR Pass, airport transfer, budget, luggage, food, or Okinawa.</p>
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

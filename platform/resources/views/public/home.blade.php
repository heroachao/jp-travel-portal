@extends('layouts.public')

@section('content')
    @php
        $leadArticle = $articles->first();
        $secondaryArticles = $articles->skip(1)->take(6);
        $latestRail = $articles->take(7);
        $routeDesk = $articles->skip(7)->take(5);
        $moduleItemCard = function ($moduleItem): ?array {
            $item = $moduleItem->item;

            if ($item instanceof \App\Models\Article) {
                if ($item->status !== \App\Enums\ArticleStatus::Published || ! $item->published_at?->lte(now())) {
                    return null;
                }

                return [
                    'url' => route('articles.show', $item),
                    'label' => $moduleItem->label ?: $item->title,
                    'summary' => $moduleItem->summary ?: $item->excerpt,
                    'external' => false,
                ];
            }

            if ($item instanceof \App\Models\Destination) {
                if (! $item->is_indexable) {
                    return null;
                }

                return [
                    'url' => route($item->is_channel ? 'regions.show' : 'destinations.show', $item),
                    'label' => $moduleItem->label ?: ($item->display_name ?: $item->name),
                    'summary' => $moduleItem->summary ?: $item->excerpt,
                    'external' => false,
                ];
            }

            if ($item instanceof \App\Models\TravelCategory) {
                if (! $item->is_visible || ! $item->is_indexable) {
                    return null;
                }

                return [
                    'url' => route('categories.show', $item),
                    'label' => $moduleItem->label ?: ($item->display_name ?: $item->title),
                    'summary' => $moduleItem->summary ?: $item->excerpt,
                    'external' => false,
                ];
            }

            if ($item instanceof \App\Models\ServiceLink) {
                if (! $item->is_enabled) {
                    return null;
                }

                return [
                    'url' => $item->url,
                    'label' => $moduleItem->label ?: $item->label,
                    'summary' => $moduleItem->summary,
                    'external' => true,
                ];
            }

            return null;
        };
    @endphp

    <section class="mx-auto grid max-w-7xl gap-5 px-4 py-5 md:grid-cols-[minmax(0,1fr)_300px] xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="public-scoreboard">
            <div class="public-score-card">
                <span>Live Planner</span>
                <b>{{ $regionChannels->count() }}</b>
                <small>regions ready</small>
                <div class="public-score-list">
                    @foreach($regionChannels->take(3) as $destination)
                        <em>{{ $destination->display_name ?: $destination->name }}</em>
                    @endforeach
                </div>
            </div>
            <div class="public-score-card">
                <span>Guide Feed</span>
                <b>{{ $articleCount }}</b>
                <small>published stories</small>
                <div class="public-score-list">
                    @foreach($latestRail->take(3) as $article)
                        <em>{{ $article->title }}</em>
                    @endforeach
                </div>
            </div>
            <div class="public-score-card">
                <span>Trip Tools</span>
                <b>{{ $serviceLinks->count() }}</b>
                <small>service links</small>
                <div class="public-score-list">
                    @forelse($serviceLinks->take(3) as $link)
                        <em>{{ $link->label }}</em>
                    @empty
                        <em>Guide Search</em>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="public-card public-trip-card">
            <p class="public-kicker">My Trip</p>
            <h2>Build a smarter Japan route.</h2>
            <p>Search guides, compare regions, and keep practical tools close while planning.</p>
            <a href="{{ route('search') }}">Open travel search</a>
        </aside>
    </section>

    <section class="mx-auto grid max-w-7xl gap-5 px-4 pb-8 xl:grid-cols-[minmax(0,1.55fr)_minmax(280px,0.75fr)]">
        <div class="public-card public-lead-card">
            <p class="public-kicker">Top Guide</p>
            @if($leadArticle)
                <a href="{{ route('articles.show', $leadArticle) }}">
                    <h1>{{ $leadArticle->title }}</h1>
                    <p>{{ $leadArticle->excerpt }}</p>
                </a>
                <div class="public-meta-row">
                    <span>{{ $leadArticle->published_at?->format('M j, Y') }}</span>
                    @if($leadArticle->reading_time_minutes)
                        <span>{{ $leadArticle->reading_time_minutes }} min read</span>
                    @endif
                    @if($leadArticle->has_coupon)
                        <span>Service available</span>
                    @endif
                </div>
            @else
                <h1>Plan Japan with practical guides and route tools.</h1>
                <p>Publish your first guide in the admin to feature it here.</p>
            @endif
            <div class="public-travel-graphic" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </div>
        </div>

        <aside class="public-card public-news-stack">
            <div class="public-section-heading">
                <h2>Latest Updates</h2>
                <a href="{{ route('articles.index') }}">All guides</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($latestRail as $article)
                    <a class="public-mini-story" href="{{ route('articles.show', $article) }}">
                        <span>{{ $article->published_at?->format('M j') }}</span>
                        <strong>{{ $article->title }}</strong>
                    </a>
                @empty
                    <p class="py-5 text-sm text-slate-500">No published guides yet.</p>
                @endforelse
            </div>
        </aside>
    </section>

    @ad('home-after-hero')

    <section class="mx-auto grid max-w-7xl gap-5 px-4 pb-10 lg:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-5">
            <div class="public-section-heading">
                <h2>Featured Guides</h2>
                <a href="{{ route('articles.index') }}">Read more</a>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($secondaryArticles as $article)
                    <a class="public-story-card" href="{{ route('articles.show', $article) }}">
                        <span class="public-story-thumb"></span>
                        <small>{{ $article->published_at?->format('M j, Y') }}</small>
                        <h3>{{ $article->title }}</h3>
                        <p>{{ $article->excerpt }}</p>
                    </a>
                @empty
                    @foreach($popularArticles->take(3) as $article)
                        <a class="public-story-card" href="{{ route('articles.show', $article) }}">
                            <span class="public-story-thumb"></span>
                            <small>{{ $article->published_at?->format('M j, Y') }}</small>
                            <h3>{{ $article->title }}</h3>
                            <p>{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                @endforelse
            </div>

            @if($routeDesk->isNotEmpty())
                <section class="public-card public-route-desk">
                    <div class="public-section-heading">
                        <div>
                            <h2>Route Desk</h2>
                            <p>More planning angles to keep exploring Japan by region, season, and transport style.</p>
                        </div>
                        <a href="{{ route('articles.index') }}">Open feed</a>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @foreach($routeDesk as $article)
                            <a class="public-route-row" href="{{ route('articles.show', $article) }}">
                                <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    <strong>{{ $article->title }}</strong>
                                    <small>{{ $article->published_at?->format('M j') }} @if($article->reading_time_minutes) / {{ $article->reading_time_minutes }} min @endif</small>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="public-card">
                <div class="public-section-heading">
                    <h2>Popular Articles</h2>
                    <a href="{{ route('search', ['sort' => 'popular']) }}">Popular feed</a>
                </div>
                <div class="mt-3 grid gap-3">
                    @foreach($popularArticles as $article)
                        <a class="public-row-story" href="{{ route('articles.show', $article) }}">
                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3>{{ $article->title }}</h3>
                                <p>{{ $article->excerpt }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            @foreach($homepageModules as $module)
                @php
                    $publicModuleItems = $module->items
                        ->map(fn ($moduleItem) => $moduleItemCard($moduleItem))
                        ->filter();
                @endphp
                @if($publicModuleItems->isNotEmpty())
                    <section class="public-card">
                        <div class="public-section-heading">
                            <div>
                                <h2>{{ $module->title }}</h2>
                                @if($module->subtitle)
                                    <p>{{ $module->subtitle }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach($publicModuleItems as $card)
                                <a class="public-tool-link" href="{{ $card['url'] }}" @if($card['external']) target="_blank" rel="nofollow noopener sponsored" @endif>
                                    <strong>{{ $card['label'] }}</strong>
                                    @if($card['summary'])
                                        <span>{{ $card['summary'] }}</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>

        <aside class="space-y-5">
            <section class="public-card">
                <div class="public-section-heading">
                    <h2>Region Standings</h2>
                    <a href="{{ route('regions.index') }}">All</a>
                </div>
                <div class="mt-3 divide-y divide-slate-100">
                    @foreach($regionChannels as $destination)
                        <a class="public-ranking-row" href="{{ route('regions.show', $destination) }}">
                            <span>{{ $loop->iteration }}</span>
                            <strong>{{ $destination->display_name ?: $destination->name }}</strong>
                            <small>{{ $destination->published_articles_count }} {{ \Illuminate\Support\Str::plural('guide', $destination->published_articles_count) }}</small>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="public-card">
                <div class="public-section-heading">
                    <h2>Travel Tools</h2>
                </div>
                <div class="mt-3 grid gap-3">
                    @forelse($serviceLinks as $link)
                        <a class="public-tool-link" target="_blank" rel="nofollow noopener sponsored" href="{{ $link->url }}">
                            <strong>{{ $link->label }}</strong>
                            <span>{{ ucfirst(str_replace('_', ' ', $link->type)) }}</span>
                        </a>
                    @empty
                        <a class="public-tool-link" href="{{ route('search') }}">
                            <strong>Guide Search</strong>
                            <span>Find routes, food, transport, and seasonal ideas.</span>
                        </a>
                    @endforelse
                </div>
            </section>

            <section class="public-card">
                <div class="public-section-heading">
                    <h2>Categories</h2>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($travelCategories as $category)
                        <a class="public-tag-pill" href="{{ route('categories.show', $category) }}">
                            {{ $category->display_name ?: $category->title }}
                            <small>{{ $category->published_articles_count }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>
@endsection

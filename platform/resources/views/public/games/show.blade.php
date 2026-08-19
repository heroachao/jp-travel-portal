@extends('layouts.public')

@push('structured-data')
    <script type="application/ld+json">{!! json_encode($gameJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card travel-game-show-hero" style="--game-accent: {{ $game['accent'] }}">
            <div>
                <a class="travel-game-back" href="{{ \App\Support\PublicUrl::route('games.index') }}">All games</a>
                <p class="public-kicker">{{ $game['category'] }}</p>
                <h1>{{ $game['name'] }}</h1>
                <p>{{ $game['summary'] }}</p>
                <div class="travel-game-hero-actions">
                    <a href="#play">Play now</a>
                    <a href="{{ \App\Support\PublicUrl::route('search', ['q' => $game['short_name']]) }}">Related guides</a>
                </div>
            </div>
            <div class="travel-game-poster">
                <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}" loading="eager">
                <dl>
                    <div>
                        <dt>Type</dt>
                        <dd>{{ $game['type'] }}</dd>
                    </div>
                    <div>
                        <dt>Time</dt>
                        <dd>{{ $game['play_time'] }}</dd>
                    </div>
                    <div>
                        <dt>Goal</dt>
                        <dd>{{ $game['hook'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    @ad('content-mid-rectangle')

    <section id="play" class="mx-auto grid max-w-7xl items-start gap-5 px-4 pb-12 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div
            class="public-card travel-game-panel"
            data-japan-game="{{ $game['slug'] }}"
            style="--game-accent: {{ $game['accent'] }}"
        >
            <div class="travel-game-panel-head">
                <div>
                    <p class="public-kicker">{{ $game['type'] }}</p>
                    <h2>{{ $game['short_name'] }}</h2>
                </div>
                <div class="travel-game-stat-row" aria-live="polite">
                    <span><b data-game-score>0</b><small>Score</small></span>
                    <span><b data-game-best>0</b><small>Best</small></span>
                    <span><b data-game-state>Ready</b><small>Status</small></span>
                </div>
            </div>

            <div class="travel-game-objective">Runs locally in your browser. {{ $game['objective'] }}</div>
            <div class="travel-game-stage" data-game-stage>
                <p>Loading game...</p>
            </div>
            <div class="travel-game-controls" data-game-controls></div>
            <script type="application/json" data-game-config>{!! json_encode($game, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
        </div>

        <aside class="space-y-5">
            <section class="public-card travel-game-side">
                <div class="public-section-heading">
                    <h2>More Games</h2>
                    <a href="{{ \App\Support\PublicUrl::route('games.index') }}">All</a>
                </div>
                <div class="mt-3 grid gap-3">
                    @foreach($games as $relatedGame)
                        @continue($relatedGame['slug'] === $game['slug'])
                        <a class="public-tool-link" href="{{ \App\Support\PublicUrl::route('games.show', $relatedGame['slug']) }}">
                            <strong>{{ $relatedGame['short_name'] }}</strong>
                            <span>{{ $relatedGame['type'] }} / {{ $relatedGame['play_time'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="public-card travel-game-side">
                <div class="public-section-heading">
                    <h2>Planning Break</h2>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-600">Play a short round, then jump back into Japan routes, transport decisions, and region guides without leaving the site.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($game['seo_keywords'] as $keyword)
                        <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('search', ['q' => $keyword]) }}">{{ $keyword }}</a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>

    <section class="mx-auto grid max-w-7xl items-start gap-5 px-4 pb-12 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="public-card content-prose p-6 md:p-8">
            <h2>{{ $game['name'] }} strategy</h2>
            <p>{{ $game['name'] }} is designed as a short, repeatable browser game for Japan travel readers. The theme keeps the game connected to trip planning while the rules stay familiar enough for visitors to understand immediately.</p>
            <p>Use it as a quick break between destination research, route planning, and transport checks. The best-score loop is stored in your browser so returning visitors can try to improve their result later.</p>
            <h2>Why travelers may come back</h2>
            <ul>
                <li>{{ $game['hook'] }} creates a simple reason to replay.</li>
                <li>The page runs locally in the browser, so gameplay remains fast on desktop and mobile.</li>
                <li>Related Japan travel links stay nearby instead of sending players to an outside game site.</li>
            </ul>
        </div>
        <aside class="public-card p-6">
            <h2 class="text-2xl font-black">Next travel step</h2>
            <div class="mt-4 grid gap-3">
                <a class="public-tool-link" href="{{ \App\Support\PublicUrl::route('tools.index') }}">
                    <strong>Open travel tools</strong>
                    <span>Plan routes, budget, packing, IC cards, luggage, and rail choices.</span>
                </a>
                <a class="public-tool-link" href="{{ \App\Support\PublicUrl::route('regions.index') }}">
                    <strong>Compare regions</strong>
                    <span>Move from a quick play session into a better Japan itinerary.</span>
                </a>
            </div>
        </aside>
    </section>
@endsection

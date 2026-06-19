@extends('layouts.public')

@push('structured-data')
    <script type="application/ld+json">{!! json_encode($gamesItemListJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card travel-game-hero">
            <div>
                <p class="public-kicker">Japan Games</p>
                <h1>Free Japan-themed browser games for quick travel breaks.</h1>
                <p>Play daily puzzles, arcade runs, memory cards, typing sprints, image puzzles, and food games without leaving Japan Trip Tools.</p>
                <div class="travel-game-hero-actions">
                    <a href="{{ \App\Support\PublicUrl::route('games.show', 'daily-japan-word') }}">Play today's word</a>
                    <a href="{{ \App\Support\PublicUrl::route('games.show', 'fuji-merge-2048') }}">Start Fuji 2048</a>
                </div>
            </div>
            <div class="travel-game-hero-board" aria-label="Japan game collection">
                @foreach(array_slice($games, 0, 4) as $game)
                    <a href="{{ \App\Support\PublicUrl::route('games.show', $game['slug']) }}" style="--game-accent: {{ $game['accent'] }}">
                        <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                        <span>{{ $game['short_name'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @ad('content-mid-rectangle')

    <section class="mx-auto max-w-7xl px-4 pb-12">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($games as $game)
                <a class="public-story-card travel-game-card" href="{{ \App\Support\PublicUrl::route('games.show', $game['slug']) }}" style="--game-accent: {{ $game['accent'] }}">
                    <span class="travel-game-card-thumb">
                        <img src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}" loading="lazy">
                    </span>
                    <small>{{ $game['category'] }}</small>
                    <h2>{{ $game['name'] }}</h2>
                    <p>{{ $game['summary'] }}</p>
                    <span class="travel-game-card-meta">
                        <em>{{ $game['type'] }}</em>
                        <em>{{ $game['play_time'] }}</em>
                        <em>{{ $game['hook'] }}</em>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-5 px-4 pb-12 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="public-card content-prose p-6 md:p-8">
            <h2>Why add games to a Japan travel site?</h2>
            <p>Lightweight browser games can turn a quick search visit into a longer session. A daily word puzzle gives returning visitors a reason to bookmark the site, while arcade and puzzle formats offer short breaks between planning articles.</p>
            <p>The games are built directly into Japan Trip Tools. Players do not need accounts, downloads, or redirects to outside game portals, so the experience keeps readers inside the travel site and close to articles, regions, and planning tools.</p>
            <h2>Game types in this collection</h2>
            <ul>
                <li>Daily word puzzle, number merge, snake arcade, memory match, minesweeper, and runner games.</li>
                <li>Match-three, typing sprint, sliding image puzzle, and ramen order timing games.</li>
                <li>Japan travel themes such as prefectures, rail stations, Mt. Fuji, ramen shops, shrines, food, and seasonal routes.</li>
            </ul>
        </div>
        <aside class="public-card p-6">
            <h2 class="text-2xl font-black">Fast starts</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach(array_slice($games, 0, 6) as $game)
                    <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('games.show', $game['slug']) }}">{{ $game['short_name'] }}</a>
                @endforeach
            </div>
        </aside>
    </section>
@endsection

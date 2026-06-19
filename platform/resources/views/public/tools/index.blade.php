@extends('layouts.public')

@push('structured-data')
    <script type="application/ld+json">{!! json_encode($toolsItemListJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card travel-tools-hero">
            <div>
                <p class="public-kicker">Tools</p>
                <h1>Japan travel tools that stay on this site.</h1>
                <p>Plan routes, budgets, rail passes, luggage, airport transfers, packing, IC cards, allergy phrases, and tax-free shopping without sending readers away.</p>
            </div>
            <div class="travel-tools-hero-grid" aria-label="Tool summary">
                <span><b>{{ count($tools) }}</b><small>on-site tools</small></span>
                <span><b>0</b><small>tool redirects</small></span>
                <span><b>EN</b><small>traveler-facing</small></span>
            </div>
        </div>
    </section>

    @ad('content-mid-rectangle')

    <section class="mx-auto max-w-7xl px-4 pb-12">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($tools as $tool)
                <a class="public-story-card travel-tool-card" href="{{ \App\Support\PublicUrl::route('tools.show', $tool['slug']) }}" style="--tool-accent: {{ $tool['accent'] }}">
                    <span class="travel-tool-card-top">
                        <small>{{ $tool['category'] }}</small>
                        <b>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</b>
                    </span>
                    <h2>{{ $tool['name'] }}</h2>
                    <p>{{ $tool['summary'] }}</p>
                    <span class="travel-tool-chip-row">
                        @foreach($tool['inputs'] as $input)
                            <em>{{ $input }}</em>
                        @endforeach
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto grid max-w-7xl gap-5 px-4 pb-12 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="public-card content-prose p-6 md:p-8">
            <h2>Practical Japan travel tools for search-led planning</h2>
            <p>Japan Trip Tools focuses on decisions travelers actually search for before a trip: whether a rail pass is worth it, how much a first Japan itinerary may cost, what to pack by season, how to move luggage, and how to prepare food allergy phrases in Japanese.</p>
            <p>Each tool keeps the planning step on this site, then links readers back into related guides, regions, and articles. That structure helps visitors move from a quick calculation to a complete trip plan instead of leaving after one answer.</p>
            <h2>High-intent planning topics covered here</h2>
            <ul>
                <li>Rail value checks for JR Pass and long-distance Shinkansen routes.</li>
                <li>Airport arrival planning for Haneda, Narita, Kansai, Fukuoka, New Chitose, and Naha.</li>
                <li>Budget, luggage, packing, IC card, shopping, and food allergy planning.</li>
            </ul>
        </div>
        <aside class="public-card p-6">
            <h2 class="text-2xl font-black">Start with a common trip question</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('search', ['q' => 'JR Pass worth it']) }}">JR Pass worth it</a>
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('search', ['q' => 'Japan travel budget']) }}">Japan travel budget</a>
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('search', ['q' => 'Narita airport transfer']) }}">Narita airport transfer</a>
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('search', ['q' => 'Japan luggage forwarding']) }}">Luggage forwarding</a>
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('articles.index') }}">Latest guides</a>
            </div>
        </aside>
    </section>
@endsection

@extends('layouts.public')

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

    <section class="mx-auto max-w-7xl px-4 pb-12">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($tools as $tool)
                <a class="public-story-card travel-tool-card" href="{{ route('tools.show', $tool['slug']) }}" style="--tool-accent: {{ $tool['accent'] }}">
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
@endsection

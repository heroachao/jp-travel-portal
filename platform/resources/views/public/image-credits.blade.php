@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card travel-tools-hero">
            <div>
                <p class="public-kicker">Image Credits</p>
                <h1>Article image sources and licenses.</h1>
                <p>Article pages keep readers inside Japan Trip Tools. Detailed image attribution, source, and license records are collected here.</p>
            </div>
            <div class="travel-tools-hero-grid" aria-label="Image credit summary">
                <span><b>{{ $credits->count() }}</b><small>image records</small></span>
                <span><b>CC</b><small>license notes</small></span>
                <span><b>Stay</b><small>no outbound image links</small></span>
            </div>
        </div>
    </section>

    @ad('content-mid-rectangle')

    <section class="mx-auto max-w-7xl px-4 pb-12">
        <div class="grid gap-4">
            @forelse($credits as $credit)
                <article id="{{ $credit['anchor'] }}" class="public-card image-credit-row">
                    @if($credit['image_url'])
                        <img src="{{ $credit['image_url'] }}" alt="{{ $credit['image_alt'] }}" loading="lazy">
                    @endif
                    <div>
                        <p class="public-kicker">{{ $credit['license'] }}</p>
                        <h2><a href="{{ $credit['url'] }}">{{ $credit['title'] }}</a></h2>
                        @if($credit['caption'])
                            <p>{{ $credit['caption'] }}</p>
                        @endif
                        <dl>
                            <div>
                                <dt>Credit</dt>
                                <dd>{{ $credit['attribution'] }}</dd>
                            </div>
                            <div>
                                <dt>Source</dt>
                                <dd>{{ $credit['source_url'] ?: 'Source recorded by attribution note' }}</dd>
                            </div>
                            @if($credit['license_url'])
                                <div>
                                    <dt>License</dt>
                                    <dd>{{ $credit['license'] }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </article>
            @empty
                <div class="public-card p-6">
                    <h2 class="text-xl font-black">No image credits yet</h2>
                    <p class="mt-2 text-slate-600">Published articles with structured image credit data will appear here.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection

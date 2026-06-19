@extends('layouts.public')

@push('structured-data')
    <script type="application/ld+json">{!! json_encode($articleJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @if($faqJsonLd)
        <script type="application/ld+json">{!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
@endpush

@section('content')
    <article class="mx-auto max-w-5xl px-4 py-8">
        <header class="public-card p-6 md:p-8">
            <p class="public-kicker">{{ $article->published_at?->format('F j, Y') }}</p>
            <h1 class="mt-3 max-w-4xl text-4xl font-black leading-tight tracking-tight md:text-6xl">{{ $article->title }}</h1>
            <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">{{ $article->excerpt }}</p>
            <div class="public-meta-row">
                @if($article->published_at)
                    <span>Published {{ $article->published_at->format('F j, Y') }}</span>
                @endif
                @if($article->display_updated_at)
                    <span>Updated {{ $article->display_updated_at->format('F j, Y') }}</span>
                @endif
                @if($contentEnhancement['reviewed_at'])
                    <span>Reviewed {{ $contentEnhancement['reviewed_at'] }}</span>
                @endif
                @if($contentEnhancement['reading_time_minutes'])
                    <span>{{ $contentEnhancement['reading_time_minutes'] }} min read</span>
                @endif
                @if($article->source_name)
                    @if($article->source_url)
                        <a class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600" href="{{ $article->source_url }}" target="_blank" rel="nofollow noopener">{{ $article->source_name }}</a>
                    @else
                        <span>{{ $article->source_name }}</span>
                    @endif
                @endif
            </div>
            <div class="article-trust-strip">
                <div>
                    <strong>Editorial review</strong>
                    <span>Original English planning guide, reviewed for practical travel decisions and official-source checks.</span>
                </div>
                <div>
                    <strong>Primary source</strong>
                    <span>{{ $contentEnhancement['source_label'] ?: 'Official tourism and transport references' }}</span>
                </div>
                <div>
                    <strong>Before booking</strong>
                    <span>Verify current prices, hours, routes, weather alerts, and reservation rules with official providers.</span>
                </div>
            </div>
        </header>
        @if($article->coverMedia)
            <figure class="mt-8">
                <img src="{{ Storage::disk($article->coverMedia->disk)->url($article->coverMedia->path) }}" alt="{{ $article->coverMedia->alt_text }}" class="aspect-[16/9] w-full rounded-lg object-cover">
                @if($article->coverMedia->source_note)
                    <figcaption class="mt-2 text-xs text-slate-500">{{ $article->coverMedia->source_note }}</figcaption>
                @endif
            </figure>
        @endif
        <div class="public-card mt-5 flex flex-wrap gap-2 p-4 text-sm text-slate-600">
            @foreach($article->destinations as $destination)
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route($destination->is_channel ? 'regions.show' : 'destinations.show', $destination) }}">{{ $destination->display_name ?: $destination->name }}</a>
            @endforeach
            @foreach($article->travelCategories as $category)
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
            @endforeach
            @foreach($article->topics as $topic)
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('topics.show', $topic) }}">{{ $topic->title }}</a>
            @endforeach
            @foreach($article->tags as $tag)
                <a class="public-tag-pill" href="{{ \App\Support\PublicUrl::route('tags.show', $tag) }}">#{{ $tag->name }}</a>
            @endforeach
        </div>
        <div class="content-prose public-card mt-5 p-6 md:p-8">
            @ad('article-body-middle')
            {!! $article->optimizedBodyHtml() !!}
            {!! $contentEnhancement['html'] !!}
        </div>
        @if($article->enabledFaqs->isNotEmpty())
            <section class="public-card mt-5 p-6">
                <h2 class="text-2xl font-black">FAQ</h2>
                <div class="mt-5 space-y-4">
                    @foreach($article->enabledFaqs as $faq)
                        <div class="rounded-lg border border-slate-200 bg-white p-5">
                            <h3 class="font-semibold">{{ $faq->question }}</h3>
                            <div class="content-prose mt-3 text-sm text-slate-700">{!! $faq->answer !!}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection

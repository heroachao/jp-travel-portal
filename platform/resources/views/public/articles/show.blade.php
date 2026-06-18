@extends('layouts.public')

@section('content')
    <article class="mx-auto max-w-3xl px-5 py-12">
        <p class="text-sm text-emerald-800">{{ $article->published_at?->format('F j, Y') }}</p>
        <h1 class="mt-3 text-4xl font-bold leading-tight">{{ $article->title }}</h1>
        <p class="mt-5 text-lg text-slate-600">{{ $article->excerpt }}</p>
        <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600">
            @if($article->published_at)
                <span>Published {{ $article->published_at->format('F j, Y') }}</span>
            @endif
            @if($article->display_updated_at)
                <span>Updated {{ $article->display_updated_at->format('F j, Y') }}</span>
            @endif
            @if($article->reading_time_minutes)
                <span>{{ $article->reading_time_minutes }} min read</span>
            @endif
            @if($article->source_name)
                @if($article->source_url)
                    <a class="underline" href="{{ $article->source_url }}" target="_blank" rel="nofollow noopener">{{ $article->source_name }}</a>
                @else
                    <span>{{ $article->source_name }}</span>
                @endif
            @endif
        </div>
        <div class="mt-8 border-y py-4 text-sm text-slate-600">
            @foreach($article->destinations as $destination)
                <a class="mr-3 underline" href="{{ route($destination->is_channel ? 'regions.show' : 'destinations.show', $destination) }}">{{ $destination->display_name ?: $destination->name }}</a>
            @endforeach
            @foreach($article->travelCategories as $category)
                <a class="mr-3 underline" href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
            @endforeach
            @foreach($article->topics as $topic)
                <a class="mr-3 underline" href="{{ route('topics.show', $topic) }}">{{ $topic->title }}</a>
            @endforeach
            @foreach($article->tags as $tag)
                <a class="mr-3 underline" href="{{ route('tags.show', $tag) }}">#{{ $tag->name }}</a>
            @endforeach
        </div>
        <div class="content-prose mt-8">
            @ad('article-body-middle')
            {!! $article->body !!}
        </div>
        @if($article->enabledFaqs->isNotEmpty())
            <section class="mt-10 border-t pt-8">
                <h2 class="text-2xl font-semibold">FAQ</h2>
                <div class="mt-5 space-y-4">
                    @foreach($article->enabledFaqs as $faq)
                        <div class="rounded border bg-white p-5">
                            <h3 class="font-semibold">{{ $faq->question }}</h3>
                            <div class="content-prose mt-3 text-sm text-slate-700">{!! $faq->answer !!}</div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
    @if($faqJsonLd)
        <script type="application/ld+json">{!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
@endsection

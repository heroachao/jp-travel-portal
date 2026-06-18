@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-14">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Independent Japan Travel</p>
        <h1 class="mt-3 max-w-3xl text-5xl font-bold leading-tight">Practical guides for deeper Japan trips.</h1>
        <p class="mt-5 max-w-2xl text-lg text-slate-600">Explore destinations, seasonal planning notes, and editorial travel guides built for search intent and real itinerary decisions.</p>
    </section>

    <section class="mx-auto grid max-w-6xl gap-8 px-5 pb-14 lg:grid-cols-[1.2fr_.8fr]">
        <div>
            <h2 class="text-2xl font-semibold">Latest Articles</h2>
            <div class="mt-5 grid gap-4">
                @foreach($articles as $article)
                    <a class="rounded-lg border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                        <h3 class="font-semibold">{{ $article->title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                    </a>
                @endforeach
            </div>
        </div>
        <aside class="grid gap-8">
            <div>
                <h2 class="text-2xl font-semibold">Destinations</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($destinations as $destination)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="text-2xl font-semibold">Travel Topics</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($topics as $topic)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('topics.show', $topic) }}">{{ $topic->title }}</a>
                    @endforeach
                </div>
            </div>
        </aside>
    </section>
@endsection

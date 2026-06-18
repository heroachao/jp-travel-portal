@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card p-6">
            <p class="public-kicker">Guide Feed</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">Japan Travel Articles</h1>
            <p class="mt-3 max-w-2xl text-slate-600">Fresh planning notes, destination explainers, route ideas, and practical travel tools for Japan trips.</p>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($articles as $article)
                <a class="public-story-card" href="{{ route('articles.show', $article) }}">
                    <span class="public-story-thumb"></span>
                    <small>{{ $article->published_at?->format('M j, Y') }}</small>
                    <h2>{{ $article->title }}</h2>
                    <p>{{ $article->excerpt }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

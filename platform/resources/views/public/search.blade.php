@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card p-6">
            <p class="public-kicker">Travel Tools</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight">Search Japan Travel Guides</h1>
            <p class="mt-3 max-w-2xl text-slate-600">Filter by region, category, tag, popularity, and service availability.</p>
        </div>
        <form method="get" action="{{ route('search') }}" class="public-card mt-5 grid gap-3 p-4 md:grid-cols-6">
            <input name="q" value="{{ $q }}" class="rounded border px-3 py-2 md:col-span-2" placeholder="Keyword">
            <select name="region" class="rounded border px-3 py-2">
                <option value="">All regions</option>
                @foreach($regions as $option)
                    <option value="{{ $option->slug }}" @selected($region === $option->slug)>{{ $option->display_name ?: $option->name }}</option>
                @endforeach
            </select>
            <select name="category" class="rounded border px-3 py-2">
                <option value="">All categories</option>
                @foreach($categories as $option)
                    <option value="{{ $option->slug }}" @selected($category === $option->slug)>{{ $option->display_name ?: $option->title }}</option>
                @endforeach
            </select>
            <select name="tag" class="rounded border px-3 py-2">
                <option value="">All tags</option>
                @foreach($tags as $option)
                    <option value="{{ $option->slug }}" @selected($tag === $option->slug)>#{{ $option->name }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded border px-3 py-2">
                <option value="newest" @selected($sort === 'newest')>Newest</option>
                <option value="updated" @selected($sort === 'updated')>Updated</option>
                <option value="popular" @selected($sort === 'popular')>Popular</option>
                <option value="recommended" @selected($sort === 'recommended')>Recommended</option>
            </select>
            <label class="flex items-center gap-2 text-sm md:col-span-2">
                <input type="checkbox" name="coupon" value="1" @checked($coupon)>
                Coupon/service available
            </label>
            <button class="rounded bg-slate-950 px-4 py-2 font-semibold text-white md:col-span-1">Search</button>
        </form>
        <div class="mt-5 grid gap-3">
            @foreach($articles as $article)
                <a class="public-row-story public-card" href="{{ route('articles.show', $article) }}">
                    <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <h2>{{ $article->title }}</h2>
                        @if($article->excerpt)
                            <p>{{ $article->excerpt }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

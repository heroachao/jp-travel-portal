@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Independent Japan Travel</p>
        <h1 class="mt-3 max-w-3xl text-5xl font-bold leading-tight">Plan Japan with regional guides, practical tools, and fresh travel ideas.</h1>
        <p class="mt-5 max-w-2xl text-lg text-slate-600">Browse region hubs, category guides, popular planning notes, and useful service links for building better Japan trips.</p>
        <form action="{{ route('search') }}" class="mt-8 flex max-w-2xl gap-2">
            <input name="q" class="min-w-0 flex-1 rounded border px-4 py-3" placeholder="Search Tokyo rail, Kyoto food, Hokkaido winter">
            <button class="rounded bg-slate-950 px-5 py-3 font-semibold text-white">Search</button>
        </form>
    </section>

    <section class="mx-auto grid max-w-6xl gap-8 px-5 pb-14 lg:grid-cols-[1.1fr_.9fr]">
        <div class="space-y-10">
            <section>
                <h2 class="text-2xl font-semibold">Featured Guides</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($articles as $article)
                        <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                            <h3 class="font-semibold">{{ $article->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Popular Articles</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($popularArticles as $article)
                        <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                            <h3 class="font-semibold">{{ $article->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
        <aside class="space-y-8">
            <section>
                <h2 class="text-2xl font-semibold">Regions</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($regionChannels as $destination)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('regions.show', $destination) }}">{{ $destination->display_name ?: $destination->name }}</a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Categories</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($travelCategories as $category)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Travel Services</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($serviceLinks as $link)
                        <a class="rounded border bg-white px-4 py-3" target="_blank" rel="nofollow noopener sponsored" href="{{ $link->url }}">{{ $link->label }}</a>
                    @endforeach
                </div>
            </section>
            @foreach($homepageModules as $module)
                <section class="rounded border bg-white p-5">
                    <h2 class="text-xl font-semibold">{{ $module->title }}</h2>
                    @if($module->subtitle)
                        <p class="mt-2 text-sm text-slate-600">{{ $module->subtitle }}</p>
                    @endif
                </section>
            @endforeach
        </aside>
    </section>
@endsection

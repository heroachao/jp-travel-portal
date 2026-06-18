@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">Search Japan Travel Guides</h1>
        <form class="mt-6 flex gap-3" action="{{ route('search') }}">
            <input class="flex-1 rounded border px-4 py-2" name="q" value="{{ $q }}" placeholder="Search articles">
            <button class="rounded bg-slate-900 px-5 py-2 text-white">Search</button>
        </form>
        <div class="mt-8 grid gap-4">
            @foreach($articles as $article)
                <a class="rounded border bg-white p-4" href="{{ route('articles.show', $article) }}">{{ $article->title }}</a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->withQueryString()->links() }}</div>
    </section>
@endsection

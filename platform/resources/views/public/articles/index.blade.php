@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12">
        <h1 class="text-4xl font-bold">Japan Travel Articles</h1>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach($articles as $article)
                <a class="rounded-lg border bg-white p-5" href="{{ route('articles.show', $article) }}">
                    <h2 class="font-semibold">{{ $article->title }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">#{{ $tag->name }}</h1>
        <p class="mt-4 text-slate-600">{{ $tag->description }}</p>
        <div class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach($tag->articles as $article)
                <a class="rounded border bg-white p-4" href="{{ route('articles.show', $article) }}">{{ $article->title }}</a>
            @endforeach
        </div>
    </section>
@endsection

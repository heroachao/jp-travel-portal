@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">{{ $topic->title }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $topic->excerpt }}</p>
        <div class="content-prose mt-8">{!! $topic->body !!}</div>
        @ad('content-mid-rectangle')
        <h2 class="mt-12 text-2xl font-semibold">Guides in this topic</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach($topic->articles as $article)
                <a class="rounded border bg-white p-4" href="{{ \App\Support\PublicUrl::route('articles.show', $article) }}">{{ $article->title }}</a>
            @endforeach
        </div>
    </section>
@endsection

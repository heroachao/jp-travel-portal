@extends('layouts.public')

@section('content')
    <article class="mx-auto max-w-3xl px-5 py-12">
        <p class="text-sm text-emerald-800">{{ $article->published_at?->format('F j, Y') }}</p>
        <h1 class="mt-3 text-4xl font-bold leading-tight">{{ $article->title }}</h1>
        <p class="mt-5 text-lg text-slate-600">{{ $article->excerpt }}</p>
        <div class="mt-8 border-y py-4 text-sm text-slate-600">
            @foreach($article->destinations as $destination)
                <a class="mr-3 underline" href="{{ route('destinations.show', $destination) }}">{{ $destination->name }}</a>
            @endforeach
            @foreach($article->topics as $topic)
                <a class="mr-3 underline" href="{{ route('topics.show', $topic) }}">{{ $topic->title }}</a>
            @endforeach
            @foreach($article->tags as $tag)
                <a class="mr-3 underline" href="{{ route('tags.show', $tag) }}">#{{ $tag->name }}</a>
            @endforeach
        </div>
        <div class="content-prose mt-8">
            {!! $article->body !!}
        </div>
    </article>
@endsection

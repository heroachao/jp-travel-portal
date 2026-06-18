@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">{{ $destination->name }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $destination->excerpt }}</p>
        <div class="content-prose mt-8">{!! $destination->body !!}</div>
        <h2 class="mt-12 text-2xl font-semibold">Related Guides</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach($destination->articles as $article)
                <a class="rounded border bg-white p-4" href="{{ route('articles.show', $article) }}">{{ $article->title }}</a>
            @endforeach
        </div>
    </section>
@endsection

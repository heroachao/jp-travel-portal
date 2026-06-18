@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">{{ $category->display_name ?: $category->title }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $category->excerpt }}</p>

        @if($category->body)
            <div class="content-prose mt-8">{!! $category->body !!}</div>
        @endif

        @if($category->children->isNotEmpty())
            <h2 class="mt-12 text-2xl font-semibold">Related Categories</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach($category->children as $child)
                    <a class="rounded border bg-white p-4" href="{{ route('categories.show', $child) }}">
                        <span class="font-semibold">{{ $child->display_name ?: $child->title }}</span>
                        @if($child->excerpt)
                            <span class="mt-2 block text-sm text-slate-600">{{ $child->excerpt }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        <h2 class="mt-12 text-2xl font-semibold">Guides in this category</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse($articles as $article)
                <a class="rounded border bg-white p-4" href="{{ route('articles.show', $article) }}">{{ $article->title }}</a>
            @empty
                <p class="text-slate-600">No published guides are available yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

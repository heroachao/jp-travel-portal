@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="text-4xl font-bold">{{ $destination->seo_title ?: ($destination->display_name ?: $destination->name).' Travel Guide' }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $destination->excerpt }}</p>

        @if($destination->body)
            <div class="content-prose mt-8">{!! $destination->body !!}</div>
        @endif

        @ad('content-mid-rectangle')

        @if($destination->children->isNotEmpty())
            <h2 class="mt-12 text-2xl font-semibold">Places in this region</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach($destination->children as $child)
                    <a class="rounded border bg-white p-4" href="{{ \App\Support\PublicUrl::route('destinations.show', $child) }}">
                        <span class="font-semibold">{{ $child->display_name ?: $child->name }}</span>
                        @if($child->excerpt)
                            <span class="mt-2 block text-sm text-slate-600">{{ $child->excerpt }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif

        <h2 class="mt-12 text-2xl font-semibold">Related Guides</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-2">
            @forelse($articles as $article)
                <a class="rounded border bg-white p-4" href="{{ \App\Support\PublicUrl::route('articles.show', $article) }}">{{ $article->title }}</a>
            @empty
                <p class="text-slate-600">No published guides are available yet.</p>
            @endforelse
        </div>

        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection

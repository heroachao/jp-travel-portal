@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-10 sm:py-14">
        <div class="public-card p-6 sm:p-8">
            <p class="public-kicker">{{ $page['eyebrow'] }}</p>
            <h1 class="mt-3 text-3xl font-black leading-tight text-slate-950 sm:text-4xl">{{ $page['title'] }}</h1>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ $page['intro'] }}</p>
            <p class="mt-5 text-sm font-semibold text-slate-500">Last updated: {{ $page['updated'] }}</p>
        </div>

        @ad('content-mid-rectangle')

        <div class="mt-6 space-y-4">
            @foreach($page['sections'] as $section)
                <article class="public-card p-6 sm:p-8">
                    <h2 class="text-xl font-black text-slate-950">{{ $section['heading'] }}</h2>
                    <div class="content-prose mt-3 text-slate-700">
                        @foreach($section['body'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>

        <nav class="mt-8 flex flex-wrap gap-3 text-sm font-extrabold" aria-label="Policy pages">
            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 no-underline" href="{{ \App\Support\PublicUrl::route('pages.about') }}">About</a>
            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 no-underline" href="{{ \App\Support\PublicUrl::route('pages.contact') }}">Contact</a>
            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 no-underline" href="{{ \App\Support\PublicUrl::route('pages.privacy') }}">Privacy Policy</a>
            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 no-underline" href="{{ \App\Support\PublicUrl::route('pages.terms') }}">Terms</a>
            <a class="rounded-full border border-slate-200 bg-white px-4 py-2 text-slate-700 no-underline" href="{{ \App\Support\PublicUrl::route('pages.disclaimer') }}">Disclaimer</a>
        </nav>
    </section>
@endsection

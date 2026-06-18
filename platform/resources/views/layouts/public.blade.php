<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta->title }}</title>
    @if($meta->description)<meta name="description" content="{{ $meta->description }}">@endif
    <link rel="canonical" href="{{ $meta->canonical }}">
    @unless($meta->indexable)<meta name="robots" content="noindex,nofollow">@endunless
    <meta property="og:title" content="{{ $meta->ogTitle ?? $meta->title }}">
    @if($meta->ogDescription ?? $meta->description)<meta property="og:description" content="{{ $meta->ogDescription ?? $meta->description }}">@endif
    @if($meta->ogImage)<meta property="og:image" content="{{ $meta->ogImage }}">@endif
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="bg-stone-50 text-slate-950 antialiased">
    <header class="border-b bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a class="font-serif text-xl font-bold" href="{{ route('home') }}">Japan Travel Guide</a>
            <div class="flex gap-5 text-sm font-medium">
                <a href="{{ route('destinations.index') }}">Destinations</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('search') }}">Search</a>
            </div>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>

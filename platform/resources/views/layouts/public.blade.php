@php
    $siteSettings = app(\App\Services\Settings\SiteSettings::class)->current();
    $layoutHeaderServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('header')->ordered()->get();
    $layoutFooterServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('footer')->ordered()->get();
    $layoutFooterDescription = $siteSettings->tagline
        ?: ($siteSettings->default_meta_description
            ?: 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.');
    $layoutRegions = \App\Models\Destination::query()
        ->channel()
        ->where('is_indexable', true)
        ->ordered()
        ->limit(11)
        ->get();
    $layoutCategories = \App\Models\TravelCategory::query()
        ->visible()
        ->where('is_indexable', true)
        ->ordered()
        ->limit(8)
        ->get();
@endphp

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
    @if($siteSettings->analytics_enabled && filled($siteSettings->ga4_measurement_id))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings->ga4_measurement_id }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $siteSettings->ga4_measurement_id }}');
        </script>
    @endif
    @if($siteSettings->ads_enabled && filled($siteSettings->adsense_publisher_id))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $siteSettings->adsense_publisher_id }}" crossorigin="anonymous"></script>
    @endif
    @unless(app()->environment('testing'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endunless
</head>
<body class="bg-stone-50 text-slate-950 antialiased">
    <header class="border-b bg-white">
        <div class="border-b bg-slate-950 text-white">
            <nav class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 py-2 text-xs font-semibold">
                <a href="{{ route('articles.index') }}">Guide</a>
                @foreach($layoutHeaderServiceLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored" @if($link->tracking_key) data-service-key="{{ $link->tracking_key }}" @endif>{{ $link->label }}</a>
                @endforeach
            </nav>
        </div>
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a class="font-serif text-xl font-bold" href="{{ route('home') }}">{{ $siteSettings->site_name }}</a>
            <div class="flex gap-5 text-sm font-medium">
                <a href="{{ route('regions.index') }}">Regions</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('search') }}">Search</a>
            </div>
        </nav>
        <div class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 pb-3 text-sm">
            <a class="font-semibold" href="{{ route('regions.index') }}">National</a>
            @foreach($layoutRegions as $region)
                <a href="{{ route('regions.show', $region) }}">{{ $region->display_name ?: $region->name }}</a>
            @endforeach
        </div>
        <div class="border-t">
            <nav class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 py-3 text-sm font-medium">
                @foreach($layoutCategories as $category)
                    <a href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
                @endforeach
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer class="mt-16 border-t bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-5 py-8 text-sm text-slate-600 md:grid-cols-3">
            <div>
                <p class="font-semibold text-slate-950">{{ $siteSettings->site_name }}</p>
                <p class="mt-2">{{ $layoutFooterDescription }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('regions.index') }}">Regions</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('search') }}">Search</a>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($layoutFooterServiceLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored">{{ $link->label }}</a>
                @endforeach
            </div>
        </div>
    </footer>
</body>
</html>

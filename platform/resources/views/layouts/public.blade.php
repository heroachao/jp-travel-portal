@php
    $siteSettings = app(\App\Services\Settings\SiteSettings::class)->current();
    $layoutHeaderServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('header')->ordered()->get();
    $layoutFooterServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('footer')->ordered()->get();
    $layoutFooterDescription = $siteSettings->tagline
        ?: ($siteSettings->default_meta_description
            ?: 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.');
    $layoutTitle = $meta->title;
    if (filled($siteSettings->seo_title_suffix) && ! str_contains($layoutTitle, $siteSettings->seo_title_suffix)) {
        $layoutTitle .= ' | '.$siteSettings->seo_title_suffix;
    }
    $layoutDescription = $meta->description ?: $siteSettings->default_meta_description;
    $organizationJsonLd = null;
    if ($siteSettings->organization_schema_enabled) {
        $organizationJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteSettings->site_name,
            'url' => route('home'),
        ];

        if (filled($siteSettings->contact_email)) {
            $organizationJsonLd['email'] = $siteSettings->contact_email;
        }

        $sameAs = collect($siteSettings->social_links ?? [])
            ->filter(fn ($url) => is_string($url) && str_starts_with($url, 'http'))
            ->values()
            ->all();

        if ($sameAs !== []) {
            $organizationJsonLd['sameAs'] = $sameAs;
        }
    }
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
    <title>{{ $layoutTitle }}</title>
    @if($layoutDescription)<meta name="description" content="{{ $layoutDescription }}">@endif
    <link rel="canonical" href="{{ $meta->canonical }}">
    @unless($meta->indexable)<meta name="robots" content="noindex,nofollow">@endunless
    @if(filled($siteSettings->google_site_verification))
        <meta name="google-site-verification" content="{{ $siteSettings->google_site_verification }}">
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <meta property="og:title" content="{{ $meta->ogTitle ?? $layoutTitle }}">
    @if($meta->ogDescription ?? $layoutDescription)<meta property="og:description" content="{{ $meta->ogDescription ?? $layoutDescription }}">@endif
    @if($meta->ogImage)<meta property="og:image" content="{{ $meta->ogImage }}">@endif
    @if($organizationJsonLd)
        <script type="application/ld+json">{!! json_encode($organizationJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
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
<body class="public-shell text-slate-950 antialiased">
    <header class="public-header">
        <div class="public-header-main">
            <a class="public-brand" href="{{ route('home') }}" aria-label="{{ $siteSettings->site_name }}">
                <img class="public-brand-logo" src="{{ asset('images/japan-trip-tools-logo.png') }}" alt="{{ $siteSettings->site_name }}">
            </a>
            <form action="{{ route('search') }}" class="public-search" role="search">
                <input name="q" placeholder="Search Tokyo rail, Kyoto food, JR Pass">
                <button aria-label="Search">Search</button>
            </form>
            <nav class="public-actions" aria-label="Primary links">
                <a href="{{ route('articles.index') }}">News</a>
                <a href="{{ route('regions.index') }}">Regions</a>
                <a href="{{ route('search') }}">Tools</a>
            </nav>
        </div>
        <div class="public-channel-bar">
            <nav class="public-channel-nav" aria-label="Japan travel channels">
                <a class="is-active" href="{{ route('home') }}">My Trip</a>
                <a href="{{ route('articles.index') }}">News</a>
                @foreach($layoutCategories as $category)
                    <a href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
                @endforeach
                @foreach($layoutHeaderServiceLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored" @if($link->tracking_key) data-service-key="{{ $link->tracking_key }}" @endif>{{ $link->label }}</a>
                @endforeach
            </nav>
        </div>
        <div class="public-region-strip">
            <nav class="public-region-nav" aria-label="Region shortcuts">
                <a href="{{ route('regions.index') }}">All Japan</a>
                @foreach($layoutRegions as $region)
                    <a href="{{ route('regions.show', $region) }}">{{ $region->display_name ?: $region->name }}</a>
                @endforeach
            </nav>
        </div>
    </header>
    @ad('global-top-leaderboard')
    <main class="public-main">
        @yield('content')
    </main>
    @ad('global-bottom-leaderboard')
    <footer class="public-footer">
        <div class="mx-auto grid max-w-7xl gap-6 px-5 py-8 text-sm text-slate-600 md:grid-cols-3">
            <div>
                <p class="font-semibold text-slate-950">{{ $siteSettings->site_name }}</p>
                <p class="mt-2">{{ $layoutFooterDescription }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('regions.index') }}">Regions</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('search') }}">Search</a>
                <a href="{{ route('pages.about') }}">About</a>
                <a href="{{ route('pages.contact') }}">Contact</a>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('pages.privacy') }}">Privacy Policy</a>
                <a href="{{ route('pages.terms') }}">Terms</a>
                <a href="{{ route('pages.disclaimer') }}">Disclaimer</a>
                @foreach($layoutFooterServiceLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored">{{ $link->label }}</a>
                @endforeach
            </div>
        </div>
    </footer>
</body>
</html>

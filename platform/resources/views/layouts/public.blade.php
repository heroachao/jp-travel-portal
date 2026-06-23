@php
    $siteSettings = app(\App\Services\Settings\SiteSettings::class)->current();
    $layoutHeaderServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('header')->ordered()->get();
    $layoutInternalServiceTargets = [
        'activities' => ['label' => 'Activities', 'url' => \App\Support\PublicUrl::route('categories.show', 'things-to-do')],
        'hotels' => ['label' => 'Hotels', 'url' => \App\Support\PublicUrl::route('categories.show', 'lodging')],
        'flights' => ['label' => 'Flights', 'url' => \App\Support\PublicUrl::route('tools.show', 'airport-transfer')],
        'rail-tickets' => ['label' => 'Rail Tickets', 'url' => \App\Support\PublicUrl::route('tools.show', 'jr-pass-calculator')],
        'shop' => ['label' => 'Shop', 'url' => \App\Support\PublicUrl::route('tools.show', 'tax-free-calculator')],
        'exchange-rate' => ['label' => 'Exchange Rate', 'url' => \App\Support\PublicUrl::route('tools.show', 'budget-calculator')],
    ];
    $layoutHeaderServiceLinks = $layoutHeaderServiceLinks
        ->map(function ($link) use ($layoutInternalServiceTargets) {
            $key = $link->tracking_key ?: \Illuminate\Support\Str::slug($link->label);
            $target = $layoutInternalServiceTargets[$key] ?? ['label' => $link->label, 'url' => \App\Support\PublicUrl::route('tools.index')];

            return [
                'label' => $target['label'],
                'url' => $target['url'],
                'tracking_key' => $key,
            ];
        })
        ->unique('url')
        ->values();
    $layoutFooterServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('footer')->ordered()->get();
    $layoutFooterDescription = $siteSettings->tagline
        ?: ($siteSettings->default_meta_description
            ?: 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.');
    $layoutTitle = app(\App\Services\Seo\PublicTitleFormatter::class)->format($meta->title, $siteSettings);
    $layoutDescription = $meta->description ?: $siteSettings->default_meta_description;
    $websiteJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteSettings->site_name,
        'url' => \App\Support\PublicUrl::route('home'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => \App\Support\PublicUrl::route('search').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
    $organizationJsonLd = null;
    if ($siteSettings->organization_schema_enabled) {
        $organizationJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteSettings->site_name,
            'url' => \App\Support\PublicUrl::route('home'),
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
    <script type="application/ld+json">{!! json_encode($websiteJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @if($organizationJsonLd)
        <script type="application/ld+json">{!! json_encode($organizationJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endif
    @stack('structured-data')
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
            <a class="public-brand" href="{{ \App\Support\PublicUrl::route('home') }}" aria-label="{{ $siteSettings->site_name }}">
                <picture>
                    <source srcset="{{ asset('images/japan-trip-tools-logo.webp') }}" type="image/webp">
                    <img class="public-brand-logo" src="{{ asset('images/japan-trip-tools-logo.png') }}" alt="{{ $siteSettings->site_name }}" width="360" height="360">
                </picture>
            </a>
            <form action="{{ \App\Support\PublicUrl::route('search') }}" class="public-search" role="search">
                <input name="q" placeholder="Search Tokyo rail, Kyoto food, JR Pass">
                <button aria-label="Search">Search</button>
            </form>
            <nav class="public-actions" aria-label="Primary links">
                <a href="{{ \App\Support\PublicUrl::route('articles.index') }}">News</a>
                <a href="{{ \App\Support\PublicUrl::route('regions.index') }}">Regions</a>
                <a href="{{ \App\Support\PublicUrl::route('tools.index') }}">Tools</a>
                <a href="{{ \App\Support\PublicUrl::route('games.index') }}">Games</a>
            </nav>
        </div>
        <div class="public-channel-bar">
            <nav class="public-channel-nav" aria-label="Japan travel channels">
                <a class="is-active" href="{{ \App\Support\PublicUrl::route('home') }}">My Trip</a>
                <a href="{{ \App\Support\PublicUrl::route('articles.index') }}">News</a>
                <a href="{{ \App\Support\PublicUrl::route('games.index') }}">Games</a>
                @foreach($layoutCategories as $category)
                    <a href="{{ \App\Support\PublicUrl::route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
                @endforeach
                @foreach($layoutHeaderServiceLinks as $link)
                    <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                @endforeach
            </nav>
        </div>
        <div class="public-region-strip">
            <nav class="public-region-nav" aria-label="Region shortcuts">
                <a href="{{ \App\Support\PublicUrl::route('regions.index') }}">All Japan</a>
                @foreach($layoutRegions as $region)
                    <a href="{{ \App\Support\PublicUrl::route('regions.show', $region) }}">{{ $region->display_name ?: $region->name }}</a>
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
                <a href="{{ \App\Support\PublicUrl::route('regions.index') }}">Regions</a>
                <a href="{{ \App\Support\PublicUrl::route('articles.index') }}">Articles</a>
                <a href="{{ \App\Support\PublicUrl::route('tools.index') }}">Tools</a>
                <a href="{{ \App\Support\PublicUrl::route('games.index') }}">Games</a>
                <a href="{{ \App\Support\PublicUrl::route('search') }}">Search</a>
                <a href="{{ \App\Support\PublicUrl::route('pages.about') }}">About</a>
                <a href="{{ \App\Support\PublicUrl::route('pages.contact') }}">Contact</a>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ \App\Support\PublicUrl::route('pages.privacy') }}">Privacy Policy</a>
                <a href="{{ \App\Support\PublicUrl::route('pages.terms') }}">Terms</a>
                <a href="{{ \App\Support\PublicUrl::route('pages.disclaimer') }}">Disclaimer</a>
                @foreach($layoutFooterServiceLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored">{{ $link->label }}</a>
                @endforeach
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>

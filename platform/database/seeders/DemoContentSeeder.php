<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\AdPlacement;
use App\Models\Article;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@example.com')->firstOrFail();

        $tokyo = Destination::updateOrCreate(
            ['slug' => 'tokyo'],
            [
                'name' => 'Tokyo',
                'display_name' => 'Tokyo',
                'type' => 'city',
                'excerpt' => 'A practical guide to Tokyo neighborhoods, transit, food, and first-time trip planning.',
                'body' => '<p>Tokyo rewards travelers who plan by neighborhood and transit line rather than by checklist.</p>',
                'seo_title' => 'Tokyo Travel Guide',
                'meta_description' => 'Plan a Tokyo trip with neighborhood guides, transport tips, and practical travel notes.',
                'is_indexable' => true,
                'is_channel' => true,
                'sort_order' => 1,
            ]
        );

        $kyoto = Destination::updateOrCreate(
            ['slug' => 'kyoto'],
            [
                'name' => 'Kyoto',
                'display_name' => 'Kyoto',
                'type' => 'city',
                'excerpt' => 'Temples, quiet lanes, seasonal walks, and cultural travel notes for Kyoto.',
                'body' => '<p>Kyoto is best explored slowly, with early starts and careful attention to local etiquette.</p>',
                'seo_title' => 'Kyoto Travel Guide',
                'meta_description' => 'Explore Kyoto temples, neighborhoods, seasonal routes, and travel planning tips.',
                'is_indexable' => true,
                'is_channel' => false,
                'sort_order' => 2,
            ]
        );

        $hokkaido = Destination::updateOrCreate(
            ['slug' => 'hokkaido'],
            [
                'name' => 'Hokkaido',
                'display_name' => 'Hokkaido',
                'type' => 'region',
                'excerpt' => 'Wide landscapes, winter travel, national parks, and food-focused routes in northern Japan.',
                'body' => '<p>Hokkaido works well for travelers who want open space, seasonal food, and nature-forward routes.</p>',
                'seo_title' => 'Hokkaido Travel Guide',
                'meta_description' => 'Plan Hokkaido travel around national parks, winter routes, food, and scenic rail journeys.',
                'is_indexable' => true,
                'is_channel' => true,
                'sort_order' => 3,
            ]
        );

        $regions = [
            'tokyo' => $tokyo,
            'kyoto' => $kyoto,
            'hokkaido' => $hokkaido,
        ];

        foreach ([
            [
                'slug' => 'tohoku',
                'name' => 'Tohoku',
                'excerpt' => 'Mountain temples, festivals, hot springs, and slower routes across northern Honshu.',
                'body' => '<p>Tohoku works well for travelers adding quieter cities, onsen towns, and seasonal landscapes to a Japan route.</p>',
                'seo_title' => 'Tohoku Travel Guide',
                'meta_description' => 'Plan Tohoku travel with regional highlights, seasonal routes, festivals, and transport notes.',
                'sort_order' => 4,
            ],
            [
                'slug' => 'hokuriku',
                'name' => 'Hokuriku',
                'excerpt' => 'Seafood, craft towns, gardens, and shinkansen stops along the Sea of Japan coast.',
                'body' => '<p>Hokuriku combines compact cities, coastal food, and cultural stops that pair well with Tokyo or Kansai trips.</p>',
                'seo_title' => 'Hokuriku Travel Guide',
                'meta_description' => 'Explore Hokuriku routes for Kanazawa, coastal food, crafts, gardens, and rail planning.',
                'sort_order' => 5,
            ],
            [
                'slug' => 'chubu',
                'name' => 'Chubu',
                'excerpt' => 'Alpine towns, central Japan rail routes, castles, and mountain scenery.',
                'body' => '<p>Chubu is useful for travelers linking Tokyo, Nagoya, alpine villages, and central mountain routes.</p>',
                'seo_title' => 'Chubu Travel Guide',
                'meta_description' => 'Plan Chubu travel around alpine towns, central Japan rail routes, castles, and mountain scenery.',
                'sort_order' => 6,
            ],
            [
                'slug' => 'kansai',
                'name' => 'Kansai',
                'excerpt' => 'Classic Japan routes through Kyoto, Osaka, Nara, Kobe, temples, food, and day trips.',
                'body' => '<p>Kansai is a strong base for first-time travelers who want history, food, and short train day trips.</p>',
                'seo_title' => 'Kansai Travel Guide',
                'meta_description' => 'Build a Kansai itinerary for Kyoto, Osaka, Nara, Kobe, food, temples, and day trips.',
                'sort_order' => 7,
            ],
            [
                'slug' => 'chugoku',
                'name' => 'Chugoku',
                'excerpt' => 'Hiroshima, islands, gardens, castle towns, and west Honshu routes.',
                'body' => '<p>Chugoku gives travelers a western Honshu route with history, island scenery, and relaxed cities.</p>',
                'seo_title' => 'Chugoku Travel Guide',
                'meta_description' => 'Explore Chugoku travel routes through Hiroshima, islands, gardens, castle towns, and west Honshu.',
                'sort_order' => 8,
            ],
            [
                'slug' => 'shikoku',
                'name' => 'Shikoku',
                'excerpt' => 'Pilgrimage roads, rivers, small cities, cycling, and quieter rural travel.',
                'body' => '<p>Shikoku suits repeat visitors who want slower travel, local food, cycling routes, and smaller cities.</p>',
                'seo_title' => 'Shikoku Travel Guide',
                'meta_description' => 'Plan Shikoku travel for pilgrimage routes, cycling, rivers, small cities, and rural stays.',
                'sort_order' => 9,
            ],
            [
                'slug' => 'kyushu',
                'name' => 'Kyushu',
                'excerpt' => 'Volcanoes, hot springs, rail journeys, food cities, and southern island routes.',
                'body' => '<p>Kyushu brings together onsen towns, active landscapes, food-focused cities, and scenic rail trips.</p>',
                'seo_title' => 'Kyushu Travel Guide',
                'meta_description' => 'Plan Kyushu trips around volcanoes, hot springs, rail routes, food cities, and nature.',
                'sort_order' => 10,
            ],
            [
                'slug' => 'okinawa',
                'name' => 'Okinawa',
                'excerpt' => 'Island beaches, local culture, road trips, family stays, and subtropical travel planning.',
                'body' => '<p>Okinawa works best with island-specific pacing, rental car planning, and weather-aware beach days.</p>',
                'seo_title' => 'Okinawa Travel Guide',
                'meta_description' => 'Plan Okinawa island travel with beach timing, local culture, road trips, and family-friendly stays.',
                'sort_order' => 11,
            ],
        ] as $region) {
            $regions[$region['slug']] = Destination::updateOrCreate(
                ['slug' => $region['slug']],
                [
                    'name' => $region['name'],
                    'display_name' => $region['name'],
                    'type' => 'region',
                    'excerpt' => $region['excerpt'],
                    'body' => $region['body'],
                    'seo_title' => $region['seo_title'],
                    'meta_description' => $region['meta_description'],
                    'is_indexable' => true,
                    'is_channel' => true,
                    'sort_order' => $region['sort_order'],
                ]
            );
        }

        $bestTime = Topic::updateOrCreate(
            ['slug' => 'best-time-to-visit-japan'],
            [
                'title' => 'Best Time to Visit Japan',
                'excerpt' => 'Season-by-season guidance for weather, crowds, festivals, and travel costs.',
                'body' => '<p>Japan changes sharply by season. Match your route to weather, daylight, and crowd patterns.</p>',
                'seo_title' => 'Best Time to Visit Japan',
                'meta_description' => 'Compare Japan travel seasons by weather, crowds, festivals, and regional highlights.',
                'is_indexable' => true,
            ]
        );

        $rail = Topic::updateOrCreate(
            ['slug' => 'japan-rail-travel'],
            [
                'title' => 'Japan Rail Travel',
                'excerpt' => 'Rail passes, route planning, station transfers, and long-distance train travel.',
                'body' => '<p>Rail travel in Japan is easiest when you plan transfers and luggage movement before booking hotels.</p>',
                'seo_title' => 'Japan Rail Travel Guide',
                'meta_description' => 'Understand Japan rail routes, passes, transfers, and practical train planning.',
                'is_indexable' => true,
            ]
        );

        $cherry = Tag::updateOrCreate(
            ['slug' => 'cherry-blossom'],
            ['name' => 'cherry blossom', 'description' => 'Spring routes, timing, and crowd-aware sakura travel notes.']
        );

        $onsen = Tag::updateOrCreate(
            ['slug' => 'onsen'],
            ['name' => 'onsen', 'description' => 'Hot spring towns, etiquette, and regional onsen planning.']
        );

        $categories = [];

        foreach ([
            'guide' => [
                'title' => 'Guide',
                'display_name' => 'Guides',
                'excerpt' => 'Destination overviews and practical planning guides for Japan trips.',
                'body' => '<p>Use guide pages to understand where to go, how to plan each stop, and what to prioritize.</p>',
                'seo_title' => 'Japan Travel Guides',
                'meta_description' => 'Browse practical Japan travel guides for destinations, routes, planning decisions, and local context.',
                'sort_order' => 1,
            ],
            'things-to-do' => [
                'title' => 'Things to Do',
                'display_name' => 'Things to Do',
                'excerpt' => 'Activities, neighborhoods, attractions, walks, and seasonal ideas.',
                'body' => '<p>Find things to do by neighborhood, season, trip length, and travel style.</p>',
                'seo_title' => 'Things to Do in Japan',
                'meta_description' => 'Find things to do in Japan, including attractions, neighborhoods, seasonal ideas, and local experiences.',
                'sort_order' => 2,
            ],
            'food' => [
                'title' => 'Food',
                'display_name' => 'Food',
                'excerpt' => 'Restaurants, local specialties, markets, cafes, and food-focused routes.',
                'body' => '<p>Food guides help travelers plan meals around neighborhoods, local specialties, and reservations.</p>',
                'seo_title' => 'Japan Food Travel Guides',
                'meta_description' => 'Explore Japan food guides for restaurants, local specialties, markets, cafes, and regional dishes.',
                'sort_order' => 3,
            ],
            'shopping' => [
                'title' => 'Shopping',
                'display_name' => 'Shopping',
                'excerpt' => 'Shopping districts, souvenirs, tax-free tips, and local product ideas.',
                'body' => '<p>Shopping notes cover neighborhoods, useful stores, souvenirs, and practical tax-free planning.</p>',
                'seo_title' => 'Japan Shopping Guides',
                'meta_description' => 'Plan Japan shopping with district guides, souvenir ideas, tax-free tips, and local product notes.',
                'sort_order' => 4,
            ],
            'lodging' => [
                'title' => 'Lodging',
                'display_name' => 'Lodging',
                'excerpt' => 'Where to stay, hotel areas, ryokan planning, and booking tradeoffs.',
                'body' => '<p>Lodging guides compare neighborhoods, transport convenience, hotel types, and stay pacing.</p>',
                'seo_title' => 'Where to Stay in Japan',
                'meta_description' => 'Compare Japan lodging areas, hotel types, ryokan stays, and booking tradeoffs.',
                'sort_order' => 5,
            ],
            'itinerary' => [
                'title' => 'Itinerary',
                'display_name' => 'Itineraries',
                'excerpt' => 'Trip plans by day count, region, season, and travel pace.',
                'body' => '<p>Itinerary pages help travelers sequence cities, transit, day trips, and rest days.</p>',
                'seo_title' => 'Japan Itineraries',
                'meta_description' => 'Browse Japan itineraries by day count, region, season, and travel pace.',
                'sort_order' => 6,
            ],
            'transport' => [
                'title' => 'Transport',
                'display_name' => 'Transport',
                'excerpt' => 'Rail passes, airport transfers, IC cards, luggage forwarding, and route planning.',
                'body' => '<p>Transport guides explain how to move between cities and around neighborhoods with less friction.</p>',
                'seo_title' => 'Japan Transport Guides',
                'meta_description' => 'Understand Japan transport, rail passes, airport transfers, IC cards, luggage forwarding, and route planning.',
                'sort_order' => 7,
            ],
            'basics' => [
                'title' => 'Basics',
                'display_name' => 'Travel Basics',
                'excerpt' => 'Essential first-trip notes for money, connectivity, etiquette, weather, and packing.',
                'body' => '<p>Basics pages cover the small practical details that make Japan trips easier on arrival.</p>',
                'seo_title' => 'Japan Travel Basics',
                'meta_description' => 'Learn Japan travel basics for money, connectivity, etiquette, weather, packing, and arrival planning.',
                'sort_order' => 8,
            ],
        ] as $slug => $category) {
            $categories[$slug] = TravelCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $category['title'],
                    'display_name' => $category['display_name'],
                    'excerpt' => $category['excerpt'],
                    'body' => $category['body'],
                    'seo_title' => $category['seo_title'],
                    'meta_description' => $category['meta_description'],
                    'is_indexable' => true,
                    'is_visible' => true,
                    'sort_order' => $category['sort_order'],
                ]
            );
        }

        $kyotoArticle = Article::updateOrCreate(
            ['slug' => 'three-days-in-kyoto'],
            [
                'author_id' => $author->id,
                'title' => 'Three Days in Kyoto',
                'excerpt' => 'A calm first-time Kyoto itinerary built around temples, lanes, food, and slower mornings.',
                'body' => '<p>Start in Higashiyama, reserve time for smaller lanes, and avoid stacking too many temple visits into one afternoon.</p>',
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(2),
                'seo_title' => 'Three Days in Kyoto Itinerary',
                'meta_description' => 'Plan three days in Kyoto with a calm itinerary for temples, food, neighborhoods, and practical transit.',
                'is_indexable' => true,
            ]
        );

        $tokyoArticle = Article::updateOrCreate(
            ['slug' => 'first-timers-guide-to-tokyo-neighborhoods'],
            [
                'author_id' => $author->id,
                'title' => "A First Timer's Guide to Tokyo Neighborhoods",
                'excerpt' => 'How to choose where to stay and how to group Tokyo days by neighborhood.',
                'body' => '<p>Tokyo becomes easier when each day has a compact neighborhood focus and a clear train line strategy.</p>',
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDay(),
                'seo_title' => "Tokyo Neighborhood Guide for First Timers",
                'meta_description' => 'Choose Tokyo neighborhoods for a first trip with practical stay, food, transit, and sightseeing advice.',
                'is_indexable' => true,
            ]
        );

        $kyotoArticle->destinations()->syncWithoutDetaching([$kyoto->id]);
        $kyotoArticle->topics()->syncWithoutDetaching([$bestTime->id, $rail->id]);
        $kyotoArticle->tags()->syncWithoutDetaching([$cherry->id, $onsen->id]);

        $tokyoArticle->destinations()->syncWithoutDetaching([$tokyo->id]);
        $tokyoArticle->topics()->syncWithoutDetaching([$bestTime->id, $rail->id]);
        $tokyoArticle->tags()->syncWithoutDetaching([$cherry->id]);

        $bestTime->destinations()->syncWithoutDetaching([$tokyo->id, $kyoto->id, $hokkaido->id]);
        $rail->destinations()->syncWithoutDetaching([$tokyo->id, $kyoto->id]);

        $kyotoArticle->travelCategories()->syncWithoutDetaching([
            $categories['guide']->id => ['sort_order' => 1],
            $categories['itinerary']->id => ['sort_order' => 2],
            $categories['food']->id => ['sort_order' => 3],
            $categories['basics']->id => ['sort_order' => 4],
        ]);

        $tokyoArticle->travelCategories()->syncWithoutDetaching([
            $categories['guide']->id => ['sort_order' => 1],
            $categories['things-to-do']->id => ['sort_order' => 2],
            $categories['shopping']->id => ['sort_order' => 3],
            $categories['lodging']->id => ['sort_order' => 4],
            $categories['transport']->id => ['sort_order' => 5],
            $categories['basics']->id => ['sort_order' => 6],
        ]);

        foreach ([
            [
                'article' => $tokyoArticle,
                'question' => 'Which Tokyo area is best for a first stay?',
                'answer' => '<p>Shinjuku, Ginza, Ueno, and Shibuya are practical first-stay areas when matched to your budget and train plans.</p>',
                'sort_order' => 1,
            ],
            [
                'article' => $tokyoArticle,
                'question' => 'How many days should I plan for Tokyo?',
                'answer' => '<p>Three to five full days works well for a first Tokyo visit, with extra time if you want day trips or slower neighborhoods.</p>',
                'sort_order' => 2,
            ],
            [
                'article' => $kyotoArticle,
                'question' => 'Is three days enough for Kyoto?',
                'answer' => '<p>Three days is enough for a balanced first Kyoto route if you group nearby temples, lanes, food stops, and transit carefully.</p>',
                'sort_order' => 1,
            ],
            [
                'article' => $kyotoArticle,
                'question' => 'Where should I start each Kyoto sightseeing day?',
                'answer' => '<p>Start early in Higashiyama, Arashiyama, or northern Kyoto, then keep afternoons flexible for food and smaller streets.</p>',
                'sort_order' => 2,
            ],
        ] as $faq) {
            $faq['article']->faqs()->updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'sort_order' => $faq['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        $serviceLinks = [];

        foreach ([
            'activities' => [
                'type' => 'activity',
                'label' => 'Activities',
                'url' => 'https://example.com/japan-activities',
                'placement' => 'header',
                'notes' => 'Demo activity booking link.',
                'sort_order' => 1,
            ],
            'hotels' => [
                'type' => 'hotel',
                'label' => 'Hotels',
                'url' => 'https://example.com/japan-hotels',
                'placement' => 'header',
                'notes' => 'Demo hotel search link.',
                'sort_order' => 2,
            ],
            'flights' => [
                'type' => 'flight',
                'label' => 'Flights',
                'url' => 'https://example.com/japan-flights',
                'placement' => 'header',
                'notes' => 'Demo flight search link.',
                'sort_order' => 3,
            ],
            'rail-tickets' => [
                'type' => 'rail',
                'label' => 'Rail Tickets',
                'url' => 'https://example.com/japan-rail-tickets',
                'placement' => 'header',
                'notes' => 'Demo rail ticket planning link.',
                'sort_order' => 4,
            ],
            'shop' => [
                'type' => 'shop',
                'label' => 'Shop',
                'url' => 'https://example.com/japan-shop',
                'placement' => 'header',
                'notes' => 'Demo shopping partner link.',
                'sort_order' => 5,
            ],
            'exchange-rate' => [
                'type' => 'exchange_rate',
                'label' => 'Exchange Rate',
                'url' => 'https://example.com/jpy-exchange-rate',
                'placement' => 'header',
                'notes' => 'Demo exchange-rate tool link.',
                'sort_order' => 6,
            ],
            'advertise' => [
                'type' => 'advertising',
                'label' => 'Advertise',
                'url' => 'https://example.com/advertise',
                'placement' => 'footer',
                'notes' => 'Demo media kit inquiry link.',
                'sort_order' => 7,
            ],
        ] as $trackingKey => $serviceLink) {
            $serviceLinks[$trackingKey] = ServiceLink::updateOrCreate(
                ['tracking_key' => $trackingKey],
                [
                    'type' => $serviceLink['type'],
                    'label' => $serviceLink['label'],
                    'url' => $serviceLink['url'],
                    'placement' => $serviceLink['placement'],
                    'tracking_key' => $trackingKey,
                    'notes' => $serviceLink['notes'],
                    'is_enabled' => true,
                    'sort_order' => $serviceLink['sort_order'],
                ]
            );
        }

        $featuredModule = HomepageModule::updateOrCreate(
            ['placement_key' => 'home-featured'],
            [
                'type' => 'featured_articles',
                'title' => 'Featured Guides',
                'subtitle' => 'Fresh planning notes for first-time Japan trips.',
                'is_enabled' => true,
                'sort_order' => 1,
            ]
        );

        $regionsModule = HomepageModule::updateOrCreate(
            ['placement_key' => 'home-regions'],
            [
                'type' => 'region_grid',
                'title' => 'Regional Starting Points',
                'subtitle' => 'Useful hubs for shaping the first draft of a route.',
                'is_enabled' => true,
                'sort_order' => 2,
            ]
        );

        $toolsModule = HomepageModule::updateOrCreate(
            ['placement_key' => 'home-tools'],
            [
                'type' => 'travel_tools',
                'title' => 'Trip Tools',
                'subtitle' => 'Quick links for booking and planning decisions.',
                'is_enabled' => true,
                'sort_order' => 3,
            ]
        );

        foreach ([
            [
                'module' => $featuredModule,
                'item_type' => Article::class,
                'item_id' => $tokyoArticle->id,
                'label' => 'Tokyo Neighborhood Starter',
                'summary' => 'Choose where to stay and group Tokyo days with less transit friction.',
                'sort_order' => 1,
            ],
            [
                'module' => $featuredModule,
                'item_type' => Article::class,
                'item_id' => $kyotoArticle->id,
                'label' => 'Three Calm Days in Kyoto',
                'summary' => 'A first Kyoto plan with temples, lanes, food, and slower mornings.',
                'sort_order' => 2,
            ],
            [
                'module' => $regionsModule,
                'item_type' => Destination::class,
                'item_id' => $tokyo->id,
                'label' => 'Tokyo',
                'summary' => 'Neighborhood planning, rail logic, food, shopping, and day-by-day city pacing.',
                'sort_order' => 1,
            ],
            [
                'module' => $regionsModule,
                'item_type' => Destination::class,
                'item_id' => $hokkaido->id,
                'label' => 'Hokkaido',
                'summary' => 'Nature-forward routes, winter planning, national parks, and regional food.',
                'sort_order' => 2,
            ],
            [
                'module' => $regionsModule,
                'item_type' => Destination::class,
                'item_id' => $regions['kansai']->id,
                'label' => 'Kansai',
                'summary' => 'Kyoto, Osaka, Nara, Kobe, food, temples, and easy day trips.',
                'sort_order' => 3,
            ],
            [
                'module' => $toolsModule,
                'item_type' => ServiceLink::class,
                'item_id' => $serviceLinks['rail-tickets']->id,
                'label' => 'Rail Tickets',
                'summary' => 'Compare demo rail ticket options before locking in long-distance routes.',
                'sort_order' => 1,
            ],
            [
                'module' => $toolsModule,
                'item_type' => ServiceLink::class,
                'item_id' => $serviceLinks['exchange-rate']->id,
                'label' => 'Exchange Rate',
                'summary' => 'Check a demo JPY conversion helper while estimating daily trip costs.',
                'sort_order' => 2,
            ],
        ] as $moduleItem) {
            HomepageModuleItem::updateOrCreate(
                [
                    'homepage_module_id' => $moduleItem['module']->id,
                    'item_type' => $moduleItem['item_type'],
                    'item_id' => $moduleItem['item_id'],
                ],
                [
                    'label' => $moduleItem['label'],
                    'summary' => $moduleItem['summary'],
                    'sort_order' => $moduleItem['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        AdPlacement::updateOrCreate(
            ['key' => 'article-body-middle'],
            [
                'name' => '文章正文中段广告',
                'page_type' => 'article',
                'position' => 'body_middle',
                'code' => '<ins class="adsbygoogle"></ins>',
                'is_enabled' => false,
                'notes' => 'Paste Google AdSense code here after approval.',
            ]
        );

        AdPlacement::updateOrCreate(
            ['key' => 'home-after-hero'],
            [
                'name' => '首页首屏后广告',
                'page_type' => 'home',
                'position' => 'after_hero',
                'code' => null,
                'is_enabled' => false,
                'notes' => 'Reserved homepage ad placement.',
            ]
        );
    }
}

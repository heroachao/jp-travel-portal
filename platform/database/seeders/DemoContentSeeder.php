<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\AdPlacement;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\Topic;
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
                'type' => 'city',
                'excerpt' => 'A practical guide to Tokyo neighborhoods, transit, food, and first-time trip planning.',
                'body' => '<p>Tokyo rewards travelers who plan by neighborhood and transit line rather than by checklist.</p>',
                'seo_title' => 'Tokyo Travel Guide',
                'meta_description' => 'Plan a Tokyo trip with neighborhood guides, transport tips, and practical travel notes.',
                'is_indexable' => true,
            ]
        );

        $kyoto = Destination::updateOrCreate(
            ['slug' => 'kyoto'],
            [
                'name' => 'Kyoto',
                'type' => 'city',
                'excerpt' => 'Temples, quiet lanes, seasonal walks, and cultural travel notes for Kyoto.',
                'body' => '<p>Kyoto is best explored slowly, with early starts and careful attention to local etiquette.</p>',
                'seo_title' => 'Kyoto Travel Guide',
                'meta_description' => 'Explore Kyoto temples, neighborhoods, seasonal routes, and travel planning tips.',
                'is_indexable' => true,
            ]
        );

        $hokkaido = Destination::updateOrCreate(
            ['slug' => 'hokkaido'],
            [
                'name' => 'Hokkaido',
                'type' => 'region',
                'excerpt' => 'Wide landscapes, winter travel, national parks, and food-focused routes in northern Japan.',
                'body' => '<p>Hokkaido works well for travelers who want open space, seasonal food, and nature-forward routes.</p>',
                'seo_title' => 'Hokkaido Travel Guide',
                'meta_description' => 'Plan Hokkaido travel around national parks, winter routes, food, and scenic rail journeys.',
                'is_indexable' => true,
            ]
        );

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

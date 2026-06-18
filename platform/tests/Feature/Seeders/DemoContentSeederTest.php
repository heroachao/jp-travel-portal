<?php

namespace Tests\Feature\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\SiteSetting;
use App\Models\TravelCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_phase_one_demo_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $expectedCategorySlugs = [
            'guide',
            'things-to-do',
            'food',
            'shopping',
            'lodging',
            'itinerary',
            'transport',
            'basics',
        ];

        $expectedChannelDestinationSlugs = [
            'hokkaido',
            'tohoku',
            'tokyo',
            'hokuriku',
            'chubu',
            'kansai',
            'chugoku',
            'shikoku',
            'kyushu',
            'okinawa',
        ];

        $actualCategorySlugs = TravelCategory::query()->orderBy('slug')->pluck('slug')->all();

        $this->assertEqualsCanonicalizing($expectedCategorySlugs, $actualCategorySlugs);

        $actualChannelDestinationSlugs = Destination::query()
            ->where('is_channel', true)
            ->pluck('slug')
            ->all();

        $this->assertEmpty(array_diff($expectedChannelDestinationSlugs, $actualChannelDestinationSlugs));
        $this->assertEmpty(array_diff($actualChannelDestinationSlugs, $expectedChannelDestinationSlugs));
        $this->assertCount(10, $actualChannelDestinationSlugs);

        $this->assertArticleHasCategories('first-timers-guide-to-tokyo-neighborhoods', ['guide', 'transport']);
        $this->assertArticleHasCategories('three-days-in-kyoto', ['itinerary', 'basics']);

        $enabledServiceLinks = ServiceLink::query()->where('is_enabled', true)->get();

        $this->assertGreaterThanOrEqual(5, $enabledServiceLinks->count());

        foreach ($enabledServiceLinks as $serviceLink) {
            $this->assertStringStartsWith('https://', $serviceLink->url);
        }

        $this->assertEmpty(array_diff(
            $enabledServiceLinks->pluck('type')->all(),
            ['guide', 'activity', 'hotel', 'flight', 'rail', 'shop', 'community', 'exchange_rate', 'advertising', 'custom'],
        ));

        $this->assertGreaterThanOrEqual(3, HomepageModule::query()->where('is_enabled', true)->count());
        $this->assertEmpty(array_diff(
            HomepageModule::query()->pluck('type')->all(),
            ['featured_articles', 'latest_articles', 'popular_articles', 'region_grid', 'category_grid', 'service_highlights', 'travel_tools'],
        ));
        $this->assertSame('featured_articles', HomepageModule::query()->where('placement_key', 'home-featured')->value('type'));
        $this->assertSame('region_grid', HomepageModule::query()->where('placement_key', 'home-regions')->value('type'));
        $this->assertSame('travel_tools', HomepageModule::query()->where('placement_key', 'home-tools')->value('type'));

        foreach (['home-featured', 'home-regions', 'home-tools'] as $placementKey) {
            $module = HomepageModule::query()
                ->where('placement_key', $placementKey)
                ->where('is_enabled', true)
                ->firstOrFail();
            $items = $module->items()->enabled()->with('item')->get();

            $this->assertGreaterThan(0, $items->count(), "Expected {$placementKey} to have enabled items.");

            foreach ($items as $moduleItem) {
                $this->assertModuleItemTargetIsRenderable($moduleItem);
            }
        }

        $this->assertGreaterThanOrEqual(2, ArticleFaq::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(6, HomepageModuleItem::query()->where('is_enabled', true)->count());

        $settings = SiteSetting::query()->findOrFail(1);

        $this->assertSame('Japan Travel Guide', $settings->site_name);
        $this->assertSame('Japan Travel Guide', $settings->seo_title_suffix);
        $this->assertSame('Independent planning guides for Japan travelers.', $settings->tagline);
        $this->assertSame(
            'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.',
            $settings->default_meta_description,
        );
        $this->assertNull($settings->ga4_measurement_id);
        $this->assertNull($settings->adsense_publisher_id);
        $this->assertFalse($settings->analytics_enabled);
        $this->assertFalse($settings->ads_enabled);
        $this->assertFalse($settings->organization_schema_enabled);
    }

    public function test_database_seeder_is_idempotent_for_phase_one_demo_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = $this->phaseOneContentCounts();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, $this->phaseOneContentCounts());
    }

    public function test_database_seeder_preserves_existing_site_settings(): void
    {
        SiteSetting::create([
            'id' => 1,
            'site_name' => 'Configured Japan Portal',
            'seo_title_suffix' => 'Configured Portal',
            'tagline' => 'Configured tagline.',
            'default_meta_description' => 'Configured default description.',
            'ga4_measurement_id' => 'G-CONFIGURED1',
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
            'analytics_enabled' => true,
            'ads_enabled' => true,
            'organization_schema_enabled' => true,
            'contact_email' => 'hello@example.com',
            'social_links' => ['x' => 'https://x.com/configured'],
            'robots_extra_rules' => 'Disallow: /configured-private',
        ]);

        $this->seed(DatabaseSeeder::class);

        $settings = SiteSetting::findOrFail(1);

        $this->assertSame('Configured Japan Portal', $settings->site_name);
        $this->assertSame('Configured Portal', $settings->seo_title_suffix);
        $this->assertSame('Configured tagline.', $settings->tagline);
        $this->assertSame('Configured default description.', $settings->default_meta_description);
        $this->assertSame('G-CONFIGURED1', $settings->ga4_measurement_id);
        $this->assertSame('ca-pub-1234567890123456', $settings->adsense_publisher_id);
        $this->assertTrue($settings->analytics_enabled);
        $this->assertTrue($settings->ads_enabled);
        $this->assertTrue($settings->organization_schema_enabled);
        $this->assertSame('hello@example.com', $settings->contact_email);
        $this->assertSame(['x' => 'https://x.com/configured'], $settings->social_links);
        $this->assertSame('Disallow: /configured-private', $settings->robots_extra_rules);
    }

    /**
     * @param  list<string>  $expectedSlugs
     */
    private function assertArticleHasCategories(string $articleSlug, array $expectedSlugs): void
    {
        $article = Article::query()->where('slug', $articleSlug)->firstOrFail();

        $this->assertEqualsCanonicalizing(
            $expectedSlugs,
            $article->travelCategories()
                ->whereIn('slug', $expectedSlugs)
                ->pluck('slug')
                ->all(),
        );
    }

    private function assertModuleItemTargetIsRenderable(HomepageModuleItem $moduleItem): void
    {
        $target = $moduleItem->item;

        $this->assertNotNull($target, "Missing target for homepage module item {$moduleItem->id}.");

        if ($target instanceof Article) {
            $this->assertSame(ArticleStatus::Published, $target->status);
            $this->assertTrue((bool) $target->published_at?->lte(now()));

            return;
        }

        if ($target instanceof Destination) {
            $this->assertTrue($target->is_indexable);

            return;
        }

        if ($target instanceof TravelCategory) {
            $this->assertTrue($target->is_visible);
            $this->assertTrue($target->is_indexable);

            return;
        }

        if ($target instanceof ServiceLink) {
            $this->assertTrue($target->is_enabled);

            return;
        }

        $this->fail("Unsupported homepage module item target [{$moduleItem->item_type}].");
    }

    /**
     * @return array<string, int>
     */
    private function phaseOneContentCounts(): array
    {
        return [
            'travel_categories' => TravelCategory::count(),
            'channel_destinations' => Destination::query()->where('is_channel', true)->count(),
            'service_links' => ServiceLink::count(),
            'homepage_modules' => HomepageModule::count(),
            'homepage_module_items' => HomepageModuleItem::count(),
            'article_faqs' => ArticleFaq::count(),
            'site_settings' => SiteSetting::count(),
        ];
    }
}

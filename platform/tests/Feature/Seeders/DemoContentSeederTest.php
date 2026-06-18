<?php

namespace Tests\Feature\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
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

        $this->assertGreaterThanOrEqual(8, TravelCategory::count());

        $actualCategorySlugs = TravelCategory::query()->pluck('slug')->all();

        $this->assertEmpty(array_diff($expectedCategorySlugs, $actualCategorySlugs));

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
    }

    public function test_database_seeder_is_idempotent_for_phase_one_demo_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $counts = $this->phaseOneContentCounts();

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($counts, $this->phaseOneContentCounts());
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
        ];
    }
}

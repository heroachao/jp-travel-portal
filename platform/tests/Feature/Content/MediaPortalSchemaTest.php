<?php

namespace Tests\Feature\Content;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPortalSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_can_connect_categories_and_ordered_faqs(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'source_name' => 'Japan Meteorological Agency',
            'source_url' => 'https://www.jma.go.jp/',
            'display_updated_at' => now()->subDay(),
            'reading_time_minutes' => 6,
            'popularity_score' => 42,
            'has_coupon' => true,
        ]);
        $category = TravelCategory::factory()->create(['title' => 'Transport', 'slug' => 'transport']);

        $article->travelCategories()->attach($category, ['sort_order' => 3]);
        ArticleFaq::factory()->for($article)->create([
            'question' => 'Do I need a rail pass for Tokyo?',
            'answer' => '<p>Most city-only trips work better with IC cards.</p>',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $fresh = $article->fresh(['travelCategories', 'faqs']);

        $this->assertTrue($fresh->travelCategories->contains($category));
        $this->assertSame('Do I need a rail pass for Tokyo?', $fresh->faqs->first()->question);
        $this->assertTrue($fresh->has_coupon);
    }

    public function test_destination_can_be_marked_as_public_region_channel(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'type' => 'region',
            'display_name' => 'Tokyo Region',
            'is_channel' => true,
            'sort_order' => 10,
        ]);

        $channels = Destination::query()->channel()->ordered()->get();

        $this->assertTrue($channels->contains($destination));
        $this->assertSame('Tokyo Region', $destination->fresh()->display_name);
    }

    public function test_homepage_module_can_hold_typed_curated_items(): void
    {
        $module = HomepageModule::factory()->create([
            'placement_key' => 'home-featured',
            'type' => 'featured_articles',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Article::class,
            'item_id' => $article->id,
            'label' => 'Featured Tokyo guide',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $this->assertSame('Featured Tokyo guide', $module->fresh('items.item')->items->first()->label);
        $this->assertTrue($module->fresh('items.item')->items->first()->item->is($article));
    }

    public function test_service_link_supports_safe_external_url_and_placement(): void
    {
        $link = ServiceLink::factory()->create([
            'type' => 'rail',
            'label' => 'Rail Tickets',
            'url' => 'https://example.com/rail',
            'placement' => 'header',
            'tracking_key' => 'rail-tickets',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('service_links', [
            'id' => $link->id,
            'type' => 'rail',
            'placement' => 'header',
            'tracking_key' => 'rail-tickets',
        ]);
    }
}

<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MediaPortalPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_index_and_show_pages_render_channel_content(): void
    {
        $tokyo = Destination::factory()->create([
            'type' => 'region',
            'name' => 'Tokyo',
            'display_name' => 'Tokyo',
            'slug' => 'tokyo',
            'excerpt' => 'Tokyo travel planning notes for first-time visitors.',
            'seo_title' => 'Tokyo Travel Guide',
            'meta_description' => 'Travel ideas and practical guides for Tokyo.',
            'is_channel' => true,
            'is_indexable' => true,
            'sort_order' => 1,
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Tokyo Rail Pass Basics',
            'slug' => 'tokyo-rail-pass-basics',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $tokyo->articles()->attach($article);

        $this->get('/regions')
            ->assertOk()
            ->assertSee('Japan Regions')
            ->assertSee('Tokyo');

        $this->get('/regions/tokyo')
            ->assertOk()
            ->assertSee('Tokyo Travel Guide')
            ->assertSee('Tokyo Rail Pass Basics');
    }

    public function test_category_page_renders_articles_and_seo_meta(): void
    {
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'display_name' => 'Transport',
            'slug' => 'transport',
            'seo_title' => 'Japan Transport Planning Guides',
            'meta_description' => 'Compare Japan train, bus, and airport transfer options.',
            'is_indexable' => true,
            'is_visible' => true,
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Choosing the Right Japan Rail Ticket',
            'slug' => 'choosing-the-right-japan-rail-ticket',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $category->articles()->attach($article, ['sort_order' => 1]);

        $this->get('/categories/transport')
            ->assertOk()
            ->assertSee('Japan Transport Planning Guides')
            ->assertSee('Choosing the Right Japan Rail Ticket')
            ->assertSee('Compare Japan train, bus, and airport transfer options', false);
    }

    public function test_category_page_orders_articles_by_latest_publication_date(): void
    {
        $category = TravelCategory::factory()->create([
            'slug' => 'transport-latest',
            'is_visible' => true,
        ]);

        $olderArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Older Transport Guide',
            'slug' => 'older-transport-guide',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDays(3),
        ]);

        $newerArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Newest Transport Guide',
            'slug' => 'newest-transport-guide',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $category->articles()->attach($olderArticle, ['sort_order' => 1]);
        $category->articles()->attach($newerArticle, ['sort_order' => 2]);

        $this->get('/categories/transport-latest')
            ->assertOk()
            ->assertSeeInOrder([
                'Newest Transport Guide',
                'Older Transport Guide',
            ]);
    }

    public function test_channel_destination_redirects_from_legacy_destination_url_to_region_url(): void
    {
        $tokyo = Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'seo_title' => 'Tokyo Travel Guide',
            'is_channel' => true,
            'is_indexable' => true,
        ]);

        $this->get('/destinations/tokyo')
            ->assertStatus(301)
            ->assertRedirect(route('regions.show', $tokyo));
    }

    public function test_non_channel_destination_still_renders_on_legacy_destination_url(): void
    {
        Destination::factory()->create([
            'name' => 'Ueno',
            'slug' => 'ueno',
            'seo_title' => 'Ueno Travel Guide',
            'is_channel' => false,
            'is_indexable' => true,
        ]);

        $this->get('/destinations/ueno')
            ->assertOk()
            ->assertSee('Ueno')
            ->assertSee('rel="canonical"', false);
    }

    public function test_hidden_category_returns_not_found(): void
    {
        $category = TravelCategory::factory()->create([
            'slug' => 'hidden-transport',
            'is_visible' => false,
        ]);

        $this->assertTrue(Route::has('categories.show'));

        $this->get("/categories/{$category->slug}")
            ->assertNotFound();
    }

    public function test_non_channel_destination_returns_not_found_on_region_route(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Ueno',
            'slug' => 'ueno',
            'is_channel' => false,
            'is_indexable' => true,
        ]);

        $this->assertTrue(Route::has('regions.show'));

        $this->get("/regions/{$destination->slug}")
            ->assertNotFound();
    }

    public function test_non_indexable_channel_destination_returns_not_found_on_region_route(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Private Tokyo',
            'slug' => 'private-tokyo',
            'is_channel' => true,
            'is_indexable' => false,
        ]);

        $this->get("/regions/{$destination->slug}")
            ->assertNotFound();
    }

    public function test_region_index_hides_noindex_channels_and_non_channel_destinations(): void
    {
        Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'is_channel' => true,
            'is_indexable' => true,
        ]);

        Destination::factory()->create([
            'name' => 'Private Hokkaido',
            'slug' => 'private-hokkaido',
            'is_channel' => true,
            'is_indexable' => false,
        ]);

        Destination::factory()->create([
            'name' => 'Ueno',
            'slug' => 'ueno',
            'is_channel' => false,
            'is_indexable' => true,
        ]);

        $this->get('/regions')
            ->assertOk()
            ->assertSee('Tokyo')
            ->assertDontSee('Private Hokkaido')
            ->assertDontSee('Ueno');
    }

    public function test_non_indexable_category_page_outputs_noindex_meta(): void
    {
        TravelCategory::factory()->create([
            'title' => 'Hidden From Search',
            'slug' => 'hidden-from-search',
            'is_visible' => true,
            'is_indexable' => false,
        ]);

        $this->get('/categories/hidden-from-search')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false);
    }

    public function test_draft_and_future_articles_do_not_render_on_category_or_region_pages(): void
    {
        $category = TravelCategory::factory()->create([
            'slug' => 'transport-published-only',
            'is_visible' => true,
        ]);

        $region = Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo-published-only',
            'is_channel' => true,
            'is_indexable' => true,
        ]);

        $publishedArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Published Planning Guide',
            'slug' => 'published-planning-guide',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $draftArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Draft Planning Guide',
            'slug' => 'draft-planning-guide',
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ]);

        $futureArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Future Planning Guide',
            'slug' => 'future-planning-guide',
            'status' => ArticleStatus::Published,
            'published_at' => now()->addDay(),
        ]);

        foreach ([$publishedArticle, $draftArticle, $futureArticle] as $article) {
            $category->articles()->attach($article, ['sort_order' => 1]);
            $region->articles()->attach($article);
        }

        $this->get('/categories/transport-published-only')
            ->assertOk()
            ->assertSee('Published Planning Guide')
            ->assertDontSee('Draft Planning Guide')
            ->assertDontSee('Future Planning Guide');

        $this->get('/regions/tokyo-published-only')
            ->assertOk()
            ->assertSee('Published Planning Guide')
            ->assertDontSee('Draft Planning Guide')
            ->assertDontSee('Future Planning Guide');
    }
}

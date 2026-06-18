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
}

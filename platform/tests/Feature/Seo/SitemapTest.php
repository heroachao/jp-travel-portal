<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_published_articles_and_excludes_drafts(): void
    {
        Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'published-kyoto',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'is_indexable' => true,
        ]);

        Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'draft-kyoto',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee('/articles/published-kyoto')
            ->assertDontSee('/articles/draft-kyoto');
    }

    public function test_sitemap_includes_region_and_category_channels(): void
    {
        Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'is_indexable' => true,
            'is_channel' => true,
        ]);

        Destination::factory()->create([
            'name' => 'Osaka',
            'slug' => 'osaka',
            'is_indexable' => true,
            'is_channel' => false,
        ]);

        TravelCategory::factory()->create([
            'title' => 'Transport',
            'display_name' => 'Transport',
            'slug' => 'transport',
            'is_indexable' => true,
            'is_visible' => true,
        ]);

        TravelCategory::factory()->create([
            'title' => 'Hidden',
            'display_name' => 'Hidden',
            'slug' => 'hidden',
            'is_indexable' => true,
            'is_visible' => false,
        ]);

        TravelCategory::factory()->create([
            'title' => 'Noindex',
            'display_name' => 'Noindex',
            'slug' => 'noindex',
            'is_indexable' => false,
            'is_visible' => true,
        ]);

        ServiceLink::factory()->create([
            'type' => 'rail',
            'label' => 'Rail Pass',
            'url' => 'https://example.com/rail',
            'is_enabled' => true,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/regions/tokyo')
            ->assertSee('/categories/transport')
            ->assertDontSee('/regions/osaka')
            ->assertDontSee('/destinations/osaka')
            ->assertDontSee('/categories/hidden')
            ->assertDontSee('/categories/noindex')
            ->assertDontSee('example.com/rail');
    }
}

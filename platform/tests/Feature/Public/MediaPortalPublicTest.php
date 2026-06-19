<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Models\User;
use App\Support\PublicUrl;
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
            ->assertRedirect(PublicUrl::route('regions.show', $tokyo));
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

    public function test_homepage_renders_service_region_category_and_module_data(): void
    {
        ServiceLink::factory()->create([
            'label' => 'Rail Tickets',
            'type' => 'rail',
            'placement' => 'header',
            'url' => 'https://example.com/rail',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        ServiceLink::factory()->create([
            'label' => 'Hidden Hotel Deals',
            'placement' => 'header',
            'is_enabled' => false,
        ]);
        ServiceLink::factory()->create([
            'label' => 'Footer Planning Desk',
            'placement' => 'footer',
            'url' => 'https://example.com/footer-planning',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        Destination::factory()->create([
            'name' => 'Kansai',
            'display_name' => 'Kansai',
            'slug' => 'kansai',
            'is_channel' => true,
            'is_indexable' => true,
            'sort_order' => 1,
        ]);
        Destination::factory()->create([
            'name' => 'Private Hokkaido',
            'slug' => 'private-hokkaido',
            'is_channel' => true,
            'is_indexable' => false,
            'sort_order' => 2,
        ]);
        Destination::factory()->create([
            'name' => 'Ueno Local Spot',
            'slug' => 'ueno-local-spot',
            'is_channel' => false,
            'is_indexable' => true,
            'sort_order' => 3,
        ]);

        TravelCategory::factory()->create([
            'title' => 'Food',
            'display_name' => 'Food',
            'slug' => 'food',
            'is_visible' => true,
            'sort_order' => 1,
        ]);
        TravelCategory::factory()->create([
            'title' => 'Hidden Experiences',
            'slug' => 'hidden-experiences',
            'is_visible' => false,
            'sort_order' => 2,
        ]);
        TravelCategory::factory()->create([
            'title' => 'Private Noindex Category',
            'display_name' => 'Private Noindex Category',
            'slug' => 'private-noindex-category',
            'is_visible' => true,
            'is_indexable' => false,
            'sort_order' => 3,
        ]);

        HomepageModule::factory()->create([
            'placement_key' => 'home-featured',
            'type' => 'featured_articles',
            'title' => 'Featured Guides',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        HomepageModule::factory()->create([
            'placement_key' => 'home-hidden',
            'type' => 'featured_articles',
            'title' => 'Hidden Homepage Module',
            'is_enabled' => false,
            'sort_order' => 2,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Rail Tickets')
            ->assertSee('rel="nofollow noopener sponsored"', false)
            ->assertSee('Footer Planning Desk')
            ->assertSee('Kansai')
            ->assertSee('Food')
            ->assertSee('Featured Guides')
            ->assertDontSee('Hidden Hotel Deals')
            ->assertDontSee('Private Hokkaido')
            ->assertDontSee('Ueno Local Spot')
            ->assertDontSee('Hidden Experiences')
            ->assertDontSee('Private Noindex Category')
            ->assertDontSee('Hidden Homepage Module');
    }

    public function test_homepage_module_items_render_public_targets_and_skip_disabled_or_unpublished_items(): void
    {
        $module = HomepageModule::factory()->create([
            'placement_key' => 'home-module-items',
            'type' => 'featured_articles',
            'title' => 'Curated Planning Set',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $publishedArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Module Rail Feature',
            'slug' => 'module-rail-feature',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
        ]);
        $draftArticle = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Draft Module Feature',
            'slug' => 'draft-module-feature',
            'status' => ArticleStatus::Draft,
            'published_at' => null,
        ]);
        $region = Destination::factory()->create([
            'name' => 'Module Kansai Channel',
            'slug' => 'module-kansai-channel',
            'is_channel' => true,
            'is_indexable' => true,
        ]);
        $noindexDestination = Destination::factory()->create([
            'name' => 'Module Private Destination',
            'slug' => 'module-private-destination',
            'is_channel' => true,
            'is_indexable' => false,
        ]);
        $category = TravelCategory::factory()->create([
            'title' => 'Module Transport Category',
            'display_name' => 'Module Transport Category',
            'slug' => 'module-transport-category',
            'is_visible' => true,
            'is_indexable' => true,
        ]);
        $noindexCategory = TravelCategory::factory()->create([
            'title' => 'Module Noindex Category',
            'display_name' => 'Module Noindex Category',
            'slug' => 'module-noindex-category',
            'is_visible' => true,
            'is_indexable' => false,
        ]);
        $serviceLink = ServiceLink::factory()->create([
            'label' => 'Module Rail Service',
            'url' => 'https://example.com/module-rail',
            'is_enabled' => true,
        ]);
        $disabledServiceLink = ServiceLink::factory()->create([
            'label' => 'Module Disabled Service',
            'url' => 'https://example.com/module-disabled',
            'is_enabled' => false,
        ]);

        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Article::class,
            'item_id' => $publishedArticle->id,
            'label' => 'Module Rail Feature Label',
            'summary' => 'Curated rail module summary.',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Destination::class,
            'item_id' => $region->id,
            'label' => 'Module Kansai Channel Label',
            'summary' => 'Regional module summary.',
            'sort_order' => 2,
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => TravelCategory::class,
            'item_id' => $category->id,
            'label' => 'Module Transport Category Label',
            'summary' => 'Category module summary.',
            'sort_order' => 3,
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => ServiceLink::class,
            'item_id' => $serviceLink->id,
            'label' => 'Module Rail Service Label',
            'summary' => 'External service module summary.',
            'sort_order' => 4,
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Article::class,
            'item_id' => $publishedArticle->id,
            'label' => 'Disabled Module Item',
            'is_enabled' => false,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Article::class,
            'item_id' => $draftArticle->id,
            'label' => 'Draft Module Item',
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Destination::class,
            'item_id' => $noindexDestination->id,
            'label' => 'Noindex Destination Module Item',
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => TravelCategory::class,
            'item_id' => $noindexCategory->id,
            'label' => 'Noindex Category Module Item',
            'is_enabled' => true,
        ]);
        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => ServiceLink::class,
            'item_id' => $disabledServiceLink->id,
            'label' => 'Disabled Service Module Item',
            'is_enabled' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Curated Planning Set')
            ->assertSee('Module Rail Feature Label')
            ->assertSee('Curated rail module summary.')
            ->assertSee('href="'.PublicUrl::route('articles.show', $publishedArticle).'"', false)
            ->assertSee('Module Kansai Channel Label')
            ->assertSee('href="'.PublicUrl::route('regions.show', $region).'"', false)
            ->assertSee('Module Transport Category Label')
            ->assertSee('href="'.PublicUrl::route('categories.show', $category).'"', false)
            ->assertSee('Module Rail Service Label')
            ->assertSee('href="https://example.com/module-rail"', false)
            ->assertSee('rel="nofollow noopener sponsored"', false)
            ->assertDontSee('Disabled Module Item')
            ->assertDontSee('Draft Module Item')
            ->assertDontSee('Noindex Destination Module Item')
            ->assertDontSee('Noindex Category Module Item')
            ->assertDontSee('Disabled Service Module Item');
    }

    public function test_article_page_renders_faq_source_and_category_links(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Tokyo Rail Basics',
            'slug' => 'tokyo-rail-basics',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
            'display_updated_at' => now(),
            'reading_time_minutes' => 7,
            'source_name' => 'Tokyo Metro Source',
            'source_url' => 'https://www.tokyometro.jp/en/',
        ]);
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'display_name' => 'Transport',
            'slug' => 'transport',
            'is_visible' => true,
        ]);
        $article->travelCategories()->attach($category, ['sort_order' => 1]);

        ArticleFaq::factory()->for($article)->create([
            'question' => 'Can I use Suica in Tokyo?',
            'answer' => '<p>Yes, for most short city trips.</p>',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        ArticleFaq::factory()->for($article)->create([
            'question' => 'Hidden FAQ Question?',
            'answer' => '<p>This answer should not render.</p>',
            'is_enabled' => false,
            'sort_order' => 2,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Tokyo Metro Source')
            ->assertSee('href="https://www.tokyometro.jp/en/"', false)
            ->assertSee('rel="nofollow noopener"', false)
            ->assertSee('Transport')
            ->assertSee('min read')
            ->assertSee('Editorial review')
            ->assertSee('Quick pre-trip checklist')
            ->assertSee('"wordCount":', false)
            ->assertSee('Can I use Suica in Tokyo?')
            ->assertSee('Yes, for most short city trips.', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"headline":"Tokyo Rail Basics"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Can I use Suica in Tokyo?', false)
            ->assertDontSee('Hidden FAQ Question?')
            ->assertDontSee('This answer should not render', false);
    }

    public function test_article_faq_json_ld_escapes_script_closing_sequences(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Safe FAQ JSON',
            'slug' => 'safe-faq-json',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        ArticleFaq::factory()->for($article)->create([
            'question' => 'Can a </script> sequence close JSON-LD?',
            'answer' => '<p>No, JSON encoding keeps it inside data.</p>',
            'is_enabled' => true,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Can a &lt;/script&gt; sequence close JSON-LD?', false)
            ->assertSee('Can a \\u003C/script\\u003E sequence close JSON-LD?', false)
            ->assertDontSee('Can a </script> sequence close JSON-LD?', false);
    }

    public function test_article_source_without_url_renders_plain_text_without_empty_link(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Nara Walking Notes',
            'slug' => 'nara-walking-notes',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'source_name' => 'Local Tourism Board',
            'source_url' => null,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Local Tourism Board')
            ->assertDontSee('href="#"', false);
    }

    public function test_article_body_rewrites_legacy_internal_links_to_canonical_https_urls(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Legacy Credit Link',
            'slug' => 'legacy-credit-link',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'body' => '<p><a href="http://japantriptools.com/image-credits#article-legacy-credit-link">Image credit details</a></p>',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('href="'.PublicUrl::route('image-credits').'#article-legacy-credit-link"', false)
            ->assertDontSee('http://japantriptools.com/image-credits', false);
    }

    public function test_article_image_urls_preserve_known_working_wikimedia_thumbnail_paths(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Wikimedia Thumbnail',
            'slug' => 'wikimedia-thumbnail',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'body' => '<p><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Nikko_toshogu_shrine.jpg/1920px-Nikko_toshogu_shrine.jpg" alt="Nikko"></p>',
        ]);

        $this->assertSame(
            'https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Nikko_toshogu_shrine.jpg/1920px-Nikko_toshogu_shrine.jpg',
            $article->firstImageUrl(640),
        );
    }
}

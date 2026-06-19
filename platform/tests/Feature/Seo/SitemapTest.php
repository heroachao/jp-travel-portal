<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\ServiceLink;
use App\Models\Tag;
use App\Models\Topic;
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

    public function test_sitemap_includes_core_public_and_policy_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('home'), false)
            ->assertSee(route('articles.index'), false)
            ->assertSee(route('regions.index'), false)
            ->assertSee(route('destinations.index'), false)
            ->assertSee(route('pages.about'), false)
            ->assertSee(route('pages.contact'), false)
            ->assertSee(route('pages.privacy'), false)
            ->assertSee(route('pages.terms'), false)
            ->assertSee(route('pages.disclaimer'), false);
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

    public function test_sitemap_only_includes_taxonomy_pages_with_enough_published_guides(): void
    {
        $author = User::factory()->create();
        $publishedArticles = Article::factory()
            ->count(3)
            ->create([
                'author_id' => $author,
                'status' => ArticleStatus::Published,
                'published_at' => now(),
            ]);
        $singleArticle = Article::factory()->create([
            'author_id' => $author,
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $strongTopic = Topic::factory()->create(['slug' => 'strong-topic']);
        $thinTopic = Topic::factory()->create(['slug' => 'thin-topic']);
        $strongTag = Tag::factory()->create(['slug' => 'strong-tag']);
        $thinTag = Tag::factory()->create(['slug' => 'thin-tag']);

        $strongTopic->articles()->attach($publishedArticles->pluck('id'));
        $strongTag->articles()->attach($publishedArticles->pluck('id'));
        $thinTopic->articles()->attach($singleArticle->id);
        $thinTag->articles()->attach($singleArticle->id);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/topics/strong-topic')
            ->assertSee('/tags/strong-tag')
            ->assertDontSee('/topics/thin-topic')
            ->assertDontSee('/tags/thin-tag');
    }

    public function test_thin_tag_pages_remain_accessible_but_noindex(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);
        $tag = Tag::factory()->create(['slug' => 'thin-tag']);
        $tag->articles()->attach($article);

        $this->get(route('tags.show', $tag))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}

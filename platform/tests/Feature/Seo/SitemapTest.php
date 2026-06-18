<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
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
}

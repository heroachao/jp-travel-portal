<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_article_is_public(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'A Quiet Guide to Nara',
            'slug' => 'quiet-guide-to-nara',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'seo_title' => 'A Quiet Guide to Nara | Japan Travel Guide',
            'meta_description' => 'Plan a quiet visit to Nara with temples, parks, and local travel notes.',
        ]);

        $this->get("/articles/{$article->slug}")
            ->assertOk()
            ->assertSee('A Quiet Guide to Nara')
            ->assertSee('Plan a quiet visit to Nara', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_draft_article_is_not_public(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'draft-osaka',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get("/articles/{$article->slug}")->assertNotFound();
    }
}

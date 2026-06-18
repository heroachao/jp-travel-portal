<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_outputs_noindex_when_disabled(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'private-but-published',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'is_indexable' => false,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }

    public function test_article_outputs_selected_og_media_as_og_image(): void
    {
        $cover = MediaAsset::factory()->create([
            'path' => 'media/2026/06/cover-image.jpg',
            'alt_text' => 'Cover image',
        ]);
        $og = MediaAsset::factory()->create([
            'path' => 'media/2026/06/social-share.jpg',
            'alt_text' => 'Social share image',
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'article-with-og-media',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'cover_media_id' => $cover->id,
            'og_media_id' => $og->id,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('property="og:image"', false)
            ->assertSee('property="og:image" content="http://localhost/storage/media/2026/06/social-share.jpg"', false)
            ->assertDontSee('property="og:image" content="http://localhost/storage/media/2026/06/cover-image.jpg"', false);
    }

    public function test_article_falls_back_to_cover_media_for_og_image(): void
    {
        $cover = MediaAsset::factory()->create([
            'path' => 'media/2026/06/fallback-cover.jpg',
            'alt_text' => 'Fallback cover image',
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'article-with-cover-media',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'cover_media_id' => $cover->id,
            'og_media_id' => null,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('property="og:image"', false)
            ->assertSee('/storage/media/2026/06/fallback-cover.jpg', false);
    }

    public function test_article_page_displays_cover_image_and_source_note(): void
    {
        $cover = MediaAsset::factory()->create([
            'path' => 'media/2026/06/visible-cover.jpg',
            'alt_text' => 'Visible Kyoto cover',
            'source_note' => 'Photo courtesy of Kyoto Tourism.',
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'article-visible-cover',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'cover_media_id' => $cover->id,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('/storage/media/2026/06/visible-cover.jpg', false)
            ->assertSee('alt="Visible Kyoto cover"', false)
            ->assertSee('Photo courtesy of Kyoto Tourism.');
    }
}

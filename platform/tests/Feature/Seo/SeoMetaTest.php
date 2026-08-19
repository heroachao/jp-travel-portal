<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\MediaAsset;
use App\Models\SiteSetting;
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
            ->assertSee('property="og:image" content="'.asset('storage/media/2026/06/social-share.jpg').'"', false)
            ->assertDontSee('property="og:image" content="'.asset('storage/media/2026/06/cover-image.jpg').'"', false);
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
            ->assertSee('property="og:image" content="'.asset('storage/media/2026/06/fallback-cover.jpg').'"', false);
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

    public function test_public_title_removes_repeated_brand_suffix_and_stays_search_result_length(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Trip Tools',
            'seo_title_suffix' => 'Japan Travel Guide',
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'okinawa-long-title',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'seo_title' => 'Okinawa Island Planning Guide: Naha, Beaches, Culture, Weather, and Outer Islands | Japan Trip Tools | Japan Travel Guide',
        ]);

        $response = $this->get(route('articles.show', $article))->assertOk();
        $html = $response->getContent();

        preg_match('/<title>(.*?)<\/title>/', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null);
        $this->assertLessThanOrEqual(70, mb_strlen($matches[1]));
        $this->assertStringNotContainsString('Japan Trip Tools | Japan Travel Guide', $matches[1]);
    }

    public function test_public_title_keeps_hyphenated_travel_terms_intact(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Trip Tools',
            'seo_title_suffix' => 'Japan Travel Guide',
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Nara Half-Day vs Full-Day Decision Guide',
            'slug' => 'nara-hyphen-title',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'seo_title' => 'Nara Half-Day vs Full-Day Decision Guide | Japan Trip Tools',
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<title>Nara Half-Day vs Full-Day Decision Guide | Japan Travel Guide</title>', false)
            ->assertDontSee('<title>Nara Half | Japan Travel Guide</title>', false);
    }
}

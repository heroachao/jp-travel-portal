<?php

namespace Tests\Feature\Content;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\TravelCategory;
use App\Models\User;
use App\Support\PublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelContentImportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_curated_travel_content_with_sources_and_relationships(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);
        TravelCategory::query()->create([
            'title' => 'Guide',
            'display_name' => 'Guides',
            'slug' => 'guide',
            'is_visible' => true,
            'is_indexable' => true,
        ]);
        Destination::query()->create([
            'name' => 'Tokyo',
            'display_name' => 'Tokyo',
            'slug' => 'tokyo',
            'type' => 'city',
            'is_channel' => true,
            'is_indexable' => true,
        ]);

        $manifestPath = storage_path('framework/testing-travel-content.json');
        file_put_contents($manifestPath, json_encode([
            'articles' => [[
                'slug' => 'official-tokyo-test-guide',
                'title' => 'Official Tokyo Test Guide',
                'excerpt' => 'A professional English summary based on official Tokyo tourism information.',
                'category_slugs' => ['guide'],
                'destination_slugs' => ['tokyo'],
                'topic_slugs' => ['tokyo-planning'],
                'tag_slugs' => ['tokyo'],
                'image' => [
                    'url' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Exterior-test.jpg',
                    'alt' => 'Tokyo Station',
                    'caption' => 'Open-license Tokyo image.',
                    'license' => 'CC0 1.0',
                    'attribution' => 'Wikimedia Commons',
                    'source_url' => 'https://commons.wikimedia.org/wiki/File:Exterior-test.jpg',
                ],
                'sources' => [[
                    'name' => 'GO TOKYO',
                    'url' => 'https://www.gotokyo.org/en/index.html',
                ]],
                'sections' => [[
                    'heading' => 'Plan by area',
                    'paragraphs' => ['Tokyo is easier when first-time visitors group nearby neighborhoods.'],
                    'bullets' => ['Keep transfers simple.'],
                ]],
                'faqs' => [[
                    'question' => 'Is this copied from the source?',
                    'answer' => 'No. It is an original summary based on official facts.',
                ]],
            ]],
        ], JSON_THROW_ON_ERROR));

        $this->artisan('content:import-travel', [
            'manifest' => $manifestPath,
            '--publish' => true,
        ])->assertExitCode(0);

        $article = Article::query()->where('slug', 'official-tokyo-test-guide')->firstOrFail();

        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertSame('GO TOKYO', $article->source_name);
        $this->assertSame('https://www.gotokyo.org/en/index.html', $article->source_url);
        $this->assertStringContainsString('data-image-source-url="https://commons.wikimedia.org/wiki/File:Exterior-test.jpg"', $article->body);
        $this->assertStringContainsString('Image: Wikimedia Commons / CC0 1.0', $article->body);
        $this->assertStringContainsString(PublicUrl::route('image-credits').'#article-official-tokyo-test-guide', $article->body);
        $this->assertStringNotContainsString('Source: <a href="https://commons.wikimedia.org/wiki/File:Exterior-test.jpg"', $article->body);
        $this->assertStringContainsString('This article is an original English summary', $article->body);
        $this->assertTrue($article->travelCategories()->where('slug', 'guide')->exists());
        $this->assertTrue($article->destinations()->where('slug', 'tokyo')->exists());
        $this->assertTrue($article->topics()->where('slug', 'tokyo-planning')->exists());
        $this->assertTrue($article->tags()->where('slug', 'tokyo')->exists());
        $this->assertTrue($article->enabledFaqs()->where('question', 'Is this copied from the source?')->exists());

        @unlink($manifestPath);
    }
}

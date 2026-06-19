<?php

namespace Tests\Feature;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTravelToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tools_index_lists_on_site_tools(): void
    {
        $this->get(route('tools.index'))
            ->assertOk()
            ->assertSee('Japan travel tools that stay on this site.')
            ->assertSee(route('tools.show', 'trip-planner'), false)
            ->assertSee(route('tools.show', 'jr-pass-calculator'), false)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_tool_detail_renders_local_calculator_shell(): void
    {
        $this->get(route('tools.show', 'budget-calculator'))
            ->assertOk()
            ->assertSee('data-travel-tool="budget-calculator"', false)
            ->assertSee('Estimate budget')
            ->assertSee('Runs locally in your browser');
    }

    public function test_sitemap_includes_tools_and_image_credits(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('tools.index'), false)
            ->assertSee(route('tools.show', 'trip-planner'), false)
            ->assertSee(route('tools.show', 'tax-free-calculator'), false)
            ->assertSee(route('image-credits'), false);
    }

    public function test_image_credits_collects_structured_article_image_sources(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Kyoto Image Credit Test',
            'slug' => 'kyoto-image-credit-test',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'body' => '<figure data-image-source-url="https://commons.wikimedia.org/wiki/File:Kyoto.jpg" data-image-license="CC BY 4.0" data-image-license-url="https://creativecommons.org/licenses/by/4.0/" data-image-attribution="Example Photographer"><img src="https://example.com/kyoto.jpg" alt="Kyoto temple"><figcaption>Kyoto temple image. <a href="'.route('image-credits').'#article-kyoto-image-credit-test">Image credit details</a>.</figcaption></figure><p>Body.</p>',
        ]);

        $this->get(route('image-credits'))
            ->assertOk()
            ->assertSee('Kyoto Image Credit Test')
            ->assertSee('Example Photographer')
            ->assertSee('CC BY 4.0')
            ->assertSee(route('articles.show', $article), false)
            ->assertSee('target="_blank" rel="nofollow noopener noreferrer"', false);
    }
}

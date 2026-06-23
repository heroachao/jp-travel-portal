<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\AdPlacement;
use App\Models\Article;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_ad_placement_renders_in_article(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => true,
        ]);

        AdPlacement::factory()->create([
            'key' => 'article-body-middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => true,
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'ad-ready-article',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('data-ad-key="article-body-middle"', false)
            ->assertSee('adsbygoogle', false);
    }

    public function test_homepage_renders_three_page_level_ad_positions_when_configured(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => true,
            'adsense_publisher_id' => 'ca-pub-3754179629894278',
        ]);

        foreach ([
            'global-top-leaderboard' => '7711442398',
            'home-after-hero' => '4678084940',
            'global-bottom-leaderboard' => '5085279057',
        ] as $key => $slot) {
            AdPlacement::factory()->create([
                'key' => $key,
                'code' => '<ins class="adsbygoogle" data-ad-slot="'.$slot.'"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
                'is_enabled' => true,
            ]);
        }

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-ad-key="global-top-leaderboard"', false)
            ->assertSee('data-ad-slot="7711442398"', false)
            ->assertSee('data-ad-key="home-after-hero"', false)
            ->assertSee('data-ad-slot="4678084940"', false)
            ->assertSee('data-ad-key="global-bottom-leaderboard"', false)
            ->assertSee('data-ad-slot="5085279057"', false);
    }

    public function test_tools_and_static_pages_render_three_page_level_ad_positions_when_configured(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => true,
            'adsense_publisher_id' => 'ca-pub-3754179629894278',
        ]);

        foreach ([
            'global-top-leaderboard' => '7711442398',
            'content-mid-rectangle' => '4678084940',
            'global-bottom-leaderboard' => '5085279057',
        ] as $key => $slot) {
            AdPlacement::factory()->create([
                'key' => $key,
                'code' => '<ins class="adsbygoogle" data-ad-slot="'.$slot.'"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script>',
                'is_enabled' => true,
            ]);
        }

        foreach ([
            route('tools.index'),
            route('tools.show', 'budget-calculator'),
            route('image-credits'),
            route('pages.about'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-ad-key="global-top-leaderboard"', false)
                ->assertSee('data-ad-key="content-mid-rectangle"', false)
                ->assertSee('data-ad-key="global-bottom-leaderboard"', false);
        }
    }

    public function test_site_level_ads_toggle_blocks_enabled_placement(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => false,
        ]);

        AdPlacement::factory()->create([
            'key' => 'article-body-middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => true,
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'site-ads-disabled',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('data-ad-key="article-body-middle"', false);
    }

    public function test_disabled_ad_placement_renders_no_empty_slot(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => true,
        ]);

        AdPlacement::factory()->create([
            'key' => 'article-body-middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => false,
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'no-ad-slot',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('data-ad-key="article-body-middle"', false);
    }

    public function test_unfilled_ads_keep_reserved_space_visible(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString(".ad-slot[data-ad-empty='true']", $css);
        $this->assertStringNotContainsString(
            ".ad-slot[data-ad-empty='true'] {\n        display: none;",
            $css,
        );
        $this->assertStringContainsString('min-height: 280px;', $css);
    }
}

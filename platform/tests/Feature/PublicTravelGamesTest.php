<?php

namespace Tests\Feature;

use App\Models\AdPlacement;
use App\Models\SiteSetting;
use App\Support\JapanGames;
use App\Support\PublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicTravelGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_index_lists_ten_on_site_games(): void
    {
        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('Free Japan-themed browser games')
            ->assertSee('Daily Japan Word Trail')
            ->assertSee('Fuji Merge 2048')
            ->assertSee('Ramen Order Rush')
            ->assertSee('"@type":"ItemList"', false)
            ->assertSee(PublicUrl::route('games.show', 'daily-japan-word'), false)
            ->assertDontSee('target="_blank"', false);

        $this->assertCount(10, JapanGames::all());
    }

    public function test_game_detail_renders_playable_browser_game_shell(): void
    {
        $this->get(route('games.show', 'sushi-snake'))
            ->assertOk()
            ->assertSee('data-japan-game="sushi-snake"', false)
            ->assertSee('data-game-stage', false)
            ->assertSee('data-game-controls', false)
            ->assertSee('Runs locally in your browser', false)
            ->assertSee('Sushi Snake Arcade')
            ->assertSee('"@type":"VideoGame"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('Score')
            ->assertSee('Best');
    }

    public function test_sitemap_includes_games(): void
    {
        $response = $this->get(route('sitemap'))
            ->assertOk();

        $response->assertSee(PublicUrl::route('games.index'), false);

        foreach (JapanGames::slugs() as $slug) {
            $response->assertSee(PublicUrl::route('games.show', $slug), false);
        }
    }

    public function test_games_render_three_page_level_ad_positions_when_configured(): void
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
            route('games.index'),
            route('games.show', 'fuji-merge-2048'),
            route('games.show', 'ramen-order-rush'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-ad-key="global-top-leaderboard"', false)
                ->assertSee('data-ad-key="content-mid-rectangle"', false)
                ->assertSee('data-ad-key="global-bottom-leaderboard"', false);
        }
    }
}

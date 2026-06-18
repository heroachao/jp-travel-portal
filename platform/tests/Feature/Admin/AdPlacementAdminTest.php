<?php

namespace Tests\Feature\Admin;

use App\Models\AdPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdPlacementAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_ad_placement_can_be_stored(): void
    {
        AdPlacement::create([
            'key' => 'article-body-middle',
            'name' => '文章正文中段广告',
            'page_type' => 'article',
            'position' => 'body_middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('ad_placements', [
            'key' => 'article-body-middle',
            'is_enabled' => true,
        ]);
    }
}

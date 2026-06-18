<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Ads\AdPlacementIndex;
use App\Models\AdPlacement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdPlacementAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_ad_admin_with_hardening_guidance(): void
    {
        AdPlacement::factory()->create([
            'key' => 'article-body-top',
            'name' => '文章顶部广告',
            'page_type' => 'article',
            'position' => 'body_top',
            'notes' => '上线前等待法务确认',
            'is_enabled' => false,
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('admin.ads.index'))
            ->assertOk()
            ->assertSee('广告代码只允许管理员维护')
            ->assertSee('上线确认后再启用')
            ->assertSee('article')
            ->assertSee('body_top')
            ->assertSee('上线前等待法务确认')
            ->assertSee('停用');
    }

    public function test_factory_creates_disabled_ad_placements_by_default(): void
    {
        $placement = AdPlacement::factory()->create();

        $this->assertFalse($placement->is_enabled);
    }

    public function test_admin_can_create_disabled_ad_placement_with_notes_and_resets_form_disabled(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(AdPlacementIndex::class)
            ->set('key', 'article-body-middle')
            ->set('name', '文章正文中段广告')
            ->set('page_type', 'article')
            ->set('position', 'body_middle')
            ->set('code', '<ins class="adsbygoogle"></ins>')
            ->set('notes', '内部备注：300x250，正文第二屏')
            ->set('is_enabled', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('is_enabled', false)
            ->assertSet('notes', null)
            ->assertSee('广告位已保存');

        $this->assertDatabaseHas('ad_placements', [
            'key' => 'article-body-middle',
            'notes' => '内部备注：300x250，正文第二屏',
            'is_enabled' => false,
        ]);
    }

    public function test_admin_can_explicitly_enable_ad_placement(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(AdPlacementIndex::class)
            ->set('key', 'article-body-bottom')
            ->set('name', '文章底部广告')
            ->set('page_type', 'article')
            ->set('position', 'body_bottom')
            ->set('code', '<ins class="adsbygoogle"></ins>')
            ->set('is_enabled', true)
            ->call('save')
            ->assertHasNoErrors();

        $placement = AdPlacement::query()->where('key', 'article-body-bottom')->firstOrFail();

        $this->assertTrue($placement->is_enabled);
    }

    public function test_edit_loads_notes_and_enabled_state_then_can_update_notes_and_disable(): void
    {
        $placement = AdPlacement::factory()->create([
            'key' => 'article-sidebar',
            'name' => '文章侧栏广告',
            'page_type' => 'article',
            'position' => 'sidebar',
            'notes' => '旧备注',
            'is_enabled' => true,
        ]);
        $this->actingAs($this->superAdmin());

        Livewire::test(AdPlacementIndex::class)
            ->call('edit', $placement->id)
            ->assertSet('notes', '旧备注')
            ->assertSet('is_enabled', true)
            ->set('notes', '更新后的内部备注')
            ->set('is_enabled', false)
            ->call('save')
            ->assertHasNoErrors();

        $placement->refresh();

        $this->assertSame('更新后的内部备注', $placement->notes);
        $this->assertFalse($placement->is_enabled);
    }

    public function test_admin_sees_validation_errors_for_invalid_key_and_overlong_notes(): void
    {
        $this->actingAs($this->superAdmin());

        Livewire::test(AdPlacementIndex::class)
            ->set('key', 'invalid key')
            ->set('name', '无效广告位')
            ->set('page_type', 'article')
            ->set('position', 'body_middle')
            ->set('notes', str_repeat('a', 1001))
            ->call('save')
            ->assertHasErrors([
                'key' => 'alpha_dash',
                'notes' => 'max',
            ]);
    }

    public function test_non_admin_cannot_directly_save_ad_placement_via_livewire_action(): void
    {
        $this->seed(RoleSeeder::class);
        $this->actingAs(User::factory()->create());

        Livewire::test(AdPlacementIndex::class)
            ->set('key', 'unauthorized-placement')
            ->set('name', '未授权广告位')
            ->set('page_type', 'article')
            ->set('position', 'body_middle')
            ->set('notes', '不应写入')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('ad_placements', [
            'key' => 'unauthorized-placement',
        ]);
    }

    public function test_non_admin_cannot_directly_delete_ad_placement_via_livewire_action(): void
    {
        $placement = AdPlacement::factory()->create();
        $this->seed(RoleSeeder::class);
        $this->actingAs(User::factory()->create());

        Livewire::test(AdPlacementIndex::class)
            ->call('delete', $placement->id)
            ->assertForbidden();

        $this->assertDatabaseHas('ad_placements', [
            'id' => $placement->id,
        ]);
    }

    private function superAdmin(): User
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }
}

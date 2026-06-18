<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\HomepageModules\HomepageModuleIndex;
use App\Livewire\Admin\ServiceLinks\ServiceLinkIndex;
use App\Livewire\Admin\TravelCategories\TravelCategoryIndex;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MediaPortalAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_travel_category_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->set('title', '东京赏樱')
            ->set('display_name', '东京樱花')
            ->set('slug', 'tokyo-sakura')
            ->set('excerpt', '东京春季赏樱路线。')
            ->set('body', '适合第一次来日本的赏樱专题。')
            ->set('seo_title', '东京赏樱旅行指南')
            ->set('meta_description', '东京赏樱景点、交通和行程建议。')
            ->set('is_indexable', true)
            ->set('is_visible', true)
            ->set('sort_order', 10)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('travel_categories', [
            'title' => '东京赏樱',
            'display_name' => '东京樱花',
            'slug' => 'tokyo-sakura',
            'is_indexable' => true,
            'is_visible' => true,
            'sort_order' => 10,
        ]);
    }

    public function test_editor_cannot_set_travel_category_parent_to_itself(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $category = TravelCategory::factory()->create([
            'title' => '关西攻略',
            'parent_id' => null,
        ]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('edit', $category->id)
            ->set('title', '循环分类')
            ->set('parent_id', $category->id)
            ->call('save')
            ->assertHasErrors(['parent_id']);

        $this->assertDatabaseHas('travel_categories', [
            'id' => $category->id,
            'title' => '关西攻略',
            'parent_id' => null,
        ]);
    }

    public function test_editor_cannot_set_travel_category_parent_to_descendant(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $parent = TravelCategory::factory()->create([
            'title' => '日本全境',
            'parent_id' => null,
        ]);
        $child = TravelCategory::factory()->create([
            'title' => '东京',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('edit', $parent->id)
            ->set('title', '循环父级')
            ->set('parent_id', $child->id)
            ->call('save')
            ->assertHasErrors(['parent_id'])
            ->assertViewHas('parentOptions', fn ($parentOptions) => $parentOptions
                ->whereIn('id', [$parent->id, $child->id])
                ->isEmpty());

        $this->assertDatabaseHas('travel_categories', [
            'id' => $parent->id,
            'title' => '日本全境',
            'parent_id' => null,
        ]);
    }

    public function test_editor_cannot_delete_travel_category_with_child(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $parent = TravelCategory::factory()->create();
        $child = TravelCategory::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('delete', $parent->id)
            ->assertHasErrors(['delete']);

        $this->assertNotSoftDeleted('travel_categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('travel_categories', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_editor_can_create_service_link_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ServiceLinkIndex::class)
            ->set('type', 'rail')
            ->set('label', 'JR Pass 预约')
            ->set('url', 'https://example.com/jr-pass')
            ->set('placement', 'header')
            ->set('tracking_key', 'jr_pass_header')
            ->set('notes', '首页导航服务入口。')
            ->set('is_enabled', true)
            ->set('sort_order', 20)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('service_links', [
            'type' => 'rail',
            'label' => 'JR Pass 预约',
            'url' => 'https://example.com/jr-pass',
            'placement' => 'header',
            'tracking_key' => 'jr_pass_header',
            'is_enabled' => true,
            'sort_order' => 20,
        ]);
    }

    public function test_editor_cannot_create_service_link_with_non_http_url_scheme(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ServiceLinkIndex::class)
            ->set('type', 'rail')
            ->set('label', 'JR Pass 预约')
            ->set('url', 'ftp://example.com/rail')
            ->set('placement', 'header')
            ->set('tracking_key', 'jr_pass_header')
            ->set('is_enabled', true)
            ->set('sort_order', 20)
            ->call('save')
            ->assertHasErrors(['url']);

        $this->assertDatabaseMissing('service_links', [
            'label' => 'JR Pass 预约',
            'url' => 'ftp://example.com/rail',
        ]);
    }

    public function test_editor_can_create_homepage_module_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(HomepageModuleIndex::class)
            ->set('placement_key', 'home_featured_sakura')
            ->set('type', 'featured_articles')
            ->set('title', '本周推荐')
            ->set('subtitle', '精选日本旅行攻略。')
            ->set('is_enabled', true)
            ->set('sort_order', 30)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('homepage_modules', [
            'placement_key' => 'home_featured_sakura',
            'type' => 'featured_articles',
            'title' => '本周推荐',
            'subtitle' => '精选日本旅行攻略。',
            'is_enabled' => true,
            'sort_order' => 30,
        ]);
    }

    public function test_editor_cannot_delete_homepage_module_with_items(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $module = HomepageModule::factory()->create();
        $item = HomepageModuleItem::factory()->create([
            'homepage_module_id' => $module->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(HomepageModuleIndex::class)
            ->call('delete', $module->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('homepage_modules', ['id' => $module->id]);
        $this->assertDatabaseHas('homepage_module_items', [
            'id' => $item->id,
            'homepage_module_id' => $module->id,
        ]);
    }

    public function test_admin_navigation_links_to_media_portal_screens(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::whereEmail('admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('分类频道')
            ->assertSee('服务入口')
            ->assertSee('首页模块');
    }

    public function test_delete_buttons_use_chinese_confirmation_prompts(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::whereEmail('admin@example.com')->firstOrFail();
        TravelCategory::factory()->create();
        ServiceLink::factory()->create();
        HomepageModule::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.travel-categories.index'))
            ->assertOk()
            ->assertSee('wire:confirm="确认删除这个分类频道吗？"', false);

        $this->actingAs($admin)
            ->get(route('admin.service-links.index'))
            ->assertOk()
            ->assertSee('wire:confirm="确认删除这个服务入口吗？"', false);

        $this->actingAs($admin)
            ->get(route('admin.homepage-modules.index'))
            ->assertOk()
            ->assertSee('wire:confirm="确认删除这个首页模块吗？"', false);
    }
}

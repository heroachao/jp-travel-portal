<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Destinations\DestinationIndex;
use App\Livewire\Admin\HomepageModules\HomepageModuleIndex;
use App\Livewire\Admin\ServiceLinks\ServiceLinkIndex;
use App\Livewire\Admin\TravelCategories\TravelCategoryIndex;
use App\Models\Article;
use App\Models\Destination;
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

    public function test_editor_sanitizes_travel_category_body_before_public_rendering(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->set('title', 'Transport')
            ->set('slug', 'transport-xss')
            ->set('body', '<script>alert(1)</script><p>Safe</p>')
            ->set('is_visible', true)
            ->set('is_indexable', true)
            ->call('save')
            ->assertHasNoErrors();

        $body = TravelCategory::where('slug', 'transport-xss')->firstOrFail()->body;

        $this->assertStringNotContainsString('<script>', $body);
        $this->assertStringContainsString('<p>Safe</p>', $body);

        $this->get('/categories/transport-xss')
            ->assertOk()
            ->assertDontSee('<script>', false)
            ->assertSee('Safe');
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

    public function test_editor_cannot_set_travel_category_parent_to_grandchild(): void
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
        $grandchild = TravelCategory::factory()->create([
            'title' => '上野',
            'parent_id' => $child->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('edit', $parent->id)
            ->assertViewHas('parentOptions', fn ($parentOptions) => $parentOptions
                ->whereIn('id', [$parent->id, $child->id, $grandchild->id])
                ->isEmpty())
            ->set('parent_id', $grandchild->id)
            ->call('save')
            ->assertHasErrors(['parent_id']);

        $this->assertDatabaseHas('travel_categories', [
            'id' => $parent->id,
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

    public function test_editor_cannot_delete_travel_category_with_article(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $category = TravelCategory::factory()->create();
        $article = Article::factory()->create([
            'author_id' => $editor->id,
        ]);
        $category->articles()->attach($article->id, ['sort_order' => 1]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('delete', $category->id)
            ->assertHasErrors(['delete']);

        $this->assertNotSoftDeleted('travel_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('article_travel_category', [
            'article_id' => $article->id,
            'travel_category_id' => $category->id,
        ]);
    }

    public function test_editor_cannot_delete_travel_category_referenced_by_homepage_module_item(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $category = TravelCategory::factory()->create();
        $item = HomepageModuleItem::factory()->create([
            'item_type' => TravelCategory::class,
            'item_id' => $category->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('delete', $category->id)
            ->assertHasErrors(['delete']);

        $this->assertNotSoftDeleted('travel_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('homepage_module_items', [
            'id' => $item->id,
            'item_type' => TravelCategory::class,
            'item_id' => $category->id,
        ]);
    }

    public function test_editor_can_delete_empty_travel_category_and_clear_previous_delete_error(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $blockedParent = TravelCategory::factory()->create();
        TravelCategory::factory()->create(['parent_id' => $blockedParent->id]);
        $emptyCategory = TravelCategory::factory()->create();

        $this->actingAs($editor);

        Livewire::test(TravelCategoryIndex::class)
            ->call('delete', $blockedParent->id)
            ->assertHasErrors(['delete'])
            ->call('delete', $emptyCategory->id)
            ->assertHasNoErrors(['delete']);

        $this->assertNotSoftDeleted('travel_categories', ['id' => $blockedParent->id]);
        $this->assertSoftDeleted('travel_categories', ['id' => $emptyCategory->id]);
    }

    public function test_editor_can_manage_destination_channel_fields_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->set('type', 'region')
            ->set('name', 'Tokyo')
            ->set('display_name', 'Tokyo Region')
            ->set('slug', 'tokyo')
            ->set('excerpt', 'Tokyo region travel planning hub.')
            ->set('body', '<p>Tokyo works best when planned by neighborhood and rail line.</p>')
            ->set('seo_title', 'Tokyo Travel Guide')
            ->set('meta_description', 'Plan Tokyo travel by neighborhood, transport, food, and season.')
            ->set('is_indexable', true)
            ->set('is_channel', true)
            ->set('sort_order', 3)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('destinations', [
            'type' => 'region',
            'slug' => 'tokyo',
            'display_name' => 'Tokyo Region',
            'body' => '<p>Tokyo works best when planned by neighborhood and rail line.</p>',
            'seo_title' => 'Tokyo Travel Guide',
            'meta_description' => 'Plan Tokyo travel by neighborhood, transport, food, and season.',
            'is_indexable' => true,
            'is_channel' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_editor_cannot_set_destination_parent_to_itself(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $destination = Destination::factory()->create([
            'name' => 'Tokyo',
            'parent_id' => null,
        ]);

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->call('edit', $destination->id)
            ->set('name', 'Loop destination')
            ->set('parent_id', $destination->id)
            ->call('save')
            ->assertHasErrors(['parent_id']);

        $this->assertDatabaseHas('destinations', [
            'id' => $destination->id,
            'name' => 'Tokyo',
            'parent_id' => null,
        ]);
    }

    public function test_editor_cannot_set_destination_parent_to_missing_destination(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->set('type', 'region')
            ->set('name', 'Tokyo')
            ->set('slug', 'tokyo')
            ->set('parent_id', 999999)
            ->call('save')
            ->assertHasErrors(['parent_id']);

        $this->assertDatabaseMissing('destinations', [
            'slug' => 'tokyo',
            'parent_id' => 999999,
        ]);
    }

    public function test_editor_can_set_destination_parent_to_valid_parent(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $parent = Destination::factory()->create([
            'name' => 'Japan',
            'parent_id' => null,
        ]);

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->set('parent_id', $parent->id)
            ->set('type', 'city')
            ->set('name', 'Tokyo')
            ->set('slug', 'tokyo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('destinations', [
            'slug' => 'tokyo',
            'parent_id' => $parent->id,
        ]);
    }

    public function test_editor_cannot_set_destination_parent_to_descendant(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $parent = Destination::factory()->create([
            'name' => 'Japan',
            'parent_id' => null,
        ]);
        $child = Destination::factory()->create([
            'name' => 'Tokyo',
            'parent_id' => $parent->id,
        ]);
        $grandchild = Destination::factory()->create([
            'name' => 'Ueno',
            'parent_id' => $child->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->call('edit', $parent->id)
            ->assertViewHas('parentOptions', fn ($parentOptions) => $parentOptions
                ->whereIn('id', [$parent->id, $child->id, $grandchild->id])
                ->isEmpty())
            ->set('parent_id', $grandchild->id)
            ->call('save')
            ->assertHasErrors(['parent_id']);

        $this->assertDatabaseHas('destinations', [
            'id' => $parent->id,
            'parent_id' => null,
        ]);
    }

    public function test_editor_sanitizes_destination_body_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->set('type', 'region')
            ->set('name', 'Tokyo')
            ->set('slug', 'tokyo-xss')
            ->set('body', '<script>alert(1)</script><p>Safe</p>')
            ->call('save')
            ->assertHasNoErrors();

        $body = Destination::where('slug', 'tokyo-xss')->firstOrFail()->body;

        $this->assertStringNotContainsString('<script>', $body);
        $this->assertStringContainsString('<p>Safe</p>', $body);
    }

    public function test_editor_cannot_delete_destination_with_child(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $parent = Destination::factory()->create();
        $child = Destination::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->call('delete', $parent->id)
            ->assertHasErrors(['delete']);

        $this->assertNotSoftDeleted('destinations', ['id' => $parent->id]);
        $this->assertDatabaseHas('destinations', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_editor_cannot_delete_destination_referenced_by_homepage_module_item(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $destination = Destination::factory()->create();
        $item = HomepageModuleItem::factory()->create([
            'item_type' => Destination::class,
            'item_id' => $destination->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->call('delete', $destination->id)
            ->assertHasErrors(['delete']);

        $this->assertNotSoftDeleted('destinations', ['id' => $destination->id]);
        $this->assertDatabaseHas('homepage_module_items', [
            'id' => $item->id,
            'item_type' => Destination::class,
            'item_id' => $destination->id,
        ]);
    }

    public function test_editor_can_delete_empty_destination_and_clear_previous_delete_error(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $blockedParent = Destination::factory()->create();
        Destination::factory()->create(['parent_id' => $blockedParent->id]);
        $emptyDestination = Destination::factory()->create();

        $this->actingAs($editor);

        Livewire::test(DestinationIndex::class)
            ->call('delete', $blockedParent->id)
            ->assertHasErrors(['delete'])
            ->call('delete', $emptyDestination->id)
            ->assertHasNoErrors(['delete']);

        $this->assertNotSoftDeleted('destinations', ['id' => $blockedParent->id]);
        $this->assertSoftDeleted('destinations', ['id' => $emptyDestination->id]);
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

    public function test_editor_cannot_delete_service_link_referenced_by_homepage_module_item(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $serviceLink = ServiceLink::factory()->create();
        $item = HomepageModuleItem::factory()->create([
            'item_type' => ServiceLink::class,
            'item_id' => $serviceLink->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(ServiceLinkIndex::class)
            ->call('delete', $serviceLink->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('service_links', ['id' => $serviceLink->id]);
        $this->assertDatabaseHas('homepage_module_items', [
            'id' => $item->id,
            'item_type' => ServiceLink::class,
            'item_id' => $serviceLink->id,
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

    public function test_editor_can_delete_empty_homepage_module_and_clear_previous_delete_error(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $blockedModule = HomepageModule::factory()->create();
        HomepageModuleItem::factory()->create([
            'homepage_module_id' => $blockedModule->id,
        ]);
        $emptyModule = HomepageModule::factory()->create();

        $this->actingAs($editor);

        Livewire::test(HomepageModuleIndex::class)
            ->call('delete', $blockedModule->id)
            ->assertHasErrors(['delete'])
            ->call('delete', $emptyModule->id)
            ->assertHasNoErrors(['delete']);

        $this->assertDatabaseHas('homepage_modules', ['id' => $blockedModule->id]);
        $this->assertDatabaseMissing('homepage_modules', ['id' => $emptyModule->id]);
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

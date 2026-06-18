<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Settings\SiteSettingsForm;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Settings\SiteSettings;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_settings_service_returns_safe_defaults_without_row(): void
    {
        $settings = app(SiteSettings::class)->current();

        $this->assertSame('Japan Travel Guide', $settings->site_name);
        $this->assertSame('Japan Travel Guide', $settings->seo_title_suffix);
        $this->assertFalse($settings->analytics_enabled);
        $this->assertFalse($settings->ads_enabled);
        $this->assertNull($settings->ga4_measurement_id);
        $this->assertNull($settings->adsense_publisher_id);
        $this->assertNull($settings->tagline);
        $this->assertFalse($settings->organization_schema_enabled);
        $this->assertNull($settings->contact_email);
        $this->assertNull($settings->social_links);
        $this->assertNull($settings->robots_extra_rules);
    }

    public function test_admin_can_save_public_analytics_and_ads_settings(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettingsForm::class)
            ->set('site_name', 'Japan Rail Travel')
            ->set('seo_title_suffix', 'Japan Rail Travel')
            ->set('tagline', 'Independent planning for rail-first Japan trips.')
            ->set('default_meta_description', 'Independent Japan travel planning guides.')
            ->set('ga4_measurement_id', 'G-ABC123DEF4')
            ->set('adsense_publisher_id', 'ca-pub-1234567890123456')
            ->set('contact_email', 'hello@example.com')
            ->set('social_links', '{"x":"https://x.com/japanrail","youtube":"https://youtube.com/@japanrail"}')
            ->set('robots_extra_rules', "Disallow: /private\nCrawl-delay: 5")
            ->set('analytics_enabled', true)
            ->set('ads_enabled', true)
            ->set('organization_schema_enabled', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('站点设置已保存');

        $this->assertDatabaseHas('site_settings', [
            'id' => 1,
            'site_name' => 'Japan Rail Travel',
            'tagline' => 'Independent planning for rail-first Japan trips.',
            'ga4_measurement_id' => 'G-ABC123DEF4',
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
            'contact_email' => 'hello@example.com',
            'robots_extra_rules' => "Disallow: /private\nCrawl-delay: 5",
            'analytics_enabled' => true,
            'ads_enabled' => true,
            'organization_schema_enabled' => true,
        ]);

        $settings = SiteSetting::query()->findOrFail(1);

        $this->assertSame([
            'x' => 'https://x.com/japanrail',
            'youtube' => 'https://youtube.com/@japanrail',
        ], $settings->social_links);
    }

    public function test_non_admin_cannot_directly_save_site_settings_via_livewire_action(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(SiteSettingsForm::class)
            ->set('site_name', 'Unauthorized Site')
            ->set('seo_title_suffix', 'Unauthorized Site')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('site_settings', [
            'site_name' => 'Unauthorized Site',
        ]);
    }

    public function test_site_settings_reject_invalid_public_configuration_values(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettingsForm::class)
            ->set('site_name', 'Japan Travel Guide')
            ->set('seo_title_suffix', 'Japan Travel Guide')
            ->set('ga4_measurement_id', 'UA-OLD-ID')
            ->set('adsense_publisher_id', 'pub-123')
            ->set('contact_email', 'not-an-email')
            ->set('social_links', '{"x":')
            ->call('save')
            ->assertHasErrors([
                'ga4_measurement_id',
                'adsense_publisher_id',
                'contact_email',
                'social_links',
            ]);
    }

    public function test_site_settings_rejects_overlong_tracking_ids(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettingsForm::class)
            ->set('site_name', 'Japan Travel Guide')
            ->set('seo_title_suffix', 'Japan Travel Guide')
            ->set('ga4_measurement_id', 'G-'.str_repeat('A', 254))
            ->set('adsense_publisher_id', 'ca-pub-'.str_repeat('1', 249))
            ->call('save')
            ->assertHasErrors([
                'ga4_measurement_id',
                'adsense_publisher_id',
            ]);
    }

    public function test_site_settings_rejects_scalar_json_social_links(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettingsForm::class)
            ->set('site_name', 'Japan Travel Guide')
            ->set('seo_title_suffix', 'Japan Travel Guide')
            ->set('social_links', '"https://x.com/example"')
            ->call('save')
            ->assertHasErrors(['social_links']);
    }

    public function test_site_settings_clears_optional_strings_to_null(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Travel Guide',
            'seo_title_suffix' => 'Japan Travel Guide',
            'tagline' => 'Rail-first Japan planning.',
            'default_meta_description' => 'Independent Japan travel planning guides.',
            'ga4_measurement_id' => 'G-ABC123DEF4',
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
            'contact_email' => 'hello@example.com',
            'social_links' => ['x' => 'https://x.com/japanrail'],
            'robots_extra_rules' => 'Disallow: /private',
        ]);

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettingsForm::class)
            ->set('tagline', '')
            ->set('default_meta_description', '')
            ->set('ga4_measurement_id', '')
            ->set('adsense_publisher_id', '')
            ->set('contact_email', '')
            ->set('social_links', '')
            ->set('robots_extra_rules', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'id' => 1,
            'tagline' => null,
            'default_meta_description' => null,
            'ga4_measurement_id' => null,
            'adsense_publisher_id' => null,
            'contact_email' => null,
            'social_links' => null,
            'robots_extra_rules' => null,
        ]);
    }

    public function test_admin_can_open_site_settings_page(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('站点设置')
            ->assertSee('Google Analytics 4 衡量 ID')
            ->assertSee('组织结构化数据')
            ->assertSee('社交链接 JSON');
    }

    public function test_admin_dashboard_sidebar_links_to_site_settings_without_media_route(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('admin.settings.index').'"', false);

        $this->assertFalse(Route::has('admin.media.index'));

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertNotFound();
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Settings\SiteSettingsForm;
use App\Models\User;
use App\Services\Settings\SiteSettings;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->set('default_meta_description', 'Independent Japan travel planning guides.')
            ->set('ga4_measurement_id', 'G-ABC123DEF4')
            ->set('adsense_publisher_id', 'ca-pub-1234567890123456')
            ->set('analytics_enabled', true)
            ->set('ads_enabled', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'id' => 1,
            'site_name' => 'Japan Rail Travel',
            'ga4_measurement_id' => 'G-ABC123DEF4',
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
            'analytics_enabled' => true,
            'ads_enabled' => true,
        ]);
    }

    public function test_site_settings_reject_invalid_google_public_ids(): void
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
            ->call('save')
            ->assertHasErrors(['ga4_measurement_id', 'adsense_publisher_id']);
    }

    public function test_admin_can_open_site_settings_page(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('站点设置');
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Services\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

<?php

namespace Tests\Feature\Public;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_omits_google_scripts_without_site_settings_row(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false)
            ->assertDontSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', false);
    }

    public function test_homepage_outputs_ga4_bootstrap_when_analytics_enabled_with_measurement_id(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'analytics_enabled' => true,
            'ga4_measurement_id' => 'G-ABC123DEF4',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123DEF4', false)
            ->assertSee("gtag('config', 'G-ABC123DEF4')", false)
            ->assertDontSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', false);
    }

    public function test_homepage_outputs_adsense_bootstrap_when_ads_enabled_with_publisher_id(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'ads_enabled' => true,
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456', false)
            ->assertSee('crossorigin="anonymous"', false)
            ->assertDontSee('googletagmanager.com/gtag/js', false);
    }

    public function test_homepage_omits_google_scripts_when_ids_or_switches_are_incomplete(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'analytics_enabled' => false,
            'ads_enabled' => true,
            'ga4_measurement_id' => 'G-ABC123DEF4',
            'adsense_publisher_id' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false)
            ->assertDontSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', false);
    }
}

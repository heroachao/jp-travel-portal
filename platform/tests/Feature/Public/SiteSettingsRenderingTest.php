<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\SiteSetting;
use App\Models\User;
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

    public function test_ads_txt_outputs_adsense_authorization_line(): void
    {
        $this->get(route('ads-txt'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('google.com, pub-3754179629894278, DIRECT, f08c47fec0942fa0', false);
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

    public function test_public_layout_applies_configured_seo_suffix_and_default_description(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Rail Planner',
            'seo_title_suffix' => 'Japan Rail Planner',
            'default_meta_description' => 'Configured default Japan travel description.',
        ]);

        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Nara Quiet Route',
            'slug' => 'nara-quiet-route',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'seo_title' => null,
            'meta_description' => null,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<title>Nara Quiet Route | Japan Rail Planner</title>', false)
            ->assertSee('name="description" content="Configured default Japan travel description."', false)
            ->assertSee('property="og:description" content="Configured default Japan travel description."', false);
    }

    public function test_public_layout_outputs_organization_schema_when_enabled(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Rail Planner',
            'organization_schema_enabled' => true,
            'contact_email' => 'hello@example.com',
            'social_links' => ['x' => 'https://x.com/japanrail'],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('type="application/ld+json"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"name":"Japan Rail Planner"', false)
            ->assertSee('"email":"hello@example.com"', false)
            ->assertSee('https://x.com/japanrail', false);
    }

    public function test_robots_txt_appends_site_settings_rules(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'robots_extra_rules' => "Disallow: /private\nCrawl-delay: 5",
        ]);

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee("User-agent: *\nDisallow:", false)
            ->assertSee('Sitemap: '.route('sitemap'), false)
            ->assertSee("Disallow: /private\nCrawl-delay: 5", false);
    }
}

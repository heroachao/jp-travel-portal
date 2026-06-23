<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\User;
use App\Support\PublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_pages_are_public_and_indexable(): void
    {
        foreach ([
            '/about' => 'About Japan Trip Tools',
            '/contact' => 'Contact Japan Trip Tools',
            '/privacy-policy' => 'Privacy Policy',
            '/privacy' => 'Privacy Policy',
            '/terms' => 'Terms of Use',
            '/disclaimer' => 'Travel Information Disclaimer',
        ] as $path => $heading) {
            $this->get($path)
                ->assertOk()
                ->assertSee($heading)
                ->assertDontSee('noindex,nofollow');
        }
    }

    public function test_footer_links_to_compliance_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.PublicUrl::route('pages.about').'"', false)
            ->assertSee('href="'.PublicUrl::route('pages.contact').'"', false)
            ->assertSee('href="'.PublicUrl::route('pages.privacy').'"', false)
            ->assertSee('href="'.PublicUrl::route('pages.terms').'"', false)
            ->assertSee('href="'.PublicUrl::route('pages.disclaimer').'"', false);
    }

    public function test_header_keeps_service_links_on_site(): void
    {
        ServiceLink::factory()->create([
            'label' => 'Rail Tickets',
            'url' => 'https://japanrailpass.net/en/',
            'placement' => 'header',
            'is_enabled' => true,
            'tracking_key' => 'rail-tickets',
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="https://japanrailpass.net/en/"', false)
            ->assertDontSee('data-service-key="rail-tickets"', false)
            ->assertSee('href="'.PublicUrl::route('tools.show', 'jr-pass-calculator').'"', false);
    }

    public function test_footer_keeps_service_links_on_site(): void
    {
        ServiceLink::factory()->create([
            'label' => 'Exchange Rate',
            'url' => 'https://www.xe.com/currencyconverter/convert/?Amount=1&From=USD&To=JPY',
            'placement' => 'footer',
            'is_enabled' => true,
            'tracking_key' => 'exchange-rate',
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="https://www.xe.com/currencyconverter', false)
            ->assertSee('href="'.PublicUrl::route('tools.show', 'budget-calculator').'"', false);
    }

    public function test_homepage_service_module_items_stay_on_site(): void
    {
        $module = HomepageModule::factory()->create([
            'placement_key' => 'home-service-links',
            'type' => 'service_highlights',
            'title' => 'Trip Tools',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $serviceLink = ServiceLink::factory()->create([
            'label' => 'Rail Tickets',
            'url' => 'https://japanrailpass.net/en/',
            'placement' => 'header',
            'is_enabled' => true,
            'tracking_key' => 'rail-tickets',
            'sort_order' => 1,
        ]);
        HomepageModuleItem::factory()->create([
            'homepage_module_id' => $module->id,
            'item_type' => ServiceLink::class,
            'item_id' => $serviceLink->id,
            'label' => 'Rail Tickets',
            'summary' => 'Plan rail ticket choices on site.',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('href="https://japanrailpass.net/en/"', false)
            ->assertSee('href="'.PublicUrl::route('tools.show', 'jr-pass-calculator').'"', false);
    }

    public function test_published_article_is_public(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'A Quiet Guide to Nara',
            'slug' => 'quiet-guide-to-nara',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'seo_title' => 'A Quiet Guide to Nara | Japan Travel Guide',
            'meta_description' => 'Plan a quiet visit to Nara with temples, parks, and local travel notes.',
        ]);

        $this->get("/articles/{$article->slug}")
            ->assertOk()
            ->assertSee('A Quiet Guide to Nara')
            ->assertSee('Plan a quiet visit to Nara', false)
            ->assertSee('rel="canonical"', false);
    }

    public function test_draft_article_is_not_public(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'draft-osaka',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get("/articles/{$article->slug}")->assertNotFound();
    }
}

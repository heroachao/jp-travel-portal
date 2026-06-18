<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\TravelCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_keyword_region_category_tag_coupon_and_sort(): void
    {
        $tokyo = Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'is_channel' => true,
            'is_indexable' => true,
        ]);
        $kyoto = Destination::factory()->create([
            'name' => 'Kyoto',
            'slug' => 'kyoto',
            'is_channel' => true,
            'is_indexable' => true,
        ]);
        $transport = TravelCategory::factory()->create([
            'title' => 'Transport',
            'slug' => 'transport',
            'is_visible' => true,
        ]);
        $food = TravelCategory::factory()->create([
            'title' => 'Food',
            'slug' => 'food',
            'is_visible' => true,
        ]);
        $rail = Tag::factory()->create(['name' => 'rail', 'slug' => 'rail']);

        $matching = $this->publishedArticle([
            'title' => 'Tokyo Rail Discount Guide',
            'slug' => 'tokyo-rail-discount-guide',
            'excerpt' => 'A focused Suica and rail coupon explainer.',
            'published_at' => now()->subDay(),
            'popularity_score' => 200,
            'has_coupon' => true,
        ]);
        $matching->destinations()->attach($tokyo);
        $matching->travelCategories()->attach($transport, ['sort_order' => 1]);
        $matching->tags()->attach($rail);

        $nonMatching = $this->publishedArticle([
            'title' => 'Kyoto Food Walk',
            'slug' => 'kyoto-food-walk',
            'excerpt' => 'A market walk with no rail discount.',
            'published_at' => now(),
            'popularity_score' => 1,
            'has_coupon' => false,
        ]);
        $nonMatching->destinations()->attach($kyoto);
        $nonMatching->travelCategories()->attach($food, ['sort_order' => 1]);

        $this->get('/search?q=rail&region=tokyo&category=transport&tag=rail&coupon=1&sort=popular')
            ->assertOk()
            ->assertSee('Tokyo Rail Discount Guide')
            ->assertDontSee('Kyoto Food Walk');
    }

    public function test_search_filter_controls_hide_non_public_region_and_category_values(): void
    {
        Destination::factory()->create([
            'name' => 'Public Kansai',
            'slug' => 'public-kansai',
            'is_channel' => true,
            'is_indexable' => true,
        ]);
        Destination::factory()->create([
            'name' => 'Private Hokkaido',
            'slug' => 'private-hokkaido',
            'is_channel' => true,
            'is_indexable' => false,
        ]);
        Destination::factory()->create([
            'name' => 'Ueno Local Spot',
            'slug' => 'ueno-local-spot',
            'is_channel' => false,
            'is_indexable' => true,
        ]);

        TravelCategory::factory()->create([
            'title' => 'Visible Transport',
            'display_name' => 'Visible Transport',
            'slug' => 'visible-transport',
            'is_visible' => true,
        ]);
        TravelCategory::factory()->create([
            'title' => 'Hidden Category',
            'display_name' => 'Hidden Category',
            'slug' => 'hidden-category',
            'is_visible' => false,
        ]);
        TravelCategory::factory()->create([
            'title' => 'Visible Noindex Category',
            'display_name' => 'Visible Noindex Category',
            'slug' => 'visible-noindex-category',
            'is_visible' => true,
            'is_indexable' => false,
        ]);

        $this->get('/search')
            ->assertOk()
            ->assertSee('Public Kansai')
            ->assertSee('Visible Transport')
            ->assertDontSee('Private Hokkaido')
            ->assertDontSee('Ueno Local Spot')
            ->assertDontSee('Hidden Category')
            ->assertDontSee('Visible Noindex Category');
    }

    public function test_noindex_category_slug_is_reset_before_filtering_search_results(): void
    {
        $noindexCategory = TravelCategory::factory()->create([
            'title' => 'Private Search Category',
            'display_name' => 'Private Search Category',
            'slug' => 'private-search-category',
            'is_visible' => true,
            'is_indexable' => false,
        ]);
        $publicCategory = TravelCategory::factory()->create([
            'title' => 'Public Search Category',
            'display_name' => 'Public Search Category',
            'slug' => 'public-search-category',
            'is_visible' => true,
            'is_indexable' => true,
        ]);

        $privateArticle = $this->publishedArticle([
            'title' => 'Noindex Category Rail Result',
            'slug' => 'noindex-category-rail-result',
        ]);
        $privateArticle->travelCategories()->attach($noindexCategory, ['sort_order' => 1]);

        $publicArticle = $this->publishedArticle([
            'title' => 'Public Category Rail Result',
            'slug' => 'public-category-rail-result',
        ]);
        $publicArticle->travelCategories()->attach($publicCategory, ['sort_order' => 1]);

        $this->get('/search?q=rail&category=private-search-category')
            ->assertOk()
            ->assertSee('Noindex Category Rail Result')
            ->assertSee('Public Category Rail Result');
    }

    public function test_popular_sort_orders_by_score_then_publication_date(): void
    {
        $olderPopular = $this->publishedArticle([
            'title' => 'Older Popular Guide',
            'slug' => 'older-popular-guide',
            'published_at' => now()->subDays(3),
            'popularity_score' => 80,
        ]);
        $newerPopular = $this->publishedArticle([
            'title' => 'Newer Popular Guide',
            'slug' => 'newer-popular-guide',
            'published_at' => now()->subDay(),
            'popularity_score' => 80,
        ]);
        $lessPopular = $this->publishedArticle([
            'title' => 'Less Popular Guide',
            'slug' => 'less-popular-guide',
            'published_at' => now(),
            'popularity_score' => 10,
        ]);

        $this->get('/search?sort=popular')
            ->assertOk()
            ->assertSeeInOrder([
                $newerPopular->title,
                $olderPopular->title,
                $lessPopular->title,
            ]);
    }

    public function test_recommended_sort_prioritizes_coupon_then_score_deterministically(): void
    {
        $highScoreWithoutCoupon = $this->publishedArticle([
            'title' => 'High Score Without Coupon',
            'slug' => 'high-score-without-coupon',
            'published_at' => now(),
            'popularity_score' => 500,
            'has_coupon' => false,
        ]);
        $lowerScoreCoupon = $this->publishedArticle([
            'title' => 'Lower Score Coupon Guide',
            'slug' => 'lower-score-coupon-guide',
            'published_at' => now()->subDay(),
            'popularity_score' => 100,
            'has_coupon' => true,
        ]);
        $higherScoreCoupon = $this->publishedArticle([
            'title' => 'Higher Score Coupon Guide',
            'slug' => 'higher-score-coupon-guide',
            'published_at' => now()->subDays(2),
            'popularity_score' => 200,
            'has_coupon' => true,
        ]);

        $this->get('/search?sort=recommended')
            ->assertOk()
            ->assertSeeInOrder([
                $higherScoreCoupon->title,
                $lowerScoreCoupon->title,
                $highScoreWithoutCoupon->title,
            ]);
    }

    public function test_updated_sort_falls_back_to_publication_date_when_display_updated_at_is_null(): void
    {
        $freshPublication = $this->publishedArticle([
            'title' => 'Fresh Publication Without Display Date',
            'slug' => 'fresh-publication-without-display-date',
            'published_at' => now(),
            'display_updated_at' => null,
        ]);
        $recentlyUpdated = $this->publishedArticle([
            'title' => 'Recently Updated Older Guide',
            'slug' => 'recently-updated-older-guide',
            'published_at' => now()->subDays(8),
            'display_updated_at' => now()->subDay(),
        ]);
        $stalePublication = $this->publishedArticle([
            'title' => 'Stale Publication Without Display Date',
            'slug' => 'stale-publication-without-display-date',
            'published_at' => now()->subDays(10),
            'display_updated_at' => null,
        ]);

        $this->get('/search?sort=updated')
            ->assertOk()
            ->assertSeeInOrder([
                $freshPublication->title,
                $recentlyUpdated->title,
                $stalePublication->title,
            ]);
    }

    public function test_invalid_sort_falls_back_to_newest(): void
    {
        $older = $this->publishedArticle([
            'title' => 'Older Newest Fallback Guide',
            'slug' => 'older-newest-fallback-guide',
            'published_at' => now()->subDays(2),
        ]);
        $newer = $this->publishedArticle([
            'title' => 'Newer Newest Fallback Guide',
            'slug' => 'newer-newest-fallback-guide',
            'published_at' => now(),
        ]);

        $this->get('/search?sort=surprise')
            ->assertOk()
            ->assertSeeInOrder([
                $newer->title,
                $older->title,
            ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function publishedArticle(array $attributes = []): Article
    {
        return Article::factory()->create(array_merge([
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ], $attributes));
    }
}

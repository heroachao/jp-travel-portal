# Japan Travel Media Portal Phase 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the first LetsGoJP-style media portal foundation: structured service links, region/category navigation, homepage modules, article FAQs, enhanced search, SEO output, and Chinese admin controls for each new public surface.

**Architecture:** Extend the existing Laravel app under `platform/` without replacing the current publishing workflow. Add small Eloquent models for portal navigation data, wire them into Livewire Chinese admin CRUD screens, then update Blade public pages and SEO services to read only published/indexable structured records. Keep Phase 2 commerce, member, coupon, partner, and activity-product models out of this plan so Phase 1 remains independently testable.

**Tech Stack:** Laravel 13, PHP 8.5 local runtime, MySQL/PostgreSQL-compatible migrations, SQLite feature tests, Livewire 4, Blade, Vite/Tailwind, Spatie Laravel Permission, HTMLPurifier, PHPUnit Laravel feature tests.

---

## Source Context

- Confirmed spec: `docs/superpowers/specs/2026-06-18-letsgojp-parity-redesign-design.md`
- Existing app root: `platform/`
- Existing public routes: `/`, `/articles`, `/articles/{article:slug}`, `/destinations`, `/destinations/{destination:slug}`, `/topics/{topic:slug}`, `/tags/{tag:slug}`, `/search`, `/sitemap.xml`, `/robots.txt`
- Existing admin routes: `/admin`, `/admin/articles`, `/admin/destinations`, `/admin/topics`, `/admin/tags`, `/admin/ads`
- Existing models to extend: `Article`, `Destination`
- Existing patterns to follow: single-file Livewire components under `platform/app/Livewire/Admin/*/*Index.php`, Blade views under `platform/resources/views/livewire/admin/**`, public controllers under `platform/app/Http/Controllers/Public`
- Do not edit the untracked root `index.html`.

## Phase 1 Scope

This plan implements only the accepted Phase 1 media portal foundation:

- Travel category model, migration, factory, public category pages, and Chinese admin CRUD.
- Region channel enhancements on existing destinations, including path-based `/regions` routes while retaining old `/destinations` compatibility.
- Homepage module model and Chinese admin CRUD for curated public home sections.
- Service link model and Chinese admin CRUD for the public service bar/footer.
- Article FAQ model and article editor support.
- Public header/footer redesign, homepage redesign, region/category pages, enhanced search filters.
- Demo seed data and tests for public pages, admin CRUD, SEO output, and seed integrity.

The following spec phases are deliberately deferred to later plans: coupons, partners, member favorites, booking/service history, activity products, service click tracking, sitemap splitting, batch SEO reports, and operational dashboards.

## File Structure

### New Models And Factories

- Create `platform/app/Models/TravelCategory.php` — hierarchical editorial navigation category such as Guide, Things to Do, Food, Shopping, Lodging, Itinerary, Transport, Basics.
- Create `platform/app/Models/ServiceLink.php` — service bar/footer links such as Activities, Hotels, Flights, Rail, Shop, Community, Exchange Rate, Advertising.
- Create `platform/app/Models/HomepageModule.php` — ordered home modules with type, placement key, title, enabled state, and curated item relations.
- Create `platform/app/Models/HomepageModuleItem.php` — typed links from a module to Article, Destination, TravelCategory, or ServiceLink.
- Create `platform/app/Models/ArticleFaq.php` — ordered FAQ blocks belonging to articles.
- Create factories for each model under `platform/database/factories/`.

### Database Changes

- Create `platform/database/migrations/2026_06_18_120000_create_travel_categories_table.php`
- Create `platform/database/migrations/2026_06_18_120001_create_service_links_table.php`
- Create `platform/database/migrations/2026_06_18_120002_create_homepage_modules_table.php`
- Create `platform/database/migrations/2026_06_18_120003_create_homepage_module_items_table.php`
- Create `platform/database/migrations/2026_06_18_120004_create_article_faqs_table.php`
- Create `platform/database/migrations/2026_06_18_120005_create_article_travel_category_table.php`
- Create `platform/database/migrations/2026_06_18_120006_add_media_portal_fields_to_articles_table.php`
- Create `platform/database/migrations/2026_06_18_120007_add_channel_fields_to_destinations_table.php`

### Modified Models

- Modify `platform/app/Models/Article.php` — add FAQ/category relationships, source fields, display-updated date, reading time, popularity score, and coupon availability flag.
- Modify `platform/app/Models/Destination.php` — add channel visibility, display name, sort order, and channel scope.

### Admin

- Create `platform/app/Livewire/Admin/TravelCategories/TravelCategoryIndex.php`
- Create `platform/resources/views/livewire/admin/travel-categories/travel-category-index.blade.php`
- Create `platform/app/Livewire/Admin/ServiceLinks/ServiceLinkIndex.php`
- Create `platform/resources/views/livewire/admin/service-links/service-link-index.blade.php`
- Create `platform/app/Livewire/Admin/HomepageModules/HomepageModuleIndex.php`
- Create `platform/resources/views/livewire/admin/homepage-modules/homepage-module-index.blade.php`
- Modify `platform/app/Livewire/Admin/Articles/ArticleForm.php`
- Modify `platform/resources/views/livewire/admin/articles/article-form.blade.php`
- Modify `platform/app/Livewire/Admin/Destinations/DestinationIndex.php`
- Modify `platform/resources/views/livewire/admin/destinations/destination-index.blade.php`
- Modify `platform/resources/views/layouts/admin.blade.php`
- Modify `platform/routes/web.php`

### Public Frontend And SEO

- Create `platform/app/Http/Controllers/Public/TravelCategoryController.php`
- Modify `platform/app/Http/Controllers/Public/HomeController.php`
- Modify `platform/app/Http/Controllers/Public/DestinationController.php`
- Modify `platform/app/Http/Controllers/Public/ArticleController.php`
- Modify `platform/app/Http/Controllers/Public/SearchController.php`
- Modify `platform/app/Services/Seo/SitemapBuilder.php`
- Modify `platform/resources/views/layouts/public.blade.php`
- Modify `platform/resources/views/public/home.blade.php`
- Modify `platform/resources/views/public/articles/show.blade.php`
- Modify `platform/resources/views/public/search.blade.php`
- Create `platform/resources/views/public/categories/show.blade.php`
- Create `platform/resources/views/public/regions/index.blade.php`
- Create `platform/resources/views/public/regions/show.blade.php`
- Modify `platform/routes/web.php`

### Seed And Tests

- Modify `platform/database/seeders/DemoContentSeeder.php`
- Create `platform/tests/Feature/Content/MediaPortalSchemaTest.php`
- Create `platform/tests/Feature/Admin/MediaPortalAdminTest.php`
- Create `platform/tests/Feature/Public/MediaPortalPublicTest.php`
- Create `platform/tests/Feature/Public/SearchFilterTest.php`
- Modify `platform/tests/Feature/Seo/SitemapTest.php`
- Create `platform/tests/Feature/Seeders/DemoContentSeederTest.php`

---

### Task 1: Content Schema And Relationships

**Files:**
- Create: `platform/tests/Feature/Content/MediaPortalSchemaTest.php`
- Create: `platform/database/migrations/2026_06_18_120000_create_travel_categories_table.php`
- Create: `platform/database/migrations/2026_06_18_120001_create_service_links_table.php`
- Create: `platform/database/migrations/2026_06_18_120002_create_homepage_modules_table.php`
- Create: `platform/database/migrations/2026_06_18_120003_create_homepage_module_items_table.php`
- Create: `platform/database/migrations/2026_06_18_120004_create_article_faqs_table.php`
- Create: `platform/database/migrations/2026_06_18_120005_create_article_travel_category_table.php`
- Create: `platform/database/migrations/2026_06_18_120006_add_media_portal_fields_to_articles_table.php`
- Create: `platform/database/migrations/2026_06_18_120007_add_channel_fields_to_destinations_table.php`
- Create: `platform/app/Models/TravelCategory.php`
- Create: `platform/app/Models/ServiceLink.php`
- Create: `platform/app/Models/HomepageModule.php`
- Create: `platform/app/Models/HomepageModuleItem.php`
- Create: `platform/app/Models/ArticleFaq.php`
- Create: `platform/database/factories/TravelCategoryFactory.php`
- Create: `platform/database/factories/ServiceLinkFactory.php`
- Create: `platform/database/factories/HomepageModuleFactory.php`
- Create: `platform/database/factories/HomepageModuleItemFactory.php`
- Create: `platform/database/factories/ArticleFaqFactory.php`
- Modify: `platform/app/Models/Article.php`
- Modify: `platform/app/Models/Destination.php`

- [ ] **Step 1: Write failing relationship tests**

Create `platform/tests/Feature/Content/MediaPortalSchemaTest.php`:

```php
<?php

namespace Tests\Feature\Content;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPortalSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_can_connect_categories_and_ordered_faqs(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'source_name' => 'Japan Meteorological Agency',
            'source_url' => 'https://www.jma.go.jp/',
            'display_updated_at' => now()->subDay(),
            'reading_time_minutes' => 6,
            'popularity_score' => 42,
            'has_coupon' => true,
        ]);
        $category = TravelCategory::factory()->create(['title' => 'Transport', 'slug' => 'transport']);

        $article->travelCategories()->attach($category, ['sort_order' => 3]);
        ArticleFaq::factory()->for($article)->create([
            'question' => 'Do I need a rail pass for Tokyo?',
            'answer' => '<p>Most city-only trips work better with IC cards.</p>',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $fresh = $article->fresh(['travelCategories', 'faqs']);

        $this->assertTrue($fresh->travelCategories->contains($category));
        $this->assertSame('Do I need a rail pass for Tokyo?', $fresh->faqs->first()->question);
        $this->assertTrue($fresh->has_coupon);
    }

    public function test_destination_can_be_marked_as_public_region_channel(): void
    {
        $destination = Destination::factory()->create([
            'name' => 'Tokyo',
            'slug' => 'tokyo',
            'type' => 'region',
            'display_name' => 'Tokyo Region',
            'is_channel' => true,
            'sort_order' => 10,
        ]);

        $channels = Destination::query()->channel()->ordered()->get();

        $this->assertTrue($channels->contains($destination));
        $this->assertSame('Tokyo Region', $destination->fresh()->display_name);
    }

    public function test_homepage_module_can_hold_typed_curated_items(): void
    {
        $module = HomepageModule::factory()->create([
            'placement_key' => 'home-featured',
            'type' => 'featured_articles',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);

        HomepageModuleItem::factory()->for($module)->create([
            'item_type' => Article::class,
            'item_id' => $article->id,
            'label' => 'Featured Tokyo guide',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $this->assertSame('Featured Tokyo guide', $module->fresh('items.item')->items->first()->label);
        $this->assertTrue($module->fresh('items.item')->items->first()->item->is($article));
    }

    public function test_service_link_supports_safe_external_url_and_placement(): void
    {
        $link = ServiceLink::factory()->create([
            'type' => 'rail',
            'label' => 'Rail Tickets',
            'url' => 'https://example.com/rail',
            'placement' => 'header',
            'tracking_key' => 'rail-tickets',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('service_links', [
            'id' => $link->id,
            'type' => 'rail',
            'placement' => 'header',
            'tracking_key' => 'rail-tickets',
        ]);
    }
}
```

- [ ] **Step 2: Run the new schema test and verify it fails**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalSchemaTest
```

Expected: tests fail because `TravelCategory`, `ServiceLink`, `HomepageModule`, `HomepageModuleItem`, `ArticleFaq`, new relationships, and new fields do not exist.

- [ ] **Step 3: Add travel categories migration**

Create `platform/database/migrations/2026_06_18_120000_create_travel_categories_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('travel_categories')->nullOnDelete();
            $table->string('title');
            $table->string('display_name')->nullable();
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_indexable')->default(true);
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_categories');
    }
};
```

- [ ] **Step 4: Add service links migration**

Create `platform/database/migrations/2026_06_18_120001_create_service_links_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_links', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('label');
            $table->string('url');
            $table->string('placement', 40)->default('header')->index();
            $table->string('tracking_key')->nullable()->index();
            $table->text('notes')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_links');
    }
};
```

- [ ] **Step 5: Add homepage module migrations**

Create `platform/database/migrations/2026_06_18_120002_create_homepage_modules_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_modules', function (Blueprint $table): void {
            $table->id();
            $table->string('placement_key')->unique();
            $table->string('type', 60)->index();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_modules');
    }
};
```

Create `platform/database/migrations/2026_06_18_120003_create_homepage_module_items_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_module_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('homepage_module_id')->constrained()->cascadeOnDelete();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->string('label')->nullable();
            $table->text('summary')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_module_items');
    }
};
```

- [ ] **Step 6: Add article FAQ and category pivot migrations**

Create `platform/database/migrations/2026_06_18_120004_create_article_faqs_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('question');
            $table->longText('answer');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_faqs');
    }
};
```

Create `platform/database/migrations/2026_06_18_120005_create_article_travel_category_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_travel_category', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('travel_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['article_id', 'travel_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_travel_category');
    }
};
```

- [ ] **Step 7: Add article and destination portal fields**

Create `platform/database/migrations/2026_06_18_120006_add_media_portal_fields_to_articles_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('source_name')->nullable()->after('body');
            $table->string('source_url')->nullable()->after('source_name');
            $table->timestamp('display_updated_at')->nullable()->after('published_at');
            $table->unsignedSmallInteger('reading_time_minutes')->nullable()->after('display_updated_at');
            $table->unsignedInteger('popularity_score')->default(0)->index()->after('reading_time_minutes');
            $table->boolean('has_coupon')->default(false)->index()->after('popularity_score');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn([
                'source_name',
                'source_url',
                'display_updated_at',
                'reading_time_minutes',
                'popularity_score',
                'has_coupon',
            ]);
        });
    }
};
```

Create `platform/database/migrations/2026_06_18_120007_add_channel_fields_to_destinations_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->string('display_name')->nullable()->after('name');
            $table->boolean('is_channel')->default(false)->index()->after('is_indexable');
            $table->unsignedInteger('sort_order')->default(0)->index()->after('is_channel');
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table): void {
            $table->dropColumn(['display_name', 'is_channel', 'sort_order']);
        });
    }
};
```

- [ ] **Step 8: Add models**

Create `platform/app/Models/TravelCategory.php`:

```php
<?php

namespace App\Models;

use Database\Factories\TravelCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelCategory extends Model
{
    /** @use HasFactory<TravelCategoryFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'display_name',
        'slug',
        'excerpt',
        'body',
        'seo_title',
        'meta_description',
        'is_indexable',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withPivot('sort_order')->withTimestamps();
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

Create `platform/app/Models/ServiceLink.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ServiceLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLink extends Model
{
    /** @use HasFactory<ServiceLinkFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'label',
        'url',
        'placement',
        'tracking_key',
        'notes',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopePlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}
```

Create `platform/app/Models/HomepageModule.php`:

```php
<?php

namespace App\Models;

use Database\Factories\HomepageModuleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomepageModule extends Model
{
    /** @use HasFactory<HomepageModuleFactory> */
    use HasFactory;

    protected $fillable = [
        'placement_key',
        'type',
        'title',
        'subtitle',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(HomepageModuleItem::class)->ordered();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
```

Create `platform/app/Models/HomepageModuleItem.php`:

```php
<?php

namespace App\Models;

use Database\Factories\HomepageModuleItemFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HomepageModuleItem extends Model
{
    /** @use HasFactory<HomepageModuleItemFactory> */
    use HasFactory;

    protected $fillable = [
        'homepage_module_id',
        'item_type',
        'item_id',
        'label',
        'summary',
        'sort_order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(HomepageModule::class, 'homepage_module_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }
}
```

Create `platform/app/Models/ArticleFaq.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ArticleFaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleFaq extends Model
{
    /** @use HasFactory<ArticleFaqFactory> */
    use HasFactory;

    protected $fillable = [
        'article_id',
        'question',
        'answer',
        'sort_order',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
```

- [ ] **Step 9: Add factories**

Create `platform/database/factories/TravelCategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\TravelCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TravelCategory>
 */
class TravelCategoryFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'title' => Str::title($title),
            'display_name' => Str::title($title),
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(14),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'seo_title' => Str::title($title).' Japan Travel Guides',
            'meta_description' => fake()->sentence(14),
            'is_indexable' => true,
            'is_visible' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
```

Create `platform/database/factories/ServiceLinkFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ServiceLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceLink>
 */
class ServiceLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['guide', 'activity', 'hotel', 'flight', 'rail', 'shop', 'community', 'exchange_rate', 'advertising']),
            'label' => fake()->words(2, true),
            'url' => 'https://example.com/'.fake()->slug(),
            'placement' => fake()->randomElement(['header', 'footer']),
            'tracking_key' => fake()->unique()->slug(),
            'notes' => fake()->sentence(),
            'is_enabled' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
```

Create `platform/database/factories/HomepageModuleFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\HomepageModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageModule>
 */
class HomepageModuleFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['featured_articles', 'latest_articles', 'popular_articles', 'region_grid', 'category_grid', 'service_highlights', 'travel_tools']);

        return [
            'placement_key' => $type.'-'.fake()->unique()->numberBetween(1, 9999),
            'type' => $type,
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(12),
            'is_enabled' => true,
            'sort_order' => fake()->numberBetween(1, 50),
        ];
    }
}
```

Create `platform/database/factories/HomepageModuleItemFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageModuleItem>
 */
class HomepageModuleItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'homepage_module_id' => HomepageModule::factory(),
            'item_type' => Article::class,
            'item_id' => Article::factory(),
            'label' => fake()->sentence(3),
            'summary' => fake()->sentence(12),
            'sort_order' => fake()->numberBetween(1, 50),
            'is_enabled' => true,
        ];
    }
}
```

Create `platform/database/factories/ArticleFaqFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleFaq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArticleFaq>
 */
class ArticleFaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'question' => fake()->sentence().'?',
            'answer' => '<p>'.fake()->paragraph().'</p>',
            'sort_order' => fake()->numberBetween(1, 20),
            'is_enabled' => true,
        ];
    }
}
```

- [ ] **Step 10: Update Article model relationships and casts**

Modify `platform/app/Models/Article.php` by adding the new fillable fields:

```php
'source_name',
'source_url',
'display_updated_at',
'reading_time_minutes',
'popularity_score',
'has_coupon',
```

Update casts:

```php
'display_updated_at' => 'datetime',
'reading_time_minutes' => 'integer',
'popularity_score' => 'integer',
'has_coupon' => 'boolean',
```

Add relationships:

```php
public function travelCategories(): BelongsToMany
{
    return $this->belongsToMany(TravelCategory::class)->withPivot('sort_order')->withTimestamps();
}

public function faqs(): HasMany
{
    return $this->hasMany(ArticleFaq::class)->ordered();
}
```

- [ ] **Step 11: Update Destination model channel fields**

Modify `platform/app/Models/Destination.php` by adding fillable fields:

```php
'display_name',
'is_channel',
'sort_order',
```

Update casts:

```php
'is_channel' => 'boolean',
'sort_order' => 'integer',
```

Add scopes:

```php
use Illuminate\Database\Eloquent\Builder;
```

```php
public function scopeChannel(Builder $query): Builder
{
    return $query->where('is_channel', true);
}

public function scopeOrdered(Builder $query): Builder
{
    return $query->orderBy('sort_order')->orderBy('name');
}
```

- [ ] **Step 12: Run schema tests**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalSchemaTest
```

Expected: four passing tests.

- [ ] **Step 13: Run the full test suite**

Run:

```bash
cd platform
php artisan test
```

Expected: existing tests still pass, plus the new `MediaPortalSchemaTest` passes.

- [ ] **Step 14: Commit schema work**

Run:

```bash
git add platform/app/Models platform/database/factories platform/database/migrations platform/tests/Feature/Content/MediaPortalSchemaTest.php
git commit -m "feat: add media portal content schema"
```

Expected: commit succeeds with only Phase 1 schema files staged.

---

### Task 2: Chinese Admin CRUD For Categories, Service Links, And Homepage Modules

**Files:**
- Create: `platform/tests/Feature/Admin/MediaPortalAdminTest.php`
- Create: `platform/app/Livewire/Admin/TravelCategories/TravelCategoryIndex.php`
- Create: `platform/resources/views/livewire/admin/travel-categories/travel-category-index.blade.php`
- Create: `platform/app/Livewire/Admin/ServiceLinks/ServiceLinkIndex.php`
- Create: `platform/resources/views/livewire/admin/service-links/service-link-index.blade.php`
- Create: `platform/app/Livewire/Admin/HomepageModules/HomepageModuleIndex.php`
- Create: `platform/resources/views/livewire/admin/homepage-modules/homepage-module-index.blade.php`
- Modify: `platform/resources/views/layouts/admin.blade.php`
- Modify: `platform/routes/web.php`

- [ ] **Step 1: Write failing Livewire admin CRUD tests**

Create `platform/tests/Feature/Admin/MediaPortalAdminTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\HomepageModules\HomepageModuleIndex;
use App\Livewire\Admin\ServiceLinks\ServiceLinkIndex;
use App\Livewire\Admin\TravelCategories\TravelCategoryIndex;
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
            ->set('title', 'Transport')
            ->set('display_name', 'Transport')
            ->set('slug', 'transport')
            ->set('excerpt', 'Rail, buses, IC cards, and airport transfers.')
            ->set('sort_order', 7)
            ->set('is_visible', true)
            ->call('save');

        $this->assertDatabaseHas('travel_categories', [
            'title' => 'Transport',
            'slug' => 'transport',
            'is_visible' => true,
            'sort_order' => 7,
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
            ->set('label', 'Rail Tickets')
            ->set('url', 'https://example.com/rail')
            ->set('placement', 'header')
            ->set('tracking_key', 'rail-tickets')
            ->set('sort_order', 3)
            ->set('is_enabled', true)
            ->call('save');

        $this->assertDatabaseHas('service_links', [
            'type' => 'rail',
            'label' => 'Rail Tickets',
            'placement' => 'header',
            'is_enabled' => true,
        ]);
    }

    public function test_editor_can_create_homepage_module_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $this->actingAs($editor);

        Livewire::test(HomepageModuleIndex::class)
            ->set('placement_key', 'home-featured')
            ->set('type', 'featured_articles')
            ->set('title', 'Featured Guides')
            ->set('subtitle', 'Editor-picked guides for first-time Japan trips.')
            ->set('sort_order', 1)
            ->set('is_enabled', true)
            ->call('save');

        $this->assertDatabaseHas('homepage_modules', [
            'placement_key' => 'home-featured',
            'type' => 'featured_articles',
            'title' => 'Featured Guides',
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
}
```

- [ ] **Step 2: Run admin tests and verify they fail**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalAdminTest
```

Expected: tests fail because the Livewire components and routes do not exist.

- [ ] **Step 3: Add admin routes**

In `platform/routes/web.php`, inside the existing `admin.` group, add:

```php
Route::get('/travel-categories', \App\Livewire\Admin\TravelCategories\TravelCategoryIndex::class)->name('travel-categories.index');
Route::get('/service-links', \App\Livewire\Admin\ServiceLinks\ServiceLinkIndex::class)->name('service-links.index');
Route::get('/homepage-modules', \App\Livewire\Admin\HomepageModules\HomepageModuleIndex::class)->name('homepage-modules.index');
```

- [ ] **Step 4: Add travel category admin component**

Create `platform/app/Livewire/Admin/TravelCategories/TravelCategoryIndex.php`:

```php
<?php

namespace App\Livewire\Admin\TravelCategories;

use App\Models\TravelCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TravelCategoryIndex extends Component
{
    public ?int $categoryId = null;
    public ?int $parent_id = null;
    public string $title = '';
    public ?string $display_name = null;
    public string $slug = '';
    public ?string $excerpt = null;
    public ?string $body = null;
    public ?string $seo_title = null;
    public ?string $meta_description = null;
    public bool $is_indexable = true;
    public bool $is_visible = true;
    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $category = TravelCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->parent_id = $category->parent_id;
        $this->title = $category->title;
        $this->display_name = $category->display_name;
        $this->slug = $category->slug;
        $this->excerpt = $category->excerpt;
        $this->body = $category->body;
        $this->seo_title = $category->seo_title;
        $this->meta_description = $category->meta_description;
        $this->is_indexable = $category->is_indexable;
        $this->is_visible = $category->is_visible;
        $this->sort_order = $category->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'parent_id' => ['nullable', 'integer', 'exists:travel_categories,id'],
            'title' => ['required', 'string', 'max:140'],
            'display_name' => ['nullable', 'string', 'max:140'],
            'slug' => ['required', 'alpha_dash:ascii', 'max:160', Rule::unique('travel_categories', 'slug')->ignore($this->categoryId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:260'],
            'is_indexable' => ['boolean'],
            'is_visible' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        if ($this->categoryId !== null && (int) $data['parent_id'] === $this->categoryId) {
            $data['parent_id'] = null;
        }

        TravelCategory::updateOrCreate(['id' => $this->categoryId], $data);

        session()->flash('status', '分类频道已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        TravelCategory::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->reset([
            'categoryId',
            'parent_id',
            'title',
            'display_name',
            'slug',
            'excerpt',
            'body',
            'seo_title',
            'meta_description',
        ]);
        $this->is_indexable = true;
        $this->is_visible = true;
        $this->sort_order = 0;
    }

    public function render(): View
    {
        return view('livewire.admin.travel-categories.travel-category-index', [
            'categories' => TravelCategory::query()->with('parent')->ordered()->get(),
            'parentOptions' => TravelCategory::query()->ordered()->get(),
        ])->layout('layouts.admin', ['title' => '分类频道管理']);
    }
}
```

- [ ] **Step 5: Add service link admin component**

Create `platform/app/Livewire/Admin/ServiceLinks/ServiceLinkIndex.php`:

```php
<?php

namespace App\Livewire\Admin\ServiceLinks;

use App\Models\ServiceLink;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ServiceLinkIndex extends Component
{
    public ?int $serviceLinkId = null;
    public string $type = 'guide';
    public string $label = '';
    public string $url = '';
    public string $placement = 'header';
    public ?string $tracking_key = null;
    public ?string $notes = null;
    public bool $is_enabled = true;
    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $link = ServiceLink::findOrFail($id);
        $this->serviceLinkId = $link->id;
        $this->type = $link->type;
        $this->label = $link->label;
        $this->url = $link->url;
        $this->placement = $link->placement;
        $this->tracking_key = $link->tracking_key;
        $this->notes = $link->notes;
        $this->is_enabled = $link->is_enabled;
        $this->sort_order = $link->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'type' => ['required', Rule::in(['guide', 'activity', 'hotel', 'flight', 'rail', 'shop', 'community', 'exchange_rate', 'advertising', 'custom'])],
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:255'],
            'placement' => ['required', Rule::in(['header', 'footer'])],
            'tracking_key' => ['nullable', 'alpha_dash:ascii', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        ServiceLink::updateOrCreate(['id' => $this->serviceLinkId], $data);

        session()->flash('status', '服务入口已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        ServiceLink::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->reset(['serviceLinkId', 'label', 'url', 'tracking_key', 'notes']);
        $this->type = 'guide';
        $this->placement = 'header';
        $this->is_enabled = true;
        $this->sort_order = 0;
    }

    public function render(): View
    {
        return view('livewire.admin.service-links.service-link-index', [
            'serviceLinks' => ServiceLink::query()->ordered()->get(),
        ])->layout('layouts.admin', ['title' => '服务入口管理']);
    }
}
```

- [ ] **Step 6: Add homepage module admin component**

Create `platform/app/Livewire/Admin/HomepageModules/HomepageModuleIndex.php`:

```php
<?php

namespace App\Livewire\Admin\HomepageModules;

use App\Models\HomepageModule;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class HomepageModuleIndex extends Component
{
    public ?int $moduleId = null;
    public string $placement_key = '';
    public string $type = 'featured_articles';
    public string $title = '';
    public ?string $subtitle = null;
    public bool $is_enabled = true;
    public int $sort_order = 0;

    public function edit(int $id): void
    {
        $module = HomepageModule::findOrFail($id);
        $this->moduleId = $module->id;
        $this->placement_key = $module->placement_key;
        $this->type = $module->type;
        $this->title = $module->title;
        $this->subtitle = $module->subtitle;
        $this->is_enabled = $module->is_enabled;
        $this->sort_order = $module->sort_order;
    }

    public function save(): void
    {
        $data = $this->validate([
            'placement_key' => ['required', 'alpha_dash:ascii', 'max:120', Rule::unique('homepage_modules', 'placement_key')->ignore($this->moduleId)],
            'type' => ['required', Rule::in(['featured_articles', 'latest_articles', 'popular_articles', 'region_grid', 'category_grid', 'service_highlights', 'travel_tools'])],
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:9999'],
        ]);

        HomepageModule::updateOrCreate(['id' => $this->moduleId], $data);

        session()->flash('status', '首页模块已保存');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        HomepageModule::findOrFail($id)->delete();
    }

    private function resetForm(): void
    {
        $this->reset(['moduleId', 'placement_key', 'title', 'subtitle']);
        $this->type = 'featured_articles';
        $this->is_enabled = true;
        $this->sort_order = 0;
    }

    public function render(): View
    {
        return view('livewire.admin.homepage-modules.homepage-module-index', [
            'modules' => HomepageModule::query()->ordered()->get(),
        ])->layout('layouts.admin', ['title' => '首页模块管理']);
    }
}
```

- [ ] **Step 7: Add admin Blade views**

Create `platform/resources/views/livewire/admin/travel-categories/travel-category-index.blade.php`:

```blade
<section class="grid gap-8 lg:grid-cols-[380px_1fr]">
    <form wire:submit="save" class="space-y-4 rounded border bg-white p-5">
        <h2 class="text-lg font-semibold">{{ $categoryId ? '编辑分类频道' : '新建分类频道' }}</h2>
        @if (session('status')) <p class="rounded bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p> @endif
        <label class="block text-sm">上级分类
            <select wire:model="parent_id" class="mt-1 w-full rounded border px-3 py-2">
                <option value="">无</option>
                @foreach($parentOptions as $option)
                    <option value="{{ $option->id }}">{{ $option->title }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm">英文标题 <input wire:model="title" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">前台显示名 <input wire:model="display_name" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Slug <input wire:model="slug" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">简介 <textarea wire:model="excerpt" rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
        <label class="block text-sm">正文 <textarea wire:model="body" rows="5" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
        <label class="block text-sm">SEO 标题 <input wire:model="seo_title" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">Meta 描述 <textarea wire:model="meta_description" rows="2" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
        <label class="block text-sm">排序 <input type="number" wire:model="sort_order" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_visible"> 前台显示</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_indexable"> 允许索引</label>
        <button class="rounded bg-slate-950 px-4 py-2 text-sm font-semibold text-white">保存分类频道</button>
    </form>

    <div class="overflow-hidden rounded border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">标题</th><th class="p-3">Slug</th><th class="p-3">排序</th><th class="p-3">状态</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($categories as $category)
                    <tr class="border-t">
                        <td class="p-3">{{ $category->title }}</td>
                        <td class="p-3">{{ $category->slug }}</td>
                        <td class="p-3">{{ $category->sort_order }}</td>
                        <td class="p-3">{{ $category->is_visible ? '显示' : '隐藏' }}</td>
                        <td class="p-3 text-right"><button wire:click="edit({{ $category->id }})" class="text-emerald-700">编辑</button> <button wire:click="delete({{ $category->id }})" class="ml-3 text-red-700">删除</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
```

Create `platform/resources/views/livewire/admin/service-links/service-link-index.blade.php`:

```blade
<section class="grid gap-8 lg:grid-cols-[360px_1fr]">
    <form wire:submit="save" class="space-y-4 rounded border bg-white p-5">
        <h2 class="text-lg font-semibold">{{ $serviceLinkId ? '编辑服务入口' : '新建服务入口' }}</h2>
        @if (session('status')) <p class="rounded bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p> @endif
        <label class="block text-sm">类型
            <select wire:model="type" class="mt-1 w-full rounded border px-3 py-2">
                @foreach(['guide' => '指南', 'activity' => '活动', 'hotel' => '酒店', 'flight' => '机票', 'rail' => '铁路', 'shop' => '购物', 'community' => '社区', 'exchange_rate' => '汇率', 'advertising' => '广告合作', 'custom' => '自定义'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm">英文标签 <input wire:model="label" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">外部链接 <input wire:model="url" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">位置
            <select wire:model="placement" class="mt-1 w-full rounded border px-3 py-2">
                <option value="header">头部</option>
                <option value="footer">底部</option>
            </select>
        </label>
        <label class="block text-sm">追踪键 <input wire:model="tracking_key" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">备注 <textarea wire:model="notes" rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
        <label class="block text-sm">排序 <input type="number" wire:model="sort_order" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_enabled"> 启用</label>
        <button class="rounded bg-slate-950 px-4 py-2 text-sm font-semibold text-white">保存服务入口</button>
    </form>

    <div class="overflow-hidden rounded border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">标签</th><th class="p-3">类型</th><th class="p-3">位置</th><th class="p-3">状态</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($serviceLinks as $link)
                    <tr class="border-t">
                        <td class="p-3">{{ $link->label }}</td>
                        <td class="p-3">{{ $link->type }}</td>
                        <td class="p-3">{{ $link->placement }}</td>
                        <td class="p-3">{{ $link->is_enabled ? '启用' : '停用' }}</td>
                        <td class="p-3 text-right"><button wire:click="edit({{ $link->id }})" class="text-emerald-700">编辑</button> <button wire:click="delete({{ $link->id }})" class="ml-3 text-red-700">删除</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
```

Create `platform/resources/views/livewire/admin/homepage-modules/homepage-module-index.blade.php`:

```blade
<section class="grid gap-8 lg:grid-cols-[360px_1fr]">
    <form wire:submit="save" class="space-y-4 rounded border bg-white p-5">
        <h2 class="text-lg font-semibold">{{ $moduleId ? '编辑首页模块' : '新建首页模块' }}</h2>
        @if (session('status')) <p class="rounded bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p> @endif
        <label class="block text-sm">位置键 <input wire:model="placement_key" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">模块类型
            <select wire:model="type" class="mt-1 w-full rounded border px-3 py-2">
                @foreach(['featured_articles' => '精选文章', 'latest_articles' => '最新文章', 'popular_articles' => '热门文章', 'region_grid' => '地区网格', 'category_grid' => '分类网格', 'service_highlights' => '服务推荐', 'travel_tools' => '旅行工具'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm">英文标题 <input wire:model="title" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="block text-sm">英文副标题 <textarea wire:model="subtitle" rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
        <label class="block text-sm">排序 <input type="number" wire:model="sort_order" class="mt-1 w-full rounded border px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_enabled"> 启用</label>
        <button class="rounded bg-slate-950 px-4 py-2 text-sm font-semibold text-white">保存首页模块</button>
    </form>

    <div class="overflow-hidden rounded border bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">标题</th><th class="p-3">位置键</th><th class="p-3">类型</th><th class="p-3">状态</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($modules as $module)
                    <tr class="border-t">
                        <td class="p-3">{{ $module->title }}</td>
                        <td class="p-3">{{ $module->placement_key }}</td>
                        <td class="p-3">{{ $module->type }}</td>
                        <td class="p-3">{{ $module->is_enabled ? '启用' : '停用' }}</td>
                        <td class="p-3 text-right"><button wire:click="edit({{ $module->id }})" class="text-emerald-700">编辑</button> <button wire:click="delete({{ $module->id }})" class="ml-3 text-red-700">删除</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
```

- [ ] **Step 8: Add admin layout navigation**

Modify the navigation block in `platform/resources/views/layouts/admin.blade.php` so it includes these links:

```blade
<a href="{{ route('admin.travel-categories.index') }}" class="rounded px-3 py-2 hover:bg-slate-800">分类频道</a>
<a href="{{ route('admin.service-links.index') }}" class="rounded px-3 py-2 hover:bg-slate-800">服务入口</a>
<a href="{{ route('admin.homepage-modules.index') }}" class="rounded px-3 py-2 hover:bg-slate-800">首页模块</a>
```

- [ ] **Step 9: Run admin tests**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalAdminTest
```

Expected: four passing tests.

- [ ] **Step 10: Run full test suite**

Run:

```bash
cd platform
php artisan test
```

Expected: all tests pass.

- [ ] **Step 11: Commit admin CRUD**

Run:

```bash
git add platform/app/Livewire/Admin platform/resources/views/livewire/admin platform/resources/views/layouts/admin.blade.php platform/routes/web.php platform/tests/Feature/Admin/MediaPortalAdminTest.php
git commit -m "feat: add media portal admin screens"
```

Expected: commit succeeds.

---

### Task 3: Destination Channel Admin Enhancements

**Files:**
- Modify: `platform/tests/Feature/Admin/MediaPortalAdminTest.php`
- Modify: `platform/app/Livewire/Admin/Destinations/DestinationIndex.php`
- Modify: `platform/resources/views/livewire/admin/destinations/destination-index.blade.php`

- [ ] **Step 1: Add failing destination channel admin test**

Append this test to `platform/tests/Feature/Admin/MediaPortalAdminTest.php`:

```php
public function test_editor_can_manage_destination_channel_fields_from_chinese_admin(): void
{
    $this->seed(RoleSeeder::class);
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $this->actingAs($editor);

    Livewire::test(\App\Livewire\Admin\Destinations\DestinationIndex::class)
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
        ->call('save');

    $this->assertDatabaseHas('destinations', [
        'slug' => 'tokyo',
        'display_name' => 'Tokyo Region',
        'is_channel' => true,
        'sort_order' => 3,
    ]);
}
```

- [ ] **Step 2: Run the targeted test and verify it fails**

Run:

```bash
cd platform
php artisan test --filter=destination_channel_fields_from_chinese_admin
```

Expected: failure because `DestinationIndex` does not expose the channel fields.

- [ ] **Step 3: Extend DestinationIndex properties and edit mapping**

Modify `platform/app/Livewire/Admin/Destinations/DestinationIndex.php` by adding public properties:

```php
public ?int $parent_id = null;
public ?string $display_name = null;
public ?string $body = null;
public ?string $seo_title = null;
public ?string $meta_description = null;
public bool $is_indexable = true;
public bool $is_channel = false;
public int $sort_order = 0;
```

In `edit()`, after existing assignments, add:

```php
$this->parent_id = $destination->parent_id;
$this->display_name = $destination->display_name;
$this->body = $destination->body;
$this->seo_title = $destination->seo_title;
$this->meta_description = $destination->meta_description;
$this->is_indexable = $destination->is_indexable;
$this->is_channel = $destination->is_channel;
$this->sort_order = $destination->sort_order;
```

- [ ] **Step 4: Extend DestinationIndex validation and reset**

Replace the validation array in `save()` with:

```php
$data = $this->validate([
    'parent_id' => ['nullable', 'integer', 'exists:destinations,id'],
    'type' => ['required', 'string', 'max:40'],
    'name' => ['required', 'string', 'max:120'],
    'display_name' => ['nullable', 'string', 'max:120'],
    'slug' => ['required', 'alpha_dash:ascii', 'max:140', Rule::unique('destinations', 'slug')->ignore($this->destinationId)],
    'excerpt' => ['nullable', 'string', 'max:500'],
    'body' => ['nullable', 'string'],
    'seo_title' => ['nullable', 'string', 'max:180'],
    'meta_description' => ['nullable', 'string', 'max:260'],
    'is_indexable' => ['boolean'],
    'is_channel' => ['boolean'],
    'sort_order' => ['integer', 'min:0', 'max:9999'],
]);

if ($this->destinationId !== null && (int) $data['parent_id'] === $this->destinationId) {
    $data['parent_id'] = null;
}
```

Replace the post-save reset with:

```php
$this->reset([
    'destinationId',
    'parent_id',
    'name',
    'display_name',
    'slug',
    'excerpt',
    'body',
    'seo_title',
    'meta_description',
]);
$this->type = 'city';
$this->is_indexable = true;
$this->is_channel = false;
$this->sort_order = 0;
```

Update `render()` data:

```php
return view('livewire.admin.destinations.destination-index', [
    'destinations' => Destination::query()->with('parent')->ordered()->get(),
    'parentOptions' => Destination::query()->ordered()->get(),
])->layout('layouts.admin', ['title' => '目的地管理']);
```

- [ ] **Step 5: Update destination admin Blade controls**

In `platform/resources/views/livewire/admin/destinations/destination-index.blade.php`, add these fields to the form:

```blade
<label class="block text-sm">上级地区
    <select wire:model="parent_id" class="mt-1 w-full rounded border px-3 py-2">
        <option value="">无</option>
        @foreach($parentOptions as $option)
            <option value="{{ $option->id }}">{{ $option->name }}</option>
        @endforeach
    </select>
</label>
<label class="block text-sm">前台显示名 <input wire:model="display_name" class="mt-1 w-full rounded border px-3 py-2"></label>
<label class="block text-sm">正文 <textarea wire:model="body" rows="5" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
<label class="block text-sm">SEO 标题 <input wire:model="seo_title" class="mt-1 w-full rounded border px-3 py-2"></label>
<label class="block text-sm">Meta 描述 <textarea wire:model="meta_description" rows="2" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
<label class="block text-sm">排序 <input type="number" wire:model="sort_order" class="mt-1 w-full rounded border px-3 py-2"></label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_channel"> 作为地区频道显示</label>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="is_indexable"> 允许索引</label>
```

In the list table, add display columns for channel and sort order:

```blade
<td class="p-3">{{ $destination->is_channel ? '频道' : '普通' }}</td>
<td class="p-3">{{ $destination->sort_order }}</td>
```

- [ ] **Step 6: Run destination admin test**

Run:

```bash
cd platform
php artisan test --filter=destination_channel_fields_from_chinese_admin
```

Expected: one passing test.

- [ ] **Step 7: Run admin tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Admin
```

Expected: all admin tests pass.

- [ ] **Step 8: Commit destination admin enhancements**

Run:

```bash
git add platform/app/Livewire/Admin/Destinations/DestinationIndex.php platform/resources/views/livewire/admin/destinations/destination-index.blade.php platform/tests/Feature/Admin/MediaPortalAdminTest.php
git commit -m "feat: manage region channel fields"
```

Expected: commit succeeds.

---

### Task 4: Article FAQ, Source Attribution, And Category Editing

**Files:**
- Modify: `platform/tests/Feature/Admin/ArticleAdminTest.php`
- Modify: `platform/app/Livewire/Admin/Articles/ArticleForm.php`
- Modify: `platform/resources/views/livewire/admin/articles/article-form.blade.php`

- [ ] **Step 1: Add failing article editor test**

Append this test to `platform/tests/Feature/Admin/ArticleAdminTest.php`:

```php
public function test_editor_can_attach_categories_and_faqs_to_article(): void
{
    $this->seed(RoleSeeder::class);
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $this->actingAs($editor);

    $category = \App\Models\TravelCategory::factory()->create([
        'title' => 'Transport',
        'slug' => 'transport',
    ]);

    Livewire::test(ArticleForm::class)
        ->set('title', 'Tokyo Rail Basics')
        ->set('slug', 'tokyo-rail-basics')
        ->set('excerpt', 'A practical guide to Tokyo trains.')
        ->set('body', '<p>Use IC cards for short city trips.</p>')
        ->set('source_name', 'Tokyo Metro')
        ->set('source_url', 'https://www.tokyometro.jp/en/')
        ->set('reading_time_minutes', 5)
        ->set('popularity_score', 11)
        ->set('has_coupon', true)
        ->set('selectedCategoryIds', [$category->id])
        ->set('faqs', [
            ['question' => 'Can I use Suica in Tokyo?', 'answer' => '<p>Yes, Suica works on most urban rail and bus services.</p>', 'sort_order' => 1, 'is_enabled' => true],
        ])
        ->call('save')
        ->assertRedirect();

    $article = \App\Models\Article::where('slug', 'tokyo-rail-basics')->firstOrFail();

    $this->assertTrue($article->travelCategories()->whereKey($category->id)->exists());
    $this->assertDatabaseHas('article_faqs', [
        'article_id' => $article->id,
        'question' => 'Can I use Suica in Tokyo?',
        'is_enabled' => true,
    ]);
}
```

- [ ] **Step 2: Run the targeted test and verify it fails**

Run:

```bash
cd platform
php artisan test --filter=editor_can_attach_categories_and_faqs
```

Expected: failure because `ArticleForm` does not expose the new properties.

- [ ] **Step 3: Extend ArticleForm properties, mount, and rules**

Modify `platform/app/Livewire/Admin/Articles/ArticleForm.php` imports:

```php
use App\Models\TravelCategory;
```

Add public properties:

```php
public ?string $source_name = null;
public ?string $source_url = null;
public ?string $display_updated_at = null;
public ?int $reading_time_minutes = null;
public int $popularity_score = 0;
public bool $has_coupon = false;
public array $selectedCategoryIds = [];
public array $faqs = [];
```

In `mount()`, after existing SEO assignments, add:

```php
$this->source_name = $article->source_name;
$this->source_url = $article->source_url;
$this->display_updated_at = $article->display_updated_at?->format('Y-m-d H:i');
$this->reading_time_minutes = $article->reading_time_minutes;
$this->popularity_score = $article->popularity_score;
$this->has_coupon = $article->has_coupon;
$this->selectedCategoryIds = $article->travelCategories()->pluck('travel_categories.id')->all();
$this->faqs = $article->faqs()->get(['question', 'answer', 'sort_order', 'is_enabled'])->map(fn ($faq) => [
    'question' => $faq->question,
    'answer' => $faq->answer,
    'sort_order' => $faq->sort_order,
    'is_enabled' => $faq->is_enabled,
])->all();
```

Add rules:

```php
'source_name' => ['nullable', 'string', 'max:180'],
'source_url' => ['nullable', 'url', 'max:255'],
'display_updated_at' => ['nullable', 'date'],
'reading_time_minutes' => ['nullable', 'integer', 'min:1', 'max:999'],
'popularity_score' => ['integer', 'min:0', 'max:999999'],
'has_coupon' => ['boolean'],
'selectedCategoryIds' => ['array'],
'selectedCategoryIds.*' => ['integer', 'exists:travel_categories,id'],
'faqs' => ['array', 'max:20'],
'faqs.*.question' => ['required_with:faqs.*.answer', 'string', 'max:255'],
'faqs.*.answer' => ['required_with:faqs.*.question', 'string'],
'faqs.*.sort_order' => ['integer', 'min:0', 'max:999'],
'faqs.*.is_enabled' => ['boolean'],
```

- [ ] **Step 4: Sync categories and FAQs on save**

In `ArticleForm::save()`, before `Article::updateOrCreate`, remove `selectedCategoryIds` and `faqs` from the article column data:

```php
$categoryIds = $data['selectedCategoryIds'] ?? [];
$faqs = $data['faqs'] ?? [];
unset($data['selectedCategoryIds'], $data['faqs']);

if (($data['display_updated_at'] ?? null) === '') {
    $data['display_updated_at'] = null;
}
```

After `$article = Article::updateOrCreate(['id' => $this->articleId], $data);`, add:

```php
$article->travelCategories()->sync($categoryIds);

$article->faqs()->delete();
foreach ($faqs as $faq) {
    if (trim((string) ($faq['question'] ?? '')) === '' && trim((string) ($faq['answer'] ?? '')) === '') {
        continue;
    }

    $article->faqs()->create([
        'question' => $faq['question'],
        'answer' => $faq['answer'],
        'sort_order' => (int) ($faq['sort_order'] ?? 0),
        'is_enabled' => (bool) ($faq['is_enabled'] ?? true),
    ]);
}
```

Add methods:

```php
public function addFaq(): void
{
    $this->faqs[] = ['question' => '', 'answer' => '', 'sort_order' => count($this->faqs) + 1, 'is_enabled' => true];
}

public function removeFaq(int $index): void
{
    unset($this->faqs[$index]);
    $this->faqs = array_values($this->faqs);
}
```

Update `render()` to pass category options:

```php
return view('livewire.admin.articles.article-form', [
    'categoryOptions' => TravelCategory::query()->ordered()->get(),
])->layout('layouts.admin', ['title' => $this->articleId ? '编辑文章' : '新建文章']);
```

- [ ] **Step 5: Update article form Blade**

In `platform/resources/views/livewire/admin/articles/article-form.blade.php`, add controls inside the existing form:

```blade
<div class="grid gap-4 md:grid-cols-2">
    <label class="block text-sm">来源名称 <input wire:model="source_name" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">来源链接 <input wire:model="source_url" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">前台更新日期 <input type="datetime-local" wire:model="display_updated_at" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">阅读分钟数 <input type="number" wire:model="reading_time_minutes" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="block text-sm">热门分 <input type="number" wire:model="popularity_score" class="mt-1 w-full rounded border px-3 py-2"></label>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="has_coupon"> 有优惠/服务关联</label>
</div>

<fieldset class="rounded border p-4">
    <legend class="px-2 text-sm font-semibold">分类频道</legend>
    <div class="grid gap-2 md:grid-cols-3">
        @foreach($categoryOptions as $category)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="selectedCategoryIds" value="{{ $category->id }}">
                {{ $category->title }}
            </label>
        @endforeach
    </div>
</fieldset>

<fieldset class="rounded border p-4">
    <legend class="px-2 text-sm font-semibold">FAQ</legend>
    <div class="space-y-4">
        @foreach($faqs as $index => $faq)
            <div class="rounded border bg-slate-50 p-3">
                <label class="block text-sm">问题 <input wire:model="faqs.{{ $index }}.question" class="mt-1 w-full rounded border px-3 py-2"></label>
                <label class="mt-3 block text-sm">答案 <textarea wire:model="faqs.{{ $index }}.answer" rows="3" class="mt-1 w-full rounded border px-3 py-2"></textarea></label>
                <div class="mt-3 flex items-center gap-4">
                    <label class="text-sm">排序 <input type="number" wire:model="faqs.{{ $index }}.sort_order" class="ml-2 w-24 rounded border px-3 py-2"></label>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="faqs.{{ $index }}.is_enabled"> 启用</label>
                    <button type="button" wire:click="removeFaq({{ $index }})" class="text-sm text-red-700">删除</button>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" wire:click="addFaq" class="mt-4 rounded border px-3 py-2 text-sm">添加 FAQ</button>
</fieldset>
```

- [ ] **Step 6: Run article editor test**

Run:

```bash
cd platform
php artisan test --filter=editor_can_attach_categories_and_faqs
```

Expected: one passing test.

- [ ] **Step 7: Run full admin and content tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Admin tests/Feature/Content
```

Expected: all admin and content tests pass.

- [ ] **Step 8: Commit article editor enhancements**

Run:

```bash
git add platform/app/Livewire/Admin/Articles/ArticleForm.php platform/resources/views/livewire/admin/articles/article-form.blade.php platform/tests/Feature/Admin/ArticleAdminTest.php
git commit -m "feat: manage article categories and faqs"
```

Expected: commit succeeds.

---

### Task 5: Region And Category Public Routes

**Files:**
- Create: `platform/tests/Feature/Public/MediaPortalPublicTest.php`
- Create: `platform/app/Http/Controllers/Public/TravelCategoryController.php`
- Modify: `platform/app/Http/Controllers/Public/DestinationController.php`
- Create: `platform/resources/views/public/categories/show.blade.php`
- Create: `platform/resources/views/public/regions/index.blade.php`
- Create: `platform/resources/views/public/regions/show.blade.php`
- Modify: `platform/routes/web.php`

- [ ] **Step 1: Write failing public route tests**

Create `platform/tests/Feature/Public/MediaPortalPublicTest.php`:

```php
<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaPortalPublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_region_index_and_show_pages_render_channel_content(): void
    {
        $tokyo = Destination::factory()->create([
            'name' => 'Tokyo',
            'display_name' => 'Tokyo',
            'slug' => 'tokyo',
            'type' => 'region',
            'is_channel' => true,
            'sort_order' => 1,
            'seo_title' => 'Tokyo Travel Guide',
            'meta_description' => 'Plan Tokyo travel by neighborhood, train line, and season.',
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Tokyo Rail Basics',
            'slug' => 'tokyo-rail-basics',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);
        $article->destinations()->attach($tokyo);

        $this->get('/regions')
            ->assertOk()
            ->assertSee('Japan Regions')
            ->assertSee('Tokyo');

        $this->get('/regions/tokyo')
            ->assertOk()
            ->assertSee('Tokyo Travel Guide')
            ->assertSee('Tokyo Rail Basics');
    }

    public function test_category_page_renders_articles_and_seo_meta(): void
    {
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'slug' => 'transport',
            'seo_title' => 'Japan Transport Guides',
            'meta_description' => 'Rail, buses, passes, IC cards, and airport transfer advice.',
            'is_visible' => true,
            'is_indexable' => true,
        ]);
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Tokyo IC Card Basics',
            'slug' => 'tokyo-ic-card-basics',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
        ]);
        $article->travelCategories()->attach($category);

        $this->get('/categories/transport')
            ->assertOk()
            ->assertSee('Japan Transport Guides')
            ->assertSee('Tokyo IC Card Basics')
            ->assertSee('Rail, buses, passes', false);
    }
}
```

- [ ] **Step 2: Run public route tests and verify they fail**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalPublicTest
```

Expected: tests fail because `/regions` and `/categories/{category}` are missing.

- [ ] **Step 3: Add public routes**

Modify `platform/routes/web.php`:

```php
Route::get('/regions', [\App\Http\Controllers\Public\DestinationController::class, 'regions'])->name('regions.index');
Route::get('/regions/{destination:slug}', [\App\Http\Controllers\Public\DestinationController::class, 'region'])->name('regions.show');
Route::get('/categories/{category:slug}', [\App\Http\Controllers\Public\TravelCategoryController::class, 'show'])->name('categories.show');
```

Keep existing `/destinations` routes for compatibility.

- [ ] **Step 4: Add TravelCategoryController**

Create `platform/app/Http/Controllers/Public/TravelCategoryController.php`:

```php
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TravelCategory;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class TravelCategoryController extends Controller
{
    public function show(TravelCategory $category): View
    {
        abort_unless($category->is_visible, 404);

        $category->load(['children' => fn ($query) => $query->visible()->ordered()]);

        return view('public.categories.show', [
            'meta' => new MetaPayload(
                $category->seo_title ?: $category->title.' Japan Travel Guides',
                $category->meta_description,
                route('categories.show', $category),
                indexable: $category->is_indexable,
            ),
            'category' => $category,
            'articles' => $category->articles()
                ->published()
                ->latest('published_at')
                ->paginate(12),
        ]);
    }
}
```

- [ ] **Step 5: Extend DestinationController for regions**

Modify `platform/app/Http/Controllers/Public/DestinationController.php` by adding methods:

```php
public function regions(): View
{
    return view('public.regions.index', [
        'meta' => new MetaPayload('Japan Regions', 'Explore Japan by region, prefecture, city, and travel zone.', route('regions.index')),
        'regions' => Destination::query()->channel()->ordered()->get(),
    ]);
}

public function region(Destination $destination): View
{
    abort_unless($destination->is_channel && $destination->is_indexable, 404);

    $destination->load(['children' => fn ($query) => $query->ordered()]);

    return view('public.regions.show', [
        'meta' => new MetaPayload(
            $destination->seo_title ?: $destination->name.' Travel Guide',
            $destination->meta_description,
            route('regions.show', $destination),
            indexable: $destination->is_indexable,
        ),
        'destination' => $destination,
        'articles' => $destination->articles()
            ->published()
            ->latest('published_at')
            ->paginate(12),
    ]);
}
```

Confirm the controller imports include:

```php
use App\Models\Destination;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;
```

- [ ] **Step 6: Add region and category Blade views**

Create `platform/resources/views/public/regions/index.blade.php`:

```blade
@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-10">
        <h1 class="text-4xl font-bold">Japan Regions</h1>
        <p class="mt-3 max-w-2xl text-slate-600">Browse regional travel hubs for practical route planning, seasonal ideas, and destination guides.</p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($regions as $region)
                <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('regions.show', $region) }}">
                    <h2 class="font-semibold">{{ $region->display_name ?: $region->name }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $region->excerpt }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
```

Create `platform/resources/views/public/regions/show.blade.php`:

```blade
@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-10">
        <p class="text-sm font-semibold uppercase text-emerald-800">Region Guide</p>
        <h1 class="mt-2 text-4xl font-bold">{{ $destination->seo_title ?: ($destination->display_name ?: $destination->name).' Travel Guide' }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $destination->excerpt }}</p>

        @if($destination->children->isNotEmpty())
            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($destination->children as $child)
                    <a class="rounded border bg-white px-4 py-3 text-sm font-medium" href="{{ route('destinations.show', $child) }}">{{ $child->display_name ?: $child->name }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-10 grid gap-4 lg:grid-cols-3">
            @foreach($articles as $article)
                <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                    <h2 class="font-semibold">{{ $article->title }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection
```

Create `platform/resources/views/public/categories/show.blade.php`:

```blade
@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-10">
        <p class="text-sm font-semibold uppercase text-emerald-800">Travel Category</p>
        <h1 class="mt-2 text-4xl font-bold">{{ $category->seo_title ?: $category->title.' Japan Travel Guides' }}</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-600">{{ $category->meta_description ?: $category->excerpt }}</p>

        @if($category->children->isNotEmpty())
            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($category->children as $child)
                    <a class="rounded border bg-white px-4 py-3 text-sm font-medium" href="{{ route('categories.show', $child) }}">{{ $child->display_name ?: $child->title }}</a>
                @endforeach
            </div>
        @endif

        <div class="mt-10 grid gap-4 lg:grid-cols-3">
            @foreach($articles as $article)
                <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                    <h2 class="font-semibold">{{ $article->title }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $articles->links() }}</div>
    </section>
@endsection
```

- [ ] **Step 7: Run public route tests**

Run:

```bash
cd platform
php artisan test --filter=MediaPortalPublicTest
```

Expected: two passing tests.

- [ ] **Step 8: Run all public tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Public
```

Expected: all public tests pass.

- [ ] **Step 9: Commit public route work**

Run:

```bash
git add platform/app/Http/Controllers/Public platform/resources/views/public platform/routes/web.php platform/tests/Feature/Public/MediaPortalPublicTest.php
git commit -m "feat: add region and category channel pages"
```

Expected: commit succeeds.

---

### Task 6: Public Layout, Homepage Modules, Article FAQ Output, And Search Filters

**Files:**
- Create: `platform/tests/Feature/Public/SearchFilterTest.php`
- Modify: `platform/tests/Feature/Public/MediaPortalPublicTest.php`
- Modify: `platform/app/Http/Controllers/Public/HomeController.php`
- Modify: `platform/app/Http/Controllers/Public/ArticleController.php`
- Modify: `platform/app/Http/Controllers/Public/SearchController.php`
- Modify: `platform/resources/views/layouts/public.blade.php`
- Modify: `platform/resources/views/public/home.blade.php`
- Modify: `platform/resources/views/public/articles/show.blade.php`
- Modify: `platform/resources/views/public/search.blade.php`

- [ ] **Step 1: Add failing public layout and article FAQ tests**

Append these tests to `platform/tests/Feature/Public/MediaPortalPublicTest.php`:

```php
public function test_homepage_renders_service_region_category_and_module_data(): void
{
    \App\Models\ServiceLink::factory()->create([
        'label' => 'Rail Tickets',
        'type' => 'rail',
        'placement' => 'header',
        'url' => 'https://example.com/rail',
        'is_enabled' => true,
        'sort_order' => 1,
    ]);
    \App\Models\Destination::factory()->create([
        'name' => 'Kansai',
        'slug' => 'kansai',
        'is_channel' => true,
        'sort_order' => 1,
    ]);
    \App\Models\TravelCategory::factory()->create([
        'title' => 'Food',
        'slug' => 'food',
        'is_visible' => true,
        'sort_order' => 1,
    ]);
    \App\Models\HomepageModule::factory()->create([
        'placement_key' => 'home-featured',
        'type' => 'featured_articles',
        'title' => 'Featured Guides',
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Rail Tickets')
        ->assertSee('Kansai')
        ->assertSee('Food')
        ->assertSee('Featured Guides');
}

public function test_article_page_renders_faq_source_and_category_links(): void
{
    $article = \App\Models\Article::factory()->create([
        'author_id' => \App\Models\User::factory(),
        'title' => 'Tokyo Rail Basics',
        'slug' => 'tokyo-rail-basics',
        'status' => \App\Enums\ArticleStatus::Published,
        'published_at' => now(),
        'source_name' => 'Tokyo Metro',
        'source_url' => 'https://www.tokyometro.jp/en/',
    ]);
    $category = \App\Models\TravelCategory::factory()->create(['title' => 'Transport', 'slug' => 'transport']);
    $article->travelCategories()->attach($category);
    \App\Models\ArticleFaq::factory()->for($article)->create([
        'question' => 'Can I use Suica in Tokyo?',
        'answer' => '<p>Yes, for most short city trips.</p>',
        'is_enabled' => true,
        'sort_order' => 1,
    ]);

    $this->get(route('articles.show', $article))
        ->assertOk()
        ->assertSee('Tokyo Metro')
        ->assertSee('Transport')
        ->assertSee('Can I use Suica in Tokyo?')
        ->assertSee('application/ld+json', false);
}
```

- [ ] **Step 2: Add failing search filter tests**

Create `platform/tests/Feature/Public/SearchFilterTest.php`:

```php
<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\TravelCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_region_category_tag_coupon_and_sort(): void
    {
        $tokyo = Destination::factory()->create(['name' => 'Tokyo', 'slug' => 'tokyo', 'is_channel' => true]);
        $kyoto = Destination::factory()->create(['name' => 'Kyoto', 'slug' => 'kyoto', 'is_channel' => true]);
        $transport = TravelCategory::factory()->create(['title' => 'Transport', 'slug' => 'transport']);
        $food = TravelCategory::factory()->create(['title' => 'Food', 'slug' => 'food']);
        $rail = Tag::factory()->create(['name' => 'rail', 'slug' => 'rail']);

        $matching = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Tokyo Rail Discount Guide',
            'slug' => 'tokyo-rail-discount-guide',
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDay(),
            'popularity_score' => 200,
            'has_coupon' => true,
        ]);
        $matching->destinations()->attach($tokyo);
        $matching->travelCategories()->attach($transport);
        $matching->tags()->attach($rail);

        $nonMatching = Article::factory()->create([
            'author_id' => User::factory(),
            'title' => 'Kyoto Food Walk',
            'slug' => 'kyoto-food-walk',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'popularity_score' => 1,
            'has_coupon' => false,
        ]);
        $nonMatching->destinations()->attach($kyoto);
        $nonMatching->travelCategories()->attach($food);

        $this->get('/search?q=rail&region=tokyo&category=transport&tag=rail&coupon=1&sort=popular')
            ->assertOk()
            ->assertSee('Tokyo Rail Discount Guide')
            ->assertDontSee('Kyoto Food Walk');
    }
}
```

- [ ] **Step 3: Run tests and verify failures**

Run:

```bash
cd platform
php artisan test --filter=homepage_renders_service_region_category_and_module_data
php artisan test --filter=article_page_renders_faq_source_and_category_links
php artisan test --filter=SearchFilterTest
```

Expected: failures because controllers and views do not load or render the new data.

- [ ] **Step 4: Update HomeController**

Modify `platform/app/Http/Controllers/Public/HomeController.php` imports:

```php
use App\Models\HomepageModule;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
```

Update returned data:

```php
'serviceLinks' => ServiceLink::query()->enabled()->placement('header')->ordered()->get(),
'regionChannels' => Destination::query()->channel()->ordered()->limit(12)->get(),
'travelCategories' => TravelCategory::query()->visible()->ordered()->limit(12)->get(),
'homepageModules' => HomepageModule::query()->enabled()->ordered()->with(['items' => fn ($query) => $query->enabled()->ordered(), 'items.item'])->get(),
'popularArticles' => Article::published()->orderByDesc('popularity_score')->latest('published_at')->limit(6)->get(),
```

- [ ] **Step 5: Update ArticleController**

Modify `ArticleController::show()`:

```php
$article->load(['destinations', 'topics', 'tags', 'travelCategories', 'faqs']);
```

Pass FAQ structured data:

```php
'faqJsonLd' => $article->faqs->isEmpty() ? null : [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $article->faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq->question,
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => strip_tags($faq->answer),
        ],
    ])->values()->all(),
],
```

- [ ] **Step 6: Update SearchController filters**

Modify `platform/app/Http/Controllers/Public/SearchController.php` imports:

```php
use App\Models\Destination;
use App\Models\Tag;
use App\Models\TravelCategory;
```

Replace the query-building body with:

```php
$query = trim((string) $request->query('q', ''));
$region = trim((string) $request->query('region', ''));
$category = trim((string) $request->query('category', ''));
$tag = trim((string) $request->query('tag', ''));
$coupon = $request->boolean('coupon');
$sort = (string) $request->query('sort', 'newest');

$articles = Article::published()
    ->when($query !== '', fn ($builder) => $builder->where(function ($inner) use ($query): void {
        $inner->where('title', 'like', "%{$query}%")
            ->orWhere('excerpt', 'like', "%{$query}%");
    }))
    ->when($region !== '', fn ($builder) => $builder->whereHas('destinations', fn ($inner) => $inner->where('slug', $region)))
    ->when($category !== '', fn ($builder) => $builder->whereHas('travelCategories', fn ($inner) => $inner->where('slug', $category)))
    ->when($tag !== '', fn ($builder) => $builder->whereHas('tags', fn ($inner) => $inner->where('slug', $tag)))
    ->when($coupon, fn ($builder) => $builder->where('has_coupon', true))
    ->when($sort === 'updated', fn ($builder) => $builder->latest('display_updated_at'))
    ->when($sort === 'popular', fn ($builder) => $builder->orderByDesc('popularity_score'))
    ->when($sort === 'recommended', fn ($builder) => $builder->orderByDesc('has_coupon')->orderByDesc('popularity_score'))
    ->when(! in_array($sort, ['updated', 'popular', 'recommended'], true), fn ($builder) => $builder->latest('published_at'))
    ->paginate(12)
    ->withQueryString();
```

Update the `view()` data:

```php
'q' => $query,
'region' => $region,
'category' => $category,
'tag' => $tag,
'coupon' => $coupon,
'sort' => $sort,
'regions' => Destination::query()->channel()->ordered()->get(),
'categories' => TravelCategory::query()->visible()->ordered()->get(),
'tags' => Tag::query()->orderBy('name')->get(),
'articles' => $articles,
```

- [ ] **Step 7: Update public layout with dynamic navigation**

At the top of `platform/resources/views/layouts/public.blade.php`, add:

```blade
@php
    $layoutServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('header')->ordered()->get();
    $layoutRegions = \App\Models\Destination::query()->channel()->ordered()->limit(11)->get();
    $layoutCategories = \App\Models\TravelCategory::query()->visible()->ordered()->limit(8)->get();
@endphp
```

Replace the header with:

```blade
<header class="border-b bg-white">
    <div class="border-b bg-slate-950 text-white">
        <nav class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 py-2 text-xs font-semibold">
            <a href="{{ route('articles.index') }}">Guide</a>
            @foreach($layoutServiceLinks as $link)
                <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored" data-service-key="{{ $link->tracking_key }}">{{ $link->label }}</a>
            @endforeach
        </nav>
    </div>
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
        <a class="font-serif text-xl font-bold" href="{{ route('home') }}">Japan Travel Guide</a>
        <div class="flex gap-5 text-sm font-medium">
            <a href="{{ route('regions.index') }}">Regions</a>
            <a href="{{ route('articles.index') }}">Articles</a>
            <a href="{{ route('search') }}">Search</a>
        </div>
    </nav>
    <div class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 pb-3 text-sm">
        <a class="font-semibold" href="{{ route('regions.index') }}">National</a>
        @foreach($layoutRegions as $region)
            <a href="{{ route('regions.show', $region) }}">{{ $region->display_name ?: $region->name }}</a>
        @endforeach
    </div>
    <div class="border-t">
        <nav class="mx-auto flex max-w-6xl gap-4 overflow-x-auto px-5 py-3 text-sm font-medium">
            @foreach($layoutCategories as $category)
                <a href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
            @endforeach
        </nav>
    </div>
</header>
```

Add a footer before `</body>`:

```blade
<footer class="mt-16 border-t bg-white">
    <div class="mx-auto grid max-w-6xl gap-6 px-5 py-8 text-sm text-slate-600 md:grid-cols-3">
        <div>
            <p class="font-semibold text-slate-950">Japan Travel Guide</p>
            <p class="mt-2">Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('regions.index') }}">Regions</a>
            <a href="{{ route('articles.index') }}">Articles</a>
            <a href="{{ route('search') }}">Search</a>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach(\App\Models\ServiceLink::query()->enabled()->placement('footer')->ordered()->get() as $link)
                <a href="{{ $link->url }}" target="_blank" rel="nofollow noopener sponsored">{{ $link->label }}</a>
            @endforeach
        </div>
    </div>
</footer>
```

- [ ] **Step 8: Update homepage view**

Replace `platform/resources/views/public/home.blade.php` with:

```blade
@extends('layouts.public')

@section('content')
    <section class="mx-auto max-w-6xl px-5 py-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-800">Independent Japan Travel</p>
        <h1 class="mt-3 max-w-3xl text-5xl font-bold leading-tight">Plan Japan with regional guides, practical tools, and fresh travel ideas.</h1>
        <form action="{{ route('search') }}" class="mt-8 flex max-w-2xl gap-2">
            <input name="q" class="min-w-0 flex-1 rounded border px-4 py-3" placeholder="Search Tokyo rail, Kyoto food, Hokkaido winter">
            <button class="rounded bg-slate-950 px-5 py-3 font-semibold text-white">Search</button>
        </form>
    </section>

    <section class="mx-auto grid max-w-6xl gap-8 px-5 pb-14 lg:grid-cols-[1.1fr_.9fr]">
        <div class="space-y-10">
            <section>
                <h2 class="text-2xl font-semibold">Featured Guides</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($articles as $article)
                        <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                            <h3 class="font-semibold">{{ $article->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Popular Articles</h2>
                <div class="mt-5 grid gap-4">
                    @foreach($popularArticles as $article)
                        <a class="rounded border bg-white p-5 hover:border-emerald-700" href="{{ route('articles.show', $article) }}">
                            <h3 class="font-semibold">{{ $article->title }}</h3>
                            <p class="mt-2 text-sm text-slate-600">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
        <aside class="space-y-8">
            <section>
                <h2 class="text-2xl font-semibold">Regions</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($regionChannels as $destination)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('regions.show', $destination) }}">{{ $destination->display_name ?: $destination->name }}</a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Categories</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($travelCategories as $category)
                        <a class="rounded border bg-white px-4 py-3" href="{{ route('categories.show', $category) }}">{{ $category->display_name ?: $category->title }}</a>
                    @endforeach
                </div>
            </section>
            <section>
                <h2 class="text-2xl font-semibold">Travel Services</h2>
                <div class="mt-5 grid gap-3">
                    @foreach($serviceLinks as $link)
                        <a class="rounded border bg-white px-4 py-3" target="_blank" rel="nofollow noopener sponsored" href="{{ $link->url }}">{{ $link->label }}</a>
                    @endforeach
                </div>
            </section>
            <section>
                @foreach($homepageModules as $module)
                    <div class="mb-6 rounded border bg-white p-5">
                        <h2 class="text-xl font-semibold">{{ $module->title }}</h2>
                        @if($module->subtitle)<p class="mt-2 text-sm text-slate-600">{{ $module->subtitle }}</p>@endif
                    </div>
                @endforeach
            </section>
        </aside>
    </section>
@endsection
```

- [ ] **Step 9: Update article show view**

In `platform/resources/views/public/articles/show.blade.php`, after topic/tag links, render category links:

```blade
@foreach($article->travelCategories as $category)
    <a class="mr-3 underline" href="{{ route('categories.show', $category) }}">{{ $category->title }}</a>
@endforeach
```

After excerpt, render source and dates:

```blade
<div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600">
    @if($article->display_updated_at)<span>Updated {{ $article->display_updated_at->format('F j, Y') }}</span>@endif
    @if($article->reading_time_minutes)<span>{{ $article->reading_time_minutes }} min read</span>@endif
    @if($article->source_name)
        <a class="underline" href="{{ $article->source_url ?: '#' }}" target="_blank" rel="nofollow noopener">{{ $article->source_name }}</a>
    @endif
</div>
```

After body content, render FAQs and JSON-LD:

```blade
@if($article->faqs->isNotEmpty())
    <section class="mt-10 border-t pt-8">
        <h2 class="text-2xl font-semibold">FAQ</h2>
        <div class="mt-5 space-y-4">
            @foreach($article->faqs as $faq)
                <div class="rounded border bg-white p-5">
                    <h3 class="font-semibold">{{ $faq->question }}</h3>
                    <div class="content-prose mt-3 text-sm text-slate-700">{!! $faq->answer !!}</div>
                </div>
            @endforeach
        </div>
    </section>
@endif

@if($faqJsonLd)
    <script type="application/ld+json">{!! json_encode($faqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
```

- [ ] **Step 10: Update search view**

Replace the form in `platform/resources/views/public/search.blade.php` with:

```blade
<form method="get" action="{{ route('search') }}" class="grid gap-3 rounded border bg-white p-4 md:grid-cols-6">
    <input name="q" value="{{ $q }}" class="rounded border px-3 py-2 md:col-span-2" placeholder="Keyword">
    <select name="region" class="rounded border px-3 py-2">
        <option value="">All regions</option>
        @foreach($regions as $option)
            <option value="{{ $option->slug }}" @selected($region === $option->slug)>{{ $option->display_name ?: $option->name }}</option>
        @endforeach
    </select>
    <select name="category" class="rounded border px-3 py-2">
        <option value="">All categories</option>
        @foreach($categories as $option)
            <option value="{{ $option->slug }}" @selected($category === $option->slug)>{{ $option->display_name ?: $option->title }}</option>
        @endforeach
    </select>
    <select name="tag" class="rounded border px-3 py-2">
        <option value="">All tags</option>
        @foreach($tags as $option)
            <option value="{{ $option->slug }}" @selected($tag === $option->slug)>#{{ $option->name }}</option>
        @endforeach
    </select>
    <select name="sort" class="rounded border px-3 py-2">
        <option value="newest" @selected($sort === 'newest')>Newest</option>
        <option value="updated" @selected($sort === 'updated')>Updated</option>
        <option value="popular" @selected($sort === 'popular')>Popular</option>
        <option value="recommended" @selected($sort === 'recommended')>Recommended</option>
    </select>
    <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" name="coupon" value="1" @checked($coupon)> Coupon/service available</label>
    <button class="rounded bg-slate-950 px-4 py-2 font-semibold text-white md:col-span-1">Search</button>
</form>
```

Keep the existing article results loop and pagination below the form.

- [ ] **Step 11: Run targeted public tests**

Run:

```bash
cd platform
php artisan test --filter=homepage_renders_service_region_category_and_module_data
php artisan test --filter=article_page_renders_faq_source_and_category_links
php artisan test --filter=SearchFilterTest
```

Expected: all targeted tests pass.

- [ ] **Step 12: Run full public tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Public
```

Expected: all public tests pass.

- [ ] **Step 13: Commit public portal redesign**

Run:

```bash
git add platform/app/Http/Controllers/Public platform/resources/views/layouts/public.blade.php platform/resources/views/public platform/tests/Feature/Public
git commit -m "feat: redesign public media portal surfaces"
```

Expected: commit succeeds.

---

### Task 7: Sitemap Coverage And SEO Verification

**Files:**
- Modify: `platform/tests/Feature/Seo/SitemapTest.php`
- Modify: `platform/app/Services/Seo/SitemapBuilder.php`

- [ ] **Step 1: Add failing sitemap test for regions and categories**

Append this test to `platform/tests/Feature/Seo/SitemapTest.php`:

```php
public function test_sitemap_includes_region_and_category_channels(): void
{
    \App\Models\Destination::factory()->create([
        'name' => 'Tokyo',
        'slug' => 'tokyo',
        'is_channel' => true,
        'is_indexable' => true,
    ]);
    \App\Models\TravelCategory::factory()->create([
        'title' => 'Transport',
        'slug' => 'transport',
        'is_visible' => true,
        'is_indexable' => true,
    ]);
    \App\Models\ServiceLink::factory()->create([
        'label' => 'Rail Tickets',
        'url' => 'https://example.com/rail',
        'is_enabled' => true,
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/regions/tokyo')
        ->assertSee('/categories/transport')
        ->assertDontSee('example.com/rail');
}
```

- [ ] **Step 2: Run sitemap test and verify it fails**

Run:

```bash
cd platform
php artisan test --filter=sitemap_includes_region_and_category_channels
```

Expected: failure because sitemap still emits `/destinations/tokyo` and does not include categories.

- [ ] **Step 3: Update SitemapBuilder imports**

Modify `platform/app/Services/Seo/SitemapBuilder.php` imports:

```php
use App\Models\TravelCategory;
```

- [ ] **Step 4: Merge category URLs into the sitemap**

In `build()`, add category URLs after destination URLs:

```php
->merge($this->categoryUrls())
```

Replace `destinationUrls()` with region channel URLs:

```php
private function destinationUrls(): Collection
{
    return Destination::query()
        ->where('is_indexable', true)
        ->where('is_channel', true)
        ->get()
        ->map(fn (Destination $destination) => [
            'loc' => route('regions.show', $destination),
            'lastmod' => $destination->updated_at?->toAtomString(),
        ]);
}
```

Add `categoryUrls()`:

```php
private function categoryUrls(): Collection
{
    return TravelCategory::query()
        ->where('is_indexable', true)
        ->where('is_visible', true)
        ->get()
        ->map(fn (TravelCategory $category) => [
            'loc' => route('categories.show', $category),
            'lastmod' => $category->updated_at?->toAtomString(),
        ]);
}
```

- [ ] **Step 5: Run sitemap tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Seo/SitemapTest.php
```

Expected: sitemap tests pass.

- [ ] **Step 6: Run all SEO tests**

Run:

```bash
cd platform
php artisan test tests/Feature/Seo
```

Expected: all SEO tests pass.

- [ ] **Step 7: Commit sitemap work**

Run:

```bash
git add platform/app/Services/Seo/SitemapBuilder.php platform/tests/Feature/Seo/SitemapTest.php
git commit -m "feat: include media channels in sitemap"
```

Expected: commit succeeds.

---

### Task 8: Demo Seed Data For Phase 1

**Files:**
- Create: `platform/tests/Feature/Seeders/DemoContentSeederTest.php`
- Modify: `platform/database/seeders/DemoContentSeeder.php`

- [ ] **Step 1: Write failing seed integrity test**

Create `platform/tests/Feature/Seeders/DemoContentSeederTest.php`:

```php
<?php

namespace Tests\Feature\Seeders;

use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_contains_phase_one_media_portal_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(8, TravelCategory::count());
        $this->assertGreaterThanOrEqual(8, Destination::query()->where('is_channel', true)->count());
        $this->assertGreaterThanOrEqual(5, ServiceLink::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(3, HomepageModule::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(2, ArticleFaq::query()->where('is_enabled', true)->count());
    }
}
```

- [ ] **Step 2: Run seed test and verify it fails**

Run:

```bash
cd platform
php artisan test --filter=DemoContentSeederTest
```

Expected: failure because seed data does not yet include enough Phase 1 records.

- [ ] **Step 3: Import new models in DemoContentSeeder**

Modify `platform/database/seeders/DemoContentSeeder.php` imports:

```php
use App\Models\HomepageModule;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
```

- [ ] **Step 4: Seed region channels**

In `DemoContentSeeder::run()`, update existing destination seed arrays with these exact channel fields:

```php
// Tokyo updateOrCreate data
'display_name' => 'Tokyo',
'is_channel' => true,
'sort_order' => 3,

// Kyoto updateOrCreate data
'display_name' => 'Kyoto',
'is_channel' => true,
'sort_order' => 6,

// Hokkaido updateOrCreate data
'display_name' => 'Hokkaido',
'is_channel' => true,
'sort_order' => 1,
```

Then add these additional region channels with `updateOrCreate`:

```php
$regionRows = [
    ['slug' => 'tohoku', 'name' => 'Tohoku', 'type' => 'region', 'sort_order' => 2],
    ['slug' => 'hokuriku', 'name' => 'Hokuriku', 'type' => 'region', 'sort_order' => 4],
    ['slug' => 'chubu', 'name' => 'Chubu', 'type' => 'region', 'sort_order' => 5],
    ['slug' => 'kansai', 'name' => 'Kansai', 'type' => 'region', 'sort_order' => 6],
    ['slug' => 'chugoku', 'name' => 'Chugoku', 'type' => 'region', 'sort_order' => 7],
    ['slug' => 'shikoku', 'name' => 'Shikoku', 'type' => 'region', 'sort_order' => 8],
    ['slug' => 'kyushu', 'name' => 'Kyushu', 'type' => 'region', 'sort_order' => 9],
    ['slug' => 'okinawa', 'name' => 'Okinawa', 'type' => 'region', 'sort_order' => 10],
];

foreach ($regionRows as $row) {
    Destination::updateOrCreate(
        ['slug' => $row['slug']],
        [
            'name' => $row['name'],
            'display_name' => $row['name'],
            'type' => $row['type'],
            'excerpt' => $row['name'].' travel guides, regional routes, seasonal ideas, and practical planning notes.',
            'body' => '<p>'.$row['name'].' works as a regional planning hub for first-time and repeat Japan travelers.</p>',
            'seo_title' => $row['name'].' Travel Guide',
            'meta_description' => 'Plan '.$row['name'].' travel with regional routes, transport tips, food ideas, and seasonal notes.',
            'is_indexable' => true,
            'is_channel' => true,
            'sort_order' => $row['sort_order'],
        ]
    );
}
```

- [ ] **Step 5: Seed travel categories**

Add:

```php
$categoryRows = [
    ['slug' => 'guide', 'title' => 'Guide', 'sort_order' => 1],
    ['slug' => 'things-to-do', 'title' => 'Things to Do', 'sort_order' => 2],
    ['slug' => 'food', 'title' => 'Food', 'sort_order' => 3],
    ['slug' => 'shopping', 'title' => 'Shopping', 'sort_order' => 4],
    ['slug' => 'lodging', 'title' => 'Lodging', 'sort_order' => 5],
    ['slug' => 'itinerary', 'title' => 'Itinerary', 'sort_order' => 6],
    ['slug' => 'transport', 'title' => 'Transport', 'sort_order' => 7],
    ['slug' => 'basics', 'title' => 'Basics', 'sort_order' => 8],
];

$categories = collect();
foreach ($categoryRows as $row) {
    $categories->put($row['slug'], TravelCategory::updateOrCreate(
        ['slug' => $row['slug']],
        [
            'title' => $row['title'],
            'display_name' => $row['title'],
            'excerpt' => $row['title'].' travel articles for Japan trip planning.',
            'body' => '<p>'.$row['title'].' articles help travelers make practical decisions before and during a Japan trip.</p>',
            'seo_title' => 'Japan '.$row['title'].' Travel Guides',
            'meta_description' => 'Read Japan '.$row['title'].' guides with practical planning notes for English-speaking travelers.',
            'is_indexable' => true,
            'is_visible' => true,
            'sort_order' => $row['sort_order'],
        ]
    ));
}
```

Attach categories to existing seeded articles:

```php
$kyotoArticle->travelCategories()->syncWithoutDetaching([$categories['itinerary']->id, $categories['basics']->id]);
$tokyoArticle->travelCategories()->syncWithoutDetaching([$categories['guide']->id, $categories['transport']->id]);
```

- [ ] **Step 6: Seed service links, modules, and FAQs**

Add:

```php
$serviceRows = [
    ['type' => 'activity', 'label' => 'Activities', 'url' => 'https://example.com/activities', 'sort_order' => 1],
    ['type' => 'hotel', 'label' => 'Hotels', 'url' => 'https://example.com/hotels', 'sort_order' => 2],
    ['type' => 'flight', 'label' => 'Flights', 'url' => 'https://example.com/flights', 'sort_order' => 3],
    ['type' => 'rail', 'label' => 'Rail Tickets', 'url' => 'https://example.com/rail', 'sort_order' => 4],
    ['type' => 'shop', 'label' => 'Shop', 'url' => 'https://example.com/shop', 'sort_order' => 5],
    ['type' => 'exchange_rate', 'label' => 'Exchange Rate', 'url' => 'https://example.com/exchange-rate', 'sort_order' => 6],
    ['type' => 'advertising', 'label' => 'Advertise', 'url' => 'https://example.com/advertising', 'sort_order' => 7],
];

foreach ($serviceRows as $row) {
    ServiceLink::updateOrCreate(
        ['tracking_key' => $row['type']],
        [
            'type' => $row['type'],
            'label' => $row['label'],
            'url' => $row['url'],
            'placement' => 'header',
            'notes' => 'Demo service entry for Phase 1.',
            'is_enabled' => true,
            'sort_order' => $row['sort_order'],
        ]
    );
}

foreach ([
    ['placement_key' => 'home-featured', 'type' => 'featured_articles', 'title' => 'Featured Guides', 'sort_order' => 1],
    ['placement_key' => 'home-regions', 'type' => 'region_grid', 'title' => 'Explore by Region', 'sort_order' => 2],
    ['placement_key' => 'home-tools', 'type' => 'travel_tools', 'title' => 'Travel Tools', 'sort_order' => 3],
] as $module) {
    HomepageModule::updateOrCreate(
        ['placement_key' => $module['placement_key']],
        [
            'type' => $module['type'],
            'title' => $module['title'],
            'subtitle' => 'Curated module managed from the Chinese admin.',
            'is_enabled' => true,
            'sort_order' => $module['sort_order'],
        ]
    );
}

$tokyoArticle->faqs()->updateOrCreate(
    ['question' => 'Can I use IC cards for most Tokyo trains?'],
    ['answer' => '<p>Yes. IC cards work for most short urban rail and bus trips in Tokyo.</p>', 'sort_order' => 1, 'is_enabled' => true]
);
$kyotoArticle->faqs()->updateOrCreate(
    ['question' => 'Is three days enough for a first Kyoto trip?'],
    ['answer' => '<p>Three days is enough for a first route if you group sights by area and start early.</p>', 'sort_order' => 1, 'is_enabled' => true]
);
```

- [ ] **Step 7: Run seed test**

Run:

```bash
cd platform
php artisan test --filter=DemoContentSeederTest
```

Expected: one passing test.

- [ ] **Step 8: Run migration and seed locally**

Run:

```bash
cd platform
php artisan migrate:fresh --seed
```

Expected: migrations complete and seeders finish without exceptions.

- [ ] **Step 9: Commit seed data**

Run:

```bash
git add platform/database/seeders/DemoContentSeeder.php platform/tests/Feature/Seeders/DemoContentSeederTest.php
git commit -m "feat: seed media portal demo content"
```

Expected: commit succeeds.

---

### Task 9: Verification, Browser Smoke, And Build

**Files:**
- Verify only: `platform/`

- [ ] **Step 1: Run full automated tests**

Run:

```bash
cd platform
php artisan test
```

Expected: all tests pass.

- [ ] **Step 2: Run frontend asset build**

Run:

```bash
cd platform
npm run build
```

Expected: Vite build succeeds and `platform/public/build` is generated.

- [ ] **Step 3: Start local server if one is not already running**

Run:

```bash
cd platform
php artisan serve --host=127.0.0.1 --port=63838
```

Expected: server starts at `http://127.0.0.1:63838`. If that port is already used by the current app, reuse the running server.

- [ ] **Step 4: HTTP smoke test key public pages**

Run in another shell:

```bash
curl -I http://127.0.0.1:63838/
curl -I http://127.0.0.1:63838/regions
curl -I http://127.0.0.1:63838/regions/tokyo
curl -I http://127.0.0.1:63838/categories/transport
curl -I 'http://127.0.0.1:63838/search?q=rail&region=tokyo&category=transport'
curl -I http://127.0.0.1:63838/sitemap.xml
```

Expected: each response is `HTTP/1.1 200 OK`.

- [ ] **Step 5: HTTP smoke test admin protection**

Run:

```bash
curl -I http://127.0.0.1:63838/admin/travel-categories
curl -I http://127.0.0.1:63838/admin/service-links
curl -I http://127.0.0.1:63838/admin/homepage-modules
```

Expected: each response redirects to `/login` for a guest.

- [ ] **Step 6: Inspect git status**

Run:

```bash
git status --short
```

Expected: only ignored runtime artifacts may exist; the root `index.html` remains untracked and untouched.

- [ ] **Step 7: Final Phase 1 commit if verification changed tracked files**

If build or verification updated tracked files, commit them:

```bash
git add platform/public/build
git commit -m "build: compile media portal assets"
```

Expected: commit succeeds only when tracked build artifacts are intentionally included. If `platform/public/build` is ignored, skip this commit.

---

## Self-Review Checklist

- Spec coverage: Phase 1 acceptance maps to Tasks 1-9. Service bar is covered by `ServiceLink` and layout rendering. Region navigation is covered by destination channel fields, destination admin editing, and `/regions` routes. Category navigation is covered by `TravelCategory` and `/categories/{category}`. Home modules are covered by `HomepageModule`. FAQ is covered by `ArticleFaq`, admin editing, article rendering, and FAQ JSON-LD. Enhanced search filters are covered by `SearchFilterTest`. Chinese admin CRUD is covered by `MediaPortalAdminTest`. SEO output is covered by sitemap and public meta tests. Seed integrity is covered by `DemoContentSeederTest`.
- Deferred scope: coupons, partners, member pages, activity products, service click history, and SEO reporting belong to Phases 2-5 and are not implemented here.
- Placeholder scan: no task relies on unspecified work; each new file path, command, validation rule, and acceptance command is listed.
- Type consistency: model names, route names, relationship names, and test names are consistent across tasks: `TravelCategory`, `ServiceLink`, `HomepageModule`, `HomepageModuleItem`, `ArticleFaq`, `travelCategories()`, `faqs()`, `regions.show`, and `categories.show`.

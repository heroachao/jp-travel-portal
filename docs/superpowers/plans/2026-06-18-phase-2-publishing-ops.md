# Phase 2 Publishing Operations Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Phase 2 publishing operations layer: media library, article image/SEO controls, site-level analytics/ad settings, and safe public rendering.

**Architecture:** Reuse the existing Laravel 13 + Livewire admin patterns. Keep public pages English and admin screens Chinese. Add one database-backed single-row site settings model, then route public analytics/ad rendering through that service.

**Tech Stack:** Laravel, Livewire, Eloquent, Blade, PHPUnit feature tests, existing Tailwind/Vite assets, SQLite test database.

---

## File Structure

Create:

- `platform/database/migrations/2026_06_18_130000_create_site_settings_table.php` - single-row site settings schema.
- `platform/app/Models/SiteSetting.php` - fillable settings model with typed boolean casts.
- `platform/app/Services/Settings/SiteSettings.php` - accessor service that returns default settings when the row does not exist.
- `platform/app/Livewire/Admin/Media/MediaAssetIndex.php` - Chinese media library upload/list/delete component.
- `platform/resources/views/livewire/admin/media/media-asset-index.blade.php` - media library admin view.
- `platform/app/Livewire/Admin/Settings/SiteSettingsForm.php` - Chinese site settings form.
- `platform/resources/views/livewire/admin/settings/site-settings-form.blade.php` - site settings admin view.
- `platform/tests/Feature/Admin/MediaLibraryAdminTest.php` - media library admin coverage.
- `platform/tests/Feature/Admin/SiteSettingsAdminTest.php` - site settings validation coverage.
- `platform/tests/Feature/Public/SiteSettingsRenderingTest.php` - analytics/ad public rendering coverage.

Modify:

- `platform/routes/web.php` - add admin routes for media and site settings.
- `platform/resources/views/layouts/admin.blade.php` - add Chinese navigation links.
- `platform/resources/views/layouts/public.blade.php` - render brand defaults, GA4, and AdSense bootstrap only when enabled.
- `platform/app/Livewire/Admin/Articles/ArticleForm.php` - persist `cover_media_id`, `og_media_id`, and provide SEO preview data.
- `platform/resources/views/livewire/admin/articles/article-form.blade.php` - add media selectors, previews, and SEO preview panel.
- `platform/app/Http/Controllers/Public/ArticleController.php` - load media relationships and pass OG image URL.
- `platform/resources/views/public/articles/show.blade.php` - render cover image on article pages.
- `platform/app/Services/Ads/AdRenderer.php` - gate placement rendering through site-level ads settings.
- `platform/app/Livewire/Admin/Ads/AdPlacementIndex.php` - keep new placements disabled by default and store notes.
- `platform/resources/views/livewire/admin/ads/ad-placement-index.blade.php` - improve Chinese helper text and notes field.
- `platform/database/factories/ArticleFactory.php` - optional media IDs remain nullable; no default image needed.
- `platform/database/factories/AdPlacementFactory.php` - default `is_enabled` should become `false`.
- `platform/database/seeders/DemoContentSeeder.php` - seed default disabled site settings and keep demo ad placements deterministic if needed.
- `docs/deployment/laravel-platform.md` - document site settings, analytics, ads, and storage link steps.

---

### Task 1: Site Settings Schema, Model, Service

**Files:**
- Create: `platform/database/migrations/2026_06_18_130000_create_site_settings_table.php`
- Create: `platform/app/Models/SiteSetting.php`
- Create: `platform/app/Services/Settings/SiteSettings.php`
- Create: `platform/tests/Feature/Admin/SiteSettingsAdminTest.php`

- [ ] **Step 1: Write failing test for settings defaults**

Create `platform/tests/Feature/Admin/SiteSettingsAdminTest.php`:

```php
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
```

- [ ] **Step 2: Run the failing settings tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/SiteSettingsAdminTest.php
```

Expected: FAIL because `SiteSettings` and `SiteSetting` do not exist.

- [ ] **Step 3: Add migration**

Create `platform/database/migrations/2026_06_18_130000_create_site_settings_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_name')->default('Japan Travel Guide');
            $table->string('seo_title_suffix')->default('Japan Travel Guide');
            $table->text('default_meta_description')->nullable();
            $table->string('ga4_measurement_id')->nullable();
            $table->string('adsense_publisher_id')->nullable();
            $table->boolean('analytics_enabled')->default(false);
            $table->boolean('ads_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
```

- [ ] **Step 4: Add model**

Create `platform/app/Models/SiteSetting.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'seo_title_suffix',
        'default_meta_description',
        'ga4_measurement_id',
        'adsense_publisher_id',
        'analytics_enabled',
        'ads_enabled',
    ];

    protected function casts(): array
    {
        return [
            'analytics_enabled' => 'boolean',
            'ads_enabled' => 'boolean',
        ];
    }
}
```

- [ ] **Step 5: Add accessor service**

Create `platform/app/Services/Settings/SiteSettings.php`:

```php
<?php

namespace App\Services\Settings;

use App\Models\SiteSetting;

class SiteSettings
{
    public function current(): SiteSetting
    {
        return SiteSetting::query()->find(1) ?? new SiteSetting([
            'site_name' => 'Japan Travel Guide',
            'seo_title_suffix' => 'Japan Travel Guide',
            'default_meta_description' => 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.',
            'analytics_enabled' => false,
            'ads_enabled' => false,
        ]);
    }

    public function update(array $data): SiteSetting
    {
        return SiteSetting::query()->updateOrCreate(['id' => 1], $data);
    }
}
```

- [ ] **Step 6: Run migration and service-focused test expectation**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan migrate:fresh
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/SiteSettingsAdminTest.php --filter=service
```

Expected: service test PASS after model/service exist.

- [ ] **Step 7: Commit**

```bash
git add platform/database/migrations/2026_06_18_130000_create_site_settings_table.php platform/app/Models/SiteSetting.php platform/app/Services/Settings/SiteSettings.php platform/tests/Feature/Admin/SiteSettingsAdminTest.php
git commit -m "feat: add site settings foundation"
```

---

### Task 2: Site Settings Admin Screen

**Files:**
- Create: `platform/app/Livewire/Admin/Settings/SiteSettingsForm.php`
- Create: `platform/resources/views/livewire/admin/settings/site-settings-form.blade.php`
- Modify: `platform/routes/web.php`
- Modify: `platform/resources/views/layouts/admin.blade.php`
- Test: `platform/tests/Feature/Admin/SiteSettingsAdminTest.php`

- [ ] **Step 1: Extend failing tests for route access and validation**

Add these imports to `SiteSettingsAdminTest`:

```php
use App\Livewire\Admin\Settings\SiteSettingsForm;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
```

Then append these tests:

```php
public function test_admin_can_save_public_analytics_and_ads_settings(): void
{
    $this->seed(RoleSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('admin');
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
    $admin->assignRole('admin');
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
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('站点设置');
}
```

- [ ] **Step 2: Run the failing route test**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/SiteSettingsAdminTest.php --filter=site_settings_page
```

Expected: FAIL because `admin.settings.index` route does not exist.

- [ ] **Step 3: Create Livewire component**

Create `platform/app/Livewire/Admin/Settings/SiteSettingsForm.php`:

```php
<?php

namespace App\Livewire\Admin\Settings;

use App\Services\Settings\SiteSettings;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SiteSettingsForm extends Component
{
    public string $site_name = 'Japan Travel Guide';
    public string $seo_title_suffix = 'Japan Travel Guide';
    public ?string $default_meta_description = null;
    public ?string $ga4_measurement_id = null;
    public ?string $adsense_publisher_id = null;
    public bool $analytics_enabled = false;
    public bool $ads_enabled = false;

    public function mount(SiteSettings $settings): void
    {
        $current = $settings->current();

        $this->site_name = $current->site_name;
        $this->seo_title_suffix = $current->seo_title_suffix;
        $this->default_meta_description = $current->default_meta_description;
        $this->ga4_measurement_id = $current->ga4_measurement_id;
        $this->adsense_publisher_id = $current->adsense_publisher_id;
        $this->analytics_enabled = $current->analytics_enabled;
        $this->ads_enabled = $current->ads_enabled;
    }

    public function save(SiteSettings $settings): void
    {
        $data = $this->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'seo_title_suffix' => ['required', 'string', 'max:120'],
            'default_meta_description' => ['nullable', 'string', 'max:260'],
            'ga4_measurement_id' => ['nullable', 'regex:/^G-[A-Z0-9]{8,16}$/'],
            'adsense_publisher_id' => ['nullable', 'regex:/^ca-pub-[0-9]{16}$/'],
            'analytics_enabled' => ['boolean'],
            'ads_enabled' => ['boolean'],
        ]);

        $settings->update($data);
        session()->flash('status', '站点设置已保存');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site-settings-form')
            ->layout('layouts.admin', ['title' => '站点设置']);
    }
}
```

- [ ] **Step 4: Create Chinese admin view**

Create `platform/resources/views/livewire/admin/settings/site-settings-form.blade.php`:

```blade
<section>
    <h1 class="text-2xl font-semibold">站点设置</h1>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <form wire:submit="save" class="mt-6 grid gap-5 rounded-lg border bg-white p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="site_name">站点名称</label>
                <input id="site_name" wire:model="site_name" class="mt-2 w-full rounded border px-3 py-2">
                @error('site_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="seo_title_suffix">SEO 标题后缀</label>
                <input id="seo_title_suffix" wire:model="seo_title_suffix" class="mt-2 w-full rounded border px-3 py-2">
                @error('seo_title_suffix')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="default_meta_description">默认 Meta Description</label>
            <textarea id="default_meta_description" wire:model="default_meta_description" rows="3" class="mt-2 w-full rounded border px-3 py-2"></textarea>
            @error('default_meta_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 border-t pt-5 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium" for="ga4_measurement_id">GA4 Measurement ID</label>
                <input id="ga4_measurement_id" wire:model="ga4_measurement_id" placeholder="G-ABC123DEF4" class="mt-2 w-full rounded border px-3 py-2">
                @error('ga4_measurement_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="adsense_publisher_id">AdSense Publisher ID</label>
                <input id="adsense_publisher_id" wire:model="adsense_publisher_id" placeholder="ca-pub-1234567890123456" class="mt-2 w-full rounded border px-3 py-2">
                @error('adsense_publisher_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-3 rounded border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="analytics_enabled">
                启用 Google Analytics 输出
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="ads_enabled">
                启用 Google AdSense 与广告位输出
            </label>
            <p>本地开发可以先保存 ID 但不启用。只有开关启用且 ID 存在时，前台才会输出对应脚本。</p>
        </div>

        <div>
            <button type="submit" class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">保存设置</button>
        </div>
    </form>
</section>
```

- [ ] **Step 5: Add route and nav**

Modify `platform/routes/web.php` inside the `admin` group:

```php
Route::get('/media', \App\Livewire\Admin\Media\MediaAssetIndex::class)->name('media.index');
Route::get('/settings', \App\Livewire\Admin\Settings\SiteSettingsForm::class)->name('settings.index');
```

For this task, add only `/settings`; `/media` is added in Task 4 if you prefer strict commit isolation.

Modify `platform/resources/views/layouts/admin.blade.php` navigation:

```blade
<a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.settings.index') }}">站点设置</a>
```

- [ ] **Step 6: Run settings tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/SiteSettingsAdminTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add platform/app/Livewire/Admin/Settings/SiteSettingsForm.php platform/resources/views/livewire/admin/settings/site-settings-form.blade.php platform/routes/web.php platform/resources/views/layouts/admin.blade.php platform/tests/Feature/Admin/SiteSettingsAdminTest.php
git commit -m "feat: manage site settings"
```

---

### Task 3: Public Analytics And Ad Gating

**Files:**
- Modify: `platform/resources/views/layouts/public.blade.php`
- Modify: `platform/app/Services/Ads/AdRenderer.php`
- Modify: `platform/tests/Feature/Public/AdRenderingTest.php`
- Create: `platform/tests/Feature/Public/SiteSettingsRenderingTest.php`

- [ ] **Step 1: Write failing public rendering tests**

Create `platform/tests/Feature/Public/SiteSettingsRenderingTest.php`:

```php
<?php

namespace Tests\Feature\Public;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_does_not_render_google_scripts_by_default(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false)
            ->assertDontSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', false);
    }

    public function test_public_layout_renders_ga4_only_when_enabled_with_id(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Travel Guide',
            'seo_title_suffix' => 'Japan Travel Guide',
            'ga4_measurement_id' => 'G-ABC123DEF4',
            'analytics_enabled' => true,
            'ads_enabled' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://www.googletagmanager.com/gtag/js?id=G-ABC123DEF4', false)
            ->assertSee("gtag('config', 'G-ABC123DEF4')", false)
            ->assertDontSee('adsbygoogle.js', false);
    }

    public function test_public_layout_renders_adsense_bootstrap_only_when_enabled_with_id(): void
    {
        SiteSetting::query()->create([
            'id' => 1,
            'site_name' => 'Japan Travel Guide',
            'seo_title_suffix' => 'Japan Travel Guide',
            'adsense_publisher_id' => 'ca-pub-1234567890123456',
            'analytics_enabled' => false,
            'ads_enabled' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1234567890123456', false)
            ->assertSee('crossorigin="anonymous"', false)
            ->assertDontSee('googletagmanager.com/gtag/js', false);
    }
}
```

Append to `AdRenderingTest`:

```php
public function test_site_level_ads_toggle_blocks_enabled_placement(): void
{
    \App\Models\SiteSetting::query()->create([
        'id' => 1,
        'site_name' => 'Japan Travel Guide',
        'seo_title_suffix' => 'Japan Travel Guide',
        'ads_enabled' => false,
    ]);

    AdPlacement::factory()->create([
        'key' => 'article-body-middle',
        'code' => '<ins class="adsbygoogle"></ins>',
        'is_enabled' => true,
    ]);

    $article = Article::factory()->create([
        'author_id' => User::factory(),
        'slug' => 'site-toggle-blocks-ad',
        'status' => ArticleStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('articles.show', $article))
        ->assertOk()
        ->assertDontSee('data-ad-key="article-body-middle"', false);
}
```

Update the existing `test_enabled_ad_placement_renders_in_article` to create a site settings row with `ads_enabled => true`.

- [ ] **Step 2: Run failing public rendering tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Public/SiteSettingsRenderingTest.php tests/Feature/Public/AdRenderingTest.php
```

Expected: FAIL because layout and `AdRenderer` do not use site settings.

- [ ] **Step 3: Inject site settings in public layout**

Modify the top of `platform/resources/views/layouts/public.blade.php`:

```blade
@php
    $siteSettings = app(\App\Services\Settings\SiteSettings::class)->current();
    $layoutHeaderServiceLinks = \App\Models\ServiceLink::query()->enabled()->placement('header')->ordered()->get();
@endphp
```

In `<head>`, after existing OG tags and before `@vite`, add:

```blade
@if($siteSettings->analytics_enabled && filled($siteSettings->ga4_measurement_id))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings->ga4_measurement_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $siteSettings->ga4_measurement_id }}');
    </script>
@endif
@if($siteSettings->ads_enabled && filled($siteSettings->adsense_publisher_id))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $siteSettings->adsense_publisher_id }}" crossorigin="anonymous"></script>
@endif
```

Use `$siteSettings->site_name` for the header/footer brand text if it remains visually compatible:

```blade
{{ $siteSettings->site_name }}
```

- [ ] **Step 4: Gate `AdRenderer` through site settings**

Modify `platform/app/Services/Ads/AdRenderer.php`:

```php
<?php

namespace App\Services\Ads;

use App\Models\AdPlacement;
use App\Services\Settings\SiteSettings;
use Illuminate\Support\HtmlString;

class AdRenderer
{
    public function __construct(private readonly SiteSettings $siteSettings) {}

    public function render(string $key): HtmlString
    {
        $settings = $this->siteSettings->current();

        if (! $settings->ads_enabled) {
            return new HtmlString('');
        }

        $placement = AdPlacement::query()
            ->where('key', $key)
            ->where('is_enabled', true)
            ->first();

        if (! $placement || blank($placement->code)) {
            return new HtmlString('');
        }

        return new HtmlString('<div class="ad-slot" data-ad-key="'.e($key).'">'.$placement->code.'</div>');
    }
}
```

- [ ] **Step 5: Run public rendering tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Public/SiteSettingsRenderingTest.php tests/Feature/Public/AdRenderingTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add platform/resources/views/layouts/public.blade.php platform/app/Services/Ads/AdRenderer.php platform/tests/Feature/Public/SiteSettingsRenderingTest.php platform/tests/Feature/Public/AdRenderingTest.php
git commit -m "feat: gate analytics and ads by site settings"
```

---

### Task 4: Media Library Admin

**Files:**
- Create: `platform/app/Livewire/Admin/Media/MediaAssetIndex.php`
- Create: `platform/resources/views/livewire/admin/media/media-asset-index.blade.php`
- Create: `platform/tests/Feature/Admin/MediaLibraryAdminTest.php`
- Modify: `platform/routes/web.php`
- Modify: `platform/resources/views/layouts/admin.blade.php`

- [ ] **Step 1: Write failing media library tests**

Create `platform/tests/Feature/Admin/MediaLibraryAdminTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Media\MediaAssetIndex;
use App\Models\Article;
use App\Models\MediaAsset;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MediaLibraryAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_media_library(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('媒体库');
    }

    public function test_admin_can_upload_safe_image_to_media_library(): void
    {
        Storage::fake('public');
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(MediaAssetIndex::class)
            ->set('file', UploadedFile::fake()->image('tokyo.webp', 1200, 800))
            ->set('alt_text', 'Tokyo station exterior')
            ->set('source_note', 'Owned demo image')
            ->call('upload')
            ->assertHasNoErrors();

        $asset = MediaAsset::query()->firstOrFail();
        Storage::disk('public')->assertExists($asset->path);
        $this->assertSame('Tokyo station exterior', $asset->alt_text);
        $this->assertSame($admin->id, $asset->uploaded_by);
    }

    public function test_admin_cannot_delete_media_used_by_article_cover(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $asset = MediaAsset::factory()->create();
        Article::factory()->create(['cover_media_id' => $asset->id]);

        Livewire::test(MediaAssetIndex::class)
            ->call('delete', $asset->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
    }
}
```

- [ ] **Step 2: Run failing media tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/MediaLibraryAdminTest.php
```

Expected: FAIL because `MediaAssetIndex` and route do not exist.

- [ ] **Step 3: Create Livewire media component**

Create `platform/app/Livewire/Admin/Media/MediaAssetIndex.php`:

```php
<?php

namespace App\Livewire\Admin\Media;

use App\Models\Article;
use App\Models\Destination;
use App\Models\MediaAsset;
use App\Models\Topic;
use App\Services\Media\SafeImageUpload;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MediaAssetIndex extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $file = null;
    public ?string $alt_text = null;
    public ?string $source_note = null;
    public string $search = '';

    public function upload(SafeImageUpload $uploader): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'source_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $uploader->store($this->file, auth()->user(), $this->alt_text, $this->source_note);

        $this->reset(['file', 'alt_text', 'source_note']);
        session()->flash('status', '图片已上传');
    }

    public function delete(int $id): void
    {
        $asset = MediaAsset::query()->findOrFail($id);

        if ($this->isReferenced($asset)) {
            session()->flash('error', '图片正在被内容使用，不能删除');
            return;
        }

        Storage::disk($asset->disk)->delete($asset->path);
        $asset->delete();
        session()->flash('status', '图片已删除');
    }

    public function render(): View
    {
        $assets = MediaAsset::query()
            ->with('uploader')
            ->when($this->search !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('path', 'like', '%'.$this->search.'%')
                    ->orWhere('mime_type', 'like', '%'.$this->search.'%')
                    ->orWhere('alt_text', 'like', '%'.$this->search.'%');
            }))
            ->latest()
            ->paginate(24);

        return view('livewire.admin.media.media-asset-index', ['assets' => $assets])
            ->layout('layouts.admin', ['title' => '媒体库']);
    }

    private function isReferenced(MediaAsset $asset): bool
    {
        return Article::query()->where('cover_media_id', $asset->id)->orWhere('og_media_id', $asset->id)->exists()
            || Topic::query()->where('cover_media_id', $asset->id)->exists()
            || Destination::query()->where('cover_media_id', $asset->id)->exists();
    }
}
```

- [ ] **Step 4: Create Chinese media view**

Create `platform/resources/views/livewire/admin/media/media-asset-index.blade.php`:

```blade
<section>
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">媒体库</h1>
        <input wire:model.live.debounce.300ms="search" class="rounded border px-3 py-2 text-sm" placeholder="搜索路径、类型或 Alt">
    </div>

    @if(session('status'))
        <p class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
    @if(session('error'))
        <p class="mt-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</p>
    @endif

    <form wire:submit="upload" class="mt-6 grid gap-4 rounded-lg border bg-white p-5 md:grid-cols-3">
        <div>
            <label class="block text-sm font-medium" for="file">上传图片</label>
            <input id="file" type="file" wire:model="file" accept="image/jpeg,image/png,image/webp" class="mt-2 w-full rounded border px-3 py-2 text-sm">
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium" for="alt_text">Alt 文本</label>
            <input id="alt_text" wire:model="alt_text" class="mt-2 w-full rounded border px-3 py-2">
            @error('alt_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium" for="source_note">来源备注</label>
            <input id="source_note" wire:model="source_note" class="mt-2 w-full rounded border px-3 py-2">
            @error('source_note')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-3">
            <button class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white">上传</button>
        </div>
    </form>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        @foreach($assets as $asset)
            <article class="rounded-lg border bg-white p-4">
                <img class="aspect-video w-full rounded object-cover" src="{{ Storage::disk($asset->disk)->url($asset->path) }}" alt="{{ $asset->alt_text }}">
                <div class="mt-3 space-y-1 text-sm">
                    <p class="font-medium">{{ $asset->alt_text ?: '未填写 Alt' }}</p>
                    <p class="break-all text-xs text-slate-500">{{ $asset->path }}</p>
                    <p class="text-xs text-slate-500">{{ $asset->mime_type }} · {{ $asset->width }}x{{ $asset->height }} · {{ number_format($asset->size / 1024, 1) }} KB</p>
                    <button type="button" wire:click="delete({{ $asset->id }})" class="text-red-700 underline">删除</button>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">{{ $assets->links() }}</div>
</section>
```

- [ ] **Step 5: Add route and nav**

Modify `platform/routes/web.php` inside admin group:

```php
Route::get('/media', \App\Livewire\Admin\Media\MediaAssetIndex::class)->name('media.index');
```

Modify admin nav:

```blade
<a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.media.index') }}">媒体库</a>
```

- [ ] **Step 6: Run media tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/MediaLibraryAdminTest.php tests/Feature/Admin/MediaUploadTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add platform/app/Livewire/Admin/Media/MediaAssetIndex.php platform/resources/views/livewire/admin/media/media-asset-index.blade.php platform/tests/Feature/Admin/MediaLibraryAdminTest.php platform/routes/web.php platform/resources/views/layouts/admin.blade.php
git commit -m "feat: add media library admin"
```

---

### Task 5: Article Media Selection And SEO Preview

**Files:**
- Modify: `platform/app/Livewire/Admin/Articles/ArticleForm.php`
- Modify: `platform/resources/views/livewire/admin/articles/article-form.blade.php`
- Modify: `platform/app/Http/Controllers/Public/ArticleController.php`
- Modify: `platform/resources/views/public/articles/show.blade.php`
- Modify: `platform/tests/Feature/Admin/ArticleAdminTest.php`
- Modify: `platform/tests/Feature/Seo/SeoMetaTest.php`

- [ ] **Step 1: Write failing admin article media test**

Append to `ArticleAdminTest`:

```php
public function test_editor_can_attach_cover_and_og_media_to_article(): void
{
    $this->seed(RoleSeeder::class);
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $cover = \App\Models\MediaAsset::factory()->create(['alt_text' => 'Tokyo cover']);
    $og = \App\Models\MediaAsset::factory()->create(['alt_text' => 'Tokyo social image']);

    $this->actingAs($editor);

    Livewire::test(ArticleForm::class)
        ->set('title', 'Tokyo Cover Guide')
        ->set('slug', 'tokyo-cover-guide')
        ->set('body', '<p>Body</p>')
        ->set('cover_media_id', $cover->id)
        ->set('og_media_id', $og->id)
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('articles', [
        'slug' => 'tokyo-cover-guide',
        'cover_media_id' => $cover->id,
        'og_media_id' => $og->id,
    ]);
}
```

- [ ] **Step 2: Write failing SEO/OG public test**

Append to `SeoMetaTest`:

```php
public function test_article_outputs_og_image_from_selected_media(): void
{
    \Illuminate\Support\Facades\Storage::fake('public');

    $media = \App\Models\MediaAsset::factory()->create([
        'disk' => 'public',
        'path' => 'media/2026/06/tokyo.jpg',
        'alt_text' => 'Tokyo skyline',
    ]);

    $article = Article::factory()->create([
        'author_id' => User::factory(),
        'slug' => 'article-with-og-image',
        'status' => ArticleStatus::Published,
        'published_at' => now(),
        'og_media_id' => $media->id,
    ]);

    $this->get(route('articles.show', $article))
        ->assertOk()
        ->assertSee('property="og:image"', false)
        ->assertSee('/storage/media/2026/06/tokyo.jpg', false);
}
```

- [ ] **Step 3: Run failing article media tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/ArticleAdminTest.php --filter=cover_and_og_media
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Seo/SeoMetaTest.php --filter=og_image
```

Expected: FAIL because `ArticleForm` does not expose media IDs and controller passes `null` OG image.

- [ ] **Step 4: Extend ArticleForm properties, validation, mount, save, render**

Modify `platform/app/Livewire/Admin/Articles/ArticleForm.php`:

```php
use App\Models\MediaAsset;
```

Add properties:

```php
public ?int $cover_media_id = null;
public ?int $og_media_id = null;
```

In `mount()`:

```php
$this->cover_media_id = $article->cover_media_id;
$this->og_media_id = $article->og_media_id;
```

In `rules()`:

```php
'cover_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')],
'og_media_id' => ['nullable', 'integer', Rule::exists('media_assets', 'id')],
```

In `render()`:

```php
'mediaOptions' => MediaAsset::query()->latest()->limit(100)->get(),
'selectedCoverMedia' => $this->cover_media_id ? MediaAsset::find($this->cover_media_id) : null,
'selectedOgMedia' => $this->og_media_id ? MediaAsset::find($this->og_media_id) : null,
'seoPreview' => [
    'title' => $this->seo_title ?: $this->title,
    'description' => $this->meta_description ?: $this->excerpt,
    'canonical' => $this->canonical_url ?: ($this->slug ? url('/articles/'.$this->slug) : null),
    'indexable' => $this->is_indexable,
],
```

- [ ] **Step 5: Add media selectors and SEO preview to form view**

Modify `platform/resources/views/livewire/admin/articles/article-form.blade.php` after excerpt/body or before SEO fields:

```blade
<fieldset class="grid gap-5 border-t pt-5 md:grid-cols-2">
    <legend class="text-sm font-medium md:col-span-2">文章图片</legend>
    <div>
        <label class="block text-sm font-medium" for="cover_media_id">封面图</label>
        <select id="cover_media_id" wire:model.live="cover_media_id" class="mt-2 w-full rounded border px-3 py-2">
            <option value="">不选择</option>
            @foreach($mediaOptions as $media)
                <option value="{{ $media->id }}">{{ $media->alt_text ?: $media->path }}</option>
            @endforeach
        </select>
        @if($selectedCoverMedia)
            <img class="mt-3 aspect-video w-full rounded object-cover" src="{{ Storage::disk($selectedCoverMedia->disk)->url($selectedCoverMedia->path) }}" alt="{{ $selectedCoverMedia->alt_text }}">
        @endif
        @error('cover_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="og_media_id">社交分享图</label>
        <select id="og_media_id" wire:model.live="og_media_id" class="mt-2 w-full rounded border px-3 py-2">
            <option value="">跟随封面图</option>
            @foreach($mediaOptions as $media)
                <option value="{{ $media->id }}">{{ $media->alt_text ?: $media->path }}</option>
            @endforeach
        </select>
        @if($selectedOgMedia)
            <img class="mt-3 aspect-video w-full rounded object-cover" src="{{ Storage::disk($selectedOgMedia->disk)->url($selectedOgMedia->path) }}" alt="{{ $selectedOgMedia->alt_text }}">
        @endif
        @error('og_media_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</fieldset>
```

Add an SEO preview panel near SEO fields:

```blade
<aside class="rounded border border-slate-200 bg-slate-50 p-4">
    <p class="text-sm font-medium">SEO 预览</p>
    <p class="mt-3 text-base font-semibold text-blue-800">{{ $seoPreview['title'] ?: '未填写标题' }}</p>
    <p class="mt-1 break-all text-xs text-emerald-700">{{ $seoPreview['canonical'] ?: '保存后生成链接' }}</p>
    <p class="mt-2 text-sm text-slate-700">{{ $seoPreview['description'] ?: '未填写描述' }}</p>
    <p class="mt-2 text-xs {{ $seoPreview['indexable'] ? 'text-emerald-700' : 'text-red-700' }}">
        {{ $seoPreview['indexable'] ? '允许索引' : '不允许索引' }}
    </p>
</aside>
```

- [ ] **Step 6: Update public article controller for OG image**

Modify `platform/app/Http/Controllers/Public/ArticleController.php` load list:

```php
'coverMedia',
'ogMedia',
```

Compute image URL before `MetaPayload`:

```php
$ogMedia = $article->ogMedia ?: $article->coverMedia;
$ogImage = $ogMedia ? \Illuminate\Support\Facades\Storage::disk($ogMedia->disk)->url($ogMedia->path) : null;
```

Pass `$ogImage` into `MetaPayload` instead of `null`.

- [ ] **Step 7: Render article cover image**

Modify `platform/resources/views/public/articles/show.blade.php` after title/excerpt:

```blade
@if($article->coverMedia)
    <figure class="mt-8">
        <img class="aspect-video w-full rounded object-cover" src="{{ Storage::disk($article->coverMedia->disk)->url($article->coverMedia->path) }}" alt="{{ $article->coverMedia->alt_text }}">
        @if($article->coverMedia->source_note)
            <figcaption class="mt-2 text-xs text-slate-500">{{ $article->coverMedia->source_note }}</figcaption>
        @endif
    </figure>
@endif
```

- [ ] **Step 8: Run article media and SEO tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/ArticleAdminTest.php tests/Feature/Seo/SeoMetaTest.php
```

Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add platform/app/Livewire/Admin/Articles/ArticleForm.php platform/resources/views/livewire/admin/articles/article-form.blade.php platform/app/Http/Controllers/Public/ArticleController.php platform/resources/views/public/articles/show.blade.php platform/tests/Feature/Admin/ArticleAdminTest.php platform/tests/Feature/Seo/SeoMetaTest.php
git commit -m "feat: add article media and seo preview"
```

---

### Task 6: Advertising Admin Hardening

**Files:**
- Modify: `platform/app/Livewire/Admin/Ads/AdPlacementIndex.php`
- Modify: `platform/resources/views/livewire/admin/ads/ad-placement-index.blade.php`
- Modify: `platform/database/factories/AdPlacementFactory.php`
- Modify: `platform/tests/Feature/Admin/AdPlacementAdminTest.php`

- [ ] **Step 1: Write failing admin ad behavior tests**

Append to `AdPlacementAdminTest`:

```php
public function test_new_ad_placement_is_disabled_by_default(): void
{
    $this->seed(\Database\Seeders\RoleSeeder::class);
    $admin = \App\Models\User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    \Livewire\Livewire::test(\App\Livewire\Admin\Ads\AdPlacementIndex::class)
        ->set('key', 'article-sidebar')
        ->set('name', '文章侧边栏')
        ->set('page_type', 'article')
        ->set('position', 'sidebar')
        ->set('code', '<ins class="adsbygoogle"></ins>')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('ad_placements', [
        'key' => 'article-sidebar',
        'is_enabled' => false,
    ]);
}
```

- [ ] **Step 2: Run failing ad admin test**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/AdPlacementAdminTest.php --filter=disabled_by_default
```

Expected: FAIL if component resets or saves enabled unexpectedly.

- [ ] **Step 3: Update component rules and reset behavior**

Modify `platform/app/Livewire/Admin/Ads/AdPlacementIndex.php`:

```php
public ?string $notes = null;
public bool $is_enabled = false;
```

In `edit()`:

```php
$this->notes = $placement->notes;
```

In validation:

```php
'notes' => ['nullable', 'string', 'max:1000'],
```

After save reset:

```php
$this->reset(['placementId', 'key', 'name', 'code', 'notes', 'is_enabled']);
$this->page_type = 'article';
$this->position = 'body_middle';
```

- [ ] **Step 4: Update ad admin view**

Modify `platform/resources/views/livewire/admin/ads/ad-placement-index.blade.php`:

```blade
<p class="mt-2 text-sm text-slate-600">广告代码只允许管理员维护。建议先保存为停用状态，上线确认位置后再启用。</p>
```

Add notes field:

```blade
<textarea wire:model="notes" class="rounded border px-3 py-2 text-sm md:col-span-4" rows="3" placeholder="内部备注，例如投放说明、尺寸、页面位置"></textarea>
```

Add validation messages for each field and show `page_type`/`position` in the listing.

- [ ] **Step 5: Update factory disabled default**

Modify `platform/database/factories/AdPlacementFactory.php`:

```php
'is_enabled' => false,
```

For public rendering tests that need ads shown, explicitly set `is_enabled => true`.

- [ ] **Step 6: Run ad tests**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Admin/AdPlacementAdminTest.php tests/Feature/Public/AdRenderingTest.php
```

Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add platform/app/Livewire/Admin/Ads/AdPlacementIndex.php platform/resources/views/livewire/admin/ads/ad-placement-index.blade.php platform/database/factories/AdPlacementFactory.php platform/tests/Feature/Admin/AdPlacementAdminTest.php
git commit -m "fix: harden ad placement admin defaults"
```

---

### Task 7: Seed Defaults And Deployment Notes

**Files:**
- Modify: `platform/database/seeders/DemoContentSeeder.php`
- Modify: `docs/deployment/laravel-platform.md`
- Test: `platform/tests/Feature/Seeders/DemoContentSeederTest.php`

- [ ] **Step 1: Write failing seed assertion**

Append to `DemoContentSeederTest`:

```php
public function test_demo_seed_creates_disabled_site_settings(): void
{
    $this->seed(\Database\Seeders\DemoContentSeeder::class);

    $this->assertDatabaseHas('site_settings', [
        'id' => 1,
        'site_name' => 'Japan Travel Guide',
        'analytics_enabled' => false,
        'ads_enabled' => false,
    ]);
}
```

- [ ] **Step 2: Run failing seed test**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Seeders/DemoContentSeederTest.php --filter=site_settings
```

Expected: FAIL because demo seed does not create site settings.

- [ ] **Step 3: Seed default site settings**

Modify `platform/database/seeders/DemoContentSeeder.php` imports:

```php
use App\Models\SiteSetting;
```

Near the top of `run()`:

```php
SiteSetting::query()->updateOrCreate(
    ['id' => 1],
    [
        'site_name' => 'Japan Travel Guide',
        'seo_title_suffix' => 'Japan Travel Guide',
        'default_meta_description' => 'Independent planning guides, regional hubs, and useful travel tools for English-speaking Japan travelers.',
        'ga4_measurement_id' => null,
        'adsense_publisher_id' => null,
        'analytics_enabled' => false,
        'ads_enabled' => false,
    ]
);
```

- [ ] **Step 4: Update deployment notes**

Modify `docs/deployment/laravel-platform.md` production checklist:

```markdown
11. In `/admin/settings`, set the public site name, SEO suffix, default meta description, and optional GA4/AdSense public IDs.
12. Keep analytics and ads disabled until the production domain is verified in Google tools.
13. In `/admin/media`, upload only licensed JPG, PNG, or WebP images and write useful English alt text.
```

Also note that AdSense/Analytics IDs are public IDs, not passwords or secrets.

- [ ] **Step 5: Run seed test**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test tests/Feature/Seeders/DemoContentSeederTest.php
```

Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add platform/database/seeders/DemoContentSeeder.php platform/tests/Feature/Seeders/DemoContentSeederTest.php docs/deployment/laravel-platform.md
git commit -m "docs: document publishing operations setup"
```

---

### Task 8: Full Verification And Smoke Test

**Files:**
- No source files expected unless failures expose required fixes.

- [ ] **Step 1: Run full PHP test suite**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test
```

Expected: PASS with all existing and new tests.

- [ ] **Step 2: Run frontend production build**

Run:

```bash
cd platform
/Users/heroachao/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node node_modules/vite/bin/vite.js build
```

Expected: PASS and produce assets in `platform/public/build`.

- [ ] **Step 3: Run database rebuild with seed**

Run:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan migrate:fresh --seed
```

Expected: PASS. Confirm `site_settings` has one disabled default row.

- [ ] **Step 4: Run HTTP smoke checks against local server**

If the local server is still running at `http://127.0.0.1:63838`, run:

```bash
/Users/heroachao/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node -e 'const paths=["/","/articles","/regions","/search","/sitemap.xml","/admin/media","/admin/settings"]; (async()=>{for (const path of paths){const res=await fetch("http://127.0.0.1:63838"+path,{redirect:"manual"}); console.log(res.status,path)}})().catch((error)=>{console.error(error); process.exit(1)})'
```

Expected:

```text
200 /
200 /articles
200 /regions
200 /search
200 /sitemap.xml
302 /admin/media
302 /admin/settings
```

- [ ] **Step 5: Inspect git status**

Run:

```bash
git status -sb
```

Expected: implementation files committed, only known untracked root `index.html` remains if still present.

- [ ] **Step 6: Commit final fixes if any**

If verification required small fixes:

```bash
git add <changed-files>
git commit -m "fix: stabilize publishing operations"
```

If no fixes were needed, do not create an empty commit.

---

## Self-Review Checklist

- Spec coverage: media library, article cover/OG image, SEO preview, site settings, analytics/ad toggles, public rendering, delete protection, and docs are covered.
- Security coverage: no secrets, no cookies, strict public Google ID validation, disabled-by-default scripts and placements.
- Testing coverage: admin Livewire, service defaults, public script rendering, ad gate, article media persistence, OG image output, seeding, and full regression commands are included.
- Scope control: revenue dashboards, keyword rank tracking, real Google account connection, and WYSIWYG editing remain outside this phase.

# Japan Travel Publishing System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the first working Laravel MVP for an English Japan travel SEO site with a Chinese custom admin, complete publishing workflow, role permissions, SEO output, and centralized AdSense placements.

**Architecture:** Create a fresh Laravel 13 application under `platform/` so the existing root `index.html` remains untouched. Use Blade for SEO-first public pages and custom Blade/Livewire admin screens for Chinese editorial workflows. Keep business rules in small domain services and policies rather than burying permission or status logic in controllers.

**Tech Stack:** Laravel 13, PHP 8.5 local runtime, PostgreSQL for production, SQLite for automated tests, Livewire 4, Blade, Vite/Tailwind, Spatie Laravel Permission, HTMLPurifier, PHPUnit/Pest-compatible Laravel feature tests.

---

## Source Context

- Product spec: `docs/superpowers/specs/2026-06-18-japan-travel-publishing-system-design.md`
- Current repo state: root contains docs, `.gitignore`, and an uncommitted `index.html` concept page.
- Runtime finding: local shell currently has no `php`, `composer`, `npm`, Docker, Colima, or Homebrew. The plan starts by installing local toolchains.
- Current official framework choice: Laravel 13.x is the current line in Laravel docs and requires PHP 8.3+. Livewire 4.x is current and supports class-based or multi-file components, which fits a custom admin.

## File Structure

The Laravel app lives in `platform/`. Laravel will generate framework files during scaffolding; the custom files that carry project behavior are:

- `platform/app/Enums/ArticleStatus.php` — canonical article workflow states.
- `platform/app/Models/Article.php` — article content, relations, scopes, version creation hook.
- `platform/app/Models/Destination.php` — region/city/attraction hierarchy.
- `platform/app/Models/Topic.php` — SEO hub/topic pages.
- `platform/app/Models/Tag.php` — lightweight taxonomy.
- `platform/app/Models/MediaAsset.php` — uploaded image metadata.
- `platform/app/Models/AdPlacement.php` — centralized AdSense placement records.
- `platform/app/Models/ArticleVersion.php` — immutable article history snapshots.
- `platform/app/Models/AuditLog.php` — admin action log records.
- `platform/app/Models/Redirect.php` — old slug to new URL redirects.
- `platform/app/Services/Publishing/ArticleWorkflow.php` — state transitions and version snapshots.
- `platform/app/Services/Seo/MetaPayload.php` — value object for page meta data.
- `platform/app/Services/Seo/SitemapBuilder.php` — sitemap XML generation.
- `platform/app/Services/Ads/AdRenderer.php` — lookup and render enabled ad placements.
- `platform/app/Policies/ArticlePolicy.php` — role-gated article actions.
- `platform/app/Http/Controllers/Public/HomeController.php` — English homepage.
- `platform/app/Http/Controllers/Public/ArticleController.php` — article listing/detail pages.
- `platform/app/Http/Controllers/Public/DestinationController.php` — destination listing/detail pages.
- `platform/app/Http/Controllers/Public/TopicController.php` — topic hub pages.
- `platform/app/Http/Controllers/Public/TagController.php` — tag pages.
- `platform/app/Http/Controllers/Public/SearchController.php` — basic title/summary search.
- `platform/app/Livewire/Admin/Articles/ArticleIndex.php` — Chinese article list and filters.
- `platform/app/Livewire/Admin/Articles/ArticleForm.php` — Chinese article editor.
- `platform/app/Livewire/Admin/Articles/ReviewPanel.php` — Chinese review/approve/reject controls.
- `platform/app/Livewire/Admin/Destinations/DestinationIndex.php` — destination admin.
- `platform/app/Livewire/Admin/Topics/TopicIndex.php` — topic admin.
- `platform/app/Livewire/Admin/Tags/TagIndex.php` — tag admin.
- `platform/app/Livewire/Admin/Ads/AdPlacementIndex.php` — ad placement admin.
- `platform/resources/views/layouts/public.blade.php` — public English layout.
- `platform/resources/views/layouts/admin.blade.php` — Chinese admin shell.
- `platform/resources/views/public/*.blade.php` — public pages.
- `platform/resources/views/livewire/admin/**/*.blade.php` — Livewire admin views.
- `platform/routes/web.php` — public and admin routes.
- `platform/routes/console.php` — scheduler command registration.
- `platform/database/factories/*.php` — factories for tests and seed content.
- `platform/database/seeders/RoleSeeder.php` — five default roles.
- `platform/database/seeders/DemoContentSeeder.php` — English sample travel content.
- `platform/tests/Feature/Admin/*.php` — admin workflow, permissions, ads, upload tests.
- `platform/tests/Feature/Public/*.php` — public SEO page tests.
- `platform/tests/Feature/Seo/*.php` — sitemap, robots, structured data tests.

## Scope Check

This plan implements the MVP described in the spec. It intentionally excludes full itinerary planning, complex maps, front-end multilingual publishing, member accounts, favorites, and external travel APIs.

---

### Task 0: Local Runtime Toolchain

**Files:**
- Modify: `.gitignore`
- Verify only: local PHP, Composer, Laravel Installer, Node/npm

- [ ] **Step 1: Ignore local runtime folders**

Add these lines to the root `.gitignore`:

```gitignore
.tools/
platform/vendor/
platform/node_modules/
platform/.env
platform/.env.backup
platform/storage/*.key
```

- [ ] **Step 2: Install PHP, Composer, and Laravel Installer**

Run from repo root:

```bash
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Expected: the installer finishes with PHP, Composer, and Laravel commands available after reloading shell configuration.

- [ ] **Step 3: Reload shell path and verify PHP stack**

Run:

```bash
exec zsh -l
php -v
composer --version
laravel --version
```

Expected:

```text
PHP 8.5.x
Composer version 2.x
Laravel Installer 5.x or newer
```

- [ ] **Step 4: Install Node with npm locally if `npm` is still missing**

Run:

```bash
mkdir -p .tools
curl -L https://nodejs.org/dist/v24.14.0/node-v24.14.0-darwin-arm64.tar.xz -o .tools/node-v24.14.0-darwin-arm64.tar.xz
tar -xJf .tools/node-v24.14.0-darwin-arm64.tar.xz -C .tools
export PATH="$PWD/.tools/node-v24.14.0-darwin-arm64/bin:$PATH"
node --version
npm --version
```

Expected:

```text
v24.14.0
11.x or newer
```

- [ ] **Step 5: Commit runtime ignore rules**

Run:

```bash
git add .gitignore
git commit -m "chore: ignore local Laravel runtime artifacts"
```

Expected: commit succeeds and `git status --short` does not show `.tools/`, `vendor/`, `node_modules/`, or `.env`.

---

### Task 1: Scaffold Laravel Application

**Files:**
- Create: `platform/`
- Modify: `platform/.env.example`
- Modify: `platform/composer.json`
- Modify: `platform/package.json`
- Test: `platform/tests/Feature/HealthCheckTest.php`

- [ ] **Step 1: Create the Laravel 13 app in `platform/`**

Run from repo root:

```bash
laravel new platform --no-interaction --database=pgsql
cd platform
composer require livewire/livewire:^4.0 spatie/laravel-permission:^6.0 mews/purifier:^3.4
php artisan vendor:publish --provider="Spatie\\Permission\\PermissionServiceProvider"
php artisan vendor:publish --provider="Mews\\Purifier\\PurifierServiceProvider"
npm install
```

Expected: `platform/artisan`, `platform/composer.json`, `platform/package.json`, and `platform/config/permission.php` exist.

- [ ] **Step 2: Configure testing database**

Edit `platform/phpunit.xml` so tests use SQLite in memory:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

- [ ] **Step 3: Add health check route**

Add to `platform/routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::view('/health', 'health')->name('health');
```

Create `platform/resources/views/health.blade.php`:

```blade
OK
```

- [ ] **Step 4: Write the failing health test**

Create `platform/tests/Feature/HealthCheckTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_page_returns_ok(): void
    {
        $this->get('/health')
            ->assertOk()
            ->assertSee('OK');
    }
}
```

- [ ] **Step 5: Run baseline tests**

Run:

```bash
php artisan test --filter=HealthCheckTest
```

Expected: one passing test.

- [ ] **Step 6: Commit scaffold**

Run:

```bash
git add platform .gitignore
git commit -m "chore: scaffold Laravel publishing platform"
```

---

### Task 2: Roles, Users, and Chinese Admin Shell

**Files:**
- Modify: `platform/app/Models/User.php`
- Create: `platform/database/seeders/RoleSeeder.php`
- Modify: `platform/database/seeders/DatabaseSeeder.php`
- Create: `platform/app/Http/Middleware/EnsureAdmin.php`
- Modify: `platform/bootstrap/app.php`
- Modify: `platform/routes/web.php`
- Create: `platform/resources/views/layouts/admin.blade.php`
- Create: `platform/resources/views/admin/dashboard.blade.php`
- Test: `platform/tests/Feature/Admin/AdminAccessTest.php`

- [ ] **Step 1: Add Spatie role support to users**

Modify `platform/app/Models/User.php`:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use HasRoles;
    use Notifiable;
}
```

- [ ] **Step 2: Create five roles and a default super admin**

Create `platform/database/seeders/RoleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.access',
            'articles.create',
            'articles.edit',
            'articles.review',
            'articles.publish',
            'seo.manage',
            'ads.manage',
            'users.manage',
            'system.logs',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $roles = [
            'super-admin' => $permissions,
            'chief-editor' => ['admin.access', 'articles.create', 'articles.edit', 'articles.review', 'articles.publish', 'seo.manage'],
            'editor' => ['admin.access', 'articles.create', 'articles.edit'],
            'seo-operator' => ['admin.access', 'seo.manage', 'articles.edit'],
            'ad-operator' => ['admin.access', 'ads.manage'],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name)->syncPermissions($rolePermissions);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => '超级管理员', 'password' => Hash::make('ChangeMe123!')]
        );

        $admin->assignRole('super-admin');
    }
}
```

Modify `platform/database/seeders/DatabaseSeeder.php`:

```php
public function run(): void
{
    $this->call(RoleSeeder::class);
}
```

- [ ] **Step 3: Add admin middleware**

Create `platform/app/Http/Middleware/EnsureAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->can('admin.access'), 403);

        return $next($request);
    }
}
```

Register it in `platform/bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureAdmin::class,
    ]);
})
```

- [ ] **Step 4: Add Chinese admin routes and views**

Add to `platform/routes/web.php`:

```php
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::view('/', 'admin.dashboard')->name('dashboard');
    });
```

Create `platform/resources/views/layouts/admin.blade.php`:

```blade
<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? '日本旅游发布后台' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        <aside class="w-64 border-r bg-white p-5">
            <a href="{{ route('admin.dashboard') }}" class="block text-lg font-semibold">日本旅游发布系统</a>
            <nav class="mt-8 space-y-2 text-sm">
                <a class="block rounded px-3 py-2 hover:bg-slate-100" href="{{ route('admin.dashboard') }}">工作台</a>
            </nav>
        </aside>
        <main class="flex-1 p-8">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</body>
</html>
```

Create `platform/resources/views/admin/dashboard.blade.php`:

```blade
@extends('layouts.admin')

@section('content')
    <h1 class="text-2xl font-semibold">工作台</h1>
    <p class="mt-3 text-slate-600">待审内容、定时发布和 SEO 待完善项会显示在这里。</p>
@endsection
```

- [ ] **Step 5: Test admin access**

Create `platform/tests/Feature/Admin/AdminAccessTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_user_without_admin_access_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_super_admin_can_view_chinese_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $admin = User::whereEmail('admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('工作台');
    }
}
```

Run:

```bash
php artisan test --filter=AdminAccessTest
```

Expected: three passing tests.

- [ ] **Step 6: Commit roles and admin shell**

Run:

```bash
git add platform
git commit -m "feat: add Chinese admin shell and role seeds"
```

---

### Task 3: Content Schema, Models, and Factories

**Files:**
- Create: `platform/app/Enums/ArticleStatus.php`
- Create/modify migrations in `platform/database/migrations/`
- Create: `platform/app/Models/Article.php`
- Create: `platform/app/Models/Destination.php`
- Create: `platform/app/Models/Topic.php`
- Create: `platform/app/Models/Tag.php`
- Create: `platform/app/Models/MediaAsset.php`
- Create: `platform/app/Models/AdPlacement.php`
- Create: `platform/app/Models/ArticleVersion.php`
- Create: `platform/app/Models/AuditLog.php`
- Create: `platform/app/Models/Redirect.php`
- Create factories in `platform/database/factories/`
- Test: `platform/tests/Feature/Content/ContentSchemaTest.php`

- [ ] **Step 1: Create article status enum**

Create `platform/app/Enums/ArticleStatus.php`:

```php
<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Rejected = 'rejected';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => '草稿',
            self::Review => '待审',
            self::Rejected => '退回',
            self::Scheduled => '定时发布',
            self::Published => '已发布',
            self::Archived => '归档',
        };
    }
}
```

- [ ] **Step 2: Generate migrations and models**

Run:

```bash
php artisan make:model Article -mf
php artisan make:model Destination -mf
php artisan make:model Topic -mf
php artisan make:model Tag -mf
php artisan make:model MediaAsset -mf
php artisan make:model AdPlacement -mf
php artisan make:model ArticleVersion -mf
php artisan make:model AuditLog -mf
php artisan make:model Redirect -mf
php artisan make:migration create_article_destination_table
php artisan make:migration create_article_tag_table
php artisan make:migration create_article_topic_table
php artisan make:migration create_destination_topic_table
```

- [ ] **Step 3: Implement core schema**

Use these fields in migrations:

```php
// articles
$table->id();
$table->foreignId('author_id')->constrained('users');
$table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
$table->string('title');
$table->string('slug')->unique();
$table->text('excerpt')->nullable();
$table->longText('body')->nullable();
$table->string('status')->default('draft')->index();
$table->timestamp('published_at')->nullable()->index();
$table->timestamp('scheduled_for')->nullable()->index();
$table->text('rejection_reason')->nullable();
$table->string('seo_title')->nullable();
$table->text('meta_description')->nullable();
$table->string('canonical_url')->nullable();
$table->string('og_title')->nullable();
$table->text('og_description')->nullable();
$table->foreignId('og_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
$table->boolean('is_indexable')->default(true);
$table->string('structured_data_type')->default('Article');
$table->softDeletes();
$table->timestamps();
```

```php
// destinations
$table->id();
$table->foreignId('parent_id')->nullable()->constrained('destinations')->nullOnDelete();
$table->string('type')->default('city')->index();
$table->string('name');
$table->string('slug')->unique();
$table->text('excerpt')->nullable();
$table->longText('body')->nullable();
$table->decimal('latitude', 10, 7)->nullable();
$table->decimal('longitude', 10, 7)->nullable();
$table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
$table->string('seo_title')->nullable();
$table->text('meta_description')->nullable();
$table->boolean('is_indexable')->default(true);
$table->softDeletes();
$table->timestamps();
```

```php
// topics
$table->id();
$table->string('title');
$table->string('slug')->unique();
$table->text('excerpt')->nullable();
$table->longText('body')->nullable();
$table->foreignId('cover_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
$table->string('seo_title')->nullable();
$table->text('meta_description')->nullable();
$table->boolean('is_indexable')->default(true);
$table->softDeletes();
$table->timestamps();
```

```php
// tags
$table->id();
$table->string('name');
$table->string('slug')->unique();
$table->text('description')->nullable();
$table->timestamps();
```

```php
// media_assets
$table->id();
$table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
$table->string('disk')->default('public');
$table->string('path');
$table->string('mime_type');
$table->unsignedBigInteger('size');
$table->unsignedInteger('width')->nullable();
$table->unsignedInteger('height')->nullable();
$table->string('alt_text')->nullable();
$table->text('source_note')->nullable();
$table->timestamps();
```

```php
// ad_placements
$table->id();
$table->string('key')->unique();
$table->string('name');
$table->string('page_type')->index();
$table->string('position')->index();
$table->longText('code')->nullable();
$table->boolean('is_enabled')->default(false);
$table->text('notes')->nullable();
$table->timestamps();
```

```php
// article_versions
$table->id();
$table->foreignId('article_id')->constrained()->cascadeOnDelete();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->string('event');
$table->json('snapshot');
$table->timestamps();
```

```php
// audit_logs
$table->id();
$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
$table->string('action')->index();
$table->string('subject_type')->nullable();
$table->unsignedBigInteger('subject_id')->nullable();
$table->json('properties')->nullable();
$table->ipAddress('ip_address')->nullable();
$table->text('user_agent')->nullable();
$table->timestamps();
```

```php
// redirects
$table->id();
$table->string('from_path')->unique();
$table->string('to_path');
$table->unsignedSmallInteger('status_code')->default(301);
$table->timestamps();
```

Pivot tables use foreign IDs, unique compound keys, and cascade deletes.

- [ ] **Step 4: Implement model casts and relations**

Use this pattern for `Article`:

```php
<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'author_id', 'cover_media_id', 'title', 'slug', 'excerpt', 'body', 'status',
        'published_at', 'scheduled_for', 'rejection_reason', 'seo_title',
        'meta_description', 'canonical_url', 'og_title', 'og_description',
        'og_media_id', 'is_indexable', 'structured_data_type',
    ];

    protected $casts = [
        'status' => ArticleStatus::class,
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'is_indexable' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class)->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class)->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ArticleVersion::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
```

For `Destination`, add `parent()`, `children()`, `articles()`, and `topics()` relations. For `Topic`, add `articles()` and `destinations()` relations. For `Tag`, add `articles()` relation.

- [ ] **Step 5: Write schema test**

Create `platform/tests/Feature/Content/ContentSchemaTest.php`:

```php
<?php

namespace Tests\Feature\Content;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_can_connect_destinations_topics_and_tags(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create([
            'author_id' => $user->id,
            'status' => ArticleStatus::Draft,
        ]);
        $destination = Destination::factory()->create(['name' => 'Kyoto', 'slug' => 'kyoto']);
        $topic = Topic::factory()->create(['title' => 'Best Time to Visit Japan', 'slug' => 'best-time-to-visit-japan']);
        $tag = Tag::factory()->create(['name' => 'Cherry Blossom', 'slug' => 'cherry-blossom']);

        $article->destinations()->attach($destination);
        $article->topics()->attach($topic);
        $article->tags()->attach($tag);

        $this->assertTrue($article->fresh()->destinations->contains($destination));
        $this->assertTrue($article->fresh()->topics->contains($topic));
        $this->assertTrue($article->fresh()->tags->contains($tag));
    }
}
```

Run:

```bash
php artisan test --filter=ContentSchemaTest
```

Expected: one passing test.

- [ ] **Step 6: Commit content foundation**

Run:

```bash
git add platform
git commit -m "feat: add travel content data model"
```

---

### Task 4: Article Workflow Service and Versioning

**Files:**
- Create: `platform/app/Services/Publishing/ArticleWorkflow.php`
- Create: `platform/app/Policies/ArticlePolicy.php`
- Modify: `platform/app/Providers/AppServiceProvider.php`
- Test: `platform/tests/Feature/Admin/ArticleWorkflowTest.php`

- [ ] **Step 1: Write failing workflow tests**

Create `platform/tests/Feature/Admin/ArticleWorkflowTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Services\Publishing\ArticleWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_submit_draft_for_review(): void
    {
        $editor = User::factory()->create();
        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'status' => ArticleStatus::Draft,
        ]);

        app(ArticleWorkflow::class)->submitForReview($article, $editor);

        $article->refresh();

        $this->assertSame(ArticleStatus::Review, $article->status);
        $this->assertDatabaseHas('article_versions', [
            'article_id' => $article->id,
            'user_id' => $editor->id,
            'event' => 'submitted_for_review',
        ]);
    }

    public function test_chief_editor_can_reject_review_article(): void
    {
        $chief = User::factory()->create();
        $article = Article::factory()->create(['status' => ArticleStatus::Review]);

        app(ArticleWorkflow::class)->reject($article, $chief, 'Please add official transport sources.');

        $article->refresh();

        $this->assertSame(ArticleStatus::Rejected, $article->status);
        $this->assertSame('Please add official transport sources.', $article->rejection_reason);
    }

    public function test_chief_editor_can_publish_immediately(): void
    {
        $chief = User::factory()->create();
        $article = Article::factory()->create(['status' => ArticleStatus::Review]);

        app(ArticleWorkflow::class)->publish($article, $chief);

        $article->refresh();

        $this->assertSame(ArticleStatus::Published, $article->status);
        $this->assertNotNull($article->published_at);
    }
}
```

Run:

```bash
php artisan test --filter=ArticleWorkflowTest
```

Expected: fails because `ArticleWorkflow` does not exist.

- [ ] **Step 2: Implement workflow service**

Create `platform/app/Services/Publishing/ArticleWorkflow.php`:

```php
<?php

namespace App\Services\Publishing;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ArticleWorkflow
{
    public function submitForReview(Article $article, User $actor): Article
    {
        $this->ensureStatus($article, [ArticleStatus::Draft, ArticleStatus::Rejected]);

        return $this->transition($article, $actor, ArticleStatus::Review, 'submitted_for_review');
    }

    public function reject(Article $article, User $actor, string $reason): Article
    {
        $this->ensureStatus($article, [ArticleStatus::Review]);

        return DB::transaction(function () use ($article, $actor, $reason): Article {
            $article->forceFill([
                'status' => ArticleStatus::Rejected,
                'rejection_reason' => $reason,
            ])->save();

            $this->snapshot($article, $actor, 'rejected');

            return $article;
        });
    }

    public function publish(Article $article, User $actor): Article
    {
        $this->ensureStatus($article, [ArticleStatus::Review, ArticleStatus::Scheduled]);

        return DB::transaction(function () use ($article, $actor): Article {
            $article->forceFill([
                'status' => ArticleStatus::Published,
                'published_at' => now(),
                'scheduled_for' => null,
                'rejection_reason' => null,
            ])->save();

            $this->snapshot($article, $actor, 'published');

            return $article;
        });
    }

    public function schedule(Article $article, User $actor, Carbon $scheduledFor): Article
    {
        $this->ensureStatus($article, [ArticleStatus::Review]);

        return DB::transaction(function () use ($article, $actor, $scheduledFor): Article {
            $article->forceFill([
                'status' => ArticleStatus::Scheduled,
                'scheduled_for' => $scheduledFor,
            ])->save();

            $this->snapshot($article, $actor, 'scheduled');

            return $article;
        });
    }

    private function transition(Article $article, User $actor, ArticleStatus $status, string $event): Article
    {
        return DB::transaction(function () use ($article, $actor, $status, $event): Article {
            $article->forceFill([
                'status' => $status,
                'rejection_reason' => null,
            ])->save();

            $this->snapshot($article, $actor, $event);

            return $article;
        });
    }

    private function snapshot(Article $article, User $actor, string $event): void
    {
        $article->versions()->create([
            'user_id' => $actor->id,
            'event' => $event,
            'snapshot' => $article->fresh()->only([
                'title', 'slug', 'excerpt', 'body', 'status', 'seo_title',
                'meta_description', 'canonical_url', 'is_indexable',
            ]),
        ]);
    }

    private function ensureStatus(Article $article, array $allowed): void
    {
        if (! in_array($article->status, $allowed, true)) {
            throw new InvalidArgumentException("Article status {$article->status->value} cannot transition here.");
        }
    }
}
```

- [ ] **Step 3: Run workflow tests**

Run:

```bash
php artisan test --filter=ArticleWorkflowTest
```

Expected: three passing tests.

- [ ] **Step 4: Commit workflow**

Run:

```bash
git add platform
git commit -m "feat: add article publishing workflow"
```

---

### Task 5: Admin Article Screens

**Files:**
- Create: `platform/app/Livewire/Admin/Articles/ArticleIndex.php`
- Create: `platform/app/Livewire/Admin/Articles/ArticleForm.php`
- Create: `platform/app/Livewire/Admin/Articles/ReviewPanel.php`
- Create views in `platform/resources/views/livewire/admin/articles/`
- Modify: `platform/routes/web.php`
- Modify: `platform/resources/views/layouts/admin.blade.php`
- Test: `platform/tests/Feature/Admin/ArticleAdminTest.php`

- [ ] **Step 1: Generate Livewire components**

Run:

```bash
php artisan make:livewire Admin/Articles/ArticleIndex --class
php artisan make:livewire Admin/Articles/ArticleForm --class
php artisan make:livewire Admin/Articles/ReviewPanel --class
```

- [ ] **Step 2: Add admin article routes**

Add inside the admin route group:

```php
Route::get('/articles', \App\Livewire\Admin\Articles\ArticleIndex::class)->name('articles.index');
Route::get('/articles/create', \App\Livewire\Admin\Articles\ArticleForm::class)->name('articles.create');
Route::get('/articles/{article}/edit', \App\Livewire\Admin\Articles\ArticleForm::class)->name('articles.edit');
Route::get('/articles/{article}/review', \App\Livewire\Admin\Articles\ReviewPanel::class)->name('articles.review');
```

- [ ] **Step 3: Implement `ArticleIndex`**

Core component behavior:

```php
public string $status = '';
public string $search = '';

public function render(): View
{
    $articles = Article::query()
        ->with('author')
        ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
        ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
        ->latest()
        ->paginate(15);

    return view('livewire.admin.articles.article-index', [
        'articles' => $articles,
        'statuses' => ArticleStatus::cases(),
    ])->layout('layouts.admin', ['title' => '文章管理']);
}
```

The Blade view must show Chinese labels: `文章管理`, `新建文章`, `状态`, `搜索标题`, `编辑`, `审核`.

- [ ] **Step 4: Implement `ArticleForm` validation and save**

Required validation rules:

```php
protected function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:180'],
        'slug' => ['required', 'alpha_dash:ascii', 'max:180', Rule::unique('articles', 'slug')->ignore($this->article?->id)],
        'excerpt' => ['nullable', 'string', 'max:500'],
        'body' => ['nullable', 'string'],
        'seo_title' => ['nullable', 'string', 'max:180'],
        'meta_description' => ['nullable', 'string', 'max:260'],
        'canonical_url' => ['nullable', 'url', 'max:255'],
        'is_indexable' => ['boolean'],
    ];
}
```

Save behavior:

```php
public function save(): RedirectResponse
{
    $data = $this->validate();
    $data['author_id'] = $this->article?->author_id ?? auth()->id();
    $data['status'] = $this->article?->status ?? ArticleStatus::Draft;

    $article = Article::updateOrCreate(['id' => $this->article?->id], $data);

    return redirect()->route('admin.articles.edit', $article)->with('status', '文章已保存');
}
```

- [ ] **Step 5: Implement review controls**

`ReviewPanel` must call `ArticleWorkflow` methods and show buttons with Chinese labels: `提交审核`, `退回修改`, `立即发布`, `定时发布`.

- [ ] **Step 6: Test article admin screens**

Create `platform/tests/Feature/Admin/ArticleAdminTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Articles\ArticleForm;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_draft_article_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Three Days in Kyoto')
            ->set('slug', 'three-days-in-kyoto')
            ->set('excerpt', 'A calm first-time Kyoto itinerary.')
            ->set('body', '<p>Start in Higashiyama and slow down near the river.</p>')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'title' => 'Three Days in Kyoto',
            'slug' => 'three-days-in-kyoto',
        ]);
    }
}
```

Run:

```bash
php artisan test --filter=ArticleAdminTest
```

Expected: one passing test.

- [ ] **Step 7: Commit article admin**

Run:

```bash
git add platform
git commit -m "feat: add custom article admin screens"
```

---

### Task 6: Destinations, Topics, Tags, Media, and Ads Admin

**Files:**
- Create Livewire components listed in File Structure
- Create views in `platform/resources/views/livewire/admin/destinations/`
- Create views in `platform/resources/views/livewire/admin/topics/`
- Create views in `platform/resources/views/livewire/admin/tags/`
- Create views in `platform/resources/views/livewire/admin/ads/`
- Create: `platform/app/Http/Requests/Admin/MediaUploadRequest.php`
- Create: `platform/app/Services/Media/SafeImageUpload.php`
- Test: `platform/tests/Feature/Admin/TaxonomyAdminTest.php`
- Test: `platform/tests/Feature/Admin/AdPlacementAdminTest.php`
- Test: `platform/tests/Feature/Admin/MediaUploadTest.php`

- [ ] **Step 1: Build simple CRUD components**

Each index component must support list, create, edit, and delete using an inline form above the table. Use Chinese labels:

```text
目的地管理
专题管理
标签管理
广告管理
保存
删除
启用
停用
```

- [ ] **Step 2: Implement safe image upload service**

Create `platform/app/Services/Media/SafeImageUpload.php`:

```php
<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SafeImageUpload
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_BYTES = 4_194_304;

    public function store(UploadedFile $file, User $user, ?string $altText, ?string $sourceNote): MediaAsset
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw ValidationException::withMessages(['file' => '仅允许 JPG、PNG、WebP 图片。']);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages(['file' => '图片大小不能超过 4MB。']);
        }

        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
        $path = $file->store('media/'.now()->format('Y/m'), 'public');

        return MediaAsset::create([
            'uploaded_by' => $user->id,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'alt_text' => $altText,
            'source_note' => $sourceNote,
        ]);
    }
}
```

- [ ] **Step 3: Test ad placement management**

Create `platform/tests/Feature/Admin/AdPlacementAdminTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AdPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdPlacementAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_enabled_ad_placement_can_be_stored(): void
    {
        AdPlacement::create([
            'key' => 'article.body.middle',
            'name' => '文章正文中段广告',
            'page_type' => 'article',
            'position' => 'body_middle',
            'code' => '<ins class="adsbygoogle"></ins>',
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('ad_placements', [
            'key' => 'article.body.middle',
            'is_enabled' => true,
        ]);
    }
}
```

Run:

```bash
php artisan test --filter=AdPlacementAdminTest
```

Expected: one passing test.

- [ ] **Step 4: Commit secondary admin modules**

Run:

```bash
git add platform
git commit -m "feat: add taxonomy media and ad admin modules"
```

---

### Task 7: Public English SEO Pages

**Files:**
- Create controllers in `platform/app/Http/Controllers/Public/`
- Create: `platform/app/Services/Seo/MetaPayload.php`
- Create: `platform/resources/views/layouts/public.blade.php`
- Create public views in `platform/resources/views/public/`
- Modify: `platform/routes/web.php`
- Test: `platform/tests/Feature/Public/PublicPagesTest.php`
- Test: `platform/tests/Feature/Seo/SeoMetaTest.php`

- [ ] **Step 1: Add public routes**

Add to `platform/routes/web.php`:

```php
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, '__invoke'])->name('home');
Route::get('/articles', [\App\Http\Controllers\Public\ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article:slug}', [\App\Http\Controllers\Public\ArticleController::class, 'show'])->name('articles.show');
Route::get('/destinations', [\App\Http\Controllers\Public\DestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [\App\Http\Controllers\Public\DestinationController::class, 'show'])->name('destinations.show');
Route::get('/topics/{topic:slug}', [\App\Http\Controllers\Public\TopicController::class, 'show'])->name('topics.show');
Route::get('/tags/{tag:slug}', [\App\Http\Controllers\Public\TagController::class, 'show'])->name('tags.show');
Route::get('/search', \App\Http\Controllers\Public\SearchController::class)->name('search');
```

- [ ] **Step 2: Implement `MetaPayload`**

Create `platform/app/Services/Seo/MetaPayload.php`:

```php
<?php

namespace App\Services\Seo;

class MetaPayload
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $canonical,
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
        public bool $indexable = true,
    ) {}
}
```

- [ ] **Step 3: Create public layout with SEO tags**

Create `platform/resources/views/layouts/public.blade.php`:

```blade
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $meta->title }}</title>
    @if($meta->description)<meta name="description" content="{{ $meta->description }}">@endif
    <link rel="canonical" href="{{ $meta->canonical }}">
    @unless($meta->indexable)<meta name="robots" content="noindex,nofollow">@endunless
    <meta property="og:title" content="{{ $meta->ogTitle ?? $meta->title }}">
    @if($meta->ogDescription ?? $meta->description)<meta property="og:description" content="{{ $meta->ogDescription ?? $meta->description }}">@endif
    @if($meta->ogImage)<meta property="og:image" content="{{ $meta->ogImage }}">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 text-slate-950 antialiased">
    <header class="border-b bg-white">
        <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4">
            <a class="font-serif text-xl font-bold" href="{{ route('home') }}">Japan Travel Guide</a>
            <div class="flex gap-5 text-sm font-medium">
                <a href="{{ route('destinations.index') }}">Destinations</a>
                <a href="{{ route('articles.index') }}">Articles</a>
                <a href="{{ route('search') }}">Search</a>
            </div>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
```

- [ ] **Step 4: Test published-only article pages**

Create `platform/tests/Feature/Public/PublicPagesTest.php`:

```php
<?php

namespace Tests\Feature\Public;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

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
```

Run:

```bash
php artisan test --filter=PublicPagesTest
```

Expected: two passing tests.

- [ ] **Step 5: Commit public pages**

Run:

```bash
git add platform
git commit -m "feat: add public SEO travel pages"
```

---

### Task 8: Sitemap, Robots, Redirects, and Ads Rendering

**Files:**
- Create: `platform/app/Services/Seo/SitemapBuilder.php`
- Create: `platform/app/Services/Ads/AdRenderer.php`
- Create: `platform/app/Http/Controllers/Public/SitemapController.php`
- Create: `platform/app/Http/Controllers/Public/RobotsController.php`
- Create: `platform/app/Http/Middleware/RedirectOldSlug.php`
- Modify: `platform/routes/web.php`
- Test: `platform/tests/Feature/Seo/SitemapTest.php`
- Test: `platform/tests/Feature/Public/AdRenderingTest.php`

- [ ] **Step 1: Implement ad renderer**

Create `platform/app/Services/Ads/AdRenderer.php`:

```php
<?php

namespace App\Services\Ads;

use App\Models\AdPlacement;
use Illuminate\Support\HtmlString;

class AdRenderer
{
    public function render(string $key): HtmlString
    {
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

- [ ] **Step 2: Add Blade helper for ads**

Add to a service provider boot method:

```php
Blade::directive('ad', function (string $expression): string {
    return "<?php echo app(\\App\\Services\\Ads\\AdRenderer::class)->render($expression); ?>";
});
```

Usage in article page:

```blade
@ad('article.body.middle')
```

- [ ] **Step 3: Implement sitemap controller**

Add routes:

```php
Route::get('/sitemap.xml', \App\Http\Controllers\Public\SitemapController::class)->name('sitemap');
Route::get('/robots.txt', \App\Http\Controllers\Public\RobotsController::class)->name('robots');
```

`SitemapController` returns XML containing only published/indexable articles and indexable destinations/topics.

- [ ] **Step 4: Test sitemap excludes drafts**

Create `platform/tests/Feature/Seo/SitemapTest.php`:

```php
<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_includes_published_articles_and_excludes_drafts(): void
    {
        Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'published-kyoto',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'is_indexable' => true,
        ]);

        Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'draft-kyoto',
            'status' => ArticleStatus::Draft,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee('/articles/published-kyoto')
            ->assertDontSee('/articles/draft-kyoto');
    }
}
```

Run:

```bash
php artisan test --filter=SitemapTest
```

Expected: one passing test.

- [ ] **Step 5: Commit SEO infrastructure**

Run:

```bash
git add platform
git commit -m "feat: add sitemap robots redirects and ads rendering"
```

---

### Task 9: Security Hardening and Audit Logs

**Files:**
- Create: `platform/app/Services/Audit/AuditLogger.php`
- Create: `platform/app/Http/Middleware/LogAdminAction.php`
- Modify: `platform/bootstrap/app.php`
- Modify: `platform/config/purifier.php`
- Modify: `platform/routes/web.php`
- Test: `platform/tests/Feature/Security/AdminSecurityTest.php`
- Test: `platform/tests/Feature/Security/UploadSecurityTest.php`

- [ ] **Step 1: Configure purifier for article body**

Set allowed HTML in `platform/config/purifier.php` to only:

```php
'HTML.Allowed' => 'p,b,strong,i,em,u,a[href|title|rel|target],ul,ol,li,blockquote,h2,h3,h4,img[src|alt|title|width|height],figure,figcaption,br',
'Attr.AllowedFrameTargets' => ['_blank'],
```

- [ ] **Step 2: Add audit logger service**

Create `platform/app/Services/Audit/AuditLogger.php`:

```php
<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(Request $request, string $action, ?Model $subject = null, array $properties = []): void
    {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
```

- [ ] **Step 3: Enforce login rate limiting**

In the login route definition, throttle login attempts using Laravel's rate limiter:

```php
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip().'|'.strtolower((string) $request->input('email')));
});
```

- [ ] **Step 4: Test admin route protection**

Create `platform/tests/Feature/Security/AdminSecurityTest.php`:

```php
<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_require_authenticated_authorized_user(): void
    {
        $this->get('/admin/articles')->assertRedirect('/login');

        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/articles')->assertForbidden();
    }
}
```

Run:

```bash
php artisan test --filter=AdminSecurityTest
```

Expected: one passing test.

- [ ] **Step 5: Commit security baseline**

Run:

```bash
git add platform
git commit -m "feat: harden admin security and audit logging"
```

---

### Task 10: Demo Content, Visual Polish, and Documentation

**Files:**
- Create: `platform/database/seeders/DemoContentSeeder.php`
- Modify: `platform/database/seeders/DatabaseSeeder.php`
- Modify: `platform/resources/css/app.css`
- Create: `platform/README.md`
- Create: `docs/deployment/laravel-platform.md`
- Test: full Laravel test suite

- [ ] **Step 1: Seed English demo content**

Create demo records:

```text
Destination: Tokyo
Destination: Kyoto
Destination: Hokkaido
Topic: Best Time to Visit Japan
Topic: Japan Rail Travel
Tag: cherry blossom
Tag: onsen
Article: Three Days in Kyoto
Article: A First Timer's Guide to Tokyo Neighborhoods
Ad placement: article.body.middle
Ad placement: home.after.hero
```

- [ ] **Step 2: Add quiet editorial styling**

Use a restrained travel editorial style:

```css
@layer components {
    .content-prose {
        @apply prose prose-slate max-w-none prose-headings:font-serif prose-a:text-emerald-800;
    }

    .admin-card {
        @apply rounded-lg border border-slate-200 bg-white p-5 shadow-sm;
    }
}
```

- [ ] **Step 3: Add local run docs**

Create `platform/README.md` with:

````markdown
# Japan Travel Publishing Platform

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

Admin URL: `/admin`

Default local admin:

- Email: `admin@example.com`
- Password: `ChangeMe123!`

Change the default password before any public deployment.
````

- [ ] **Step 4: Run full verification**

Run:

```bash
php artisan test
npm run build
php artisan route:list
```

Expected:

```text
All tests pass
Vite build completes
Routes include /, /articles, /destinations, /admin, /sitemap.xml, /robots.txt
```

- [ ] **Step 5: Commit demo and docs**

Run:

```bash
git add platform docs/deployment
git commit -m "docs: add demo content and local run guide"
```

---

## Final Verification Checklist

- [ ] `git status --short` shows only user-intended uncommitted files.
- [ ] `cd platform && php artisan test` passes.
- [ ] `cd platform && npm run build` passes.
- [ ] Public homepage renders English content.
- [ ] `/admin` renders Chinese admin after login.
- [ ] Article workflow supports draft, review, rejected, scheduled, published, archived.
- [ ] `sitemap.xml` contains only published/indexable content.
- [ ] Disabled or empty ad placements render no visible empty slot.
- [ ] Upload service rejects non-image files and files over 4 MB.
- [ ] No secrets, cookies, passwords beyond local demo credentials, or private user data are committed.

## Self-Review Notes

- Spec coverage: this plan covers Laravel setup, Chinese admin, English front end, five roles, article workflow, versioning, destination/topic/tag/media/ad models, SEO metadata, sitemap, robots, ad rendering, upload security, audit logs, and verification.
- Intentional exclusions: itinerary planner, maps, multilingual front end, member features, and external APIs stay outside MVP.
- Runtime risk: the repo currently lacks PHP/Composer/npm. Task 0 resolves this before Laravel code starts.

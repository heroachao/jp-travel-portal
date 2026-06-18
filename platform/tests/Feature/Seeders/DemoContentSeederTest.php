<?php

namespace Tests\Feature\Seeders;

use App\Models\ArticleFaq;
use App\Models\Destination;
use App\Models\HomepageModule;
use App\Models\HomepageModuleItem;
use App\Models\ServiceLink;
use App\Models\TravelCategory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_phase_one_demo_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(8, TravelCategory::count());
        $this->assertGreaterThanOrEqual(8, Destination::query()->where('is_channel', true)->count());
        $this->assertGreaterThanOrEqual(5, ServiceLink::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(3, HomepageModule::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(2, ArticleFaq::query()->where('is_enabled', true)->count());
        $this->assertGreaterThanOrEqual(6, HomepageModuleItem::query()->where('is_enabled', true)->count());
    }
}

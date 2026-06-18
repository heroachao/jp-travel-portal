<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Tags\TagIndex;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaxonomyAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_can_create_tag_from_chinese_admin(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(TagIndex::class)
            ->set('name', 'Cherry Blossom')
            ->set('slug', 'cherry-blossom')
            ->set('description', 'Seasonal Japan travel tag.')
            ->call('save');

        $this->assertDatabaseHas('tags', [
            'name' => 'Cherry Blossom',
            'slug' => 'cherry-blossom',
        ]);
    }
}

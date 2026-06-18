<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Articles\ArticleForm;
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

<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Articles\ArticleForm;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\TravelCategory;
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

    public function test_editor_can_attach_categories_and_faqs_to_article(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'slug' => 'transport',
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Tokyo Rail Basics')
            ->set('slug', 'tokyo-rail-basics')
            ->set('excerpt', 'Simple rail planning notes for first-time Tokyo trips.')
            ->set('body', '<p>Use IC cards for most city trips.</p>')
            ->set('source_name', 'Tokyo Metro')
            ->set('source_url', 'https://www.tokyometro.jp/')
            ->set('reading_time_minutes', 5)
            ->set('popularity_score', 25)
            ->set('has_coupon', true)
            ->set('selectedCategoryIds', [$category->id])
            ->set('faqs', [
                [
                    'question' => 'Do I need a rail pass in Tokyo?',
                    'answer' => '<p>Most city-only trips work better with IC cards.</p>',
                    'sort_order' => 1,
                    'is_enabled' => true,
                ],
            ])
            ->call('save')
            ->assertRedirect();

        $article = Article::where('slug', 'tokyo-rail-basics')->firstOrFail();

        $this->assertTrue($article->travelCategories()->whereKey($category->id)->exists());
        $this->assertDatabaseHas('article_faqs', [
            'article_id' => $article->id,
            'question' => 'Do I need a rail pass in Tokyo?',
            'is_enabled' => true,
        ]);
    }

    public function test_editor_cannot_use_non_http_source_url_for_article(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Unsafe Source URL')
            ->set('slug', 'unsafe-source-url')
            ->set('body', '<p>Body</p>')
            ->set('source_name', 'Unsafe')
            ->set('source_url', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['source_url']);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'unsafe-source-url',
        ]);
    }

    public function test_editor_sanitizes_article_faq_answers(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Tokyo FAQ Safety')
            ->set('slug', 'tokyo-faq-safety')
            ->set('body', '<p>Body</p>')
            ->set('faqs', [
                [
                    'question' => 'Can FAQ answers include basic HTML?',
                    'answer' => '<script>alert(1)</script><p>Safe FAQ</p>',
                    'sort_order' => 1,
                    'is_enabled' => true,
                ],
            ])
            ->call('save')
            ->assertRedirect();

        $faq = Article::where('slug', 'tokyo-faq-safety')->firstOrFail()->faqs()->firstOrFail();

        $this->assertStringNotContainsString('<script>', $faq->answer);
        $this->assertStringContainsString('<p>Safe FAQ</p>', $faq->answer);
    }

    public function test_editor_article_form_loads_existing_categories_and_faqs(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $displayUpdatedAt = now()->setSeconds(0);
        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'title' => 'Existing Tokyo Rail Guide',
            'slug' => 'existing-tokyo-rail-guide',
            'source_name' => 'JR East',
            'source_url' => 'https://www.jreast.co.jp/',
            'display_updated_at' => $displayUpdatedAt,
            'reading_time_minutes' => 7,
            'popularity_score' => 88,
            'has_coupon' => true,
        ]);
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'slug' => 'transport',
        ]);
        $article->travelCategories()->attach($category->id, ['sort_order' => 0]);
        ArticleFaq::factory()->for($article)->create([
            'question' => 'Which IC card should I use?',
            'answer' => '<p>Suica and PASMO both work well.</p>',
            'sort_order' => 2,
            'is_enabled' => false,
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->assertSet('source_name', 'JR East')
            ->assertSet('source_url', 'https://www.jreast.co.jp/')
            ->assertSet('display_updated_at', $displayUpdatedAt->format('Y-m-d\TH:i'))
            ->assertSet('reading_time_minutes', 7)
            ->assertSet('popularity_score', 88)
            ->assertSet('has_coupon', true)
            ->assertSet('selectedCategoryIds', [$category->id])
            ->assertSet('faqs.0.question', 'Which IC card should I use?')
            ->assertSet('faqs.0.answer', '<p>Suica and PASMO both work well.</p>')
            ->assertSet('faqs.0.sort_order', 2)
            ->assertSet('faqs.0.is_enabled', false);
    }
}

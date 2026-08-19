<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Articles\ArticleForm;
use App\Models\Article;
use App\Models\ArticleFaq;
use App\Models\MediaAsset;
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
        $displayUpdatedAt = now()->setSeconds(0)->setMicrosecond(0);
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
            ->set('display_updated_at', $displayUpdatedAt->format('Y-m-d\TH:i'))
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

        $this->assertSame('Tokyo Metro', $article->source_name);
        $this->assertSame('https://www.tokyometro.jp/', $article->source_url);
        $this->assertTrue($article->display_updated_at->equalTo($displayUpdatedAt));
        $this->assertSame(5, $article->reading_time_minutes);
        $this->assertSame(25, $article->popularity_score);
        $this->assertTrue($article->has_coupon);
        $this->assertTrue($article->travelCategories()->whereKey($category->id)->exists());
        $this->assertDatabaseHas('article_faqs', [
            'article_id' => $article->id,
            'question' => 'Do I need a rail pass in Tokyo?',
            'is_enabled' => true,
        ]);
    }

    public function test_editor_can_save_article_cover_and_og_media(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $cover = MediaAsset::factory()->create([
            'alt_text' => 'Kyoto cover image',
            'path' => 'media/2026/06/kyoto-cover.jpg',
        ]);
        $og = MediaAsset::factory()->create([
            'alt_text' => 'Kyoto social share image',
            'path' => 'media/2026/06/kyoto-og.jpg',
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Kyoto Image Guide')
            ->set('slug', 'kyoto-image-guide')
            ->set('excerpt', 'A Kyoto article with selected media.')
            ->set('body', '<p>Bring good walking shoes.</p>')
            ->set('cover_media_id', (string) $cover->id)
            ->set('og_media_id', $og->id)
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('articles', [
            'slug' => 'kyoto-image-guide',
            'cover_media_id' => $cover->id,
            'og_media_id' => $og->id,
        ]);
    }

    public function test_editor_can_clear_article_media_selection_with_empty_select_values(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $cover = MediaAsset::factory()->create();
        $og = MediaAsset::factory()->create([
            'path' => 'media/2026/06/clear-og.jpg',
        ]);
        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'cover_media_id' => $cover->id,
            'og_media_id' => $og->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->set('cover_media_id', '')
            ->set('og_media_id', '')
            ->call('save')
            ->assertRedirect();

        $article->refresh();

        $this->assertNull($article->cover_media_id);
        $this->assertNull($article->og_media_id);
    }

    public function test_editor_article_form_loads_existing_cover_and_og_media_ids(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $cover = MediaAsset::factory()->create([
            'alt_text' => 'Existing cover image',
        ]);
        $og = MediaAsset::factory()->create([
            'alt_text' => 'Existing OG image',
            'path' => 'media/2026/06/existing-og.jpg',
        ]);
        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'cover_media_id' => $cover->id,
            'og_media_id' => $og->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->assertSet('cover_media_id', $cover->id)
            ->assertSet('og_media_id', $og->id);
    }

    public function test_editor_article_form_keeps_old_selected_media_in_select_options(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $oldTimestamp = now()->subDays(30);
        $newTimestamp = now();
        $oldCover = MediaAsset::factory()->create([
            'alt_text' => 'Very old cover image',
            'path' => 'media/2026/04/very-old-cover.jpg',
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp,
        ]);
        $oldOg = MediaAsset::factory()->create([
            'alt_text' => '',
            'path' => 'media/2026/04/very-old-og.jpg',
            'created_at' => $oldTimestamp->copy()->addSecond(),
            'updated_at' => $oldTimestamp->copy()->addSecond(),
        ]);

        foreach (range(1, 101) as $index) {
            MediaAsset::factory()->create([
                'alt_text' => "New media option {$index}",
                'path' => "media/2026/06/new-media-option-{$index}.jpg",
                'created_at' => $newTimestamp->copy()->addSeconds($index),
                'updated_at' => $newTimestamp->copy()->addSeconds($index),
            ]);
        }

        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'cover_media_id' => $oldCover->id,
            'og_media_id' => $oldOg->id,
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->assertSet('cover_media_id', $oldCover->id)
            ->assertSet('og_media_id', $oldOg->id)
            ->assertSee('Very old cover image')
            ->assertSee('media/2026/04/very-old-og.jpg')
            ->assertSeeHtml('<option value="'.$oldCover->id.'">Very old cover image</option>')
            ->assertSeeHtml('<option value="'.$oldOg->id.'">media/2026/04/very-old-og.jpg</option>');
    }

    public function test_editor_cannot_save_invalid_article_media_ids(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Invalid Media Article')
            ->set('slug', 'invalid-media-article')
            ->set('body', '<p>Body</p>')
            ->set('cover_media_id', 999999)
            ->set('og_media_id', 999998)
            ->call('save')
            ->assertHasErrors([
                'cover_media_id' => 'exists',
                'og_media_id' => 'exists',
            ]);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'invalid-media-article',
        ]);
    }

    public function test_editor_cannot_save_malformed_article_media_id_strings(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Malformed Media Article')
            ->set('slug', 'malformed-media-article')
            ->set('body', '<p>Body</p>')
            ->set('cover_media_id', 'not-an-id')
            ->set('og_media_id', 'still-not-an-id')
            ->call('save')
            ->assertHasErrors([
                'cover_media_id' => 'integer',
                'og_media_id' => 'integer',
            ]);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'malformed-media-article',
        ]);
    }

    public function test_article_form_shows_media_controls_and_seo_preview(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        MediaAsset::factory()->create([
            'alt_text' => 'Arashiyama cover option',
            'path' => 'media/2026/06/arashiyama.jpg',
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Arashiyama Morning Walk')
            ->set('slug', 'arashiyama-morning-walk')
            ->set('excerpt', 'A quiet early walk before the crowds arrive.')
            ->assertSee('文章图片')
            ->assertSee('封面图')
            ->assertSee('社交分享图')
            ->assertSee('SEO 预览')
            ->assertSee('Arashiyama Morning Walk')
            ->assertSee(url('/articles/arashiyama-morning-walk'))
            ->assertSee('A quiet early walk before the crowds arrive.')
            ->assertSee('Arashiyama cover option');
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

    public function test_editor_cannot_use_non_http_canonical_url_for_article(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Unsafe Canonical URL')
            ->set('slug', 'unsafe-canonical-url')
            ->set('body', '<p>Body</p>')
            ->set('canonical_url', 'ftp://example.com/post')
            ->call('save')
            ->assertHasErrors(['canonical_url']);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'unsafe-canonical-url',
        ]);
    }

    public function test_editor_cannot_save_answer_only_article_faq_row(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Answer Only FAQ')
            ->set('slug', 'answer-only-faq')
            ->set('body', '<p>Body</p>')
            ->set('faqs', [
                [
                    'question' => '',
                    'answer' => '<p>This answer needs a question.</p>',
                    'sort_order' => 1,
                    'is_enabled' => true,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['faqs.0.question']);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'answer-only-faq',
        ]);
        $this->assertDatabaseMissing('article_faqs', [
            'answer' => '<p>This answer needs a question.</p>',
        ]);
    }

    public function test_editor_cannot_save_question_only_article_faq_row(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Question Only FAQ')
            ->set('slug', 'question-only-faq')
            ->set('body', '<p>Body</p>')
            ->set('faqs', [
                [
                    'question' => 'This question needs an answer?',
                    'answer' => '',
                    'sort_order' => 1,
                    'is_enabled' => true,
                ],
            ])
            ->call('save')
            ->assertHasErrors(['faqs.0.answer']);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'question-only-faq',
        ]);
        $this->assertDatabaseMissing('article_faqs', [
            'question' => 'This question needs an answer?',
        ]);
    }

    public function test_editor_cannot_save_malformed_article_faq_row(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Malformed FAQ')
            ->set('slug', 'malformed-faq')
            ->set('body', '<p>Body</p>')
            ->set('faqs', ['not-a-faq-row'])
            ->call('save')
            ->assertHasErrors(['faqs.0']);

        $this->assertDatabaseMissing('articles', [
            'slug' => 'malformed-faq',
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

    public function test_editor_sanitizes_article_body_before_raw_rendering(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class)
            ->set('title', 'Tokyo Article Body Safety')
            ->set('slug', 'tokyo-article-body-safety')
            ->set('body', '<script>alert(1)</script><p>Safe article body.</p>')
            ->call('save')
            ->assertRedirect();

        $body = Article::where('slug', 'tokyo-article-body-safety')->firstOrFail()->body;

        $this->assertStringNotContainsString('<script>', $body);
        $this->assertStringContainsString('<p>Safe article body.</p>', $body);
    }

    public function test_editor_can_replace_article_faqs_and_clear_categories(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $article = Article::factory()->create([
            'author_id' => $editor->id,
            'title' => 'Existing FAQ Guide',
            'slug' => 'existing-faq-guide',
        ]);
        $category = TravelCategory::factory()->create([
            'title' => 'Transport',
            'slug' => 'transport',
        ]);
        $article->travelCategories()->attach($category->id, ['sort_order' => 0]);
        ArticleFaq::factory()->for($article)->create([
            'question' => 'Old question?',
            'answer' => '<p>Old answer.</p>',
            'sort_order' => 1,
            'is_enabled' => true,
        ]);

        $this->actingAs($editor);

        Livewire::test(ArticleForm::class, ['article' => $article])
            ->set('selectedCategoryIds', [])
            ->set('faqs', [
                [
                    'question' => 'New question?',
                    'answer' => '<p>New answer.</p>',
                    'sort_order' => 3,
                    'is_enabled' => false,
                ],
            ])
            ->call('save')
            ->assertRedirect();

        $article->refresh();

        $this->assertFalse($article->travelCategories()->whereKey($category->id)->exists());
        $this->assertDatabaseMissing('article_faqs', [
            'article_id' => $article->id,
            'question' => 'Old question?',
        ]);
        $this->assertDatabaseHas('article_faqs', [
            'article_id' => $article->id,
            'question' => 'New question?',
            'sort_order' => 3,
            'is_enabled' => false,
        ]);
    }

    public function test_editor_article_form_loads_existing_categories_and_faqs(): void
    {
        $this->seed(RoleSeeder::class);
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $displayUpdatedAt = now()->setSeconds(0)->setMicrosecond(0);
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

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

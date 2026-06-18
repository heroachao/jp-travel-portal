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

    public function archive(Article $article, User $actor): Article
    {
        return $this->transition($article, $actor, ArticleStatus::Archived, 'archived');
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
        $fresh = $article->fresh();

        $article->versions()->create([
            'user_id' => $actor->id,
            'event' => $event,
            'snapshot' => [
                'title' => $fresh->title,
                'slug' => $fresh->slug,
                'excerpt' => $fresh->excerpt,
                'body' => $fresh->body,
                'status' => $fresh->status->value,
                'seo_title' => $fresh->seo_title,
                'meta_description' => $fresh->meta_description,
                'canonical_url' => $fresh->canonical_url,
                'is_indexable' => $fresh->is_indexable,
            ],
        ]);
    }

    private function ensureStatus(Article $article, array $allowed): void
    {
        if (! in_array($article->status, $allowed, true)) {
            throw new InvalidArgumentException("Article status {$article->status->value} cannot transition here.");
        }
    }
}

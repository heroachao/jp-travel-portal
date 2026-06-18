<?php

namespace Tests\Feature\Seo;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_outputs_noindex_when_disabled(): void
    {
        $article = Article::factory()->create([
            'author_id' => User::factory(),
            'slug' => 'private-but-published',
            'status' => ArticleStatus::Published,
            'published_at' => now(),
            'is_indexable' => false,
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}

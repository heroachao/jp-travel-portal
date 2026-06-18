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

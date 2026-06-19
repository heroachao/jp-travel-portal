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

    public function test_compliance_pages_are_public_and_indexable(): void
    {
        foreach ([
            '/about' => 'About Japan Trip Tools',
            '/contact' => 'Contact Japan Trip Tools',
            '/privacy-policy' => 'Privacy Policy',
            '/privacy' => 'Privacy Policy',
            '/terms' => 'Terms of Use',
            '/disclaimer' => 'Travel Information Disclaimer',
        ] as $path => $heading) {
            $this->get($path)
                ->assertOk()
                ->assertSee($heading)
                ->assertDontSee('noindex,nofollow');
        }
    }

    public function test_footer_links_to_compliance_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('pages.about').'"', false)
            ->assertSee('href="'.route('pages.contact').'"', false)
            ->assertSee('href="'.route('pages.privacy').'"', false)
            ->assertSee('href="'.route('pages.terms').'"', false)
            ->assertSee('href="'.route('pages.disclaimer').'"', false);
    }

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

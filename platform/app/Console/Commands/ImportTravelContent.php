<?php

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\TravelCategory;
use App\Models\User;
use App\Support\PublicUrl;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use JsonException;

class ImportTravelContent extends Command
{
    protected $signature = 'content:import-travel
        {manifest=database/content/japan-official-curated.json : JSON content manifest path}
        {--publish : Publish imported articles immediately}
        {--dry-run : Validate and report without writing}
        {--limit= : Import only the first N entries}';

    protected $description = 'Import curated Japan travel content from official-source manifests.';

    public function handle(): int
    {
        $manifestPath = $this->resolveManifestPath((string) $this->argument('manifest'));

        if (! is_file($manifestPath)) {
            $this->error("Manifest not found: {$manifestPath}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode((string) file_get_contents($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Manifest is not valid JSON: '.$exception->getMessage());

            return self::FAILURE;
        }

        $articles = collect($payload['articles'] ?? []);
        $limit = $this->option('limit');

        if ($limit !== null && $limit !== '') {
            $articles = $articles->take((int) $limit);
        }

        if ($articles->isEmpty()) {
            $this->warn('No articles found in manifest.');

            return self::SUCCESS;
        }

        $this->info('Validated '.$articles->count().' manifest entries.');

        if ($this->option('dry-run')) {
            $articles->each(fn (array $entry): null => $this->line('DRY RUN '.$entry['slug'].' | '.$entry['title']));

            return self::SUCCESS;
        }

        $author = User::firstOrCreate(
            ['email' => 'content-importer@example.com'],
            ['name' => 'Content Importer', 'password' => Hash::make(Str::random(32))]
        );

        $published = (bool) $this->option('publish');
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($articles, $author, $published, &$created, &$updated): void {
            foreach ($articles as $entry) {
                $this->validateEntry($entry);

                $body = $this->buildBodyHtml($entry);
                $wordCount = str_word_count(strip_tags($body));
                $publishedAt = filled($entry['published_at'] ?? null)
                    ? CarbonImmutable::parse((string) $entry['published_at'])
                    : now();
                $primarySource = collect($entry['sources'] ?? [])->first();
                $existing = Article::query()->where('slug', $entry['slug'])->first();

                $article = Article::updateOrCreate(
                    ['slug' => $entry['slug']],
                    [
                        'author_id' => $existing?->author_id ?? $author->id,
                        'title' => $entry['title'],
                        'excerpt' => $entry['excerpt'],
                        'body' => $body,
                        'source_name' => $primarySource['name'] ?? null,
                        'source_url' => $primarySource['url'] ?? null,
                        'status' => $published ? ArticleStatus::Published : ($existing?->status ?? ArticleStatus::Draft),
                        'published_at' => $published ? $publishedAt : $existing?->published_at,
                        'display_updated_at' => now(),
                        'reading_time_minutes' => max(1, (int) ceil($wordCount / 220)),
                        'popularity_score' => (int) ($entry['popularity_score'] ?? 0),
                        'has_coupon' => (bool) ($entry['has_coupon'] ?? false),
                        'seo_title' => $entry['seo_title'] ?? $entry['title'].' | Japan Travel Guide',
                        'meta_description' => $entry['meta_description'] ?? Str::limit($entry['excerpt'], 155, ''),
                        'is_indexable' => (bool) ($entry['is_indexable'] ?? true),
                        'structured_data_type' => $entry['structured_data_type'] ?? 'Article',
                    ]
                );

                $article->travelCategories()->sync($this->categoryIds($entry['category_slugs'] ?? []));
                $article->destinations()->sync($this->destinationIds($entry['destination_slugs'] ?? []));
                $article->topics()->sync($this->topicIds($entry['topic_slugs'] ?? []));
                $article->tags()->sync($this->tagIds($entry['tag_slugs'] ?? []));

                $article->faqs()->delete();
                foreach ($entry['faqs'] ?? [] as $index => $faq) {
                    if (filled($faq['question'] ?? null) && filled($faq['answer'] ?? null)) {
                        $article->faqs()->create([
                            'question' => $faq['question'],
                            'answer' => '<p>'.e($faq['answer']).'</p>',
                            'sort_order' => $index + 1,
                            'is_enabled' => true,
                        ]);
                    }
                }

                $existing ? $updated++ : $created++;
                $this->line(($existing ? 'Updated ' : 'Created ').$article->slug);
            }
        });

        $this->info("Import complete. Created {$created}, updated {$updated}.");

        return self::SUCCESS;
    }

    private function resolveManifestPath(string $path): string
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path);
    }

    private function validateEntry(array $entry): void
    {
        foreach (['slug', 'title', 'excerpt', 'sections', 'sources'] as $field) {
            if (blank($entry[$field] ?? null)) {
                throw new \InvalidArgumentException("Manifest entry is missing {$field}.");
            }
        }
    }

    private function buildBodyHtml(array $entry): string
    {
        $html = '';

        if (isset($entry['image']) && is_array($entry['image'])) {
            $image = $entry['image'];
            $creditUrl = PublicUrl::route('image-credits').'#article-'.$entry['slug'];
            $license = $image['license'] ?? 'Open license';
            $attribution = $image['attribution'] ?? 'Image source';

            $html .= '<figure class="public-card public-image-figure my-6 overflow-hidden"';
            if (filled($image['source_url'] ?? null)) {
                $html .= ' data-image-source-url="'.e($image['source_url']).'"';
            }
            if (filled($image['license_url'] ?? null)) {
                $html .= ' data-image-license-url="'.e($image['license_url']).'"';
            }
            $html .= ' data-image-license="'.e($license).'"';
            $html .= ' data-image-attribution="'.e($attribution).'"';
            $html .= '>';
            $html .= '<img src="'.e($entry['image']['url']).'" alt="'.e($entry['image']['alt'] ?? $entry['title']).'" class="aspect-[16/9] w-full object-cover">';
            $html .= '<figcaption class="px-4 py-3 text-sm text-slate-600">';
            $html .= e($entry['image']['caption'] ?? '');
            $html .= ' Image: '.e($attribution).' / '.e($license).'.';
            if (filled($entry['image']['source_url'] ?? null)) {
                $html .= ' <a href="'.e($creditUrl).'">Image credit details</a>.';
            }
            $html .= '</figcaption></figure>';
        }

        foreach ($entry['sections'] as $section) {
            $html .= '<section>';
            $html .= '<h2>'.e($section['heading']).'</h2>';

            foreach ($section['paragraphs'] ?? [] as $paragraph) {
                $html .= '<p>'.e($paragraph).'</p>';
            }

            if (! empty($section['bullets'])) {
                $html .= '<ul>';
                foreach ($section['bullets'] as $bullet) {
                    $html .= '<li>'.e($bullet).'</li>';
                }
                $html .= '</ul>';
            }

            $html .= '</section>';
        }

        if (! empty($entry['internal_links']) && is_array($entry['internal_links'])) {
            $html .= '<section><h2>Use next on Japan Trip Tools</h2>';
            $html .= '<ul>';
            foreach ($entry['internal_links'] as $link) {
                if (blank($link['slug'] ?? null) || blank($link['title'] ?? null)) {
                    continue;
                }

                $html .= '<li><a href="'.e(PublicUrl::route('articles.show', ['article' => $link['slug']])).'">'.e($link['title']).'</a>';
                if (filled($link['reason'] ?? null)) {
                    $html .= ' — '.e($link['reason']);
                }
                $html .= '</li>';
            }
            $html .= '</ul></section>';
        }

        $html .= '<section><h2>Sources and image licensing</h2>';
        $html .= '<p>This article is an original English summary written from official tourism and transport sources. It is not a copied translation of those pages.</p>';
        $html .= '<ul>';
        foreach ($entry['sources'] as $source) {
            $html .= '<li><a href="'.e($source['url']).'" rel="nofollow noopener noreferrer" target="_blank">'.e($source['name']).'</a></li>';
        }
        if (isset($entry['image']['source_url'])) {
            $html .= '<li><a href="'.e(PublicUrl::route('image-credits').'#article-'.$entry['slug']).'">Image credit and source record</a> — '.e($entry['image']['license'] ?? 'Open license').'</li>';
        }
        $html .= '</ul></section>';

        return $html;
    }

    private function categoryIds(array $slugs): array
    {
        return TravelCategory::query()->whereIn('slug', $slugs)->pluck('id')->all();
    }

    private function destinationIds(array $slugs): array
    {
        return Destination::query()->whereIn('slug', $slugs)->pluck('id')->all();
    }

    private function topicIds(array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug): int => Topic::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => Str::headline(str_replace('-', ' ', $slug)),
                    'excerpt' => null,
                    'body' => null,
                    'seo_title' => null,
                    'meta_description' => null,
                    'is_indexable' => true,
                ]
            )->id)
            ->all();
    }

    private function tagIds(array $slugs): array
    {
        return collect($slugs)
            ->map(fn (string $slug): int => Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => str_replace('-', ' ', $slug), 'description' => null]
            )->id)
            ->all();
    }
}

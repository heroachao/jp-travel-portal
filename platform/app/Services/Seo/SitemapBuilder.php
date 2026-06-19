<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Destination;
use App\Models\Tag;
use App\Models\Topic;
use App\Models\TravelCategory;
use App\Support\JapanGames;
use App\Support\PublicUrl;
use App\Support\TravelTools;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class SitemapBuilder
{
    public function build(): string
    {
        $urls = collect()
            ->merge($this->staticUrls())
            ->merge($this->articleUrls())
            ->merge($this->destinationUrls())
            ->merge($this->categoryUrls())
            ->merge($this->topicUrls())
            ->merge($this->tagUrls());

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', htmlspecialchars($entry['loc'], ENT_XML1));
            if ($entry['lastmod']) {
                $url->addChild('lastmod', $entry['lastmod']);
            }
        }

        return $xml->asXML() ?: '';
    }

    private function staticUrls(): Collection
    {
        return collect([
            ['loc' => PublicUrl::route('home'), 'lastmod' => null],
            ['loc' => PublicUrl::route('articles.index'), 'lastmod' => null],
            ['loc' => PublicUrl::route('tools.index'), 'lastmod' => null],
            ['loc' => PublicUrl::route('games.index'), 'lastmod' => null],
            ['loc' => PublicUrl::route('image-credits'), 'lastmod' => null],
            ['loc' => PublicUrl::route('regions.index'), 'lastmod' => null],
            ['loc' => PublicUrl::route('destinations.index'), 'lastmod' => null],
            ['loc' => PublicUrl::route('pages.about'), 'lastmod' => null],
            ['loc' => PublicUrl::route('pages.contact'), 'lastmod' => null],
            ['loc' => PublicUrl::route('pages.privacy'), 'lastmod' => null],
            ['loc' => PublicUrl::route('pages.terms'), 'lastmod' => null],
            ['loc' => PublicUrl::route('pages.disclaimer'), 'lastmod' => null],
        ])->merge(
            collect(TravelTools::slugs())->map(fn (string $slug) => [
                'loc' => PublicUrl::route('tools.show', $slug),
                'lastmod' => null,
            ])
        )->merge(
            collect(JapanGames::slugs())->map(fn (string $slug) => [
                'loc' => PublicUrl::route('games.show', $slug),
                'lastmod' => null,
            ])
        );
    }

    private function articleUrls(): Collection
    {
        return Article::published()
            ->where('is_indexable', true)
            ->get()
            ->map(fn (Article $article) => [
                'loc' => PublicUrl::route('articles.show', $article),
                'lastmod' => $article->updated_at?->toAtomString(),
            ]);
    }

    private function destinationUrls(): Collection
    {
        return Destination::query()
            ->where('is_indexable', true)
            ->where('is_channel', true)
            ->get()
            ->map(fn (Destination $destination) => [
                'loc' => PublicUrl::route('regions.show', $destination),
                'lastmod' => $destination->updated_at?->toAtomString(),
            ]);
    }

    private function categoryUrls(): Collection
    {
        return TravelCategory::query()
            ->where('is_indexable', true)
            ->where('is_visible', true)
            ->get()
            ->map(fn (TravelCategory $category) => [
                'loc' => PublicUrl::route('categories.show', $category),
                'lastmod' => $category->updated_at?->toAtomString(),
            ]);
    }

    private function topicUrls(): Collection
    {
        return Topic::query()
            ->where('is_indexable', true)
            ->whereNotNull('body')
            ->where('body', '!=', '')
            ->whereNotNull('meta_description')
            ->where('meta_description', '!=', '')
            ->whereHas('articles', fn ($query) => $query->published(), '>=', 3)
            ->get()
            ->map(fn (Topic $topic) => [
                'loc' => PublicUrl::route('topics.show', $topic),
                'lastmod' => $topic->updated_at?->toAtomString(),
            ]);
    }

    private function tagUrls(): Collection
    {
        return Tag::query()
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->whereHas('articles', fn ($query) => $query->published(), '>=', 3)
            ->get()
            ->map(fn (Tag $tag) => [
                'loc' => PublicUrl::route('tags.show', $tag),
                'lastmod' => $tag->updated_at?->toAtomString(),
            ]);
    }
}

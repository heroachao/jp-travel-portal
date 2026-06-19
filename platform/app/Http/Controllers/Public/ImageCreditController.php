<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Seo\MetaPayload;
use App\Support\PublicUrl;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImageCreditController extends Controller
{
    public function __invoke(): View
    {
        $credits = Article::published()
            ->where('body', 'like', '%data-image-source-url%')
            ->latest('published_at')
            ->get()
            ->map(fn (Article $article): ?array => $this->extractCredit($article))
            ->filter()
            ->values();

        return view('public.image-credits', [
            'meta' => new MetaPayload(
                'Image Credits | Japan Trip Tools',
                'Image attribution, source, and license records for Japan Trip Tools article images.',
                PublicUrl::route('image-credits'),
            ),
            'credits' => $credits,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractCredit(Article $article): ?array
    {
        if (! preg_match('/<figure\b([^>]*)>/i', (string) $article->body, $figureMatch)) {
            return null;
        }

        $attributes = $this->parseDataAttributes($figureMatch[1]);
        $sourceUrl = $attributes['image-source-url'] ?? null;

        if (! $sourceUrl) {
            return null;
        }

        $caption = null;
        if (preg_match('/<figcaption[^>]*>(.*?)<\/figcaption>/is', (string) $article->body, $captionMatch)) {
            $caption = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($captionMatch[1], ENT_QUOTES | ENT_HTML5))) ?? '');
            $caption = Str::of($caption)->before(' Image credit details')->trim()->toString();
        }

        return [
            'anchor' => 'article-'.$article->slug,
            'article' => $article,
            'title' => $article->title,
            'slug' => $article->slug,
            'url' => PublicUrl::route('articles.show', $article),
            'image_url' => $article->firstImageUrl(),
            'image_alt' => $article->firstImageAlt(),
            'caption' => $caption,
            'attribution' => $attributes['image-attribution'] ?? 'Image source',
            'license' => $attributes['image-license'] ?? 'Open license',
            'license_url' => $attributes['image-license-url'] ?? null,
            'source_url' => $sourceUrl,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parseDataAttributes(string $attributeHtml): array
    {
        preg_match_all('/\sdata-([a-z0-9-]+)=([\"\'])(.*?)\2/is', $attributeHtml, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $match) {
            $attributes[$match[1]] = html_entity_decode($match[3], ENT_QUOTES | ENT_HTML5);
        }

        return $attributes;
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Content\ArticleContentEnhancer;
use App\Services\Seo\MetaPayload;
use App\Services\Settings\SiteSettings;
use App\Support\PublicUrl;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleContentEnhancer $contentEnhancer) {}

    public function index(): View
    {
        return view('public.articles.index', [
            'meta' => new MetaPayload('Japan Travel Articles', 'Latest Japan travel guides and practical planning notes.', PublicUrl::route('articles.index')),
            'articles' => Article::published()->where('is_indexable', true)->latest('published_at')->paginate(240),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status->value === 'published' && $article->published_at?->lte(now()), 404);

        $article->load([
            'destinations' => fn ($query) => $query->where('is_indexable', true)->ordered(),
            'topics' => fn ($query) => $query->where('is_indexable', true)->orderBy('title'),
            'tags' => fn ($query) => $query->orderBy('name'),
            'travelCategories' => fn ($query) => $query->visible(),
            'enabledFaqs',
            'author',
            'coverMedia',
            'ogMedia',
        ]);

        $ogMedia = $article->ogMedia ?: $article->coverMedia;
        $ogImage = $ogMedia ? Storage::disk($ogMedia->disk)->url($ogMedia->path) : null;
        $absoluteOgImage = $ogImage && str_starts_with($ogImage, 'http') ? $ogImage : ($ogImage ? url($ogImage) : null);
        $canonical = $article->canonical_url
            ? PublicUrl::canonicalize($article->canonical_url)
            : PublicUrl::route('articles.show', $article);
        $siteSettings = app(SiteSettings::class)->current();
        $contentEnhancement = $this->contentEnhancer->build($article);
        $keywords = $article->tags
            ->pluck('name')
            ->merge($article->topics->pluck('title'))
            ->merge($article->destinations->map(fn ($destination) => $destination->display_name ?: $destination->name))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $articleSections = $article->travelCategories
            ->map(fn ($category) => $category->display_name ?: $category->title)
            ->filter()
            ->values()
            ->all();

        $faqJsonLd = $article->enabledFaqs->isEmpty() ? null : [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $article->enabledFaqs
                ->map(fn ($faq) => [
                    '@type' => 'Question',
                    'name' => $faq->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => trim(strip_tags($faq->answer)),
                    ],
                ])
                ->values()
                ->all(),
        ];
        $articleJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => $article->structured_data_type ?: 'Article',
            'headline' => $article->title,
            'description' => $article->meta_description ?: $article->excerpt,
            'url' => $canonical,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonical,
            ],
            'datePublished' => $article->published_at?->toAtomString(),
            'dateModified' => ($article->display_updated_at ?: $article->updated_at)?->toAtomString(),
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
            'wordCount' => $contentEnhancement['word_count'],
            'articleSection' => $articleSections,
            'keywords' => $keywords,
            'author' => [
                '@type' => 'Organization',
                'name' => $article->author?->name ?: $siteSettings->site_name,
            ],
            'reviewedBy' => [
                '@type' => 'Organization',
                'name' => $siteSettings->site_name.' editorial desk',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteSettings->site_name,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/japan-trip-tools-logo.png'),
                ],
            ],
        ];

        if ($absoluteOgImage) {
            $articleJsonLd['image'] = [$absoluteOgImage];
        }

        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Japan Trip Tools',
                    'item' => PublicUrl::route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Articles',
                    'item' => PublicUrl::route('articles.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $article->title,
                    'item' => $canonical,
                ],
            ],
        ];

        return view('public.articles.show', [
            'meta' => new MetaPayload(
                $article->seo_title ?: $article->title,
                $article->meta_description,
                $canonical,
                $article->og_title,
                $article->og_description,
                $absoluteOgImage,
                $article->is_indexable,
            ),
            'article' => $article,
            'contentEnhancement' => $contentEnhancement,
            'faqJsonLd' => $faqJsonLd,
            'articleJsonLd' => $articleJsonLd,
            'breadcrumbJsonLd' => $breadcrumbJsonLd,
        ]);
    }
}

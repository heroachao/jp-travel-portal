<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Seo\MetaPayload;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('public.articles.index', [
            'meta' => new MetaPayload('Japan Travel Articles', 'Latest Japan travel guides and practical planning notes.', route('articles.index')),
            'articles' => Article::published()->latest('published_at')->paginate(240),
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
            'coverMedia',
            'ogMedia',
        ]);

        $ogMedia = $article->ogMedia ?: $article->coverMedia;
        $ogImage = $ogMedia ? Storage::disk($ogMedia->disk)->url($ogMedia->path) : null;

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

        return view('public.articles.show', [
            'meta' => new MetaPayload(
                $article->seo_title ?: $article->title,
                $article->meta_description,
                $article->canonical_url ?: route('articles.show', $article),
                $article->og_title,
                $article->og_description,
                $ogImage,
                $article->is_indexable,
            ),
            'article' => $article,
            'faqJsonLd' => $faqJsonLd,
        ]);
    }
}

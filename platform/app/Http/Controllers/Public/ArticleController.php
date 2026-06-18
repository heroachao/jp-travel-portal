<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('public.articles.index', [
            'meta' => new MetaPayload('Japan Travel Articles', 'Latest Japan travel guides and practical planning notes.', route('articles.index')),
            'articles' => Article::published()->latest('published_at')->paginate(12),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status->value === 'published' && $article->published_at?->lte(now()), 404);

        $article->load(['destinations', 'topics', 'tags']);

        return view('public.articles.show', [
            'meta' => new MetaPayload(
                $article->seo_title ?: $article->title,
                $article->meta_description,
                $article->canonical_url ?: route('articles.show', $article),
                $article->og_title,
                $article->og_description,
                null,
                $article->is_indexable,
            ),
            'article' => $article,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Seo\MetaPayload;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        return view('public.search', [
            'meta' => new MetaPayload('Search Japan Travel Guides', 'Search published Japan travel articles.', route('search')),
            'q' => $query,
            'articles' => Article::published()
                ->when($query !== '', fn ($builder) => $builder->where(function ($inner) use ($query): void {
                    $inner->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%");
                }))
                ->latest('published_at')
                ->paginate(12),
        ]);
    }
}

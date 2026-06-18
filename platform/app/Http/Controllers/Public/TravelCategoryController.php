<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\TravelCategory;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class TravelCategoryController extends Controller
{
    public function show(TravelCategory $category): View
    {
        abort_unless($category->is_visible, 404);

        $category->load(['children' => fn ($query) => $query->visible()->ordered()]);

        return view('public.categories.show', [
            'meta' => new MetaPayload(
                $category->seo_title ?: $category->title,
                $category->meta_description,
                route('categories.show', $category),
                indexable: $category->is_indexable,
            ),
            'category' => $category,
            'articles' => $category->articles()->published()->latest('published_at')->paginate(12),
        ]);
    }
}

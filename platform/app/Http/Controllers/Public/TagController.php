<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\Seo\MetaPayload;
use App\Support\PublicUrl;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $tag->load(['articles' => fn ($query) => $query->published()->where('is_indexable', true)->latest('published_at')]);
        $isSearchIndexable = $tag->articles->count() >= 3 && filled($tag->description);

        return view('public.tags.show', [
            'meta' => new MetaPayload(
                $tag->name.' Japan Travel Guides',
                $tag->description ?: 'Related Japan travel guides and planning notes.',
                PublicUrl::route('tags.show', $tag),
                indexable: $isSearchIndexable,
            ),
            'tag' => $tag,
            'isSearchIndexable' => $isSearchIndexable,
        ]);
    }
}

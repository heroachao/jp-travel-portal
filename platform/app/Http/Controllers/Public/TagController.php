<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class TagController extends Controller
{
    public function show(Tag $tag): View
    {
        $tag->load(['articles' => fn ($query) => $query->published()->latest('published_at')]);

        return view('public.tags.show', [
            'meta' => new MetaPayload($tag->name.' Japan Travel Guides', $tag->description, route('tags.show', $tag)),
            'tag' => $tag,
        ]);
    }
}

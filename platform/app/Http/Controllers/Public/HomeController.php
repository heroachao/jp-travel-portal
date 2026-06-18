<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Destination;
use App\Models\Topic;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'meta' => new MetaPayload(
                'Japan Travel Guide | Practical Itineraries, Destinations, and Tips',
                'Independent Japan travel guides, destination hubs, itineraries, and practical planning notes.',
                url()->current(),
            ),
            'articles' => Article::published()->latest('published_at')->limit(6)->get(),
            'destinations' => Destination::query()->where('is_indexable', true)->latest()->limit(6)->get(),
            'topics' => Topic::query()->where('is_indexable', true)->latest()->limit(4)->get(),
        ]);
    }
}

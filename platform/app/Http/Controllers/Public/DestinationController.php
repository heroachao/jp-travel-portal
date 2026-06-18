<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function regions(): View
    {
        return view('public.regions.index', [
            'meta' => new MetaPayload('Japan Regions', 'Explore Japan region guides and related travel planning articles.', route('regions.index')),
            'regions' => Destination::query()
                ->channel()
                ->where('is_indexable', true)
                ->ordered()
                ->get(),
        ]);
    }

    public function region(Destination $destination): View
    {
        abort_unless($destination->is_channel && $destination->is_indexable, 404);

        $destination->load(['children' => fn ($query) => $query->where('is_indexable', true)->ordered()]);

        return view('public.regions.show', [
            'meta' => new MetaPayload(
                $destination->seo_title ?: $destination->name.' Travel Guide',
                $destination->meta_description,
                route('regions.show', $destination),
            ),
            'destination' => $destination,
            'articles' => $destination->articles()->published()->latest('published_at')->paginate(12),
        ]);
    }

    public function index(): View
    {
        return view('public.destinations.index', [
            'meta' => new MetaPayload('Japan Destinations', 'Explore Japanese regions, cities, attractions, and related travel guides.', route('destinations.index')),
            'destinations' => Destination::query()->where('is_indexable', true)->latest()->paginate(24),
        ]);
    }

    public function show(Destination $destination): View
    {
        abort_unless($destination->is_indexable, 404);

        $destination->load(['articles' => fn ($query) => $query->published()->latest('published_at'), 'topics']);

        return view('public.destinations.show', [
            'meta' => new MetaPayload(
                $destination->seo_title ?: $destination->name.' Travel Guide',
                $destination->meta_description,
                route('destinations.show', $destination),
            ),
            'destination' => $destination,
        ]);
    }
}

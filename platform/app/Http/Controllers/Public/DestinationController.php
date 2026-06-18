<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Services\Seo\MetaPayload;
use Illuminate\View\View;

class DestinationController extends Controller
{
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

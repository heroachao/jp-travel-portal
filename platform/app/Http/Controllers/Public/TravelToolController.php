<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\MetaPayload;
use App\Support\TravelTools;
use Illuminate\View\View;

class TravelToolController extends Controller
{
    public function index(): View
    {
        return view('public.tools.index', [
            'meta' => new MetaPayload(
                'Japan Travel Tools',
                'Free on-site Japan travel calculators, route planners, packing lists, and practical trip tools.',
                route('tools.index'),
            ),
            'tools' => TravelTools::all(),
        ]);
    }

    public function show(string $tool): View
    {
        $toolConfig = TravelTools::find($tool);

        abort_unless($toolConfig !== null, 404);

        return view('public.tools.show', [
            'meta' => new MetaPayload(
                $toolConfig['name'].' | Japan Travel Tools',
                $toolConfig['meta_description'],
                route('tools.show', $toolConfig['slug']),
            ),
            'tool' => $toolConfig,
            'tools' => TravelTools::all(),
        ]);
    }
}

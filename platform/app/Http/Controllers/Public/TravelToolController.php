<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\MetaPayload;
use App\Support\PublicUrl;
use App\Support\TravelTools;
use Illuminate\View\View;

class TravelToolController extends Controller
{
    public function index(): View
    {
        $tools = TravelTools::all();
        $toolsItemListJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Japan Travel Tools',
            'itemListElement' => collect($tools)
                ->values()
                ->map(fn (array $tool, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $tool['name'],
                    'url' => PublicUrl::route('tools.show', $tool['slug']),
                ])
                ->all(),
        ];

        return view('public.tools.index', [
            'meta' => new MetaPayload(
                'Japan Travel Tools',
                'Free on-site Japan travel calculators, route planners, packing lists, and practical trip tools.',
                PublicUrl::route('tools.index'),
            ),
            'tools' => $tools,
            'toolsItemListJsonLd' => $toolsItemListJsonLd,
        ]);
    }

    public function show(string $tool): View
    {
        $toolConfig = TravelTools::find($tool);

        abort_unless($toolConfig !== null, 404);

        $toolJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $toolConfig['name'],
            'url' => PublicUrl::route('tools.show', $toolConfig['slug']),
            'applicationCategory' => 'TravelApplication',
            'operatingSystem' => 'All',
            'description' => $toolConfig['meta_description'],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];
        $toolFaqJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($toolConfig['faqs'])
                ->map(fn (array $faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['answer'],
                    ],
                ])
                ->all(),
        ];
        $breadcrumbJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Japan Trip Tools',
                    'item' => PublicUrl::route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Tools',
                    'item' => PublicUrl::route('tools.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $toolConfig['name'],
                    'item' => PublicUrl::route('tools.show', $toolConfig['slug']),
                ],
            ],
        ];

        return view('public.tools.show', [
            'meta' => new MetaPayload(
                $toolConfig['name'].' | Japan Travel Tools',
                $toolConfig['meta_description'],
                PublicUrl::route('tools.show', $toolConfig['slug']),
            ),
            'tool' => $toolConfig,
            'tools' => TravelTools::all(),
            'toolJsonLd' => $toolJsonLd,
            'toolFaqJsonLd' => $toolFaqJsonLd,
            'breadcrumbJsonLd' => $breadcrumbJsonLd,
        ]);
    }
}

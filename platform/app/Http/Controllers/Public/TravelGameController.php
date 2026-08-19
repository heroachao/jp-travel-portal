<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Seo\MetaPayload;
use App\Support\JapanGames;
use App\Support\PublicUrl;
use Illuminate\View\View;

class TravelGameController extends Controller
{
    public function index(): View
    {
        $games = JapanGames::all();
        $gamesItemListJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Japan Travel Games',
            'itemListElement' => collect($games)
                ->values()
                ->map(fn (array $game, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $game['name'],
                    'url' => PublicUrl::route('games.show', $game['slug']),
                ])
                ->all(),
        ];

        return view('public.games.index', [
            'meta' => new MetaPayload(
                'Free Japan Travel Games',
                'Play free Japan-themed browser games including word puzzles, 2048, snake, memory cards, typing, minesweeper, match-three, and ramen order games.',
                PublicUrl::route('games.index'),
            ),
            'games' => $games,
            'gamesItemListJsonLd' => $gamesItemListJsonLd,
        ]);
    }

    public function show(string $game): View
    {
        $gameConfig = JapanGames::find($game);

        abort_unless($gameConfig !== null, 404);

        $gameJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoGame',
            'name' => $gameConfig['name'],
            'url' => PublicUrl::route('games.show', $gameConfig['slug']),
            'applicationCategory' => 'Game',
            'operatingSystem' => 'All',
            'gamePlatform' => 'Web browser',
            'genre' => $gameConfig['type'],
            'description' => $gameConfig['meta_description'],
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
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
                    'name' => 'Games',
                    'item' => PublicUrl::route('games.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $gameConfig['name'],
                    'item' => PublicUrl::route('games.show', $gameConfig['slug']),
                ],
            ],
        ];

        return view('public.games.show', [
            'meta' => new MetaPayload(
                $gameConfig['name'].' | Free Japan Travel Game',
                $gameConfig['meta_description'],
                PublicUrl::route('games.show', $gameConfig['slug']),
                ogImage: asset($gameConfig['image']),
            ),
            'game' => $gameConfig,
            'games' => JapanGames::all(),
            'gameJsonLd' => $gameJsonLd,
            'breadcrumbJsonLd' => $breadcrumbJsonLd,
        ]);
    }
}

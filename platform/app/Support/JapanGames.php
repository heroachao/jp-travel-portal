<?php

namespace App\Support;

use Illuminate\Support\Arr;

class JapanGames
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'slug' => 'daily-japan-word',
                'name' => 'Daily Japan Word Trail',
                'short_name' => 'Word Trail',
                'category' => 'Daily puzzle',
                'type' => 'Word game',
                'summary' => 'Guess a five-letter Japan travel word in six tries, with a fresh daily target for repeat visits.',
                'meta_description' => 'Play a free daily Japan word puzzle for travelers, inspired by popular browser word games and built around Japan place names and trip terms.',
                'accent' => '#6d28d9',
                'image' => 'images/photo-ac/tokyo-night.jpeg',
                'play_time' => '3 min',
                'difficulty' => 'Medium',
                'hook' => 'Daily repeat play',
                'objective' => 'Find the hidden Japan travel word before the sixth guess.',
                'seo_keywords' => ['Japan word game', 'daily travel word puzzle', 'Japan browser game'],
            ],
            [
                'slug' => 'fuji-merge-2048',
                'name' => 'Fuji Merge 2048',
                'short_name' => 'Fuji 2048',
                'category' => 'Number puzzle',
                'type' => '2048 merge',
                'summary' => 'Slide Japan-themed tiles, merge values, and climb from a station snack to a Mt. Fuji sunrise tile.',
                'meta_description' => 'Play Fuji Merge 2048, a free Japan-themed number merge puzzle for browser visitors.',
                'accent' => '#0f766e',
                'image' => 'images/photo-ac/tokyo-tower.jpeg',
                'play_time' => '5 min',
                'difficulty' => 'Medium',
                'hook' => 'High-score chasing',
                'objective' => 'Merge tiles to reach the 2048 Fuji Sunrise tile.',
                'seo_keywords' => ['Japan 2048 game', 'Fuji puzzle game', 'free browser 2048'],
            ],
            [
                'slug' => 'sushi-snake',
                'name' => 'Sushi Snake Arcade',
                'short_name' => 'Sushi Snake',
                'category' => 'Arcade',
                'type' => 'Snake',
                'summary' => 'Guide a growing sushi line through a compact arcade board without hitting the edge or yourself.',
                'meta_description' => 'Play a free Japan-themed snake game with sushi scoring and simple mobile controls.',
                'accent' => '#dc2626',
                'image' => 'images/photo-ac/dotonbori.jpeg',
                'play_time' => '2 min',
                'difficulty' => 'Easy',
                'hook' => 'Quick replay loop',
                'objective' => 'Eat as much sushi as possible while keeping the line alive.',
                'seo_keywords' => ['sushi snake game', 'Japan arcade game', 'free snake browser game'],
            ],
            [
                'slug' => 'torii-memory-match',
                'name' => 'Torii Memory Match',
                'short_name' => 'Torii Match',
                'category' => 'Memory',
                'type' => 'Card match',
                'summary' => 'Flip shrine, rail, food, and festival cards to match all pairs with as few moves as possible.',
                'meta_description' => 'Play Torii Memory Match, a free Japan-themed card matching game for casual browser visitors.',
                'accent' => '#b91c1c',
                'image' => 'images/photo-ac/osaka-castle.jpeg',
                'play_time' => '4 min',
                'difficulty' => 'Easy',
                'hook' => 'Best-move challenge',
                'objective' => 'Match every pair on the board.',
                'seo_keywords' => ['Japan memory game', 'card matching game', 'torii browser game'],
            ],
            [
                'slug' => 'sakura-minesweeper',
                'name' => 'Sakura Minesweeper',
                'short_name' => 'Sakura Sweep',
                'category' => 'Logic',
                'type' => 'Minesweeper',
                'summary' => 'Clear a spring picnic grid by avoiding hidden storm clouds and marking risky squares.',
                'meta_description' => 'Play Sakura Minesweeper, a free Japan-themed logic puzzle with flags, reveal cells, and win detection.',
                'accent' => '#db2777',
                'image' => 'images/photo-ac/yura-coast.jpeg',
                'play_time' => '5 min',
                'difficulty' => 'Medium',
                'hook' => 'Classic logic loop',
                'objective' => 'Reveal every safe square without uncovering a storm cloud.',
                'seo_keywords' => ['sakura minesweeper', 'Japan logic game', 'free minesweeper browser'],
            ],
            [
                'slug' => 'tokyo-metro-dash',
                'name' => 'Tokyo Metro Dash',
                'short_name' => 'Metro Dash',
                'category' => 'Reaction',
                'type' => 'Runner',
                'summary' => 'Jump ticket gates, luggage, and platform gaps in a fast Tokyo transit reaction game.',
                'meta_description' => 'Play Tokyo Metro Dash, a free Japan transit runner game designed for quick browser sessions.',
                'accent' => '#2563eb',
                'image' => 'images/photo-ac/tokyo-museum.jpeg',
                'play_time' => '1 min',
                'difficulty' => 'Fast',
                'hook' => 'One-more-run score',
                'objective' => 'Stay on the line as long as possible and beat your distance.',
                'seo_keywords' => ['Tokyo runner game', 'metro dash browser game', 'Japan reaction game'],
            ],
            [
                'slug' => 'omamori-match-three',
                'name' => 'Omamori Match Three',
                'short_name' => 'Omamori Match',
                'category' => 'Match puzzle',
                'type' => 'Match-three',
                'summary' => 'Swap colorful travel charms, make rows of three, and build a high score before moves run out.',
                'meta_description' => 'Play Omamori Match Three, a free Japan-themed match-three puzzle with browser-friendly controls.',
                'accent' => '#ca8a04',
                'image' => 'images/photo-ac/okinawa-yakena.jpeg',
                'play_time' => '6 min',
                'difficulty' => 'Medium',
                'hook' => 'Combo scoring',
                'objective' => 'Score as many charm matches as possible in 24 moves.',
                'seo_keywords' => ['Japan match three game', 'omamori puzzle', 'free match 3 browser game'],
            ],
            [
                'slug' => 'prefecture-typing',
                'name' => 'Prefecture Typing Sprint',
                'short_name' => 'Typing Sprint',
                'category' => 'Typing',
                'type' => 'Typing challenge',
                'summary' => 'Type Japan prefectures and route terms quickly to train spelling while racing the timer.',
                'meta_description' => 'Play a Japan prefecture typing sprint with travel words, speed scoring, and browser-only gameplay.',
                'accent' => '#16a34a',
                'image' => 'images/photo-ac/cape-soya.jpeg',
                'play_time' => '1 min',
                'difficulty' => 'Fast',
                'hook' => 'Personal best timer',
                'objective' => 'Type as many Japan prefecture and travel terms as you can before time ends.',
                'seo_keywords' => ['Japan typing game', 'prefecture typing challenge', 'travel typing game'],
            ],
            [
                'slug' => 'ukiyo-e-slider',
                'name' => 'Ukiyo-e Slider Puzzle',
                'short_name' => 'Slider Puzzle',
                'category' => 'Spatial puzzle',
                'type' => 'Sliding puzzle',
                'summary' => 'Rebuild a Japan travel photo by sliding tiles into place, with solvable shuffles and move tracking.',
                'meta_description' => 'Play Ukiyo-e Slider Puzzle, a free Japan image sliding puzzle for casual browser sessions.',
                'accent' => '#7c3aed',
                'image' => 'images/photo-ac/kohamajima-view.jpeg',
                'play_time' => '4 min',
                'difficulty' => 'Medium',
                'hook' => 'Move-count challenge',
                'objective' => 'Restore the travel image by arranging every tile in order.',
                'seo_keywords' => ['Japan slider puzzle', 'image sliding puzzle', 'free browser puzzle'],
            ],
            [
                'slug' => 'ramen-order-rush',
                'name' => 'Ramen Order Rush',
                'short_name' => 'Ramen Rush',
                'category' => 'Timing',
                'type' => 'Order rush',
                'summary' => 'Serve ramen orders by tapping the right broth, noodles, and toppings before customers lose patience.',
                'meta_description' => 'Play Ramen Order Rush, a quick Japan food order game with timing, streaks, and replay scoring.',
                'accent' => '#ea580c',
                'image' => 'images/photo-ac/hokkaido-winter.jpeg',
                'play_time' => '2 min',
                'difficulty' => 'Fast',
                'hook' => 'Streak building',
                'objective' => 'Complete as many ramen orders as possible before the shift ends.',
                'seo_keywords' => ['ramen game', 'Japan food browser game', 'restaurant order game'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function featured(int $limit = 4): array
    {
        return array_slice(self::all(), 0, $limit);
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return Arr::pluck(self::all(), 'slug');
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $slug): ?array
    {
        foreach (self::all() as $game) {
            if ($game['slug'] === $slug) {
                return $game;
            }
        }

        return null;
    }
}

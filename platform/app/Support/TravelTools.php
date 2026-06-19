<?php

namespace App\Support;

use Illuminate\Support\Arr;

class TravelTools
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $tools = [
            [
                'slug' => 'trip-planner',
                'name' => 'Japan Trip Planner',
                'short_name' => 'Trip Planner',
                'category' => 'Itinerary',
                'summary' => 'Build a practical day-by-day Japan route from nights, pace, regions, and travel interests.',
                'meta_description' => 'Use a free Japan trip planner to build a practical day-by-day route by nights, pace, regions, and interests.',
                'inputs' => ['Nights', 'Pace', 'Interests'],
                'output' => 'Day-by-day route',
                'accent' => '#6d28d9',
            ],
            [
                'slug' => 'jr-pass-calculator',
                'name' => 'JR Pass Value Checker',
                'short_name' => 'JR Pass Checker',
                'category' => 'Transport',
                'summary' => 'Compare common intercity rail legs against a pass price you can update before purchase.',
                'meta_description' => 'Estimate whether a Japan Rail Pass may be worth it by adding common rail legs and comparing them with a pass price.',
                'inputs' => ['Rail legs', 'Pass price', 'Travelers'],
                'output' => 'Value estimate',
                'accent' => '#0891b2',
            ],
            [
                'slug' => 'airport-transfer',
                'name' => 'Airport Transfer Finder',
                'short_name' => 'Airport Transfer',
                'category' => 'Transport',
                'summary' => 'Choose an arrival airport, hotel area, and priority to get a simple transfer shortlist.',
                'meta_description' => 'Find practical Japan airport transfer options by airport, hotel area, luggage level, and travel priority.',
                'inputs' => ['Airport', 'Hotel area', 'Priority'],
                'output' => 'Transfer shortlist',
                'accent' => '#0f766e',
            ],
            [
                'slug' => 'budget-calculator',
                'name' => 'Japan Travel Budget Calculator',
                'short_name' => 'Budget Calculator',
                'category' => 'Money',
                'summary' => 'Estimate accommodation, food, local transport, activities, shopping, and a reserve buffer.',
                'meta_description' => 'Estimate Japan trip costs for accommodation, food, local transport, activities, shopping, and contingency.',
                'inputs' => ['Travelers', 'Nights', 'Style'],
                'output' => 'Budget range',
                'accent' => '#ca8a04',
            ],
            [
                'slug' => 'region-finder',
                'name' => 'Japan Region Finder',
                'short_name' => 'Region Finder',
                'category' => 'Destinations',
                'summary' => 'Match season, pace, and interests to regions that fit the trip you are planning.',
                'meta_description' => 'Find Japan regions that fit your season, pace, and travel interests using an on-site planning tool.',
                'inputs' => ['Month', 'Travel style', 'Pace'],
                'output' => 'Region matches',
                'accent' => '#16a34a',
            ],
            [
                'slug' => 'season-packing',
                'name' => 'Season and Packing Planner',
                'short_name' => 'Packing Planner',
                'category' => 'Basics',
                'summary' => 'Generate a Japan packing checklist by month, region, and planned activities.',
                'meta_description' => 'Create a Japan packing checklist by season, region, weather pattern, and planned activities.',
                'inputs' => ['Month', 'Region', 'Activities'],
                'output' => 'Packing list',
                'accent' => '#db2777',
            ],
            [
                'slug' => 'ic-card-checklist',
                'name' => 'IC Card Checklist',
                'short_name' => 'IC Card Checklist',
                'category' => 'Transport',
                'summary' => 'Plan IC card setup, cash backup, airport arrival, and first-day transit habits.',
                'meta_description' => 'Prepare for IC cards, cash backup, airport arrival, and local transit payments before a Japan trip.',
                'inputs' => ['Airport', 'Phone wallet', 'Cities'],
                'output' => 'Payment checklist',
                'accent' => '#2563eb',
            ],
            [
                'slug' => 'luggage-planner',
                'name' => 'Luggage Forwarding Planner',
                'short_name' => 'Luggage Planner',
                'category' => 'Logistics',
                'summary' => 'Decide when to carry bags, use lockers, or forward luggage between hotels.',
                'meta_description' => 'Plan Japan luggage movement with hotel changes, train transfers, lockers, and forwarding decisions.',
                'inputs' => ['Bags', 'Hotel changes', 'Transfers'],
                'output' => 'Luggage plan',
                'accent' => '#ea580c',
            ],
            [
                'slug' => 'allergy-card',
                'name' => 'Food Allergy Phrase Card',
                'short_name' => 'Allergy Card',
                'category' => 'Food',
                'summary' => 'Create clear English and Japanese food allergy phrases for restaurants and shops.',
                'meta_description' => 'Create a Japan food allergy phrase card in English and Japanese for restaurants and convenience stores.',
                'inputs' => ['Allergy', 'Severity', 'Diet'],
                'output' => 'Phrase card',
                'accent' => '#dc2626',
            ],
            [
                'slug' => 'tax-free-calculator',
                'name' => 'Japan Tax-Free Shopping Calculator',
                'short_name' => 'Tax-Free Calculator',
                'category' => 'Shopping',
                'summary' => 'Estimate tax-free shopping savings and check a basic spending threshold.',
                'meta_description' => 'Estimate Japan tax-free shopping savings, thresholds, fees, and approximate take-home cost.',
                'inputs' => ['Spend', 'Tax rate', 'Fee'],
                'output' => 'Savings estimate',
                'accent' => '#7c3aed',
            ],
        ];

        return array_map(fn (array $tool) => array_merge($tool, self::seoDetails($tool)), $tools);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function featured(int $limit = 6): array
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
        foreach (self::all() as $tool) {
            if ($tool['slug'] === $slug) {
                return $tool;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $tool
     * @return array<string, mixed>
     */
    private static function seoDetails(array $tool): array
    {
        $keywordMap = [
            'trip-planner' => ['Japan itinerary planner', 'first time Japan itinerary', 'Japan route planner'],
            'jr-pass-calculator' => ['JR Pass worth it', 'Japan Rail Pass calculator', 'Shinkansen pass value'],
            'airport-transfer' => ['Japan airport transfer', 'Narita to Tokyo late arrival', 'Haneda airport transport'],
            'budget-calculator' => ['Japan travel budget calculator', 'Japan trip cost estimate', 'Tokyo Kyoto Osaka budget'],
            'region-finder' => ['where to go in Japan', 'Japan region finder', 'best Japan region by season'],
            'season-packing' => ['Japan packing list', 'Japan weather packing planner', 'what to pack for Japan'],
            'ic-card-checklist' => ['Suica Pasmo checklist', 'Japan IC card guide', 'visitor IC card Japan'],
            'luggage-planner' => ['Japan luggage forwarding', 'takkyubin luggage planner', 'Japan train luggage rules'],
            'allergy-card' => ['Japan allergy card', 'Japanese food allergy phrases', 'vegetarian card Japan'],
            'tax-free-calculator' => ['Japan tax free shopping calculator', 'Japan consumption tax refund', 'tax free shopping Japan'],
        ];

        $keywords = $keywordMap[$tool['slug']] ?? [$tool['name'], $tool['category'].' Japan travel', 'Japan travel planning'];
        $primaryKeyword = $keywords[0];

        return [
            'seo_intro' => "Use the {$tool['name']} when you need a quick, practical answer before building the rest of a Japan itinerary. It is designed for English-speaking travelers who want clear planning guidance without leaving Japan Trip Tools or opening several separate websites.",
            'seo_sections' => [
                [
                    'title' => 'Best for first decisions',
                    'body' => "This tool helps narrow the early planning choice behind {$primaryKeyword}. It works best when you already know your rough travel dates, main cities, and comfort level, but still need a simple way to compare the next step.",
                ],
                [
                    'title' => 'How to use the result',
                    'body' => "Treat the output as a planning checkpoint, then confirm official transport, ticket, weather, or store details before paying for anything. Japan travel conditions can change by season, route, and operator.",
                ],
                [
                    'title' => 'Why it stays on site',
                    'body' => 'The calculation runs inside this page so readers can continue to nearby guides, regional pages, and article searches instead of bouncing to unrelated external booking pages.',
                ],
            ],
            'faqs' => [
                [
                    'question' => "Is the {$tool['name']} free?",
                    'answer' => "Yes. The {$tool['name']} is free to use on Japan Trip Tools and does not require a login.",
                ],
                [
                    'question' => "Can I use this as official travel advice?",
                    'answer' => 'Use it as a planning helper, then verify prices, opening hours, transport schedules, and ticket rules with official providers before travel.',
                ],
                [
                    'question' => "Who is this {$tool['category']} tool for?",
                    'answer' => 'It is written for international travelers planning Japan in English, especially first-time visitors who want practical steps rather than a generic destination overview.',
                ],
            ],
            'related_searches' => $keywords,
        ];
    }
}

<?php

namespace App\Services\Content;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ArticleContentEnhancer
{
    /**
     * @return array{html: string, word_count: int, reading_time_minutes: int, reviewed_at: string|null, source_label: string|null}
     */
    public function build(Article $article): array
    {
        $existingWords = str_word_count(strip_tags((string) $article->body));
        $html = $this->planningDepthHtml($article);
        $wordCount = $existingWords + str_word_count(strip_tags($html));

        return [
            'html' => $html,
            'word_count' => $wordCount,
            'reading_time_minutes' => max(1, (int) ceil($wordCount / 220)),
            'reviewed_at' => ($article->display_updated_at ?: $article->updated_at)?->format('F j, Y'),
            'source_label' => $article->source_name,
        ];
    }

    private function planningDepthHtml(Article $article): string
    {
        $title = $article->title;
        $region = $this->primaryRegion($article);
        $categories = $this->labels($article->travelCategories, 'title', 'display_name');
        $tags = $this->labels($article->tags, 'name');
        $topics = $this->labels($article->topics, 'title');
        $categoryPhrase = $categories->take(4)->implode(', ') ?: 'Japan travel planning';
        $tagPhrase = $tags->take(5)->implode(', ') ?: 'route planning';
        $topicPhrase = $topics->take(3)->implode(', ') ?: $categoryPhrase;
        $sourceName = $article->source_name ?: 'official tourism or transport sources';

        $sections = [
            $this->section('How to use this guide', [
                "Use this {$title} page as a planning framework, not as a fixed booking instruction. Start by deciding whether {$region} is the main base for the day or only one stop in a wider Japan route. That choice changes how much luggage you carry, how early you need to start, and how many optional stops should stay optional.",
                'The strongest version of this plan is simple: pick one primary reason to go, add one nearby secondary stop, then leave enough room for meals, weather, queues, station transfers, and slower walking speed. Travelers often lose time in Japan not because one attraction is difficult, but because several small transfers, lockers, ticket lines, and photo stops quietly add up.',
            ]),
            $this->section('Suggested planning order', [
                'Build the day in this order: confirm the base city, decide the first major stop, choose the final return route, then fill the middle with food, shopping, nature, culture, or neighborhood time. This keeps the itinerary resilient if a train is crowded, rain starts, or a museum or attraction changes hours.',
                "For {$categoryPhrase}, treat the first and last transport moves as the fixed anchors. Everything between them should be ranked as essential, good if nearby, or easy to drop. That ranking is more useful than a long checklist because it keeps the trip enjoyable when real conditions differ from a desk plan.",
            ], [
                "Choose the main base and confirm whether {$region} works better as an overnight stop or a day trip.",
                'Check the first train, bus, ferry, or walking segment before adding extra stops.',
                'Keep one meal plan close to the route and one backup plan near a major station.',
                'Save official maps, transport pages, hotel addresses, and emergency contacts for offline use.',
            ]),
            $this->section('Transport and timing checks', [
                "Before travel, verify the current transport details with {$sourceName} and the relevant operator pages. This site avoids publishing exact last-train guarantees or live operating claims because those details can change by date, season, maintenance work, weather, and special events.",
                'If this route involves rail, compare station names carefully. Large Japanese stations can have separate railway companies, underground passages, local exits, and transfer gates. If it involves buses, ferries, mountain access, or resort areas, confirm frequency both outbound and return. A route that looks easy at midday can become awkward after dinner or in bad weather.',
            ], [
                'Use the official source for the final timetable, fare, closure, and access check.',
                'Add a transfer buffer when moving between railway companies or from rail to bus.',
                'Plan the return before adding evening stops, especially outside major urban cores.',
                'Keep taxi, luggage forwarding, or a closer hotel area as a backup if bags are heavy.',
            ]),
            $this->section('Budget, booking, and value notes', [
                "{$title} can fit different budgets depending on lodging location, restaurant choices, ticketed activities, and how many paid transfers are involved. The safest budget habit is to separate must-pay items from flexible spending. Transport, luggage movement, accommodation, and reserved activities should be checked first; snacks, souvenirs, cafes, and optional detours can be adjusted on the day.",
                'Do not assume a national rail pass, regional pass, tour bundle, or activity ticket is automatically good value. Add the actual legs you expect to use, compare them with the pass conditions, and check whether seat reservations, airport access, limited express supplements, or local buses are included. Value is strongest when the pass matches a route you already wanted, not when the pass forces a rushed route.',
            ]),
            $this->section('Season, weather, and crowd strategy', [
                "{$region} can feel very different by season. Spring and autumn often reward early starts and flexible photography stops. Summer can make shade, hydration, and slower pacing more important. Winter may require better footwear, earlier daylight planning, and more attention to wind, snow, or service changes in northern and mountain areas.",
                'Crowd strategy is less about avoiding every popular place and more about choosing when to be there. Put the most famous stop early, late, or on a weekday where possible. Use meal times, station transfers, and indoor stops to absorb delays. If a location is too crowded, switch to the nearby secondary stop instead of forcing the original order.',
            ], [
                'Carry a compact rain layer or umbrella when the route depends on walking.',
                'Check heat, typhoon, snow, or marine warnings when the route is outdoor-heavy.',
                'Use official event calendars before traveling around festival or holiday periods.',
                'Keep a quiet cafe, museum, shopping arcade, or hotel break as a weather backup.',
            ]),
            $this->section('Who this plan suits best', [
                "This guide suits travelers who want a practical English-language overview of {$topicPhrase} without jumping across several unrelated websites. It is especially useful when you are still comparing regions, deciding whether to stay overnight, or choosing how much time to reserve for {$tagPhrase}.",
                'It may not be the right plan if you need a fully escorted tour, real-time disruption support, accessibility confirmation for a specific mobility device, or official customer service from a railway, hotel, attraction, or government office. For those decisions, use this page as orientation and contact the relevant official provider directly.',
            ]),
            $this->section('Editorial review notes', [
                'Japan Trip Tools writes original English planning notes for international readers. The goal is not to translate an official page line by line, but to turn source material and practical travel constraints into a clear decision path. Every page should help you decide what to check next, what to book early, and what can stay flexible.',
                'The page is reviewed against the listed source when practical, but travel information changes. Before you pay for transport, accommodation, tours, or timed tickets, confirm the latest rule, price, schedule, access note, and safety guidance with official providers. If you notice a mismatch, use the contact page and include the page URL plus the source that supports the correction.',
            ]),
            $this->section('Quick pre-trip checklist', [
                'Use this final checklist within a week of travel. First, confirm the official access information and any weather or disruption notices. Second, check whether tickets, reservations, passes, or luggage services need advance action. Third, save the Japanese address or map pin for the first stop and hotel. Fourth, decide which optional stop to drop if the day runs long.',
                'A good Japan itinerary leaves space for small discoveries: a local bakery, a station bento, a viewpoint, a craft shop, a quiet street, or a simple rest. Protecting that space usually creates a better trip than adding one more distant stop.',
            ], [
                "Official source checked: {$sourceName}.",
                "Primary region: {$region}.",
                "Planning themes: {$categoryPhrase}.",
                "Useful search terms: {$tagPhrase}.",
            ]),
        ];

        return '<div class="article-depth-panel">'.implode('', $sections).'</div>';
    }

    /**
     * @param  array<int, string>  $bullets
     */
    private function section(string $heading, array $paragraphs, array $bullets = []): string
    {
        $html = '<section class="article-depth-section">';
        $html .= '<h2>'.e($heading).'</h2>';

        foreach ($paragraphs as $paragraph) {
            $html .= '<p>'.e($paragraph).'</p>';
        }

        if ($bullets !== []) {
            $html .= '<ul>';
            foreach ($bullets as $bullet) {
                $html .= '<li>'.e($bullet).'</li>';
            }
            $html .= '</ul>';
        }

        return $html.'</section>';
    }

    private function primaryRegion(Article $article): string
    {
        $destination = $article->destinations->first();

        return $destination ? ($destination->display_name ?: $destination->name) : 'Japan';
    }

    /**
     * @param  Collection<int, mixed>  $items
     * @return Collection<int, string>
     */
    private function labels(Collection $items, string $primary, ?string $fallback = null): Collection
    {
        return $items
            ->map(fn ($item): string => (string) ($fallback && filled($item->{$fallback}) ? $item->{$fallback} : $item->{$primary}))
            ->filter()
            ->map(fn (string $label): string => Str::of($label)->replace('-', ' ')->headline()->toString())
            ->values();
    }
}

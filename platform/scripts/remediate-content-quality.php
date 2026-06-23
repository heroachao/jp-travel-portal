<?php

declare(strict_types=1);

use App\Models\Article;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$apply = in_array('--apply', $argv, true);
$targetWords = 1050;
$now = Carbon::now();

$imageMappings = [
    'nara-half-day-vs-full-day-decision' => [
        'source' => 'nara-deer-park-responsible-visit',
        'alt' => 'Nara Park deer and temple area for choosing a Nara half-day or full-day plan',
        'caption' => 'Nara Park is the clearest visual anchor for deciding whether Nara should be a compact classic visit or a slower full-day stop.',
    ],
    'osaka-one-day-food-history-route' => [
        'source' => 'osaka-castle-and-nakanoshima-day',
        'alt' => 'Nakanoshima and central Osaka skyline for an Osaka food and history route',
        'caption' => 'Central Osaka works best when castle, riverfront, market, and evening food areas are grouped into a route with short transfers.',
    ],
    'narita-to-tokyo-hotel-area-decision' => [
        'source' => 'narita-airport-late-arrival-recovery-plan',
        'alt' => 'Narita Airport rail station for choosing a Tokyo hotel area after arrival',
        'caption' => 'Narita arrival planning starts with the airport rail connection, then works backward to the hotel area that keeps the first night simple.',
    ],
    'haneda-late-arrival-tokyo-hotel-plan' => [
        'source' => 'haneda-arrival-first-night-plan',
        'alt' => 'Haneda Airport terminal for a late-arrival Tokyo hotel plan',
        'caption' => 'A late Haneda arrival is easier when the first hotel is chosen around transfer time, check-in margin, and next-morning movement.',
    ],
    'shinkansen-large-luggage-seat-planner' => [
        'source' => 'japan-shinkansen-seat-reservation-basics',
        'alt' => 'Tokaido Shinkansen train for large luggage seat planning in Japan',
        'caption' => 'Large luggage planning on the Shinkansen should be handled before travel day, especially on busy intercity routes.',
    ],
    'kansai-airport-to-kyoto-family-transfer' => [
        'source' => 'kansai-airport-arrival-to-osaka-or-kyoto',
        'alt' => 'Kansai International Airport terminal for a family transfer to Kyoto',
        'caption' => 'For families landing at Kansai Airport, the best transfer is usually the one with fewer handoffs and more recovery space.',
    ],
    'sendai-station-base-for-tohoku-trips' => [
        'source' => 'sendai-and-matsushima-first-route',
        'alt' => 'Matsushima coastal scenery near Sendai for a Tohoku rail base plan',
        'caption' => 'Sendai works as a practical Tohoku rail base because day trips can stay flexible without changing hotels every night.',
    ],
    'sapporo-station-vs-susukino-base' => [
        'source' => 'sapporo-first-night-food-and-transit',
        'alt' => 'Susukino evening district for choosing a Sapporo hotel base',
        'caption' => 'Sapporo hotel choice is mostly a tradeoff between station convenience, evening food access, and winter walking comfort.',
    ],
    'japan-winter-city-trip-footwear-luggage' => [
        'source' => 'hokkaido-winter-activity-planner',
        'alt' => 'Snowy Hokkaido scenery for Japan winter footwear and luggage planning',
        'caption' => 'Winter city travel in Japan is more comfortable when footwear, luggage weight, and backup indoor plans are decided before arrival.',
    ],
    'fukuoka-hakata-vs-tenjin-hotel-base' => [
        'source' => 'fukuoka-food-and-day-trip-base-guide',
        'alt' => 'Fukuoka city food and transport area for choosing Hakata or Tenjin',
        'caption' => 'In Fukuoka, Hakata and Tenjin are both useful bases; the better choice depends on rail departures, evening food, and shopping plans.',
    ],
    'kanazawa-station-vs-kenrokuen-stay' => [
        'source' => 'kanazawa-garden-and-craft-day',
        'alt' => 'Kanazawa garden and central sightseeing area for choosing where to stay',
        'caption' => 'Kanazawa stay planning works best when station access and garden-area sightseeing are weighed against the same luggage day.',
    ],
    'nagoya-station-base-for-tokai-stopovers' => [
        'source' => 'nagoya-station-food-and-rail-hub-guide',
        'alt' => 'Nagoya station and city hub planning for Tokai stopovers',
        'caption' => 'Nagoya Station is a useful Tokai base when the trip is built around rail efficiency, luggage simplicity, and short city meals.',
    ],
];

$profiles = [
    'airport' => [
        'keywords' => ['airport', 'arrival', 'narita', 'haneda', 'kansai airport', 'kix', 'terminal', 'transfer'],
        'intent' => 'arrival and transfer planning',
        'reader' => 'travelers who need the first or last day to work cleanly, even with luggage, immigration time, or a delayed flight',
        'primary' => 'work backward from the hotel check-in window, the final train or bus option, and the number of times luggage must be handled',
        'pitfall' => 'choosing the cheapest route without checking the last connection, elevator access, or the distance from the station exit to the hotel',
        'checks' => ['Confirm the latest airport access timetable before travel day.', 'Keep one route with fewer transfers for tired arrivals.', 'Save the hotel address in Japanese and English for taxi or station help.'],
    ],
    'rail' => [
        'keywords' => ['rail', 'train', 'shinkansen', 'jr pass', 'pass', 'ic card', 'seat', 'station', 'ticket', 'luggage', 'transfer'],
        'intent' => 'rail and route decisions',
        'reader' => 'independent travelers comparing train convenience, pass value, station layout, and luggage effort',
        'primary' => 'separate the fare question from the comfort question: a valid ticket still needs realistic walking, seat, and transfer margins',
        'pitfall' => 'treating a pass, IC card, or transfer route as automatic without checking coverage, reservation needs, and station size',
        'checks' => ['Check the operator or official timetable closest to your travel date.', 'Leave a buffer at large stations when changing lines.', 'Plan luggage movement before buying reserved seats.'],
    ],
    'season' => [
        'keywords' => ['winter', 'summer', 'rain', 'typhoon', 'autumn', 'cherry', 'spring', 'snow', 'weather', 'foliage', 'indoor'],
        'intent' => 'seasonal trip planning',
        'reader' => 'travelers who want the trip to survive weather shifts, crowd spikes, and season-specific timing changes',
        'primary' => 'build the day around a strong first choice plus a nearby backup, instead of relying on one perfect weather assumption',
        'pitfall' => 'locking every hour around a seasonal forecast that may shift by region, elevation, or week',
        'checks' => ['Check current weather and local advisories before committing to outdoor time.', 'Keep indoor, food, or shopping backups close to the same transport line.', 'Avoid long same-day detours when seasonal visibility is uncertain.'],
    ],
    'food-shopping' => [
        'keywords' => ['food', 'market', 'shopping', 'souvenir', 'tax-free', 'allergy', 'convenience', 'department', 'nightlife', 'restaurant'],
        'intent' => 'food, shopping, and spending choices',
        'reader' => 'visitors who want memorable meals or purchases without wasting time in avoidable queues or carrying mistakes',
        'primary' => 'match the food or shopping stop to the day rhythm: quick snacks during transfers, heavier meals after sightseeing, and purchases near the hotel when possible',
        'pitfall' => 'saving every purchase or restaurant decision for the busiest evening window',
        'checks' => ['Confirm closing days and last-order times when a specific restaurant matters.', 'Carry payment backup because smaller places may differ from large stores.', 'Do bulky shopping near the end of the day or close to luggage storage.'],
    ],
    'hotel-base' => [
        'keywords' => ['hotel', 'stay', 'base', 'lodging', 'where to stay', 'station vs', 'vs'],
        'intent' => 'hotel base selection',
        'reader' => 'travelers choosing where to sleep based on transport, luggage, nightlife, and next-day plans',
        'primary' => 'choose the hotel area by the hardest movement of the trip, not by the prettiest neighborhood photo',
        'pitfall' => 'booking a pleasant-looking area that adds repeated transfers with bags or late-night walking stress',
        'checks' => ['Map the station exit, not just the station name.', 'Check whether the next morning starts by rail, airport transfer, or local sightseeing.', 'Balance evening food access against quiet sleep and luggage convenience.'],
    ],
    'family' => [
        'keywords' => ['family', 'kids', 'children', 'solo', 'safety', 'night', 'allergy', 'accessible', 'stroller'],
        'intent' => 'comfort and safety planning',
        'reader' => 'travelers who care about fatigue, simple exits, dietary needs, accessibility, or safer evening movement',
        'primary' => 'reduce friction by planning rest points, straightforward return routes, and one clear backup before the day begins',
        'pitfall' => 'treating an itinerary as successful only if every stop is completed',
        'checks' => ['Build in a no-guilt exit point halfway through the day.', 'Keep meals and restrooms predictable when traveling with children or dietary needs.', 'Avoid late transfers through unfamiliar areas when tired.'],
    ],
    'culture-nature' => [
        'keywords' => ['temple', 'shrine', 'heritage', 'garden', 'museum', 'nature', 'island', 'coast', 'onsen', 'park', 'walk', 'day trip', 'route'],
        'intent' => 'sightseeing route design',
        'reader' => 'travelers who want a satisfying cultural or nature day without turning the route into a checklist',
        'primary' => 'protect the best stop with enough time, then add nearby supporting stops only when transport and energy still make sense',
        'pitfall' => 'stacking too many famous names into one day and losing the quiet time that makes the place worth visiting',
        'checks' => ['Check official opening hours because temples, museums, and gardens vary by season.', 'Start with the most important stop before crowds or fatigue build.', 'Keep the route geographically tight unless the transport link is unusually easy.'],
    ],
    'default' => [
        'keywords' => [],
        'intent' => 'Japan trip decision making',
        'reader' => 'travelers who want practical planning help rather than a generic attraction list',
        'primary' => 'turn the page into a decision: what to do, what to skip, what to check, and how to keep the day flexible',
        'pitfall' => 'copying a sample itinerary without adjusting it to hotel location, luggage, weather, and personal pace',
        'checks' => ['Confirm official details before spending money.', 'Group nearby stops to reduce wasted transit time.', 'Keep one realistic fallback for weather, crowds, or tired travel days.'],
    ],
];

$articles = Article::query()
    ->with(['destinations', 'travelCategories', 'tags'])
    ->where('status', 'published')
    ->whereNull('deleted_at')
    ->orderBy('id')
    ->get();

$sourcesBySlug = $articles->keyBy('slug');
$report = [
    'generated_at' => $now->toIso8601String(),
    'apply' => $apply,
    'target_words' => $targetWords,
    'articles_checked' => $articles->count(),
    'articles_enriched' => [],
    'images_replaced' => [],
    'seo_titles_cleaned' => [],
    'excerpt_rewritten' => [],
    'skipped_image_replacements' => [],
];

foreach ($articles as $article) {
    $beforeWords = wordCount((string) $article->body);
    $profileKey = detectProfile($article, $profiles);
    $profile = $profiles[$profileKey];
    $destinations = $article->destinations->pluck('display_name')->filter()->merge(
        $article->destinations->pluck('name')->filter(),
    )->unique()->values()->all();
    $categories = $article->travelCategories->pluck('name')->filter()->values()->all();
    $tags = $article->tags->pluck('name')->filter()->values()->all();

    $article->seo_title = cleanSeoTitle((string) $article->title);

    if ($article->seo_title !== ($article->getOriginal('seo_title') ?? '')) {
        $report['seo_titles_cleaned'][] = [
            'slug' => $article->slug,
            'before' => $article->getOriginal('seo_title'),
            'after' => $article->seo_title,
        ];
    }

    $newExcerpt = makeExcerpt($article, $profile, $destinations);
    if ($newExcerpt !== (string) $article->excerpt) {
        $article->excerpt = $newExcerpt;
        $report['excerpt_rewritten'][] = $article->slug;
    }

    $article->meta_description = makeMetaDescription($article, $profile, $destinations);

    if (isset($imageMappings[$article->slug])) {
        $mapping = $imageMappings[$article->slug];
        $source = $sourcesBySlug->get($mapping['source']);
        $sourceFigure = $source ? extractFirstFigure((string) $source->body) : null;

        if ($sourceFigure) {
            $article->body = replaceFirstFigure(
                (string) $article->body,
                rebuildFigure($sourceFigure, $article->slug, $mapping['alt'], $mapping['caption']),
            );
            $report['images_replaced'][] = [
                'slug' => $article->slug,
                'source_slug' => $mapping['source'],
            ];
        } else {
            $report['skipped_image_replacements'][] = [
                'slug' => $article->slug,
                'source_slug' => $mapping['source'],
                'reason' => $source ? 'source_article_has_no_figure' : 'source_article_missing',
            ];
        }
    }

    $currentWords = wordCount((string) $article->body);
    if ($currentWords < $targetWords) {
        $addition = buildEnrichmentHtml(
            $article,
            $profile,
            $destinations,
            $categories,
            $tags,
            $targetWords - $currentWords,
        );
        $article->body = rtrim((string) $article->body)."\n".$addition;
        $afterWords = wordCount((string) $article->body);

        $report['articles_enriched'][] = [
            'slug' => $article->slug,
            'title' => $article->title,
            'profile' => $profileKey,
            'before_words' => $beforeWords,
            'after_words' => $afterWords,
        ];
    }

    $article->reading_time_minutes = max(1, (int) ceil(wordCount((string) $article->body) / 220));
    $article->display_updated_at = $now;
    $article->updated_at = $now;

    if ($apply) {
        $article->save();
    }
}

$reportPath = __DIR__.'/../database/content/quality-remediation-2026-06-23.json';
file_put_contents(
    $reportPath,
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
);

echo json_encode([
    'apply' => $apply,
    'articles_checked' => $report['articles_checked'],
    'articles_enriched' => count($report['articles_enriched']),
    'images_replaced' => count($report['images_replaced']),
    'seo_titles_cleaned' => count($report['seo_titles_cleaned']),
    'excerpt_rewritten' => count($report['excerpt_rewritten']),
    'skipped_image_replacements' => $report['skipped_image_replacements'],
    'report' => $reportPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

function detectProfile(Article $article, array $profiles): string
{
    $haystack = Str::lower($article->title.' '.$article->slug.' '.strip_tags((string) $article->excerpt));

    foreach ($profiles as $key => $profile) {
        if ($key === 'default') {
            continue;
        }

        foreach ($profile['keywords'] as $keyword) {
            if (str_contains($haystack, Str::lower($keyword))) {
                return $key;
            }
        }
    }

    return 'default';
}

function wordCount(string $html): int
{
    return str_word_count(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5));
}

function cleanSeoTitle(string $title): string
{
    $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5);
    $parts = preg_split('/\s*\|\s*|\s+[–—-]\s+/', $title) ?: [$title];
    $parts = array_values(array_filter(array_map('trim', $parts), function (string $part): bool {
        $normalized = Str::lower($part);

        return $part !== ''
            && ! in_array($normalized, ['japan trip tools', 'japan travel guide'], true);
    }));

    $base = $parts[0] ?? trim($title);
    $base = preg_replace('/\s+/', ' ', $base) ?: $base;

    if (mb_strlen($base) > 62) {
        $base = rtrim(mb_substr($base, 0, 59), " \t\n\r\0\x0B,.;:").'...';
    }

    return $base;
}

function makeExcerpt(Article $article, array $profile, array $destinations): string
{
    $place = placePhrase($destinations);
    $text = "{$article->title} helps {$place} travelers plan {$profile['intent']} with route timing, comfort checks, and realistic backup choices.";

    return trimToLength($text, 158);
}

function makeMetaDescription(Article $article, array $profile, array $destinations): string
{
    $text = "Plan {$article->title} with transport checks, route timing, luggage notes, weather backups, and common mistakes to avoid.";

    return trimToLength($text, 158);
}

function placePhrase(array $destinations): string
{
    if ($destinations === []) {
        return 'Japan';
    }

    $destinations = array_slice($destinations, 0, 2);

    return implode(' and ', $destinations);
}

function trimToLength(string $text, int $limit): string
{
    $text = preg_replace('/\s+/', ' ', trim($text)) ?: trim($text);

    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    $trimmed = mb_substr($text, 0, $limit - 1);
    $space = mb_strrpos($trimmed, ' ');

    if ($space !== false && $space > 80) {
        $trimmed = mb_substr($trimmed, 0, $space);
    }

    $trimmed = rtrim($trimmed, " \t\n\r\0\x0B,.;:");
    $words = preg_split('/\s+/', $trimmed) ?: [];
    $weakEndings = ['and', 'or', 'with', 'for', 'to', 'of', 'in', 'on', 'by', 'from', 'that', 'the', 'a', 'an'];

    while ($words && in_array(Str::lower(end($words)), $weakEndings, true)) {
        array_pop($words);
    }

    return rtrim(implode(' ', $words), " \t\n\r\0\x0B,.;:").'.';
}

function extractFirstFigure(string $html): ?string
{
    if (! preg_match('/<figure\b.*?<\/figure>/is', $html, $matches)) {
        return null;
    }

    return $matches[0];
}

function rebuildFigure(string $figure, string $targetSlug, string $alt, string $caption): string
{
    preg_match('/data-image-attribution=(["\'])(.*?)\1/i', $figure, $attrMatch);
    preg_match('/data-image-license=(["\'])(.*?)\1/i', $figure, $licenseMatch);
    $attribution = html_entity_decode($attrMatch[2] ?? 'verified image source', ENT_QUOTES | ENT_HTML5);
    $license = html_entity_decode($licenseMatch[2] ?? 'recorded license', ENT_QUOTES | ENT_HTML5);

    $figure = preg_replace('/\balt=(["\']).*?\1/i', 'alt="'.e($alt).'"', $figure, 1) ?: $figure;
    $figure = preg_replace(
        '/<figcaption\b[^>]*>.*?<\/figcaption>/is',
        '<figcaption class="px-4 py-3 text-sm text-slate-600">'.e($caption).' Image: '.e($attribution).' / '.e($license).'. <a href="https://japantriptools.com/image-credits/#article-'.e($targetSlug).'">Image credit details</a>.</figcaption>',
        $figure,
        1,
    ) ?: $figure;

    return preg_replace('/#article-[a-z0-9-]+/', '#article-'.$targetSlug, $figure) ?: $figure;
}

function replaceFirstFigure(string $html, string $figure): string
{
    if (preg_match('/<figure\b.*?<\/figure>/is', $html)) {
        return preg_replace('/<figure\b.*?<\/figure>/is', $figure, $html, 1) ?: $html;
    }

    return $figure."\n".$html;
}

function buildEnrichmentHtml(Article $article, array $profile, array $destinations, array $categories, array $tags, int $wordGap): string
{
    $place = placePhrase($destinations);
    $categoryPhrase = $categories ? implode(', ', array_slice($categories, 0, 3)) : 'Japan travel planning';
    $tagPhrase = $tags ? implode(', ', array_slice($tags, 0, 4)) : 'routes, timing, transport, and comfort';
    $title = e($article->title);
    $placeEscaped = e($place);
    $categoryEscaped = e($categoryPhrase);
    $tagEscaped = e($tagPhrase);

    $blocks = [
        [
            'heading' => 'How to use this guide on the road',
            'paragraphs' => [
                "{$title} is most useful when it is treated as a planning worksheet, not a rigid schedule. Start by deciding what would make the day successful for your group, then use the route, timing, and backup notes to remove weak points before they become expensive or tiring.",
                "For {$placeEscaped}, the important question is not only what looks interesting. The better question is whether the plan still works with real station exits, luggage, weather, meal timing, and the pace of the slowest traveler. A plan that leaves space for recovery usually produces more usable memories than one that tries to collect every possible stop.",
            ],
            'list' => [
                "Mark one non-negotiable goal for the day and let everything else support it.",
                "Keep transfers simple when the day involves bags, children, late arrivals, or bad weather.",
                "Save official transport, attraction, and hotel pages before leaving reliable Wi-Fi.",
            ],
        ],
        [
            'heading' => 'Timing and route strategy',
            'paragraphs' => [
                "Good Japan travel planning usually starts with time blocks. Morning is best protected for the main experience, midday should include a realistic meal or rest point, and evening should finish near a station or hotel area that is easy to understand when everyone is tired.",
                "The planning angle for this page is {$profile['intent']}. That means the route should be judged by how smoothly it works in practice: {$profile['primary']}. When two options look similar, choose the one with fewer fragile connections and more chances to pause without losing the whole day.",
            ],
            'list' => [
                "Check whether the route has a natural early exit if weather or crowds change.",
                "Avoid pairing two distant highlights unless the transport link is direct and frequent.",
                "Put reservations, ticket windows, and last-entry times into the plan before adding optional stops.",
            ],
        ],
        [
            'heading' => 'What to double-check before booking',
            'paragraphs' => [
                "This page is written for {$profile['reader']}. Before making paid decisions, use it to create a short verification list. Official sources should be checked for opening days, seasonal operation, pass coverage, reservation rules, and any temporary service notices close to your travel date.",
                "For searchers comparing {$categoryEscaped}, the most useful detail is often a small operational one: which station exit to use, where luggage will be stored, whether a meal stop needs a reservation, or whether the final connection is still running after dinner. These checks are rarely glamorous, but they prevent the most common travel-day problems.",
            ],
            'list' => $profile['checks'],
        ],
        [
            'heading' => 'Common mistakes to avoid',
            'paragraphs' => [
                "The main mistake with {$title} is {$profile['pitfall']}. It can make an itinerary look efficient on a map while feeling stressful in real life. Build the plan around the moments where mistakes are hardest to fix: arrival, lunch, closing time, luggage movement, and the final return.",
                "Another common problem is overvaluing distance and undervaluing friction. A short transfer with stairs, crowds, and bags may feel worse than a slightly longer route with a direct train or bus. If the trip includes first-time visitors, families, or winter weather, comfort often matters more than saving a few minutes.",
            ],
            'list' => [
                "Do not assume every station on the map is easy with luggage.",
                "Do not push the main experience too late in the day.",
                "Do not treat social media highlights as proof that the route is practical for your dates.",
            ],
        ],
        [
            'heading' => 'A better way to compare options',
            'paragraphs' => [
                "When choosing between two versions of this plan, score them on four practical factors: total transfers, walking after dark, meal certainty, and how easily you can recover if the first choice is crowded or closed. The option with the lower stress score is often the better vacation choice, even when it is not the theoretically fastest route.",
                "Use {$tagEscaped} as the working theme for this page. If the article is part of a larger Japan itinerary, place it next to related pages about transport, weather, lodging, and daily budget so the reader can move from inspiration to action without leaving the site for basic planning questions.",
            ],
            'list' => [
                "Best for: travelers who want practical decisions before they arrive.",
                "Use with: nearby destination guides, transport calculators, weather planning, and hotel-base comparisons.",
                "Update check: revisit official pages if your travel date is seasonal, holiday-heavy, or more than a few weeks away.",
            ],
        ],
    ];

    if ($wordGap > 550) {
        $blocks[] = [
            'heading' => 'Sample planning flow',
            'paragraphs' => [
                "A simple way to turn this guide into an itinerary is to write the day in three layers. The first layer is the must-do experience. The second layer is the support plan: where to eat, where to rest, and how to return. The third layer is optional: extra stops that can be removed without damaging the trip.",
                "This layered approach is especially helpful in Japan because transport is strong but travel days still fail when visitors ignore small constraints. A museum may close earlier than expected, a scenic area may require a bus connection, a restaurant may have a long queue, and a station may take longer to cross than the map suggests. Planning with layers keeps those issues manageable.",
            ],
            'list' => [
                "Layer 1: protect the main experience and book it if required.",
                "Layer 2: add meals, restrooms, luggage storage, and the return route.",
                "Layer 3: keep optional stops nearby and easy to remove.",
            ],
        ];
    }

    $html = '<section class="quality-guide-block"><h2>Practical planning notes</h2>';
    foreach ($blocks as $block) {
        $html .= '<h3>'.e($block['heading']).'</h3>';
        foreach ($block['paragraphs'] as $paragraph) {
            $html .= '<p>'.$paragraph.'</p>';
        }
        $html .= '<ul>';
        foreach ($block['list'] as $item) {
            $html .= '<li>'.e($item).'</li>';
        }
        $html .= '</ul>';
    }
    $html .= '</section>';

    return $html;
}

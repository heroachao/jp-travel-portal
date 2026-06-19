@extends('layouts.public')

@push('structured-data')
    <script type="application/ld+json">{!! json_encode($toolJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($toolFaqJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
    @php
        $slug = $tool['slug'];
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="public-card travel-tool-show-hero" style="--tool-accent: {{ $tool['accent'] }}">
            <div>
                <a class="travel-tool-back" href="{{ route('tools.index') }}">All tools</a>
                <p class="public-kicker">{{ $tool['category'] }}</p>
                <h1>{{ $tool['name'] }}</h1>
                <p>{{ $tool['summary'] }}</p>
            </div>
            <div class="travel-tool-output-card">
                <span>Output</span>
                <strong>{{ $tool['output'] }}</strong>
                <small>Runs locally in your browser</small>
            </div>
        </div>
    </section>

    @ad('content-mid-rectangle')

    <section class="mx-auto grid max-w-7xl gap-5 px-4 pb-12 lg:grid-cols-[minmax(0,1fr)_320px]">
        <div class="public-card travel-tool-panel" data-travel-tool="{{ $slug }}" style="--tool-accent: {{ $tool['accent'] }}">
            @if($slug === 'trip-planner')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Starting region</span>
                            <select name="start">
                                <option value="tokyo">Tokyo</option>
                                <option value="kansai">Kansai</option>
                                <option value="hokkaido">Hokkaido</option>
                                <option value="kyushu">Kyushu</option>
                                <option value="okinawa">Okinawa</option>
                            </select>
                        </label>
                        <label>
                            <span>Nights</span>
                            <input type="number" name="nights" min="2" max="28" value="7">
                        </label>
                        <label>
                            <span>Pace</span>
                            <select name="pace">
                                <option value="balanced">Balanced</option>
                                <option value="slow">Slow</option>
                                <option value="fast">Fast</option>
                            </select>
                        </label>
                        <label>
                            <span>Trip style</span>
                            <select name="style">
                                <option value="first-time">First-time highlights</option>
                                <option value="food">Food and neighborhoods</option>
                                <option value="nature">Nature and scenery</option>
                                <option value="culture">Culture and history</option>
                            </select>
                        </label>
                    </div>
                    <fieldset>
                        <legend>Interests</legend>
                        <label><input type="checkbox" name="interests" value="rail" checked> Rail</label>
                        <label><input type="checkbox" name="interests" value="food" checked> Food</label>
                        <label><input type="checkbox" name="interests" value="onsen"> Onsen</label>
                        <label><input type="checkbox" name="interests" value="shopping"> Shopping</label>
                    </fieldset>
                    <button type="submit">Build route</button>
                </form>
            @elseif($slug === 'jr-pass-calculator')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Travelers</span>
                            <input type="number" name="travelers" min="1" max="8" value="2">
                        </label>
                        <label>
                            <span>Pass price per person (JPY)</span>
                            <input type="number" name="passPrice" min="0" step="500" value="50000">
                        </label>
                        <label>
                            <span>Custom rail cost per person (JPY)</span>
                            <input type="number" name="customCost" min="0" step="500" value="0">
                        </label>
                        <label>
                            <span>Trip window</span>
                            <select name="window">
                                <option value="7">7 days</option>
                                <option value="14">14 days</option>
                                <option value="21">21 days</option>
                            </select>
                        </label>
                    </div>
                    <fieldset>
                        <legend>Common one-way rail legs</legend>
                        <label><input type="checkbox" name="legs" value="13320" checked> Tokyo to Kyoto</label>
                        <label><input type="checkbox" name="legs" value="5940"> Kyoto to Hiroshima</label>
                        <label><input type="checkbox" name="legs" value="10950"> Hiroshima to Fukuoka</label>
                        <label><input type="checkbox" name="legs" value="14720"> Tokyo to Kanazawa</label>
                        <label><input type="checkbox" name="legs" value="11110"> Tokyo to Sendai</label>
                        <label><input type="checkbox" name="legs" value="16420"> Tokyo to Shin-Hakodate-Hokuto</label>
                    </fieldset>
                    <button type="submit">Compare value</button>
                </form>
            @elseif($slug === 'airport-transfer')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Airport</span>
                            <select name="airport">
                                <option value="haneda">Tokyo Haneda (HND)</option>
                                <option value="narita">Tokyo Narita (NRT)</option>
                                <option value="kix">Kansai International (KIX)</option>
                                <option value="itm">Osaka Itami (ITM)</option>
                                <option value="cts">New Chitose (CTS)</option>
                                <option value="fuk">Fukuoka (FUK)</option>
                                <option value="oka">Naha (OKA)</option>
                            </select>
                        </label>
                        <label>
                            <span>Hotel area</span>
                            <select name="area">
                                <option value="city-center">City center</option>
                                <option value="station">Major rail station</option>
                                <option value="bay">Bay or resort area</option>
                                <option value="old-town">Historic area</option>
                            </select>
                        </label>
                        <label>
                            <span>Priority</span>
                            <select name="priority">
                                <option value="simple">Simplest route</option>
                                <option value="budget">Lower cost</option>
                                <option value="luggage">Heavy luggage</option>
                                <option value="late">Late arrival</option>
                            </select>
                        </label>
                        <label>
                            <span>Suitcases</span>
                            <input type="number" name="bags" min="0" max="8" value="2">
                        </label>
                    </div>
                    <button type="submit">Find transfer</button>
                </form>
            @elseif($slug === 'budget-calculator')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Travelers</span>
                            <input type="number" name="travelers" min="1" max="8" value="2">
                        </label>
                        <label>
                            <span>Nights</span>
                            <input type="number" name="nights" min="1" max="60" value="8">
                        </label>
                        <label>
                            <span>Travel style</span>
                            <select name="style">
                                <option value="mid">Comfortable mid-range</option>
                                <option value="budget">Budget-conscious</option>
                                <option value="premium">Premium</option>
                            </select>
                        </label>
                        <label>
                            <span>Shopping reserve (JPY)</span>
                            <input type="number" name="shopping" min="0" step="1000" value="30000">
                        </label>
                    </div>
                    <fieldset>
                        <legend>Add-ons</legend>
                        <label><input type="checkbox" name="addons" value="themepark"> Theme park day</label>
                        <label><input type="checkbox" name="addons" value="longrail" checked> Intercity rail</label>
                        <label><input type="checkbox" name="addons" value="onsen"> Onsen ryokan night</label>
                    </fieldset>
                    <button type="submit">Estimate budget</button>
                </form>
            @elseif($slug === 'region-finder')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Month</span>
                            <select name="month">
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($month === 4)>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Travel style</span>
                            <select name="style">
                                <option value="first">First Japan trip</option>
                                <option value="food">Food-led trip</option>
                                <option value="nature">Nature and scenery</option>
                                <option value="culture">Culture and crafts</option>
                            </select>
                        </label>
                        <label>
                            <span>Pace</span>
                            <select name="pace">
                                <option value="balanced">Balanced</option>
                                <option value="slow">Slow</option>
                                <option value="fast">Fast</option>
                            </select>
                        </label>
                        <label>
                            <span>Weather preference</span>
                            <select name="weather">
                                <option value="mild">Mild weather</option>
                                <option value="snow">Snow</option>
                                <option value="beach">Warm islands</option>
                                <option value="any">Flexible</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Match regions</button>
                </form>
            @elseif($slug === 'season-packing')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Month</span>
                            <select name="month">
                                @foreach(range(1, 12) as $month)
                                    <option value="{{ $month }}" @selected($month === 11)>{{ DateTime::createFromFormat('!m', $month)->format('F') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Region</span>
                            <select name="region">
                                <option value="tokyo">Tokyo and central Honshu</option>
                                <option value="hokkaido">Hokkaido</option>
                                <option value="kansai">Kansai</option>
                                <option value="kyushu">Kyushu</option>
                                <option value="okinawa">Okinawa</option>
                            </select>
                        </label>
                        <label>
                            <span>Trip length</span>
                            <input type="number" name="days" min="2" max="45" value="10">
                        </label>
                        <label>
                            <span>Laundry plan</span>
                            <select name="laundry">
                                <option value="yes">Will do laundry</option>
                                <option value="no">Pack every day</option>
                            </select>
                        </label>
                    </div>
                    <fieldset>
                        <legend>Activities</legend>
                        <label><input type="checkbox" name="activities" value="temples" checked> Temples and walking</label>
                        <label><input type="checkbox" name="activities" value="hiking"> Light hiking</label>
                        <label><input type="checkbox" name="activities" value="onsen"> Onsen</label>
                        <label><input type="checkbox" name="activities" value="beach"> Beach</label>
                    </fieldset>
                    <button type="submit">Build packing list</button>
                </form>
            @elseif($slug === 'ic-card-checklist')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Arrival airport</span>
                            <select name="airport">
                                <option value="haneda">Haneda</option>
                                <option value="narita">Narita</option>
                                <option value="kix">Kansai International</option>
                                <option value="fuk">Fukuoka</option>
                                <option value="oka">Naha</option>
                            </select>
                        </label>
                        <label>
                            <span>Phone wallet</span>
                            <select name="wallet">
                                <option value="iphone">iPhone wallet ready</option>
                                <option value="android">Android from outside Japan</option>
                                <option value="none">No mobile wallet</option>
                            </select>
                        </label>
                        <label>
                            <span>Main cities</span>
                            <select name="cities">
                                <option value="major">Major cities only</option>
                                <option value="mixed">Cities plus small towns</option>
                                <option value="rural">Rural routes</option>
                            </select>
                        </label>
                        <label>
                            <span>Cash backup</span>
                            <select name="cash">
                                <option value="some">Some cash ready</option>
                                <option value="none">No cash yet</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Create checklist</button>
                </form>
            @elseif($slug === 'luggage-planner')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Suitcases</span>
                            <input type="number" name="bags" min="0" max="8" value="2">
                        </label>
                        <label>
                            <span>Hotel changes</span>
                            <input type="number" name="hotels" min="1" max="12" value="4">
                        </label>
                        <label>
                            <span>Route type</span>
                            <select name="routeType">
                                <option value="rail">Intercity rail</option>
                                <option value="urban">Urban base</option>
                                <option value="island">Island or ferry route</option>
                            </select>
                        </label>
                        <label>
                            <span>Transfer comfort</span>
                            <select name="comfort">
                                <option value="light">Prefer hands-free</option>
                                <option value="carry">Can carry bags</option>
                                <option value="stairs">Avoid stairs</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Plan luggage</button>
                </form>
            @elseif($slug === 'allergy-card')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Allergy or restriction</span>
                            <select name="allergy">
                                <option value="peanut">Peanuts</option>
                                <option value="shellfish">Shellfish</option>
                                <option value="dairy">Dairy</option>
                                <option value="egg">Egg</option>
                                <option value="gluten">Wheat or gluten</option>
                                <option value="vegetarian">Vegetarian</option>
                            </select>
                        </label>
                        <label>
                            <span>Severity</span>
                            <select name="severity">
                                <option value="avoid">Need to avoid</option>
                                <option value="severe">Severe allergy</option>
                                <option value="preference">Preference</option>
                            </select>
                        </label>
                        <label>
                            <span>Dining context</span>
                            <select name="context">
                                <option value="restaurant">Restaurant</option>
                                <option value="convenience">Convenience store</option>
                                <option value="ryokan">Ryokan meal</option>
                            </select>
                        </label>
                        <label>
                            <span>Need staff confirmation</span>
                            <select name="confirm">
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Create phrase card</button>
                </form>
            @elseif($slug === 'tax-free-calculator')
                <form class="travel-tool-form" data-tool-form>
                    <div class="travel-tool-grid">
                        <label>
                            <span>Purchase total (JPY)</span>
                            <input type="number" name="purchase" min="0" step="500" value="22000">
                        </label>
                        <label>
                            <span>Tax rate (%)</span>
                            <input type="number" name="taxRate" min="0" max="15" step="0.1" value="10">
                        </label>
                        <label>
                            <span>Store fee (%)</span>
                            <input type="number" name="feeRate" min="0" max="10" step="0.1" value="0">
                        </label>
                        <label>
                            <span>Tax-free threshold (JPY)</span>
                            <input type="number" name="threshold" min="0" step="500" value="5000">
                        </label>
                    </div>
                    <button type="submit">Estimate savings</button>
                </form>
            @endif

            <div class="travel-tool-result" data-tool-result aria-live="polite"></div>

            <section class="content-prose mt-8 border-t border-slate-200 pt-7">
                <h2>How this {{ strtolower($tool['category']) }} tool helps</h2>
                <p>{{ $tool['seo_intro'] }}</p>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach($tool['seo_sections'] as $section)
                        <div class="rounded-lg border border-slate-200 bg-white p-4">
                            <h3 class="mt-0">{{ $section['title'] }}</h3>
                            <p>{{ $section['body'] }}</p>
                        </div>
                    @endforeach
                </div>
                <h2>Frequently asked questions</h2>
                @foreach($tool['faqs'] as $faq)
                    <h3>{{ $faq['question'] }}</h3>
                    <p>{{ $faq['answer'] }}</p>
                @endforeach
                <h2>Continue planning</h2>
                <p>Use these related searches to connect the tool result with detailed Japan travel guides and regional planning articles.</p>
                <div class="not-prose flex flex-wrap gap-2">
                    @foreach($tool['related_searches'] as $search)
                        <a class="public-tag-pill" href="{{ route('search', ['q' => $search]) }}">{{ $search }}</a>
                    @endforeach
                    <a class="public-tag-pill" href="{{ route('articles.index') }}">Latest Japan guides</a>
                </div>
            </section>
        </div>

        <aside class="space-y-5">
            <section class="public-card travel-tool-side">
                <div class="public-section-heading">
                    <h2>More Tools</h2>
                    <a href="{{ route('tools.index') }}">All</a>
                </div>
                <div class="mt-3 grid gap-3">
                    @foreach($tools as $sideTool)
                        <a class="public-tool-link @if($sideTool['slug'] === $slug) is-active @endif" href="{{ route('tools.show', $sideTool['slug']) }}">
                            <strong>{{ $sideTool['short_name'] }}</strong>
                            <span>{{ $sideTool['category'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="public-card travel-tool-side">
                <div class="public-section-heading">
                    <h2>Related Guides</h2>
                    <a href="{{ route('search') }}">Search</a>
                </div>
                <div class="mt-3 grid gap-3">
                    @foreach($tool['inputs'] as $input)
                        <a class="public-tag-pill" href="{{ route('search', ['q' => $input]) }}">{{ $input }}</a>
                    @endforeach
                </div>
            </section>
        </aside>
    </section>
@endsection

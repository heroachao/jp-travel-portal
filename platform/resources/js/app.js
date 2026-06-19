import './japan-games';

const normalizeAnalyticsText = (value, maxLength = 120) => {
    if (typeof value !== 'string') {
        return null;
    }

    const normalized = value.replace(/\s+/g, ' ').trim();

    if (normalized === '') {
        return null;
    }

    return normalized.slice(0, maxLength);
};

const cleanAnalyticsParams = (params) => Object.fromEntries(
    Object.entries(params).filter(([, value]) => value !== null && value !== undefined && value !== ''),
);

const sendAnalyticsEvent = (eventName, params = {}) => {
    if (typeof window.gtag !== 'function') {
        return;
    }

    window.gtag('event', eventName, cleanAnalyticsParams({
        page_path: window.location.pathname,
        page_title: document.title,
        ...params,
    }));
};

const getAnalyticsPageType = () => {
    const path = window.location.pathname;

    if (path === '/') {
        return 'home';
    }

    if (path.startsWith('/articles/')) {
        return 'article';
    }

    if (path.startsWith('/destinations/') || path.startsWith('/regions/')) {
        return 'destination';
    }

    if (path.startsWith('/categories/')) {
        return 'category';
    }

    if (path.startsWith('/search')) {
        return 'search';
    }

    if (path.startsWith('/tools')) {
        return 'tool';
    }

    return 'public';
};

const trackSiteSearch = (term, context) => {
    const searchTerm = normalizeAnalyticsText(String(term ?? ''), 100);

    if (! searchTerm) {
        return;
    }

    sendAnalyticsEvent('search', {
        search_term: searchTerm,
        search_context: context,
    });
};

const trackSearchInteractions = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (! (form instanceof HTMLFormElement)) {
            return;
        }

        const action = form.getAttribute('action') || '';
        const isSearchForm = form.getAttribute('role') === 'search' || action.includes('/search');

        if (! isSearchForm) {
            return;
        }

        trackSiteSearch(new FormData(form).get('q'), 'site_search_form');
    });

    const query = new URLSearchParams(window.location.search).get('q');
    const normalizedPath = window.location.pathname.replace(/\/$/, '');

    if (normalizedPath === '/search' && query) {
        const searchTerm = normalizeAnalyticsText(query, 100);

        if (searchTerm) {
            sendAnalyticsEvent('view_search_results', {
                search_term: searchTerm,
                search_context: 'search_results_page',
            });
        }
    }
};

const normalizeSearchValue = (value) => String(value ?? '').trim().toLowerCase();

const initStaticSearchPage = () => {
    const form = document.querySelector('[data-search-page-form]');
    const results = document.querySelector('[data-search-results]');

    if (! form || ! results) {
        return;
    }

    const cards = [...results.querySelectorAll('[data-index]')];
    const countNode = document.querySelector('[data-search-result-count]');
    const emptyNode = document.querySelector('[data-search-empty]');
    const activeLabel = document.querySelector('[data-search-active-label]');
    const fields = ['q', 'region', 'category', 'tag', 'sort', 'coupon'];
    const params = new URLSearchParams(window.location.search);

    fields.forEach((name) => {
        const field = form.elements.namedItem(name);

        if (! field || ! params.has(name)) {
            return;
        }

        if (field instanceof HTMLInputElement && field.type === 'checkbox') {
            field.checked = params.get(name) === '1';
            return;
        }

        if ('value' in field) {
            field.value = params.get(name) || '';
        }
    });

    const getValue = (name) => {
        const field = form.elements.namedItem(name);

        if (field instanceof HTMLInputElement && field.type === 'checkbox') {
            return field.checked ? '1' : '';
        }

        return field && 'value' in field ? String(field.value || '') : '';
    };

    const updateUrl = () => {
        const next = new URLSearchParams();

        fields.forEach((name) => {
            const value = getValue(name);

            if (value && !(name === 'sort' && value === 'newest')) {
                next.set(name, value);
            }
        });

        const query = next.toString();
        window.history.replaceState(null, '', `${window.location.pathname}${query ? `?${query}` : ''}`);
    };

    const sortCards = (visibleCards, sort) => {
        const number = (card, key) => Number(card.dataset[key] || 0);

        return [...visibleCards].sort((a, b) => {
            if (sort === 'popular') {
                return number(b, 'popularity') - number(a, 'popularity') || number(b, 'published') - number(a, 'published');
            }

            if (sort === 'updated') {
                return number(b, 'updated') - number(a, 'updated') || number(b, 'published') - number(a, 'published');
            }

            if (sort === 'recommended') {
                return Number(b.dataset.coupon || 0) - Number(a.dataset.coupon || 0)
                    || number(b, 'popularity') - number(a, 'popularity')
                    || number(b, 'published') - number(a, 'published');
            }

            return number(b, 'published') - number(a, 'published');
        });
    };

    const applyFilters = () => {
        const term = normalizeSearchValue(getValue('q'));
        const region = normalizeSearchValue(getValue('region'));
        const category = normalizeSearchValue(getValue('category'));
        const tag = normalizeSearchValue(getValue('tag'));
        const coupon = getValue('coupon') === '1';
        const sort = getValue('sort') || 'newest';

        const visibleCards = cards.filter((card) => {
            const matchesTerm = term === '' || (card.dataset.index || '').includes(term);
            const matchesRegion = region === '' || (` ${card.dataset.region || ''} `).includes(` ${region} `);
            const matchesCategory = category === '' || (` ${card.dataset.category || ''} `).includes(` ${category} `);
            const matchesTag = tag === '' || (` ${card.dataset.tag || ''} `).includes(` ${tag} `);
            const matchesCoupon = ! coupon || card.dataset.coupon === '1';

            return matchesTerm && matchesRegion && matchesCategory && matchesTag && matchesCoupon;
        });

        cards.forEach((card) => {
            card.hidden = true;
        });

        sortCards(visibleCards, sort).forEach((card, index) => {
            card.hidden = false;
            card.querySelector(':scope > span').textContent = String(index + 1).padStart(2, '0');
            results.appendChild(card);
        });

        if (countNode) {
            countNode.textContent = String(visibleCards.length);
        }

        if (emptyNode) {
            emptyNode.classList.toggle('hidden', visibleCards.length !== 0);
        }

        if (activeLabel) {
            const labels = [];
            if (term) labels.push(`keyword "${term}"`);
            if (region) labels.push(region);
            if (category) labels.push(category);
            if (tag) labels.push(`#${tag}`);
            if (coupon) labels.push('service available');

            activeLabel.textContent = labels.length
                ? `Filtered by ${labels.join(', ')}.`
                : 'Use keywords, regions, categories, tags, and sorting to narrow the guide feed.';
        }
    };

    form.addEventListener('input', () => {
        applyFilters();
        updateUrl();
    });
    form.addEventListener('change', () => {
        applyFilters();
        updateUrl();
    });
    form.addEventListener('submit', () => {
        updateUrl();
    });

    applyFilters();
};

const trackOutboundClicks = () => {
    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');

        if (! link) {
            return;
        }

        let url;

        try {
            url = new URL(link.getAttribute('href'), window.location.href);
        } catch {
            return;
        }

        if (! ['http:', 'https:'].includes(url.protocol) || url.hostname === window.location.hostname) {
            return;
        }

        sendAnalyticsEvent('outbound_click', {
            link_domain: url.hostname,
            link_url: url.href.slice(0, 250),
            link_text: normalizeAnalyticsText(link.textContent ?? '', 80),
            service_key: normalizeAnalyticsText(link.dataset.serviceKey ?? '', 80),
            page_type: getAnalyticsPageType(),
        });
    });
};

const trackScrollDepth = () => {
    const thresholds = [50, 75, 90];
    const reached = new Set();
    let ticking = false;

    const measure = () => {
        ticking = false;

        const scrollHeight = document.documentElement.scrollHeight;
        const viewportBottom = window.scrollY + window.innerHeight;

        if (scrollHeight <= window.innerHeight + 40) {
            return;
        }

        const depth = Math.min(100, Math.round((viewportBottom / scrollHeight) * 100));

        thresholds.forEach((threshold) => {
            if (depth >= threshold && ! reached.has(threshold)) {
                reached.add(threshold);
                sendAnalyticsEvent('scroll_depth', {
                    percent_scrolled: threshold,
                    page_type: getAnalyticsPageType(),
                });
            }
        });
    };

    window.addEventListener('scroll', () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(measure);
    }, { passive: true });

    measure();
};

const trackEngagementCheckpoints = () => {
    const checkpoints = [30, 60, 120, 300];
    const startedAt = Date.now();

    checkpoints.forEach((seconds) => {
        window.setTimeout(() => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            sendAnalyticsEvent('article_engagement', {
                engagement_seconds: seconds,
                elapsed_seconds: Math.round((Date.now() - startedAt) / 1000),
                page_type: getAnalyticsPageType(),
            });
        }, seconds * 1000);
    });
};

const formatJpy = (value) => new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'JPY',
    maximumFractionDigits: 0,
}).format(Math.round(Number(value) || 0));

const numberFromForm = (form, name, fallback = 0) => {
    const value = Number(new FormData(form).get(name));

    return Number.isFinite(value) ? value : fallback;
};

const selectedText = (form, name) => {
    const field = form.elements.namedItem(name);

    if (! (field instanceof HTMLSelectElement)) {
        return '';
    }

    return field.selectedOptions[0]?.textContent?.trim() || field.value;
};

const checkedValues = (form, name) => new FormData(form)
    .getAll(name)
    .map((value) => String(value));

const resultCard = (title, body, metrics = []) => `
    <article>
        <h2>${title}</h2>
        ${body}
        ${metrics.length ? `<div class="tool-result-metric">${metrics.map((metric) => `
            <span><b>${metric.value}</b><small>${metric.label}</small></span>
        `).join('')}</div>` : ''}
    </article>
`;

const renderList = (items) => `<ul>${items.map((item) => `<li>${item}</li>`).join('')}</ul>`;

const renderOrdered = (items) => `<ol>${items.map((item) => `<li>${item}</li>`).join('')}</ol>`;

const trackTravelToolUse = (slug, outcome) => {
    sendAnalyticsEvent('travel_tool_use', {
        tool_slug: slug,
        tool_outcome: normalizeAnalyticsText(outcome, 120),
    });
};

const travelToolHandlers = {
    'trip-planner': (form) => {
        const data = new FormData(form);
        const nights = Math.max(2, numberFromForm(form, 'nights', 7));
        const pace = String(data.get('pace') || 'balanced');
        const style = String(data.get('style') || 'first-time');
        const start = String(data.get('start') || 'tokyo');
        const interests = checkedValues(form, 'interests');
        const routeSeeds = {
            tokyo: ['Tokyo arrival and hotel area walk', 'Shibuya, Harajuku, and Shinjuku', 'Asakusa, Ueno, and Tokyo Station', 'Kamakura or Yokohama day', 'Kyoto transfer and Higashiyama', 'Kyoto temples and market dinner', 'Osaka food night', 'Departure buffer'],
            kansai: ['Osaka or Kyoto arrival base', 'Kyoto Higashiyama', 'Nara and Uji', 'Osaka food neighborhoods', 'Himeji or Kobe', 'Kyoto Arashiyama', 'Tokyo transfer if needed', 'Departure buffer'],
            hokkaido: ['Sapporo arrival and Susukino dinner', 'Sapporo parks and markets', 'Otaru day trip', 'Biei or Furano scenery', 'Noboribetsu onsen', 'Hakodate or Lake Toya', 'Return to Sapporo', 'Departure buffer'],
            kyushu: ['Fukuoka arrival and yatai evening', 'Dazaifu or Itoshima', 'Nagasaki culture route', 'Kumamoto and Aso', 'Beppu onsen', 'Yufuin or Kagoshima', 'Return to Fukuoka', 'Departure buffer'],
            okinawa: ['Naha arrival and Kokusai Street', 'Shuri and local food', 'Central island beaches', 'Northern Okinawa road trip', 'Island ferry or resort day', 'Rain backup and shopping', 'Naha final night', 'Departure buffer'],
        };
        const styleNotes = {
            'first-time': 'Keep major sights grouped by area and leave one flexible buffer block.',
            food: 'Anchor each day around one food district, then add nearby walks instead of cross-city jumps.',
            nature: 'Put scenery days after arrival days so weather changes do not break the route.',
            culture: 'Book slower mornings around temples, museums, crafts, and local streets.',
        };
        const paceNotes = {
            slow: 'Limit most days to one main area plus one optional stop.',
            balanced: 'Use two focused areas on city days and avoid late long transfers.',
            fast: 'Add one extra side trip, but keep luggage movement simple.',
        };
        const seed = routeSeeds[start] || routeSeeds.tokyo;
        const days = Array.from({ length: nights }, (_, index) => {
            const base = seed[index % seed.length];
            const interest = interests[index % Math.max(1, interests.length)] || 'planning';

            return `Day ${index + 1}: ${base}. Add a ${interest} angle and keep meals close to the route.`;
        });

        return resultCard(
            `${nights}-night ${selectedText(form, 'style')} route`,
            `<p>${paceNotes[pace]} ${styleNotes[style]}</p>${renderOrdered(days)}`,
            [
                { label: 'Pace', value: selectedText(form, 'pace') },
                { label: 'Start', value: selectedText(form, 'start') },
                { label: 'Interests', value: interests.length || 0 },
            ],
        );
    },

    'jr-pass-calculator': (form) => {
        const travelers = Math.max(1, numberFromForm(form, 'travelers', 1));
        const passPrice = Math.max(0, numberFromForm(form, 'passPrice', 0));
        const customCost = Math.max(0, numberFromForm(form, 'customCost', 0));
        const selectedLegs = checkedValues(form, 'legs').map(Number).filter(Number.isFinite);
        const railPerPerson = selectedLegs.reduce((sum, fare) => sum + fare, 0) + customCost;
        const railGroup = railPerPerson * travelers;
        const passGroup = passPrice * travelers;
        const delta = railPerPerson - passPrice;
        const verdict = delta >= 0
            ? `The pass may save about ${formatJpy(delta)} per person on this estimate.`
            : `Point-to-point tickets may be about ${formatJpy(Math.abs(delta))} cheaper per person.`;

        return resultCard(
            delta >= 0 ? 'Pass looks competitive' : 'Point-to-point looks better',
            `<p>${verdict} Update the pass price and fare legs before buying because rail fares and pass rules can change.</p>`,
            [
                { label: 'Rail estimate / person', value: formatJpy(railPerPerson) },
                { label: 'Pass price / person', value: formatJpy(passPrice) },
                { label: 'Group rail estimate', value: formatJpy(railGroup) },
                { label: 'Group pass estimate', value: formatJpy(passGroup) },
            ],
        );
    },

    'airport-transfer': (form) => {
        const airport = String(new FormData(form).get('airport') || 'haneda');
        const priority = String(new FormData(form).get('priority') || 'simple');
        const bags = numberFromForm(form, 'bags', 0);
        const airportOptions = {
            haneda: ['Keikyu train to Shinagawa or Yokohama', 'Tokyo Monorail to Hamamatsucho', 'Airport limousine bus if your hotel area is served'],
            narita: ['Skyliner to Ueno or Nippori', 'Narita Express to Tokyo, Shinjuku, or Yokohama', 'Airport bus for hotel-door simplicity'],
            kix: ['JR Haruka to Osaka, Kyoto, or Tennoji', 'Nankai train to Namba', 'Airport bus for late arrivals or heavy bags'],
            itm: ['Airport limousine bus to Osaka or Kyoto', 'Monorail plus rail transfer', 'Taxi only for short local hops'],
            cts: ['JR rapid train to Sapporo', 'Airport bus to hotel districts', 'Pre-booked transfer for ski luggage'],
            fuk: ['Subway to Hakata or Tenjin', 'Taxi for short city-center hops', 'Bus for selected hotel districts'],
            oka: ['Yui Rail monorail to Naha', 'Airport bus for resort areas', 'Rental car pickup for island road trips'],
        };
        const notes = {
            simple: 'Choose the option with the fewest transfers, especially after a long-haul flight.',
            budget: 'Prioritize rail or subway first, then compare bus only if it avoids a taxi.',
            luggage: 'Use airport buses or direct trains where possible; avoid tight station transfers with large bags.',
            late: 'Check the last train and bus times before landing day; keep taxi as the backup.',
        };
        const areaNote = `Hotel area: ${selectedText(form, 'area')}. ${bags >= 3 ? 'Heavy luggage makes direct bus or luggage forwarding more attractive.' : 'Your luggage count is manageable for rail if transfers are simple.'}`;

        return resultCard(
            `${selectedText(form, 'airport')} transfer shortlist`,
            `<p>${notes[priority]} ${areaNote}</p>${renderList(airportOptions[airport] || airportOptions.haneda)}`,
            [
                { label: 'Priority', value: selectedText(form, 'priority') },
                { label: 'Suitcases', value: bags },
            ],
        );
    },

    'budget-calculator': (form) => {
        const travelers = Math.max(1, numberFromForm(form, 'travelers', 1));
        const nights = Math.max(1, numberFromForm(form, 'nights', 1));
        const style = String(new FormData(form).get('style') || 'mid');
        const shopping = Math.max(0, numberFromForm(form, 'shopping', 0));
        const days = nights + 1;
        const profiles = {
            budget: { lodging: 8000, food: 3500, local: 1200, activities: 1800 },
            mid: { lodging: 15000, food: 6500, local: 1800, activities: 3200 },
            premium: { lodging: 32000, food: 12000, local: 3500, activities: 7000 },
        };
        const profile = profiles[style] || profiles.mid;
        const addons = checkedValues(form, 'addons');
        const addonCosts = addons.reduce((sum, addon) => sum + ({ themepark: 9500, longrail: 30000, onsen: 22000 }[addon] || 0), 0) * travelers;
        const lodging = profile.lodging * nights * travelers;
        const daily = (profile.food + profile.local + profile.activities) * days * travelers;
        const subtotal = lodging + daily + shopping + addonCosts;
        const reserve = subtotal * 0.12;
        const total = subtotal + reserve;

        return resultCard(
            `${selectedText(form, 'style')} budget range`,
            '<p>This planning range includes a 12% reserve for schedule changes, weather, local transport mistakes, or last-minute meals.</p>',
            [
                { label: 'Lodging', value: formatJpy(lodging) },
                { label: 'Food, local transit, activities', value: formatJpy(daily) },
                { label: 'Add-ons and shopping', value: formatJpy(addonCosts + shopping) },
                { label: 'Estimated total', value: formatJpy(total) },
            ],
        );
    },

    'region-finder': (form) => {
        const data = new FormData(form);
        const month = Number(data.get('month') || 4);
        const style = String(data.get('style') || 'first');
        const weather = String(data.get('weather') || 'mild');
        const regions = [
            { name: 'Tokyo', url: '/regions/tokyo', months: [3, 4, 5, 10, 11, 12], styles: ['first', 'food'], weather: ['mild', 'any'], reason: 'dense neighborhoods, rail access, food, museums, and easy day trips' },
            { name: 'Kansai', url: '/regions/kansai', months: [3, 4, 5, 10, 11], styles: ['first', 'culture', 'food'], weather: ['mild', 'any'], reason: 'Kyoto, Osaka, Nara, food streets, temples, and short rail hops' },
            { name: 'Hokkaido', url: '/regions/hokkaido', months: [1, 2, 6, 7, 8, 9], styles: ['nature'], weather: ['snow', 'mild', 'any'], reason: 'cooler weather, winter scenery, flower fields, seafood, and wide landscapes' },
            { name: 'Kyushu', url: '/regions/kyushu', months: [3, 4, 5, 10, 11], styles: ['food', 'nature', 'culture'], weather: ['mild', 'any'], reason: 'onsen, volcano scenery, food cities, and good rail or driving routes' },
            { name: 'Okinawa', url: '/regions/okinawa', months: [1, 2, 3, 4, 10, 11, 12], styles: ['nature'], weather: ['beach', 'mild', 'any'], reason: 'islands, beaches, resorts, road trips, and warm-weather pacing' },
            { name: 'Tohoku', url: '/regions/tohoku', months: [2, 4, 5, 8, 10, 11], styles: ['nature', 'culture'], weather: ['snow', 'mild', 'any'], reason: 'seasonal festivals, onsen, mountain scenery, and slower crowd levels' },
        ];
        const scored = regions
            .map((region) => ({
                ...region,
                score: (region.months.includes(month) ? 3 : 0)
                    + (region.styles.includes(style) ? 3 : 0)
                    + (region.weather.includes(weather) ? 2 : 0),
            }))
            .sort((a, b) => b.score - a.score)
            .slice(0, 3);

        return resultCard(
            'Best region matches',
            scored.map((region, index) => `
                <div class="tool-result-card">
                    <h3>${index + 1}. <a href="${region.url}">${region.name}</a></h3>
                    <p>Good fit for ${selectedText(form, 'month')} because of ${region.reason}.</p>
                </div>
            `).join(''),
            [
                { label: 'Month', value: selectedText(form, 'month') },
                { label: 'Style', value: selectedText(form, 'style') },
            ],
        );
    },

    'season-packing': (form) => {
        const month = numberFromForm(form, 'month', 1);
        const days = Math.max(2, numberFromForm(form, 'days', 10));
        const region = String(new FormData(form).get('region') || 'tokyo');
        const laundry = String(new FormData(form).get('laundry') || 'yes');
        const activities = checkedValues(form, 'activities');
        const season = month <= 2 || month === 12 ? 'winter' : month <= 5 ? 'spring' : month <= 9 ? 'summer' : 'autumn';
        const seasonalItems = {
            winter: ['Warm layers', 'Compact gloves', 'Moisturizer', 'Socks for shoes-off interiors'],
            spring: ['Light jacket', 'Layerable tops', 'Pollen-friendly tissues', 'Comfortable walking shoes'],
            summer: ['Breathable shirts', 'Sun protection', 'Small towel', 'Rain shell or compact umbrella'],
            autumn: ['Light knit or fleece', 'Wind layer', 'Evening layer', 'Comfortable walking shoes'],
        };
        const regionItems = {
            hokkaido: ['Extra thermal layer', 'Shoes with grip in cold months'],
            okinawa: ['Swimwear', 'Reef-safe sun protection', 'Sandals'],
            kyushu: ['Onsen-friendly small towel', 'Flexible rain layer'],
            kansai: ['Temple-friendly socks', 'Day bag for city walks'],
            tokyo: ['Compact day bag', 'Battery pack'],
        };
        const activityItems = activities.flatMap((activity) => ({
            temples: ['Easy-off shoes', 'Modest layer for religious sites'],
            hiking: ['Trail shoes', 'Light first-aid kit'],
            onsen: ['Small towel', 'Simple overnight pouch'],
            beach: ['Swimwear', 'Waterproof pouch'],
        }[activity] || []));
        const clothingCount = laundry === 'yes' ? Math.min(6, Math.ceil(days / 2) + 1) : days;
        const items = [...seasonalItems[season], ...(regionItems[region] || []), ...activityItems, `${clothingCount} days of core outfits`];

        return resultCard(
            `${season} packing list`,
            `<p>Pack for ${days} days in ${selectedText(form, 'region')}. Adjust shoes and layers around your longest walking day.</p>${renderList([...new Set(items)])}`,
            [
                { label: 'Season', value: season },
                { label: 'Laundry', value: selectedText(form, 'laundry') },
            ],
        );
    },

    'ic-card-checklist': (form) => {
        const wallet = String(new FormData(form).get('wallet') || 'iphone');
        const cities = String(new FormData(form).get('cities') || 'major');
        const cash = String(new FormData(form).get('cash') || 'some');
        const checklist = [
            `Arrival plan: start at ${selectedText(form, 'airport')} and prepare a first ride before leaving the terminal.`,
            wallet === 'iphone' ? 'Set up a mobile IC card before heavy travel days.' : 'Plan for a physical IC card or paper tickets where mobile setup is limited.',
            cash === 'none' ? 'Withdraw cash early for buses, lockers, temples, and small shops.' : 'Keep small cash for rural buses, lockers, and temples.',
            cities === 'rural' ? 'Expect more paper tickets, cash-only buses, and station staff checks outside major cities.' : 'Major city rail and convenience store payments should be straightforward.',
            'Keep one backup payment card separate from your phone.',
        ];

        return resultCard(
            'IC card readiness checklist',
            renderList(checklist),
            [
                { label: 'Wallet', value: selectedText(form, 'wallet') },
                { label: 'Route type', value: selectedText(form, 'cities') },
            ],
        );
    },

    'luggage-planner': (form) => {
        const bags = numberFromForm(form, 'bags', 0);
        const hotels = numberFromForm(form, 'hotels', 1);
        const routeType = String(new FormData(form).get('routeType') || 'rail');
        const comfort = String(new FormData(form).get('comfort') || 'light');
        const plan = [];

        if (bags >= 2 || comfort !== 'carry') {
            plan.push('Use luggage forwarding before long rail transfer days or multi-station connections.');
        }
        if (hotels >= 4) {
            plan.push('Split the route into bases and avoid moving every night where possible.');
        }
        if (routeType === 'island') {
            plan.push('Confirm ferry, bus, and rental car luggage limits before island movement.');
        } else if (routeType === 'urban') {
            plan.push('Use station lockers only for short daytime gaps; choose hotel luggage storage for same-city moves.');
        } else {
            plan.push('Reserve oversized luggage space on eligible trains when your bag dimensions require it.');
        }
        plan.push('Carry one overnight kit in a small backpack in case forwarding arrives the next day.');

        return resultCard(
            'Luggage movement plan',
            renderList(plan),
            [
                { label: 'Suitcases', value: bags },
                { label: 'Hotel changes', value: hotels },
            ],
        );
    },

    'allergy-card': (form) => {
        const data = new FormData(form);
        const allergy = String(data.get('allergy') || 'peanut');
        const severity = String(data.get('severity') || 'avoid');
        const terms = {
            peanut: { en: 'peanuts', ja: 'ピーナッツ・落花生' },
            shellfish: { en: 'shellfish', ja: '甲殻類・貝類' },
            dairy: { en: 'dairy products', ja: '乳製品' },
            egg: { en: 'egg', ja: '卵' },
            gluten: { en: 'wheat or gluten', ja: '小麦・グルテン' },
            vegetarian: { en: 'meat or fish', ja: '肉・魚' },
        };
        const item = terms[allergy] || terms.peanut;
        const severe = severity === 'severe';
        const english = severe
            ? `I have a severe allergy to ${item.en}. Could you please confirm that this dish does not contain it and has not been cross-contaminated?`
            : `I cannot eat ${item.en}. Could you please confirm whether this dish contains it?`;
        const japanese = severe
            ? `私は${item.ja}に重いアレルギーがあります。この料理に含まれていないか、混入の可能性がないか確認していただけますか。`
            : `私は${item.ja}を食べられません。この料理に含まれているか確認していただけますか。`;
        const contextNote = {
            restaurant: 'Show this before ordering and ask staff to check the kitchen.',
            convenience: 'Use it when asking staff to help read an ingredient label.',
            ryokan: 'Send it before arrival and show it again at dinner.',
        }[String(data.get('context') || 'restaurant')];

        return resultCard(
            'Food phrase card',
            `
                <div class="tool-result-card"><h3>English</h3><p>${english}</p></div>
                <div class="tool-result-card"><h3>Japanese</h3><p>${japanese}</p></div>
                <p>${contextNote}</p>
            `,
            [
                { label: 'Item', value: item.en },
                { label: 'Severity', value: selectedText(form, 'severity') },
            ],
        );
    },

    'tax-free-calculator': (form) => {
        const purchase = Math.max(0, numberFromForm(form, 'purchase', 0));
        const taxRate = Math.max(0, numberFromForm(form, 'taxRate', 10));
        const feeRate = Math.max(0, numberFromForm(form, 'feeRate', 0));
        const threshold = Math.max(0, numberFromForm(form, 'threshold', 5000));
        const eligible = purchase >= threshold;
        const taxPortion = purchase * (taxRate / (100 + taxRate));
        const fee = taxPortion * (feeRate / 100);
        const savings = eligible ? Math.max(0, taxPortion - fee) : 0;
        const afterSavings = purchase - savings;

        return resultCard(
            eligible ? 'Tax-free savings estimate' : 'Below the threshold',
            `<p>${eligible ? 'This purchase appears to meet the basic amount threshold.' : 'Increase the eligible purchase amount or combine qualifying items if store rules allow it.'} Keep passports, receipts, and store packaging rules in mind.</p>`,
            [
                { label: 'Estimated tax portion', value: formatJpy(taxPortion) },
                { label: 'Store fee estimate', value: formatJpy(fee) },
                { label: 'Net savings', value: formatJpy(savings) },
                { label: 'After savings', value: formatJpy(afterSavings) },
            ],
        );
    },
};

const initializeTravelTools = () => {
    document.querySelectorAll('[data-travel-tool]').forEach((toolElement) => {
        const slug = toolElement.dataset.travelTool;
        const form = toolElement.querySelector('[data-tool-form]');
        const result = toolElement.querySelector('[data-tool-result]');
        const handler = travelToolHandlers[slug];

        if (! form || ! result || typeof handler !== 'function') {
            return;
        }

        const render = (shouldTrack = false) => {
            result.innerHTML = handler(form);
            if (shouldTrack) {
                trackTravelToolUse(slug, result.querySelector('h2')?.textContent || 'rendered');
            }
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            render(true);
        });

        render();
    });
};

const collapseUnfilledAds = () => {
    const markEmptySlots = () => {
        document.querySelectorAll('.ad-slot').forEach((slot) => {
            const adElement = slot.querySelector('.adsbygoogle');

            if (! adElement) {
                return;
            }

            if (adElement.dataset.adStatus === 'filled') {
                slot.removeAttribute('data-ad-empty');

                return;
            }

            if (adElement.dataset.adStatus === 'unfilled' || ! slot.querySelector('iframe')) {
                slot.setAttribute('data-ad-empty', 'true');
            }
        });
    };

    window.setTimeout(markEmptySlots, 1200);
    window.setTimeout(markEmptySlots, 3200);

    const observer = new MutationObserver(markEmptySlots);
    document.querySelectorAll('.ad-slot .adsbygoogle').forEach((adElement) => {
        observer.observe(adElement, {
            attributes: true,
            attributeFilter: ['data-ad-status'],
        });
    });
};

trackSearchInteractions();
initStaticSearchPage();
trackOutboundClicks();
trackScrollDepth();
trackEngagementCheckpoints();
initializeTravelTools();
collapseUnfilledAds();

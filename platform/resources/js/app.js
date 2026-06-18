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

trackSearchInteractions();
trackOutboundClicks();
trackScrollDepth();
trackEngagementCheckpoints();

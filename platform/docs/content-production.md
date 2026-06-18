# Japan Travel Content Production

## Goal

Produce a large English Japan travel portal from reliable source material without copying copyrighted text or using unlicensed images.

## Editorial Rules

- Use official tourism, transport, prefecture, city, or operator pages as factual references.
- Do not copy official website text into articles.
- Rewrite every page as original professional English.
- Store the primary source in `source_name` and `source_url`.
- Add a visible "Sources and image licensing" section to every imported article.
- Images must be public domain, CC0, or another license that allows commercial reuse.
- Store image attribution, license, and source URL in each manifest entry.
- Avoid mass-publishing thin pages. Draft pages first when source coverage is incomplete.

## Import Command

```bash
php artisan content:import-travel database/content/japan-official-curated.json --publish
```

Options:

- `--publish`: publish imported articles immediately.
- `--dry-run`: validate and print entries without writing.
- `--limit=50`: import only the first 50 entries.

## Scaling to 3000 Pages

Use batches instead of one giant import:

1. Region hub pages: 47 prefectures + major city pages.
2. Category pages by region: transport, food, lodging, shopping, itinerary, basics.
3. Seasonal pages: spring, summer, autumn, winter, festivals, weather notes.
4. Practical tool pages: rail passes, IC cards, airport transfers, luggage forwarding, tax-free shopping.
5. Official-source city pages: Tokyo wards, Kyoto areas, Osaka districts, Hokkaido cities, Okinawa islands.

Recommended publishing cadence:

- Import 100-200 pages as draft.
- Review source quality, images, titles, and SEO descriptions.
- Publish only pages with enough original value.
- Repeat until the 3000-page target is reached.

## Current Seed Manifest

`database/content/japan-official-curated.json` contains the first curated sample batch using official sources such as GO TOKYO, Kyoto City Official Guide, OSAKA-INFO, HOKKAIDO LOVE!, VISIT OKINAWA JAPAN, JNTO, and the official JAPAN RAIL PASS site.

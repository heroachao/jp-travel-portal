# LetsGoJP Parity Redesign Design

## Context

The current Laravel platform already provides a publishing foundation: articles, destinations, topics, tags, media assets, ad placements, SEO metadata, redirects, audit logs, Chinese admin screens, and an English public site.

The next product objective is to redesign the site by studying the public feature model of `letsgojp.com`, then build an original English Japan travel platform with equivalent functional coverage and a Chinese admin that can operate every front-end surface.

This project must not copy the LetsGoJP brand, text, images, trade dress, or proprietary content. It should reproduce the platform capabilities in an original product for English-speaking Japan travelers.

## Reference Feature Model

Publicly visible LetsGoJP surfaces include:

- A travel media home page with service links, region navigation, category navigation, trending articles, and social/service links.
- Regional channels such as national, Hokkaido, Tohoku, Tokyo, Hokuriku, Chubu, Kansai, Chugoku, Shikoku, Kyushu, and Okinawa.
- Editorial categories such as guide, things to do, food, shopping, lodging, itinerary, transport, and basic information.
- Deep location taxonomies for prefectures, cities, stations, neighborhoods, and travel zones.
- Search pages with filters for region/station, category, keyword, and coupon availability.
- Coupon pages with region/category/sort filters.
- Article detail pages with long-form body content, images, source attribution, author block, sharing/favorite affordances, related links, and FAQ.
- Member surfaces for login, profile/home, settings, favorites, and booking confirmation entry points.
- Service and commerce links for activities, hotels, flights, rail tickets, shopping, community, exchange rate, and advertising inquiries.
- Activity booking surfaces with filters for region, area, activity type, date, budget, duration, keyword, popular categories, popular cities, and popular products.

## Product Direction

Build an English public site and Chinese admin for a comprehensive Japan travel media and tools platform.

The public product should feel like a useful travel operating system rather than a generic blog. The admin should expose every front-end object as structured, searchable, auditable data.

## Functional Scope

### Public Frontend

The redesigned frontend must support:

- Global service bar: guide, activities, hotels, flights, rail, shop, community, exchange rate, and advertising contact.
- Primary region navigation with national and regional channels.
- Primary category navigation with guide, things to do, food, shopping, lodging, itinerary, transport, and basics.
- Home page modules:
  - hero search
  - region grid
  - category grid
  - featured articles
  - latest articles
  - popular articles
  - coupon/service highlights
  - travel tools
- Region channel pages:
  - region introduction
  - child locations
  - featured articles
  - latest articles
  - related categories
  - coupons/services for that region
  - SEO metadata and canonical URL
- Category channel pages:
  - category introduction
  - child categories
  - featured articles
  - latest articles
  - related regions
  - SEO metadata and canonical URL
- Enhanced article pages:
  - title, excerpt, published/updated date
  - author block
  - cover image and optional source attribution
  - body content
  - FAQ blocks
  - related articles
  - related services
  - related coupons
  - favorite/share placeholders
  - structured data
- Search page:
  - keyword search
  - region filter
  - category filter
  - tag filter
  - coupon availability filter
  - sort by newest, updated, popular, or recommended
- Coupon listing and coupon detail pages.
- Activity/service listing pages with filters for region, service type, budget, duration, and keyword.
- Member pages for login, profile, settings, favorites, and booking/service history placeholders.
- Static company/legal pages: about, contact, terms, privacy, advertising.

### Chinese Admin

The admin must manage every public feature:

- 工作台:
  - pending reviews
  - scheduled articles
  - SEO warnings
  - missing FAQ/media/meta counts
  - recent audit logs
- 文章管理:
  - editorial workflow
  - region/category/tag assignment
  - FAQ blocks
  - related articles
  - related services and coupons
  - source attribution
  - SEO fields
- 地区频道管理:
  - hierarchy
  - channel slug
  - English display name
  - type: country, region, prefecture, city, station, neighborhood, airport, attraction zone
  - intro body
  - SEO metadata
  - sort order
  - visibility
- 分类频道管理:
  - hierarchy
  - English display name
  - slug
  - intro body
  - SEO metadata
  - sort order
  - visibility
- 首页模块管理:
  - module type
  - title
  - placement key
  - linked articles/regions/categories/coupons/services
  - sort order
  - enabled state
- 优惠券管理:
  - merchant
  - coupon title
  - region/category association
  - description
  - coupon code or external URL
  - validity window
  - enabled state
  - SEO metadata
- 商家/合作伙伴管理:
  - name
  - type
  - website
  - region/category associations
  - notes
  - enabled state
- 服务入口管理:
  - activity, hotel, flight, rail, shop, community, exchange rate, advertising, custom
  - label
  - external URL
  - placement
  - tracking key
  - enabled state
- 活动产品管理:
  - product title
  - region/area
  - activity type
  - budget range
  - duration
  - external booking URL
  - featured/popular flags
- 会员管理:
  - member profile
  - favorites
  - service click/bookmark history
  - status and admin notes
- SEO 管理:
  - sitemap settings
  - redirect rules
  - indexable flags
  - batch meta editing
  - missing metadata reports
- 广告管理:
  - placement code
  - page type
  - position
  - enabled state
  - notes
- 审计日志:
  - admin action history
  - actor
  - route
  - subject summary

## Data Model Changes

Add or extend these concepts:

- `travel_categories`: hierarchical editorial categories.
- `navigation_items` or `service_links`: service bar and footer links.
- `homepage_modules` and module items for curated front-page sections.
- `article_faqs`.
- `article_related_links` or structured relationships for related content and services.
- `coupons`.
- `partners`.
- `activity_products`.
- `member_favorites`.
- `service_click_events` for lightweight tracking.
- Additional fields on `destinations` for channel visibility, display order, and location type.
- Additional fields on `articles` for updated display date, source attribution, reading time, popularity score, and coupon availability flag.

Existing `Topic` may remain for editorial themes, while `travel_categories` should represent the LetsGoJP-like navigation taxonomy.

## Routing

Preferred public routes:

- `/`
- `/regions`
- `/regions/{region:slug}`
- `/categories/{category:slug}`
- `/articles`
- `/articles/{article:slug}`
- `/search`
- `/coupons`
- `/coupons/{coupon:slug}`
- `/services`
- `/services/{serviceType}`
- `/activities`
- `/member`
- `/member/favorites`
- `/about`
- `/contact`
- `/terms`
- `/privacy`
- `/advertising`
- `/sitemap.xml`
- `/robots.txt`

The app should not require separate subdomains at first. Regions should be path-based for simpler deployment, while the data model should not prevent future regional subdomains.

## SEO Requirements

- Every indexable channel, article, coupon, and service landing page must have title, meta description, canonical URL, and indexable flag.
- Sitemaps should include articles, regions, categories, coupons, and service landing pages.
- The sitemap layer should be ready to split by content type once URLs exceed a practical threshold.
- Article pages should support Article structured data.
- FAQ blocks should support FAQ structured data when present.
- Region/category pages should support CollectionPage or Breadcrumb structured data.
- Redirect rules must continue to work for migrated slugs and old paths.

## Security Requirements

- Admin remains Chinese and protected by authentication, authorization, and audit logging.
- User generated or admin-entered HTML must continue to pass through the purifier policy.
- Coupon/service/partner external URLs must be validated and rendered with safe attributes.
- File uploads remain limited to safe image MIME types and size limits.
- Member features should avoid storing sensitive travel booking details in the first implementation; store external click/bookmark history only.

## Implementation Phases

### Phase 1: Media Portal Foundation

Build:

- Travel category model, migration, factory, and admin CRUD.
- Region channel enhancements on existing destinations.
- Homepage module model and admin CRUD.
- Service link model and admin CRUD.
- Article FAQ model and admin editing.
- Public header/footer redesign.
- Home page redesign.
- Region and category channel pages.
- Enhanced search filters.
- Demo seed data for regions, categories, modules, FAQs, and service links.
- Tests for public pages, admin CRUD, SEO output, and seed integrity.

Phase 1 is accepted when a local seeded site shows a LetsGoJP-like media portal structure: service bar, region navigation, category navigation, home modules, region pages, category pages, article FAQ, and search filters, all managed from the Chinese admin.

### Phase 2: Coupons and Partners

Build:

- Partner model and admin CRUD.
- Coupon model and admin CRUD.
- Coupon listing/detail pages.
- Coupon filters by region/category.
- Article coupon associations.
- Tests for coupon routing, filtering, and admin permissions.

### Phase 3: Member Favorites

Build:

- Public member profile shell.
- Favorite article/coupon support.
- Member settings page.
- Admin member list.
- Tests for favorite authorization and visibility.

### Phase 4: Activity and Service Marketplace

Build:

- Activity product model and admin CRUD.
- Activity listing/detail or external-link pages.
- Filters for region, activity type, budget, duration, and keyword.
- Featured/popular activity modules.
- Service click tracking.
- Tests for filtering and tracking.

### Phase 5: SEO Scale and Operational Reporting

Build:

- Sitemap splitting by content type.
- SEO issue dashboard.
- Batch metadata editing.
- Internal link recommendation rules.
- Redirect import/export.
- Reporting for popular content, missing metadata, missing FAQ, and stale articles.

## Acceptance Criteria

The goal is complete only when:

- Public frontend exposes the same major functional surfaces as the reference site without copying protected content or branding.
- Chinese admin can create, edit, order, publish, hide, and audit every public object.
- Region/category/search/coupon/service/member/activity surfaces are backed by structured database models.
- SEO metadata and sitemap coverage exist for every indexable public object.
- Tests cover the major admin and public workflows.
- A seeded demo site demonstrates all major surfaces locally.
- The implementation has been verified with automated tests, asset build, database migration/seed, and HTTP smoke checks.

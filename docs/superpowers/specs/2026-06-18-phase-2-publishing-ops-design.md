# Phase 2 Publishing Operations Design

## Goal

Build the second-phase operating layer for the Japan travel publishing system: Chinese admin media management, article image/SEO controls, site-level analytics and ad configuration, and safe public rendering for English pages.

## Confirmed Direction

The chosen direction is **Publish + SEO + Ads**. This phase should make the site practical for daily publishing and monetization preparation without connecting real Google account data during local development.

Real Google account login, private credentials, cookies, revenue dashboards, keyword rank monitoring, and a full WYSIWYG editor are out of scope for this phase.

## Product Scope

### Media Library

Editors need a Chinese admin screen for uploading and managing images. The existing `media_assets` table and `App\Services\Media\SafeImageUpload` service should be reused.

The media library supports:

- JPG, PNG, and WebP uploads only.
- Existing 4 MB upload limit.
- Alt text and source note fields.
- Width, height, MIME type, size, uploader, and path display.
- Search or filtering by filename/path, MIME type, and uploader when cheap to implement.
- No image deletion while the asset is referenced by an article, topic, or destination cover/OG field.

### Article Publishing Enhancements

The article form remains a Chinese admin surface while article content stays English. The form should become a practical publishing workspace rather than a plain database form.

Article enhancements include:

- Select an existing media asset as `cover_media_id`.
- Select an existing media asset as `og_media_id`.
- Show a compact preview of the selected images.
- Show an SEO preview based on title, SEO title, meta description, canonical URL, indexable state, and OG image.
- Keep existing FAQ, category, source, coupon, popularity, and review workflow behavior.
- Continue sanitizing article body and FAQ answers with the existing purifier flow.

### Site Settings

Add a small site settings feature for values that affect public layout, analytics, and ad bootstrapping.

Settings include:

- Site name.
- Default SEO title suffix.
- Default meta description.
- GA4 Measurement ID.
- AdSense Publisher ID.
- Enable analytics toggle.
- Enable ads toggle.

Local development and tests must not inject live Google scripts unless the toggles are enabled. Empty IDs must never render script tags.

### Advertising Controls

The existing `AdPlacement` model and admin screen remain the base. This phase should make the ad setup safer and easier to understand.

Ad controls include:

- Keep custom ad code per placement.
- Keep enable/disable per placement.
- Add clearer Chinese helper text around AdSense code safety.
- Prefer disabled-by-default behavior for new ad placements.
- Public pages render ad placement code only when both the placement and site-level ads toggle are enabled.

### Public Rendering

The English public frontend should consume the new configuration without exposing admin implementation details.

Public rendering includes:

- Use site settings for the brand/title fallback where appropriate.
- Render GA4 only when analytics are enabled and a measurement ID exists.
- Render the AdSense bootstrap only when ads are enabled and a publisher ID exists.
- Continue rendering placement-specific ad code through the existing ad renderer, gated by site-level ads enablement.
- Article OG image should prefer `og_media_id`, then `cover_media_id`, then no image.

## Architecture

### Data Model

Use the existing `media_assets` table for uploads. Add or reuse relationships on `Article`, `Topic`, and `Destination` for media references. Introduce a minimal `site_settings` storage model or equivalent single-row settings table so admin-entered values are database backed and deployment friendly.

### Admin Components

Follow the current Livewire admin pattern:

- `App\Livewire\Admin\Media\MediaAssetIndex` for media upload/list management.
- `App\Livewire\Admin\Settings\SiteSettingsForm` for site-level settings.
- Extend `App\Livewire\Admin\Articles\ArticleForm` for media selection and SEO preview.
- Harden `App\Livewire\Admin\Ads\AdPlacementIndex` with site-level awareness and clearer validation messages.

### Services

Keep business rules in small services:

- Continue using `SafeImageUpload` for validation and storage.
- Add a site settings accessor service if direct model reads would spread setting lookup across Blade templates and controllers.
- Extend `AdRenderer` so public ad rendering respects both placement state and site-level ads state.

### Public Views

Keep public pages English. Update the shared public layout for analytics/ad bootstrap and article views for image metadata. Avoid adding visible instructional text to the frontend.

## Security And Safety

- Do not store Google passwords, cookies, private account data, API secrets, or personal data.
- Validate GA4 IDs and AdSense publisher IDs with strict public-ID formats.
- Do not inject analytics or ad scripts when IDs are blank or toggles are disabled.
- Keep ad code controlled by authenticated admins only.
- Preserve existing HTML sanitization for article body and FAQ content.
- Prevent deleting media assets that are still referenced.

## Testing Strategy

Add focused tests for:

- Safe image upload accepts allowed MIME types and rejects unsupported types or oversized files.
- Media assets cannot be deleted while referenced.
- Article form persists cover and OG media choices.
- SEO preview data is derived from article fields and selected media.
- Site settings save valid analytics/ad IDs and reject invalid formats.
- Public layout injects GA4/AdSense only when toggles and IDs are present.
- `AdRenderer` returns empty output when site-level ads are disabled.

Run the existing full suite after implementation:

```bash
cd platform
/Users/heroachao/.config/herd-lite/bin/php artisan test
/Users/heroachao/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/bin/node node_modules/vite/bin/vite.js build
/Users/heroachao/.config/herd-lite/bin/php artisan migrate:fresh --seed
```

## Acceptance Criteria

- A Chinese admin user can upload an image and see it in the media library.
- A Chinese admin user can select cover and OG images for an English article.
- Article public pages output correct title, description, canonical, and OG image metadata.
- Site analytics and ads are controlled by database settings and disabled by default.
- No Google script renders with blank IDs.
- Existing Phase 1 public pages and sitemap behavior continue to pass tests.

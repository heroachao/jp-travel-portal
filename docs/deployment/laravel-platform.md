# Laravel Platform Deployment Notes

## Requirements

- PHP 8.5 locally, PHP 8.3+ minimum for Laravel 13.
- Composer 2.x.
- Node 24.x and npm 11.x for asset builds.
- PostgreSQL recommended for production. SQLite is used for automated tests.

## Production Checklist

1. Create a production `.env` from `platform/.env.example`.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
3. Configure PostgreSQL credentials in `DB_*` variables.
4. Run `composer install --no-dev --optimize-autoloader`.
5. Run `npm ci && npm run build`.
6. Run `php artisan key:generate` once if `APP_KEY` is empty.
7. Run `php artisan migrate --force`.
8. Run `php artisan storage:link`.
9. Change or disable the seeded local admin password before exposing `/admin`.
10. Paste Google AdSense code only through the Chinese ad placement admin.

Do not commit `.env`, credentials, cookies, private user data, or real ad account secrets.

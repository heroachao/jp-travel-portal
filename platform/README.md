# Japan Travel Publishing Platform

English public Japan travel guide with a Chinese Laravel admin for editorial publishing, SEO operations, and ad placement management.

## Local Setup

For PostgreSQL or MySQL, update the `DB_*` values in `.env` before running migrations.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

For a quick SQLite demo:

```bash
touch database/database.sqlite
```

Then set these values in `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/platform/database/database.sqlite
```

After that:

```bash
php artisan migrate:fresh --seed
php artisan serve
```

Admin URL: `/admin`

Default local admin:

- Email: `admin@example.com`
- Password: `ChangeMe123!`

Change the default password before any public deployment.

## Verification

```bash
php artisan test
npm run build
php artisan route:list
```

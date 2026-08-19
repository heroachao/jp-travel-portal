#!/bin/sh
set -eu

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache public

php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan view:clear --no-interaction >/dev/null 2>&1 || true

php artisan storage:link --no-interaction >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

php artisan config:cache --no-interaction
php artisan view:cache --no-interaction

exec "$@"

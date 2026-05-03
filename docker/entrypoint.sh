#!/bin/sh
set -eu

echo "Starting Porky on Render..."

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY was not set, generated a temporary key for this container."
fi

export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-/tmp/database.sqlite}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export LOG_STACK="${LOG_STACK:-stderr}"

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache "$(dirname "$DB_DATABASE")"
chmod -R 775 storage bootstrap/cache
touch "$DB_DATABASE"

echo "Clearing old Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Running database migrations..."
php artisan migrate --force

echo "Caching Laravel config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Launching Laravel on port ${PORT:-10000}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"

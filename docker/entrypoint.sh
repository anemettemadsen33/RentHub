#!/bin/sh
set -e

# Wait for database to be ready
until php artisan migrate:status 2>/dev/null; do
    echo "Waiting for database connection..."
    sleep 2
done

# Run migrations
php artisan migrate --force --no-interaction

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
exec "$@"

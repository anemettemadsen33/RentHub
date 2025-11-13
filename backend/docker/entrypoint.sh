#!/bin/sh
set -e

echo "🚀 Starting RentHub Backend..."

# Wait for database
echo "⏳ Waiting for database..."
until php artisan db:show 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "✅ Database is ready!"

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force

# Cache config
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start PHP-FPM
echo "✅ Starting PHP-FPM..."
exec php-fpm

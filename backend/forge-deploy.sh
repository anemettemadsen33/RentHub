#!/bin/bash
set -e

echo "================================"
echo "Starting deployment..."
echo "================================"

# Navigate to site directory
cd /home/forge/api.renthub.com

# Enter maintenance mode
echo "Entering maintenance mode..."
php artisan down --render="errors::503" --retry=60 || true

# Pull latest changes
echo "Pulling latest changes from git..."
git pull origin main

# Install/update composer dependencies
echo "Installing/updating Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear all caches
echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Create storage link if it doesn't exist
echo "Setting up storage link..."
php artisan storage:link || true

# Cache configuration and routes for performance
echo "Caching configuration and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Install NPM dependencies and build assets
echo "Building frontend assets..."
npm ci
npm run build

# Set correct permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# Restart queue workers
echo "Restarting queue workers..."
php artisan queue:restart

# Restart PHP-FPM if needed (uncomment if using PHP-FPM)
# sudo service php8.2-fpm reload

# Exit maintenance mode
echo "Exiting maintenance mode..."
php artisan up

# Run a health check
echo "Running health check..."
php artisan optimize

echo "================================"
echo "Deployment completed successfully!"
echo "================================"

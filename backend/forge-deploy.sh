#!/bin/bash
set -e

echo "Starting deployment..."

# Navigate to site directory
cd /home/forge/api.renthub.com

# Enter maintenance mode
php artisan down --render="errors::503" --retry=60 || true

# Pull latest changes
git pull origin main

# Install/update composer dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Install NPM dependencies and build assets
npm ci
npm run build

# Restart queue workers
php artisan queue:restart

# Exit maintenance mode
php artisan up

echo "Deployment completed successfully!"

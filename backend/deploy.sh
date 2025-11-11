#!/bin/bash

# ===================================
# Laravel Forge Deployment Script
# ===================================

set -e

echo "🚀 Starting deployment..."

cd /home/forge/yourdomain.com

# Maintenance mode
php artisan down || true

# Pull latest changes
git pull origin $FORGE_SITE_BRANCH

# Install/update composer dependencies (optimized for production)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force

# Restart queue workers
php artisan queue:restart

# Generate sitemap
php artisan sitemap:generate || true

# Clear expired password reset tokens
php artisan auth:clear-resets

# Optimize images
php artisan storage:link

# Restart services
sudo supervisorctl restart all

# Exit maintenance mode
php artisan up

echo "✅ Deployment completed successfully!"

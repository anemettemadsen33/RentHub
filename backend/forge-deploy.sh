#!/bin/bash
# Laravel Forge Auto-Deployment Script
# Paste this in: Forge → Your Site → App → Deployment Script

set -e

echo "🚀 Starting Laravel Forge deployment..."

# Navigate to project directory
cd /home/forge/rental-platform.private.on-forge.com

# Enter maintenance mode
php artisan down || true

# Pull latest changes from Git
git pull origin main

# Install/Update dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link (if not exists)
php artisan storage:link || true

# Restart queue workers
php artisan queue:restart

# Exit maintenance mode
php artisan up

echo "✅ Deployment completed successfully!"

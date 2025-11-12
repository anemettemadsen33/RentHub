#!/bin/bash
# Forge Deployment Script - FINAL WORKING VERSION
# This script should be pasted in Forge → Deployment Script

cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

# Navigate to backend directory
cd backend

# Install Composer dependencies (production only)
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Clear all caches
php artisan optimize:clear

# Cache configuration for production
php artisan config:cache

# Fix permissions
chmod -R 775 storage bootstrap/cache

echo "✅ Backend deployment complete!"

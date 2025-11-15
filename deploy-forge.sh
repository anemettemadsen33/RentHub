#!/bin/bash

# RentHub Backend Deployment Script for Laravel Forge
# This script should be run on the Forge server via SSH

echo "🚀 Starting RentHub Backend Deployment on Forge..."
echo ""

# Navigate to project directory
cd /home/forge/renthub-tbj7yxj7.on-forge.com || exit 1

echo "📥 Step 1: Pulling latest code from GitHub..."
git pull origin master

echo ""
echo "📦 Step 2: Installing/Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ""
echo "🧹 Step 3: Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo ""
echo "⚙️  Step 4: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "🗄️  Step 5: Running database migrations..."
php artisan migrate --force

echo ""
echo "🔐 Step 6: Fixing permissions..."
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

echo ""
echo "♻️  Step 7: Restarting services..."
# Restart PHP-FPM (adjust version if needed)
sudo service php8.3-fpm reload || sudo service php8.2-fpm reload || sudo service php8.1-fpm reload

# Restart queue workers if using queues
php artisan queue:restart 2>/dev/null || true

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "🧪 Testing key endpoints..."
curl -s -o /dev/null -w "  /api/v1/health: %{http_code}\n" https://renthub-tbj7yxj7.on-forge.com/api/v1/health
curl -s -o /dev/null -w "  /api/v1/properties: %{http_code}\n" https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

echo ""
echo "🎉 Deployment finished! Check the URLs above."

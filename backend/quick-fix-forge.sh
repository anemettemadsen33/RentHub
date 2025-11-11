#!/bin/bash
# Quick Fix Script for Forge Deployment Issues

echo "🔧 Forge Quick Fix Script"
echo "=========================="
echo ""

# Navigate to backend
cd /home/forge/YOUR_SITE_PATH/backend

echo "1️⃣ Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "2️⃣ Regenerating autoloader..."
composer dump-autoload

echo "3️⃣ Fixing permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R forge:forge storage
chown -R forge:forge bootstrap/cache

echo "4️⃣ Generating APP_KEY if missing..."
php artisan key:generate --force

echo "5️⃣ Running migrations..."
php artisan migrate --force

echo "6️⃣ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan optimize

echo "7️⃣ Restarting services..."
sudo service php8.3-fpm reload

echo "✅ Quick fix complete!"
echo "🧪 Test: curl https://YOUR_DOMAIN/api/health"

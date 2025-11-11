#!/bin/bash
# Fix Filament Component Error in Forge

echo "🔧 Fixing Filament Component Error"
echo "==================================="

cd /home/forge/renthub-mnnzqvzb.on-forge.com/backend

echo "1️⃣ Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "2️⃣ Installing ALL dependencies (including dev)..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "3️⃣ Publishing Filament assets..."
php artisan filament:assets

echo "4️⃣ Fixing permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

echo "5️⃣ Optimizing (without route cache)..."
php artisan config:cache
php artisan view:cache

echo "6️⃣ Restarting PHP-FPM..."
sudo service php8.3-fpm reload

echo ""
echo "✅ Fix complete!"
echo "🧪 Testing routes..."
php artisan route:list | grep api/health

echo ""
echo "🧪 Testing health endpoint..."
curl http://localhost/api/health

echo ""
echo "If no errors above, your backend is ready! 🎉"

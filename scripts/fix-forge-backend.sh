#!/bin/bash
# Fix Laravel backend on Forge server
# Run this on the Forge server via SSH

set -e

echo "🔧 Fixing RentHub Backend on Forge..."

# Navigate to project
cd /home/forge/renthub-tbj7yxj7.on-forge.com/current/backend

# Fix all PHP namespace issues
echo "📝 Fixing namespace double backslashes..."
find . -name "*.php" -type f -not -path "./vendor/*" -exec sed -i 's/namespace App\\\\/namespace App\\/g' {} \;
find . -name "*.php" -type f -not -path "./vendor/*" -exec sed -i 's/use App\\\\/use App\\/g' {} \;

# Clear all caches
echo "🗑️ Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild optimized caches
echo "⚡ Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# Test API
echo ""
echo "🧪 Testing API..."
response=$(curl -s -o /dev/null -w "%{http_code}" http://localhost/api/v1/properties)
if [ "$response" = "200" ] || [ "$response" = "401" ]; then
  echo "✅ Backend API: OK (HTTP $response)"
else
  echo "❌ Backend API: FAILED (HTTP $response)"
  echo "📋 Checking logs..."
  tail -20 storage/logs/laravel.log
  exit 1
fi

echo ""
echo "✅ Backend fixed successfully!"
echo "🌐 Test at: https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"

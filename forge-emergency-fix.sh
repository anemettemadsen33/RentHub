#!/bin/bash

# ================================================
# RentHub - Emergency Fix Script for Forge
# ================================================
# Rulează acest script pe serverul Forge via SSH
# ================================================

set -e  # Exit on error

echo "🚀 Starting RentHub Emergency Fix..."
echo "================================================"

# Navigate to project directory
cd /home/forge/renthub-tbj7yxj7.on-forge.com || exit 1

echo "📁 Current directory: $(pwd)"
echo ""

# Step 1: Clear all caches
echo "🧹 Step 1: Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "✅ Caches cleared"
echo ""

# Step 2: Fix permissions
echo "🔐 Step 2: Fixing permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache
echo "✅ Permissions fixed"
echo ""

# Step 3: Database migrations and seeders
echo "💾 Step 3: Running migrations and seeders..."
read -p "⚠️  This will RESET the database! Continue? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]
then
    php artisan migrate:fresh --force
    php artisan db:seed --force --class=DatabaseSeeder
    echo "✅ Database reset and seeded"
else
    echo "⏭️  Skipping database reset"
fi
echo ""

# Step 4: Optimize application
echo "⚡ Step 4: Optimizing application..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimized"
echo ""

# Step 5: Test API endpoints
echo "🧪 Step 5: Testing API endpoints..."
echo ""

echo "Testing Health Check..."
curl -s https://renthub-tbj7yxj7.on-forge.com/api/health | jq .status || echo "❌ Health check failed"
echo ""

echo "Testing Properties Endpoint..."
PROPERTIES_RESPONSE=$(curl -s -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/v1/properties)
HTTP_CODE="${PROPERTIES_RESPONSE: -3}"
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Properties endpoint working (HTTP $HTTP_CODE)"
else
    echo "❌ Properties endpoint failed (HTTP $HTTP_CODE)"
fi
echo ""

# Step 6: Check logs for errors
echo "📋 Step 6: Checking recent logs..."
if [ -f storage/logs/laravel.log ]; then
    echo "Last 20 lines of Laravel log:"
    tail -n 20 storage/logs/laravel.log
else
    echo "⚠️  No Laravel log file found"
fi
echo ""

# Step 7: Display system info
echo "📊 Step 7: System Information..."
echo "PHP Version: $(php -v | head -n 1)"
echo "Laravel Version: $(php artisan --version)"
echo "Disk Usage: $(df -h . | tail -n 1)"
echo "Memory Usage: $(free -h | grep Mem)"
echo ""

# Final message
echo "================================================"
echo "✅ Emergency fix completed!"
echo "================================================"
echo ""
echo "📝 Next steps:"
echo "1. Check if properties endpoint works: curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
echo "2. Monitor logs: tail -f storage/logs/laravel.log"
echo "3. Restart PHP-FPM from Forge dashboard if needed"
echo "4. Test frontend: https://rent-n91e2fmia-madsens-projects.vercel.app/"
echo ""
echo "🔗 Useful commands:"
echo "  - View logs: tail -f storage/logs/laravel.log"
echo "  - Test health: curl https://renthub-tbj7yxj7.on-forge.com/api/health"
echo "  - Clear cache: php artisan cache:clear"
echo ""

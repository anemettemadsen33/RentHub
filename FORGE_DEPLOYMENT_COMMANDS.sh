#!/bin/bash

################################################################################
# RentHub - Forge Deployment Script
# 
# Acest script trebuie rulat pe serverul Laravel Forge după deploy
# pentru a aplica modificările și a curăța cache-ul
#
# Utilizare: bash FORGE_DEPLOYMENT_COMMANDS.sh
################################################################################

set -e  # Exit on error

echo "🚀 RentHub Deployment - Clearing Laravel Cache..."
echo ""

# Change to project directory
cd /home/forge/renthub-tbj7yxj7.on-forge.com || exit 1

# Clear all caches
echo "📦 Clearing route cache..."
php artisan route:clear

echo "📦 Caching routes..."
php artisan route:cache

echo "📦 Clearing config cache..."
php artisan config:clear

echo "📦 Caching config..."
php artisan config:cache

echo "📦 Clearing view cache..."
php artisan view:clear

echo "📦 Clearing application cache..."
php artisan cache:clear

echo "📦 Clearing compiled classes..."
php artisan clear-compiled

echo "📦 Optimizing autoloader..."
composer dump-autoload --optimize

echo ""
echo "✅ Deployment complete!"
echo ""
echo "🧪 Testing endpoints..."
echo ""

# Test health endpoint
echo "Testing /api/health..."
curl -s https://renthub-tbj7yxj7.on-forge.com/api/health | jq '.' || echo "Health check failed"

echo ""
echo "Testing /api/v1/properties..."
curl -s https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | jq '.data | length' || echo "Properties endpoint failed"

echo ""
echo "📋 Checking registered routes..."
php artisan route:list --path=api/v1/auth --columns=method,uri,name

echo ""
echo "🎉 All done! Your deployment is ready."
echo ""
echo "Next steps:"
echo "1. Configure Vercel environment variables (see DEPLOYMENT_FIX_GUIDE.md)"
echo "2. Redeploy frontend on Vercel"
echo "3. Test complete flow: register → login → dashboard"
echo ""

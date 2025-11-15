#!/bin/bash

# COMENZILE EXACTE PENTRU SSH DEPLOY PE FORGE
# Copy-paste în terminal după ce te conectezi la server

echo "🚀 RentHub Backend Deployment Started..."
echo ""

# Step 1: Pull latest code
echo "📥 Pulling latest code from GitHub..."
cd /home/forge/renthub-tbj7yxj7.on-forge.com
git pull origin master
echo "✅ Code pulled"
echo ""

# Step 2: Composer install
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo "✅ Dependencies installed"
echo ""

# Step 3: Clear ALL caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "✅ Caches cleared"
echo ""

# Step 4: Optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimized"
echo ""

# Step 5: Migrate database
echo "🗄️  Running migrations..."
php artisan migrate --force
echo "✅ Migrations complete"
echo ""

# Step 6: Fix permissions
echo "🔐 Fixing permissions..."
chmod -R 755 storage bootstrap/cache
echo "✅ Permissions fixed"
echo ""

# Step 7: Restart PHP-FPM
echo "♻️  Restarting PHP-FPM..."
sudo service php8.3-fpm reload 2>/dev/null || sudo service php8.2-fpm reload 2>/dev/null || sudo service php8.1-fpm reload
echo "✅ PHP-FPM restarted"
echo ""

# Step 8: Restart queue workers (if needed)
echo "🔄 Restarting queue workers..."
php artisan queue:restart 2>/dev/null || echo "No queue workers running"
echo ""

echo "✅ DEPLOYMENT COMPLETE!"
echo ""
echo "🧪 Testing endpoints..."
echo ""

# Test health endpoint
HEALTH_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/v1/health)
echo "  /api/v1/health: HTTP $HEALTH_STATUS"

# Test properties endpoint
PROPS_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/v1/properties)
echo "  /api/v1/properties: HTTP $PROPS_STATUS"

# Test root (should NOT be Laravel welcome)
ROOT_STATUS=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/)
echo "  / (root): HTTP $ROOT_STATUS"

echo ""
echo "🎉 Done! Check the results above."
echo ""
echo "📝 Next steps:"
echo "  1. Seed database: php artisan db:seed --class=PropertySeeder"
echo "  2. Test OAuth: curl https://renthub-tbj7yxj7.on-forge.com/api/v1/auth/social/google/redirect"
echo "  3. Test frontend: https://rent-hub-git-master-madsens-projects.vercel.app/properties"

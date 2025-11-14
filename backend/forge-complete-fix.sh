#!/bin/bash

# 🚀 FORGE DEPLOYMENT SCRIPT - AUTOMATED FIX
# Run this on Forge server to fix all backend issues

set -e

echo "🔧 Starting RentHub Backend Fix..."

cd /home/forge/renthub-tbj7yxj7.on-forge.com

# 1. Update .env with correct settings
echo "📝 Updating .env configuration..."
cat > .env << 'EOL'
APP_NAME=RentHub
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_TIMEZONE=UTC
APP_URL=https://renthub-tbj7yxj7.on-forge.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database - SQLite for quick start
DB_CONNECTION=sqlite

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_STORE=file

# Queue
QUEUE_CONNECTION=sync

# Mail
MAIL_MAILER=log

# CORS Settings
SANCTUM_STATEFUL_DOMAINS=rent-hub-beta.vercel.app,rent-hub-git-master-madsens-projects.vercel.app,localhost:3000
SESSION_DOMAIN=.on-forge.com

# Frontend URL
FRONTEND_URL=https://rent-hub-beta.vercel.app
EOL

# 2. Generate APP_KEY if not exists
echo "🔑 Generating application key..."
php artisan key:generate --force

# 3. Create SQLite database
echo "💾 Setting up SQLite database..."
touch database/database.sqlite
chmod 664 database/database.sqlite

# 4. Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate:fresh --force --seed

# 5. Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Optimize for production
echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set correct permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# 8. Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# 9. Create admin user
echo "👤 Creating admin user..."
php artisan db:seed --class=AdminUserSeeder --force

echo ""
echo "✅ Backend fix completed!"
echo ""
echo "📊 Test endpoints:"
echo "   Health: https://renthub-tbj7yxj7.on-forge.com/api/health"
echo "   Properties: https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
echo ""
echo "🎉 Backend should be working now!"

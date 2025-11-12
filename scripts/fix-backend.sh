#!/bin/bash
# RentHub Backend Quick Fix Script

echo "🚀 RentHub Backend Fix - Starting..."

# Step 1: Find project root
echo "📁 Finding project root..."
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Check if we're in the right place
if [ -f "artisan" ]; then
    echo "✅ Found artisan in current directory"
    PROJECT_ROOT=$(pwd)
elif [ -f "current/artisan" ]; then
    echo "✅ Found artisan in current/"
    cd current
    PROJECT_ROOT=$(pwd)
elif [ -f "current/backend/artisan" ]; then
    echo "✅ Found artisan in current/backend/"
    cd current/backend
    PROJECT_ROOT=$(pwd)
else
    echo "❌ Cannot find artisan! Searching..."
    find /home/forge/renthub-tbj7yxj7.on-forge.com -name "artisan" -type f 2>/dev/null | head -1
    exit 1
fi

echo "📍 Project root: $PROJECT_ROOT"

# Step 2: Check .env
echo ""
echo "📋 Checking .env file..."
if [ -f ".env" ]; then
    echo "✅ .env exists"
    if grep -q "APP_KEY=base64:" .env; then
        echo "✅ APP_KEY is set"
    else
        echo "⚠️  Generating APP_KEY..."
        php artisan key:generate --force
    fi
else
    echo "❌ .env missing! Copying from .env.example..."
    cp .env.example .env
    php artisan key:generate --force
fi

# Step 3: Fix permissions
echo ""
echo "🔒 Fixing permissions..."
chmod -R 775 storage bootstrap/cache
chown -R forge:www-data storage bootstrap/cache 2>/dev/null || chown -R forge:forge storage bootstrap/cache
echo "✅ Permissions fixed"

# Step 4: Clear all caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"

# Step 5: Setup database
echo ""
echo "💾 Setting up database..."
DB_FILE="database/database.sqlite"
if [ ! -f "$DB_FILE" ]; then
    echo "Creating SQLite database..."
    touch $DB_FILE
    chmod 664 $DB_FILE
    echo "✅ SQLite database created"
fi

# Step 6: Run migrations
echo ""
echo "🗄️  Running migrations..."
php artisan migrate:status
if [ $? -eq 0 ]; then
    php artisan migrate --force
    echo "✅ Migrations complete"
else
    echo "⚠️  Migration check failed"
fi

# Step 7: Cache config
echo ""
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
echo "✅ Config cached"

# Step 8: Test API
echo ""
echo "🧪 Testing API..."
curl -s http://localhost/api/v1/properties | head -100
echo ""

# Step 9: Show logs
echo ""
echo "📋 Recent logs (last 20 lines):"
tail -20 storage/logs/laravel.log 2>/dev/null || echo "No log file yet"

echo ""
echo "✅ Backend fix complete!"
echo ""
echo "🌐 Test API externally:"
echo "curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"

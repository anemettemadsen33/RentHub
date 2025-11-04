#!/bin/bash

# RentHub Security & Performance Installation Script (Bash)

echo "=================================="
echo "  RentHub Security & Performance  "
echo "      Installation Script          "
echo "=================================="
echo ""

# Check if running in backend directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the backend directory"
    exit 1
fi

echo "📦 Step 1: Installing Dependencies..."
composer require predis/predis --no-interaction || echo "⚠️  Warning: Failed to install predis, continuing..."

echo ""
echo "🗄️  Step 2: Running Database Migrations..."
php artisan migrate --force || {
    echo "❌ Error: Database migration failed"
    exit 1
}

echo ""
echo "🔧 Step 3: Publishing Configuration..."
php artisan vendor:publish --tag=config --force || echo "⚠️  Warning: Failed to publish config, continuing..."

echo ""
echo "🧹 Step 4: Clearing Caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "📝 Step 5: Generating Application Key..."
if ! grep -q "APP_KEY=" .env 2>/dev/null; then
    php artisan key:generate
fi

echo ""
echo "🔐 Step 6: Setting up Security Configuration..."

# Check if .env exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ Created .env file"
fi

# Add security and performance configurations to .env if not present
if ! grep -q "RATE_LIMIT_ENABLED" .env; then
    cat >> .env << 'EOF'

# Security Configuration
RATE_LIMIT_ENABLED=true
RATE_LIMIT_DEFAULT=60:1
GDPR_DATA_RETENTION_DAYS=365

# Performance Configuration
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PROPERTY_TTL=3600
CACHE_SEARCH_TTL=1800
SLOW_QUERY_THRESHOLD=100
COMPRESSION_ENABLED=true
COMPRESSION_PREFER_BROTLI=true

# Monitoring
MONITORING_ENABLED=true
SLOW_REQUEST_THRESHOLD=1000
LOG_SLOW_REQUESTS=true
EOF
    echo "✅ Added security and performance configuration to .env"
fi

echo ""
echo "🔄 Step 7: Creating Performance Indexes..."
php artisan migrate --path=database/migrations/2025_01_03_200001_create_performance_indexes.php --force || echo "⚠️  Warning: Performance indexes migration may have already run"

echo ""
echo "📊 Step 8: Optimizing Application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Installation Complete!"
echo ""
echo "=================================="
echo "      Next Steps                  "
echo "=================================="
echo ""
echo "1. Configure Redis connection in .env:"
echo "   REDIS_HOST=127.0.0.1"
echo "   REDIS_PASSWORD=null"
echo "   REDIS_PORT=6379"
echo ""
echo "2. Test security features:"
echo "   php artisan test --filter SecurityTest"
echo ""
echo "3. Check health status:"
echo "   curl http://localhost:8000/api/health"
echo ""
echo "4. View documentation:"
echo "   SECURITY_PERFORMANCE_IMPLEMENTATION_COMPLETE.md"
echo ""
echo "🎉 Your application is now secured and optimized!"
echo ""

#!/bin/bash

# Setări
NETWORK="renthub_renthub-network"
APP_DIR="/workspaces/RentHub/backend"
IMAGE="php:8.3-cli-alpine"

# Environment variables
export APP_KEY="base64:JJbZoOgDVutqa9ZPrcpxPoNT3PUgONPInumvvo/8UTI="
export DB_CONNECTION="mysql"
export DB_HOST="renthub-mysql"
export DB_PORT="3306"
export DB_DATABASE="renthub"
export DB_USERNAME="root"
export DB_PASSWORD="secret"
export REDIS_HOST="renthub-redis"
export REDIS_PASSWORD="secret"
export REDIS_PORT="6379"
export CACHE_STORE="redis"
export SESSION_DRIVER="database"
export QUEUE_CONNECTION="redis"

echo "🔧 RentHub Laravel Setup Script"
echo "================================="
echo ""

# Function to run artisan command
run_artisan() {
    docker run --rm --network $NETWORK -v "$APP_DIR:/app" -w /app \
        -e APP_KEY="$APP_KEY" \
        -e DB_CONNECTION="$DB_CONNECTION" \
        -e DB_HOST="$DB_HOST" \
        -e DB_PORT="$DB_PORT" \
        -e DB_DATABASE="$DB_DATABASE" \
        -e DB_USERNAME="$DB_USERNAME" \
        -e DB_PASSWORD="$DB_PASSWORD" \
        -e REDIS_HOST="$REDIS_HOST" \
        -e REDIS_PASSWORD="$REDIS_PASSWORD" \
        -e REDIS_PORT="$REDIS_PORT" \
        -e CACHE_STORE="$CACHE_STORE" \
        -e SESSION_DRIVER="$SESSION_DRIVER" \
        -e QUEUE_CONNECTION="$QUEUE_CONNECTION" \
        $IMAGE sh -c "
            apk add --no-cache mysql-dev postgresql-dev icu-dev libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev oniguruma-dev > /dev/null 2>&1 && \
            docker-php-ext-install pdo_mysql > /dev/null 2>&1 && \
            php artisan $@
        "
}

# 1. Test database connection
echo "1️⃣  Testing database connection..."
run_artisan db:show 2>&1 | grep -E "(mysql|MySQL|Connection)" || echo "   ✅ Connected"

echo ""
echo "2️⃣  Checking migration status..."
run_artisan migrate:status 2>&1 | head -20

echo ""
echo "3️⃣  Running migrations..."
run_artisan migrate --force 2>&1 | tail -10

echo ""
echo "4️⃣  Clearing cache..."
run_artisan cache:clear 2>&1 | grep -i "cleared"
run_artisan config:clear 2>&1 | grep -i "cleared"
run_artisan route:clear 2>&1 | grep -i "cleared"

echo ""
echo "5️⃣  Rebuilding cache..."
run_artisan config:cache 2>&1 | grep -i "cached"
run_artisan route:cache 2>&1 | grep -i "cached"

echo ""
echo "✅ Setup complete!"
echo ""
echo "Test API with:"
echo "  docker run --rm --network $NETWORK $IMAGE wget -q -O- http://backend:9000/api/health"

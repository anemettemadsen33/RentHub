#!/bin/bash

# Automatic Backend Deployment Script for Forge
# This script will be executed via SSH on the Forge server

set -e

echo "🚀 Starting Automatic Backend Deployment..."
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
FORGE_USER="forge"
SITE_PATH="/home/forge/renthub-tbj7yxj7.on-forge.com"

echo -e "${YELLOW}📋 Server Information:${NC}"
echo "User: $FORGE_USER"
echo "Site Path: $SITE_PATH"
echo ""

# You need to provide the Forge server IP
read -p "Enter your Forge server IP address: " FORGE_IP

if [ -z "$FORGE_IP" ]; then
    echo -e "${RED}❌ Error: Server IP is required${NC}"
    exit 1
fi

echo ""
echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"

# Test SSH connection
if ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no ${FORGE_USER}@${FORGE_IP} "echo 'SSH OK'" 2>/dev/null; then
    echo -e "${GREEN}✅ SSH connection successful${NC}"
else
    echo -e "${RED}❌ Cannot connect to Forge server${NC}"
    echo ""
    echo "Please make sure:"
    echo "1. You have SSH key added to Forge"
    echo "2. Server IP is correct: $FORGE_IP"
    echo "3. Firewall allows SSH connections"
    echo ""
    echo "To add your SSH key to Forge:"
    echo "1. Run: cat ~/.ssh/id_rsa.pub"
    echo "2. Copy the output"
    echo "3. Go to Forge → Server → SSH Keys → Add Key"
    exit 1
fi

echo ""
echo -e "${YELLOW}📦 Deploying to Forge server...${NC}"
echo ""

# Execute deployment commands on Forge server
ssh -o StrictHostKeyChecking=no ${FORGE_USER}@${FORGE_IP} << 'ENDSSH'
set -e

cd /home/forge/renthub-tbj7yxj7.on-forge.com

echo "📍 Current directory: $(pwd)"
echo ""

echo "1️⃣  Checking Laravel logs for errors..."
if [ -f storage/logs/laravel.log ]; then
    echo "Last 20 lines of Laravel log:"
    tail -20 storage/logs/laravel.log
    echo ""
fi

echo "2️⃣  Pulling latest code..."
git fetch origin master
git reset --hard origin/master
echo "✅ Code updated"
echo ""

echo "3️⃣  Installing dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
echo "✅ Dependencies installed"
echo ""

echo "4️⃣  Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✅ Caches cleared"
echo ""

echo "5️⃣  Testing database connection..."
php artisan tinker --execute="echo 'DB: ' . (DB::connection()->getPdo() ? 'Connected' : 'Failed') . PHP_EOL;"
echo ""

echo "6️⃣  Running migrations..."
php artisan migrate --force
echo "✅ Migrations completed"
echo ""

echo "7️⃣  Checking tables..."
php artisan db:show 2>/dev/null || echo "Cannot show DB info"
echo ""

echo "8️⃣  Seeding database (if needed)..."
php artisan db:seed --force --class=DatabaseSeeder 2>/dev/null || echo "⚠️  Seeding skipped or failed"
echo ""

echo "9️⃣  Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Application optimized"
echo ""

echo "🔟 Creating storage link..."
php artisan storage:link || echo "Storage link already exists"
echo ""

echo "1️⃣1️⃣ Restarting queue workers..."
php artisan queue:restart
echo "✅ Queue workers restarted"
echo ""

echo "1️⃣2️⃣ Testing API endpoint..."
curl -s http://localhost/api/health | head -c 200 || echo "Health check response"
echo ""
echo ""

echo "1️⃣3️⃣ Testing properties endpoint..."
curl -s http://localhost/api/v1/properties | head -c 300 || echo "Properties response"
echo ""
echo ""

ENDSSH

echo ""
echo -e "${GREEN}✅ Backend deployment completed!${NC}"
echo ""
echo -e "${YELLOW}📊 Testing external API...${NC}"

# Test from outside
echo "Testing Health Endpoint:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/health)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✅ Health: OK ($HTTP_CODE)${NC}"
else
    echo -e "${RED}❌ Health: Failed ($HTTP_CODE)${NC}"
fi

echo ""
echo "Testing Properties Endpoint:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://renthub-tbj7yxj7.on-forge.com/api/v1/properties)
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "401" ]; then
    echo -e "${GREEN}✅ Properties API: OK ($HTTP_CODE)${NC}"
    echo "Response preview:"
    curl -s https://renthub-tbj7yxj7.on-forge.com/api/v1/properties | head -c 500
    echo ""
else
    echo -e "${RED}❌ Properties API: Failed ($HTTP_CODE)${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Deployment Complete!${NC}"
echo ""
echo "Next steps:"
echo "1. Check if API works: curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
echo "2. Deploy frontend with Vercel"
echo "3. Test full integration"

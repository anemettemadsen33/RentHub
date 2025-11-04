#!/bin/bash

# RentHub - Complete Security & DevOps Installation Script
# Bash Script for Linux/Mac
# Version: 1.0.0
# Date: November 3, 2025

echo "================================="
echo "RentHub Security & DevOps Setup"
echo "================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Step 1: Check Prerequisites
echo -e "${YELLOW}Step 1: Checking Prerequisites...${NC}"
command -v php >/dev/null 2>&1 && echo -e "  ${GREEN}✓ PHP is installed${NC}" || { echo -e "  ${RED}✗ PHP is not installed${NC}"; exit 1; }
command -v composer >/dev/null 2>&1 && echo -e "  ${GREEN}✓ Composer is installed${NC}" || { echo -e "  ${RED}✗ Composer is not installed${NC}"; exit 1; }
command -v docker >/dev/null 2>&1 && echo -e "  ${GREEN}✓ Docker is installed${NC}" || { echo -e "  ${RED}✗ Docker is not installed${NC}"; exit 1; }
command -v node >/dev/null 2>&1 && echo -e "  ${GREEN}✓ Node.js is installed${NC}" || { echo -e "  ${RED}✗ Node.js is not installed${NC}"; exit 1; }

# Step 2: Install PHP Dependencies
echo -e "\n${YELLOW}Step 2: Installing PHP dependencies...${NC}"
cd backend
composer install --no-interaction --prefer-dist --optimize-autoloader
composer require firebase/php-jwt

echo -e "  ${GREEN}✓ PHP dependencies installed${NC}"

# Step 3: Configure Environment
echo -e "\n${YELLOW}Step 3: Configuring environment...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "  ${GREEN}✓ Created .env file${NC}"
fi

php artisan key:generate --force

# Generate JWT secret
JWT_SECRET=$(openssl rand -base64 32)
sed -i.bak "s|JWT_SECRET=.*|JWT_SECRET=$JWT_SECRET|" .env

echo -e "  ${GREEN}✓ Environment configured${NC}"

# Step 4: Run Database Migrations
echo -e "\n${YELLOW}Step 4: Running database migrations...${NC}"
php artisan migrate --force

echo -e "  ${GREEN}✓ Database migrations completed${NC}"

# Step 5: Set up Monitoring Stack
echo -e "\n${YELLOW}Step 5: Setting up monitoring stack...${NC}"
cd ../docker/monitoring

if [ -f docker-compose.monitoring.yml ]; then
    docker-compose -f docker-compose.monitoring.yml up -d
    echo -e "  ${GREEN}✓ Monitoring stack started${NC}"
else
    echo -e "  ${YELLOW}⚠ Monitoring configuration not found${NC}"
fi

# Step 6: Install Frontend Dependencies
echo -e "\n${YELLOW}Step 6: Installing frontend dependencies...${NC}"
cd ../../frontend
npm install

echo -e "  ${GREEN}✓ Frontend dependencies installed${NC}"

# Step 7: Create OAuth Client
echo -e "\n${YELLOW}Step 7: Creating OAuth client...${NC}"
cd ../backend

php artisan tinker --execute="
use App\Models\OAuthClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

\$client = OAuthClient::create([
    'client_id' => 'renthub_web',
    'client_secret' => Hash::make(Str::random(40)),
    'name' => 'RentHub Web Application',
    'redirect_uris' => json_encode(['http://localhost:3000/callback']),
    'scopes' => json_encode(['read', 'write']),
    'is_confidential' => true,
    'is_active' => true,
]);

echo 'OAuth Client Created: ' . \$client->client_id;
"

echo -e "  ${GREEN}✓ OAuth client created${NC}"

# Step 8: Display Access URLs
echo -e "\n================================="
echo -e "${GREEN}Installation Complete!${NC}"
echo -e "================================="
echo ""
echo -e "${YELLOW}Access Points:${NC}"
echo -e "  Backend API:      http://localhost:8000"
echo -e "  Frontend:         http://localhost:3000"
echo -e "  Prometheus:       http://localhost:9090"
echo -e "  Grafana:          http://localhost:3001"
echo -e "  Alertmanager:     http://localhost:9093"
echo ""
echo -e "${YELLOW}Default Credentials:${NC}"
echo -e "  Grafana:          admin / admin"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo -e "  1. Configure Slack webhook in .env"
echo -e "  2. Set up SSL certificates"
echo -e "  3. Review security audit logs"
echo -e "  4. Configure monitoring alerts"
echo ""
echo -e "${YELLOW}Documentation:${NC}"
echo -e "  Security Guide:   ./COMPLETE_SECURITY_DEVOPS_IMPLEMENTATION_2025_11_03.md"
echo ""
echo -e "${GREEN}🎉 Ready to launch!${NC}"

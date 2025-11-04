#!/bin/bash

# Security, Performance & UI/UX Installation Script
# RentHub Platform - Complete Implementation

echo "========================================"
echo "  Security, Performance & UI/UX Setup  "
echo "========================================"
echo ""

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Check if in correct directory
if [ ! -d "backend" ] || [ ! -d "frontend" ]; then
    echo -e "${RED}Error: Please run this script from the RentHub root directory${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing Backend Dependencies...${NC}"
cd backend

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer not found. Please install Composer first.${NC}"
    exit 1
fi

composer_version=$(composer --version)
echo -e "${GREEN}✓ Composer found: $composer_version${NC}"

# Install PHP dependencies
echo -e "${CYAN}Installing/updating Composer packages...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

# Run migrations
echo -e "\n${YELLOW}Step 2: Running Database Migrations...${NC}"
php artisan migrate --force

# Create security audit logs table
if [ -f "database/migrations/2025_01_01_000001_create_security_audit_logs_table.php" ]; then
    echo -e "${CYAN}Running security audit logs migration...${NC}"
    php artisan migrate --path=database/migrations/2025_01_01_000001_create_security_audit_logs_table.php --force
    echo -e "${GREEN}✓ Security audit logs table created${NC}"
fi

# Clear caches
echo -e "\n${YELLOW}Step 3: Clearing Caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

# Optimize application
echo -e "\n${YELLOW}Step 4: Optimizing Application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"

# Set up storage
echo -e "\n${YELLOW}Step 5: Setting Up Storage...${NC}"
php artisan storage:link
echo -e "${GREEN}✓ Storage linked${NC}"

# Generate key if needed
if ! grep -q "APP_KEY" .env 2>/dev/null; then
    echo -e "\n${CYAN}Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✓ Application key generated${NC}"
fi

cd ..

# Frontend Setup
echo -e "\n${YELLOW}Step 6: Installing Frontend Dependencies...${NC}"
cd frontend

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}✗ Node.js not found. Please install Node.js first.${NC}"
    cd ..
    exit 1
fi

node_version=$(node --version)
echo -e "${GREEN}✓ Node.js found: $node_version${NC}"

# Check npm
if ! command -v npm &> /dev/null; then
    echo -e "${RED}✗ npm not found. Please install npm first.${NC}"
    cd ..
    exit 1
fi

npm_version=$(npm --version)
echo -e "${GREEN}✓ npm found: v$npm_version${NC}"

# Install packages
echo -e "${CYAN}Installing npm packages...${NC}"
npm install

# Build frontend
echo -e "\n${YELLOW}Step 7: Building Frontend...${NC}"
npm run build
echo -e "${GREEN}✓ Frontend built successfully${NC}"

cd ..

# Create required directories
echo -e "\n${YELLOW}Step 8: Creating Required Directories...${NC}"
directories=(
    "backend/storage/logs"
    "backend/storage/framework/cache"
    "backend/storage/framework/sessions"
    "backend/storage/framework/views"
    "backend/storage/app/public"
)

for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo -e "${GREEN}✓ Created $dir${NC}"
    fi
done

# Set permissions (Linux/Mac)
echo -e "\n${YELLOW}Step 9: Setting Permissions...${NC}"
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Create .env if not exists
echo -e "\n${YELLOW}Step 10: Checking Environment Configuration...${NC}"
if [ ! -f "backend/.env" ]; then
    if [ -f "backend/.env.example" ]; then
        cp backend/.env.example backend/.env
        echo -e "${GREEN}✓ Created .env from .env.example${NC}"
        echo -e "${YELLOW}  Please update the .env file with your settings${NC}"
    fi
fi

# Summary
echo -e "\n${CYAN}========================================${NC}"
echo -e "${GREEN}  Installation Complete!  ${NC}"
echo -e "${CYAN}========================================${NC}"
echo ""
echo -e "${YELLOW}Features Installed:${NC}"
echo -e "${GREEN}  ✓ Advanced Rate Limiting & DDoS Protection${NC}"
echo -e "${GREEN}  ✓ Security Headers Middleware${NC}"
echo -e "${GREEN}  ✓ Data Encryption Service${NC}"
echo -e "${GREEN}  ✓ GDPR Compliance Service${NC}"
echo -e "${GREEN}  ✓ Security Audit Logging${NC}"
echo -e "${GREEN}  ✓ Advanced Caching System${NC}"
echo -e "${GREEN}  ✓ Query Optimization${NC}"
echo -e "${GREEN}  ✓ UI/UX Components (Loading, Error, Toast)${NC}"
echo ""

echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Update backend/.env with your database credentials"
echo "  2. Configure Redis for caching (recommended)"
echo "  3. Set up SSL/TLS certificate for production"
echo "  4. Review SECURITY_PERFORMANCE_UI_COMPLETE.md for usage"
echo "  5. Run: cd backend && php artisan serve"
echo "  6. Run: cd frontend && npm run dev"
echo ""

echo -e "${YELLOW}Documentation:${NC}"
echo "  - SECURITY_PERFORMANCE_UI_COMPLETE.md - Complete guide"
echo "  - QUICK_START_SECURITY_PERFORMANCE_UI_V2.md - Quick reference"
echo ""

echo -e "${CYAN}Happy coding! 🚀${NC}"

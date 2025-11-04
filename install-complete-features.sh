#!/bin/bash

# RentHub - Complete Security, Performance & UI/UX Installation Script
# Bash Script for Linux/Mac

echo "================================================"
echo "RentHub - Complete Features Installation"
echo "================================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Check if we're in the correct directory
if [ ! -f "backend/composer.json" ]; then
    echo -e "${RED}❌ Error: Please run this script from the RentHub root directory${NC}"
    exit 1
fi

# Backend Setup
echo -e "${YELLOW}📦 Setting up Backend...${NC}"
echo ""

cd backend

# Install/Update Composer dependencies
echo -e "${CYAN}Installing Composer dependencies...${NC}"
composer install --no-interaction --prefer-dist --optimize-autoloader

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer installation failed${NC}"
    exit 1
fi

# Copy environment file if it doesn't exist
if [ ! -f ".env" ]; then
    echo -e "${CYAN}Creating .env file...${NC}"
    cp .env.example .env
    echo -e "${YELLOW}⚠️  Please configure your .env file before continuing${NC}"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${CYAN}Generating application key...${NC}"
    php artisan key:generate --no-interaction
fi

# Run migrations
echo -e "${CYAN}Running database migrations...${NC}"
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠️  Migration failed - please check your database configuration${NC}"
fi

# Clear all caches
echo -e "${CYAN}Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo -e "${CYAN}Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache

# Register middleware
echo ""
echo -e "${YELLOW}📝 Registering Security Middleware...${NC}"
echo ""
echo -e "${CYAN}Please add the following middleware to app/Http/Kernel.php:${NC}"
echo ""
echo -e "${NC}In the 'web' middleware group:${NC}"
echo -e "${GREEN}    \App\Http\Middleware\XssProtection::class,${NC}"
echo -e "${GREEN}    \App\Http\Middleware\SecurityHeadersMiddleware::class,${NC}"
echo ""
echo -e "${NC}In the 'api' middleware group:${NC}"
echo -e "${GREEN}    \App\Http\Middleware\SqlInjectionProtection::class,${NC}"
echo -e "${GREEN}    \App\Http\Middleware\DdosProtection::class,${NC}"
echo -e "${GREEN}    \App\Http\Middleware\CompressionMiddleware::class,${NC}"
echo ""

cd ..

# Frontend Setup
echo -e "${YELLOW}📦 Setting up Frontend...${NC}"
echo ""

if [ -f "frontend/package.json" ]; then
    cd frontend
    
    echo -e "${CYAN}Installing npm dependencies...${NC}"
    npm install
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ npm installation failed${NC}"
        cd ..
        exit 1
    fi
    
    # Build frontend
    echo -e "${CYAN}Building frontend assets...${NC}"
    npm run build
    
    cd ..
else
    echo -e "${YELLOW}⚠️  Frontend directory not found, skipping...${NC}"
fi

# Create necessary directories
echo ""
echo -e "${YELLOW}📁 Creating necessary directories...${NC}"
directories=(
    "backend/storage/app/private/uploads"
    "backend/storage/logs/security"
    "backend/storage/framework/cache/data"
)

for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        echo -e "${GREEN}✓ Created $dir${NC}"
    fi
done

# Set permissions (Linux/Mac)
echo ""
echo -e "${YELLOW}🔒 Setting permissions...${NC}"
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
echo -e "${GREEN}✓ Permissions set${NC}"

# Check Redis connection
echo ""
echo -e "${YELLOW}🔍 Checking Redis connection...${NC}"
if command -v redis-cli &> /dev/null; then
    if redis-cli ping &> /dev/null; then
        echo -e "${GREEN}✓ Redis is running${NC}"
    else
        echo -e "${YELLOW}⚠️  Redis is not running - caching features may not work${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Redis CLI not found - make sure Redis is installed${NC}"
fi

# Summary
echo ""
echo "================================================"
echo -e "${GREEN}✅ Installation Complete!${NC}"
echo "================================================"
echo ""
echo -e "${YELLOW}📚 Next Steps:${NC}"
echo ""
echo -e "${NC}1. Configure your .env file:${NC}"
echo -e "   ${CYAN}- Database credentials${NC}"
echo -e "   ${CYAN}- Redis configuration${NC}"
echo -e "   ${CYAN}- Security settings${NC}"
echo ""
echo -e "${NC}2. Register middleware in app/Http/Kernel.php (see above)${NC}"
echo ""
echo -e "${NC}3. Start the development server:${NC}"
echo -e "   ${CYAN}cd backend${NC}"
echo -e "   ${CYAN}php artisan serve${NC}"
echo ""
echo -e "${NC}4. Run tests:${NC}"
echo -e "   ${CYAN}php artisan test${NC}"
echo ""
echo -e "${CYAN}📖 Documentation: COMPLETE_SECURITY_PERFORMANCE_UI_IMPLEMENTATION.md${NC}"
echo ""
echo -e "${GREEN}🎉 Happy coding!${NC}"
echo ""

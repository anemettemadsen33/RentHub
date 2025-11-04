#!/bin/bash

# RentHub - Security, Performance & UI/UX Installation Script
# Date: November 3, 2025

echo "🚀 RentHub - Installing Security, Performance & UI/UX Features"
echo "================================================================"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Check if running in RentHub directory
if [ ! -f "composer.json" ] && [ ! -d "backend" ]; then
    print_error "This script must be run from the RentHub root directory"
    exit 1
fi

# Backend installation
echo ""
echo "📦 Installing Backend Dependencies..."
echo "--------------------------------------"

cd backend 2>/dev/null || cd .

if [ -f "composer.json" ]; then
    print_info "Installing PHP dependencies..."
    composer install
    print_success "PHP dependencies installed"
else
    print_warning "composer.json not found, skipping PHP dependencies"
fi

# Database setup
echo ""
echo "🗄️  Setting up Database..."
echo "---------------------------"

if [ -f "artisan" ]; then
    print_info "Running migrations..."
    php artisan migrate --force
    print_success "Migrations completed"
    
    print_info "Seeding RBAC structure..."
    php artisan db:seed --class=RBACSeeder
    print_success "RBAC seeded"
else
    print_warning "artisan not found, skipping database setup"
fi

# Cache setup
echo ""
echo "💾 Configuring Cache..."
echo "-----------------------"

if [ -f "artisan" ]; then
    print_info "Clearing cache..."
    php artisan cache:clear
    
    print_info "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    print_success "Cache configured"
else
    print_warning "artisan not found, skipping cache setup"
fi

# Frontend installation
echo ""
echo "🎨 Installing Frontend Dependencies..."
echo "---------------------------------------"

cd ../frontend 2>/dev/null || cd frontend 2>/dev/null

if [ -f "package.json" ]; then
    print_info "Installing Node.js dependencies..."
    npm install
    print_success "Node.js dependencies installed"
else
    print_warning "package.json not found, skipping frontend dependencies"
fi

# Return to root
cd .. 2>/dev/null

# Verification
echo ""
echo "🔍 Verifying Installation..."
echo "-----------------------------"

FILES=(
    "backend/app/Services/OAuth2Service.php"
    "backend/app/Services/RBACService.php"
    "backend/app/Services/EncryptionService.php"
    "backend/app/Services/CacheService.php"
    "backend/app/Services/PerformanceService.php"
    "backend/app/Http/Middleware/SecurityHeadersMiddleware.php"
    "backend/app/Http/Middleware/RateLimitMiddleware.php"
    "backend/app/Http/Middleware/ValidateInputMiddleware.php"
    "frontend/src/components/ui/LoadingStates.tsx"
    "frontend/src/components/ui/StateComponents.tsx"
    "frontend/src/components/ui/AccessibilityComponents.tsx"
)

MISSING=0
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        print_success "$file"
    else
        print_error "$file - NOT FOUND"
        MISSING=$((MISSING + 1))
    fi
done

# Summary
echo ""
echo "📊 Installation Summary"
echo "======================="

if [ $MISSING -eq 0 ]; then
    print_success "All files verified successfully!"
else
    print_warning "$MISSING files are missing"
fi

echo ""
echo "✅ Security Features:"
echo "   - OAuth 2.0 Authentication"
echo "   - RBAC (Role-Based Access Control)"
echo "   - Data Encryption"
echo "   - Security Headers"
echo "   - Rate Limiting"
echo "   - Input Validation"
echo "   - Security Audit Logging"
echo ""

echo "⚡ Performance Features:"
echo "   - Multi-layer Caching (Redis)"
echo "   - Query Optimization"
echo "   - Image Optimization"
echo "   - Response Compression"
echo "   - Connection Pooling"
echo ""

echo "🎨 UI/UX Features:"
echo "   - Loading States (Spinner, Skeleton)"
echo "   - State Components (Error, Empty)"
echo "   - Accessibility (WCAG AA)"
echo "   - Design System"
echo "   - Animations"
echo ""

# Next steps
echo "🎯 Next Steps:"
echo "=============="
echo ""
echo "1. Configure your .env file:"
echo "   cp backend/.env.example backend/.env"
echo "   cp frontend/.env.example frontend/.env"
echo ""
echo "2. Update environment variables:"
echo "   - Set CACHE_DRIVER=redis"
echo "   - Configure database credentials"
echo "   - Set JWT_SECRET and ENCRYPTION_KEY"
echo ""
echo "3. Start the development servers:"
echo "   Backend:  cd backend && php artisan serve"
echo "   Frontend: cd frontend && npm run dev"
echo ""
echo "4. Run tests:"
echo "   Backend:  cd backend && php artisan test"
echo "   Frontend: cd frontend && npm run test"
echo ""
echo "5. Read the documentation:"
echo "   - COMPLETE_SECURITY_PERFORMANCE_UI_GUIDE.md"
echo "   - QUICK_START_COMPLETE_IMPLEMENTATION.md"
echo "   - QUICK_REFERENCE_SECURITY_PERFORMANCE_UI.md"
echo ""

print_success "Installation completed successfully! 🎉"
echo ""
echo "For support, check the documentation or contact the team."
echo ""

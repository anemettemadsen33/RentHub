#!/bin/bash

# ===================================
# RentHub - Complete Testing Script
# ===================================

set -e

echo "🧪 RentHub - Running Complete Test Suite"
echo "========================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track failures
FAILURES=0

# ===================================
# Backend Tests
# ===================================

echo ""
echo "${YELLOW}📦 Testing Backend (Laravel)${NC}"
echo "-----------------------------------"

cd backend

echo "→ Installing dependencies..."
composer install --quiet

echo "→ Running PHPUnit tests..."
if php artisan test --parallel; then
    echo "${GREEN}✓ Backend tests passed${NC}"
else
    echo "${RED}✗ Backend tests failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

echo "→ Running PHPStan static analysis..."
if ./vendor/bin/phpstan analyse --no-progress; then
    echo "${GREEN}✓ PHPStan analysis passed${NC}"
else
    echo "${RED}✗ PHPStan analysis failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

echo "→ Checking code style..."
if ./vendor/bin/pint --test; then
    echo "${GREEN}✓ Code style check passed${NC}"
else
    echo "${RED}✗ Code style check failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

cd ..

# ===================================
# Frontend Tests
# ===================================

echo ""
echo "${YELLOW}🎨 Testing Frontend (Next.js)${NC}"
echo "-----------------------------------"

cd frontend

echo "→ Installing dependencies..."
npm install --silent

echo "→ Running Vitest unit tests..."
if npm test -- --run; then
    echo "${GREEN}✓ Frontend unit tests passed${NC}"
else
    echo "${RED}✗ Frontend unit tests failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

echo "→ Running TypeScript type check..."
if npm run type-check; then
    echo "${GREEN}✓ Type checking passed${NC}"
else
    echo "${RED}✗ Type checking failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

echo "→ Running ESLint..."
if npm run lint; then
    echo "${GREEN}✓ Linting passed${NC}"
else
    echo "${RED}✗ Linting failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

echo "→ Testing production build..."
if npm run build; then
    echo "${GREEN}✓ Production build successful${NC}"
else
    echo "${RED}✗ Production build failed${NC}"
    FAILURES=$((FAILURES + 1))
fi

cd ..

# ===================================
# Summary
# ===================================

echo ""
echo "========================================"
echo "📊 Test Summary"
echo "========================================"

if [ $FAILURES -eq 0 ]; then
    echo "${GREEN}✅ All tests passed! Ready for deployment.${NC}"
    exit 0
else
    echo "${RED}❌ $FAILURES test suite(s) failed.${NC}"
    echo "${YELLOW}Please fix the issues before deploying.${NC}"
    exit 1
fi

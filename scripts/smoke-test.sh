#!/bin/bash

# Smoke Test Script for Blue-Green Deployments
# Usage: ./smoke-test.sh <version> <environment>

VERSION=$1
ENVIRONMENT=$2
BASE_URL="https://${ENVIRONMENT}.renthub.com"

echo "Running smoke tests on ${VERSION} version in ${ENVIRONMENT}..."

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

FAILED=0

# Function to test endpoint
test_endpoint() {
    local endpoint=$1
    local expected_status=$2
    local description=$3
    
    echo -n "Testing: $description... "
    
    response=$(curl -s -o /dev/null -w "%{http_code}" "${BASE_URL}${endpoint}")
    
    if [ "$response" -eq "$expected_status" ]; then
        echo -e "${GREEN}✓ PASSED${NC} (HTTP $response)"
        return 0
    else
        echo -e "${RED}✗ FAILED${NC} (Expected HTTP $expected_status, got HTTP $response)"
        FAILED=$((FAILED + 1))
        return 1
    fi
}

# Health check
test_endpoint "/health" 200 "Health check"

# API endpoints
test_endpoint "/api/properties" 200 "List properties"
test_endpoint "/api/properties/1" 200 "Get property details"
test_endpoint "/api/auth/login" 405 "Auth endpoint exists"
test_endpoint "/api/bookings" 401 "Protected route requires auth"

# Database connectivity
test_endpoint "/api/health/database" 200 "Database connection"

# Redis connectivity
test_endpoint "/api/health/redis" 200 "Redis connection"

# Queue system
test_endpoint "/api/health/queue" 200 "Queue system"

# Storage access
test_endpoint "/api/health/storage" 200 "Storage system"

# Search functionality
test_endpoint "/api/search?city=NewYork" 200 "Search functionality"

echo ""
echo "========================================="
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}All smoke tests passed!${NC}"
    exit 0
else
    echo -e "${RED}$FAILED test(s) failed!${NC}"
    exit 1
fi

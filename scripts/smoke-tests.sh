#!/bin/bash

# Smoke Tests for RentHub Deployment
# Usage: ./smoke-tests.sh <hostname>

set -e

HOSTNAME=${1:-"localhost"}
API_URL="https://${HOSTNAME}/api"
MAX_RETRIES=5
RETRY_DELAY=10

echo "🔍 Running smoke tests against ${HOSTNAME}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper function to check HTTP status
check_endpoint() {
    local endpoint=$1
    local expected_status=${2:-200}
    local description=$3
    
    echo -n "Testing ${description}... "
    
    for i in $(seq 1 $MAX_RETRIES); do
        response=$(curl -s -o /dev/null -w "%{http_code}" "${API_URL}${endpoint}" || echo "000")
        
        if [ "$response" = "$expected_status" ]; then
            echo -e "${GREEN}✓ PASS${NC} (HTTP $response)"
            return 0
        fi
        
        if [ $i -lt $MAX_RETRIES ]; then
            echo -n "."
            sleep $RETRY_DELAY
        fi
    done
    
    echo -e "${RED}✗ FAIL${NC} (HTTP $response, expected $expected_status)"
    return 1
}

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Health Checks
echo -e "\n${YELLOW}=== Health Checks ===${NC}"
if check_endpoint "/health" 200 "Health endpoint"; then
    ((TESTS_PASSED++))
else
    ((TESTS_FAILED++))
fi

if check_endpoint "/ready" 200 "Readiness endpoint"; then
    ((TESTS_PASSED++))
else
    ((TESTS_FAILED++))
fi

# API Endpoints
echo -e "\n${YELLOW}=== API Endpoints ===${NC}"
if check_endpoint "/properties" 200 "Properties listing"; then
    ((TESTS_PASSED++))
else
    ((TESTS_FAILED++))
fi

if check_endpoint "/categories" 200 "Categories listing"; then
    ((TESTS_PASSED++))
else
    ((TESTS_FAILED++))
fi

# Authentication
echo -e "\n${YELLOW}=== Authentication ===${NC}"
if check_endpoint "/auth/login" 200 "Login endpoint"; then
    ((TESTS_PASSED++))
else
    ((TESTS_FAILED++))
fi

# Database Connectivity
echo -e "\n${YELLOW}=== Database Tests ===${NC}"
response=$(curl -s "${API_URL}/health/database" | jq -r '.status' 2>/dev/null || echo "error")
if [ "$response" = "ok" ]; then
    echo -e "Database connectivity... ${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "Database connectivity... ${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Redis Connectivity
echo -e "\n${YELLOW}=== Cache Tests ===${NC}"
response=$(curl -s "${API_URL}/health/redis" | jq -r '.status' 2>/dev/null || echo "error")
if [ "$response" = "ok" ]; then
    echo -e "Redis connectivity... ${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "Redis connectivity... ${RED}✗ FAIL${NC}"
    ((TESTS_FAILED++))
fi

# Performance Tests
echo -e "\n${YELLOW}=== Performance Tests ===${NC}"
response_time=$(curl -s -o /dev/null -w "%{time_total}" "${API_URL}/properties")
if (( $(echo "$response_time < 2.0" | bc -l) )); then
    echo -e "Response time (${response_time}s)... ${GREEN}✓ PASS${NC}"
    ((TESTS_PASSED++))
else
    echo -e "Response time (${response_time}s)... ${RED}✗ FAIL${NC} (should be < 2s)"
    ((TESTS_FAILED++))
fi

# Security Headers
echo -e "\n${YELLOW}=== Security Headers ===${NC}"
headers=$(curl -s -I "${API_URL}/properties")

check_header() {
    local header=$1
    local description=$2
    
    if echo "$headers" | grep -qi "^${header}:"; then
        echo -e "${description}... ${GREEN}✓ PASS${NC}"
        ((TESTS_PASSED++))
    else
        echo -e "${description}... ${RED}✗ FAIL${NC}"
        ((TESTS_FAILED++))
    fi
}

check_header "X-Frame-Options" "X-Frame-Options header"
check_header "X-Content-Type-Options" "X-Content-Type-Options header"
check_header "Strict-Transport-Security" "HSTS header"
check_header "Content-Security-Policy" "CSP header"

# Summary
echo -e "\n${YELLOW}=== Test Summary ===${NC}"
TOTAL_TESTS=$((TESTS_PASSED + TESTS_FAILED))
echo "Total tests: $TOTAL_TESTS"
echo -e "Passed: ${GREEN}$TESTS_PASSED${NC}"
echo -e "Failed: ${RED}$TESTS_FAILED${NC}"

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "\n${GREEN}✓ All smoke tests passed!${NC}"
    exit 0
else
    echo -e "\n${RED}✗ Some smoke tests failed!${NC}"
    exit 1
fi

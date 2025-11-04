#!/bin/bash

# Post-Deployment Integration Tests
# Usage: ./post-deployment-tests.sh <environment>

ENVIRONMENT=$1
BASE_URL="https://${ENVIRONMENT}.renthub.com"
API_URL="${BASE_URL}/api"

echo "Running post-deployment tests on ${ENVIRONMENT}..."

# Load test credentials
if [ "$ENVIRONMENT" == "staging" ]; then
    EMAIL="test@staging.renthub.com"
    PASSWORD="Staging123!"
elif [ "$ENVIRONMENT" == "production" ]; then
    EMAIL="test@renthub.com"
    PASSWORD="Production123!"
fi

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

PASSED=0
FAILED=0

# Test function
test_api() {
    local name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    local expected_status=$5
    
    echo -n "Testing: $name... "
    
    if [ -z "$data" ]; then
        response=$(curl -s -w "\n%{http_code}" -X $method "${API_URL}${endpoint}" \
            -H "Authorization: Bearer ${TOKEN}" \
            -H "Content-Type: application/json")
    else
        response=$(curl -s -w "\n%{http_code}" -X $method "${API_URL}${endpoint}" \
            -H "Authorization: Bearer ${TOKEN}" \
            -H "Content-Type: application/json" \
            -d "$data")
    fi
    
    status_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$status_code" -eq "$expected_status" ]; then
        echo -e "${GREEN}✓ PASSED${NC}"
        PASSED=$((PASSED + 1))
        return 0
    else
        echo -e "${RED}✗ FAILED${NC} (Expected $expected_status, got $status_code)"
        echo "Response: $body"
        FAILED=$((FAILED + 1))
        return 1
    fi
}

echo ""
echo "========================================="
echo "1. Authentication Tests"
echo "========================================="

# Login
echo -n "Logging in... "
login_response=$(curl -s -X POST "${API_URL}/auth/login" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $login_response | jq -r '.access_token')

if [ ! -z "$TOKEN" ] && [ "$TOKEN" != "null" ]; then
    echo -e "${GREEN}✓ SUCCESS${NC}"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗ FAILED${NC}"
    FAILED=$((FAILED + 1))
    exit 1
fi

echo ""
echo "========================================="
echo "2. Property Tests"
echo "========================================="

test_api "List properties" "GET" "/properties" "" 200
test_api "Get property details" "GET" "/properties/1" "" 200
test_api "Search properties" "GET" "/search?city=NewYork" "" 200
test_api "Get property amenities" "GET" "/properties/1/amenities" "" 200

echo ""
echo "========================================="
echo "3. Booking Tests"
echo "========================================="

test_api "List bookings" "GET" "/bookings" "" 200
test_api "Create booking" "POST" "/bookings" '{"property_id":1,"check_in":"2025-01-15","check_out":"2025-01-20","guests":2}' 201

echo ""
echo "========================================="
echo "4. User Tests"
echo "========================================="

test_api "Get user profile" "GET" "/user/profile" "" 200
test_api "Update profile" "PUT" "/user/profile" '{"name":"Test User"}' 200

echo ""
echo "========================================="
echo "5. Review Tests"
echo "========================================="

test_api "Get property reviews" "GET" "/properties/1/reviews" "" 200
test_api "Submit review" "POST" "/reviews" '{"property_id":1,"rating":5,"comment":"Great place!"}' 201

echo ""
echo "========================================="
echo "6. Wishlist Tests"
echo "========================================="

test_api "Get wishlist" "GET" "/wishlist" "" 200
test_api "Add to wishlist" "POST" "/wishlist" '{"property_id":1}' 201

echo ""
echo "========================================="
echo "7. Message Tests"
echo "========================================="

test_api "Get messages" "GET" "/messages" "" 200
test_api "Send message" "POST" "/messages" '{"recipient_id":1,"message":"Hello"}' 201

echo ""
echo "========================================="
echo "8. Payment Tests"
echo "========================================="

test_api "Get payment methods" "GET" "/payments/methods" "" 200

echo ""
echo "========================================="
echo "9. Analytics Tests"
echo "========================================="

test_api "Get dashboard stats" "GET" "/analytics/dashboard" "" 200

echo ""
echo "========================================="
echo "10. Health Checks"
echo "========================================="

test_api "Database health" "GET" "/health/database" "" 200
test_api "Redis health" "GET" "/health/redis" "" 200
test_api "Queue health" "GET" "/health/queue" "" 200

echo ""
echo "========================================="
echo "Test Summary"
echo "========================================="
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}✗ Some tests failed!${NC}"
    exit 1
fi

#!/bin/bash

# RentHub Performance Testing Script
# This script tests the performance improvements after optimization

echo "🚀 RentHub Performance Testing Script"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
BASE_URL="http://localhost:8000"
API_BASE="${BASE_URL}/api"
TEST_ITERATIONS=10
CONCURRENT_REQUESTS=5

# Function to make API requests and measure response time
make_request() {
    local endpoint=$1
    local method=${2:-GET}
    local data=$3
    local auth_token=$4
    
    local start_time=$(date +%s%N)
    
    if [ "$method" = "POST" ]; then
        if [ -n "$auth_token" ]; then
            response=$(curl -s -w "\n%{http_code}" -X POST "$API_BASE/$endpoint" \
                -H "Authorization: Bearer $auth_token" \
                -H "Content-Type: application/json" \
                -H "Accept: application/json" \
                -d "$data" 2>/dev/null)
        else
            response=$(curl -s -w "\n%{http_code}" -X POST "$API_BASE/$endpoint" \
                -H "Content-Type: application/json" \
                -H "Accept: application/json" \
                -d "$data" 2>/dev/null)
        fi
    else
        if [ -n "$auth_token" ]; then
            response=$(curl -s -w "\n%{http_code}" -X GET "$API_BASE/$endpoint" \
                -H "Authorization: Bearer $auth_token" \
                -H "Accept: application/json" 2>/dev/null)
        else
            response=$(curl -s -w "\n%{http_code}" -X GET "$API_BASE/$endpoint" \
                -H "Accept: application/json" 2>/dev/null)
        fi
    fi
    
    local end_time=$(date +%s%N)
    local response_time=$((($end_time - $start_time) / 1000000)) # Convert to milliseconds
    
    local http_code=$(echo "$response" | tail -n1)
    local body=$(echo "$response" | sed '$d')
    
    echo "$response_time|$http_code|$body"
}

# Function to test authentication performance
test_auth_performance() {
    echo -e "\n${YELLOW}🔐 Testing Authentication Performance${NC}"
    echo "======================================="
    
    local total_time=0
    local success_count=0
    local failed_count=0
    
    echo "Testing login endpoint performance..."
    
    for i in $(seq 1 $TEST_ITERATIONS); do
        local login_data='{"email":"test@example.com","password":"password"}'
        local result=$(make_request "login" "POST" "$login_data")
        
        local response_time=$(echo "$result" | cut -d'|' -f1)
        local http_code=$(echo "$result" | cut -d'|' -f2)
        
        total_time=$((total_time + response_time))
        
        if [ "$http_code" = "200" ] || [ "$http_code" = "401" ]; then
            success_count=$((success_count + 1))
            echo -e "Request $i: ${GREEN}${response_time}ms${NC} (HTTP $http_code)"
        else
            failed_count=$((failed_count + 1))
            echo -e "Request $i: ${RED}${response_time}ms${NC} (HTTP $http_code)"
        fi
        
        sleep 0.1 # Small delay between requests
    done
    
    local avg_time=$((total_time / TEST_ITERATIONS))
    echo -e "\nAuthentication Performance Summary:"
    echo "Total requests: $TEST_ITERATIONS"
    echo "Successful: $success_count"
    echo "Failed: $failed_count"
    echo -e "Average response time: ${YELLOW}${avg_time}ms${NC}"
    
    if [ $avg_time -lt 1000 ]; then
        echo -e "${GREEN}✅ Good performance!${NC}"
    elif [ $avg_time -lt 3000 ]; then
        echo -e "${YELLOW}⚠️  Acceptable performance${NC}"
    else
        echo -e "${RED}❌ Poor performance - needs optimization${NC}"
    fi
}

# Function to test payment performance
test_payment_performance() {
    echo -e "\n${YELLOW}💳 Testing Payment Performance${NC}"
    echo "================================="
    
    # Note: This would require a valid auth token and test data
    echo "Payment performance testing requires valid authentication and test data."
    echo "Please ensure you have test payment data set up."
    
    # Test connection pool stats
    echo -e "\nTesting connection pool statistics..."
    local result=$(make_request "optimized/pool/connection-stats")
    local response_time=$(echo "$result" | cut -d'|' -f1)
    local http_code=$(echo "$result" | cut -d'|' -f2)
    
    echo -e "Connection pool stats: ${YELLOW}${response_time}ms${NC} (HTTP $http_code)"
}

# Function to test database performance
test_database_performance() {
    echo -e "\n${YELLOW}🗄️  Testing Database Performance${NC}"
    echo "====================================="
    
    # Test basic database connectivity
    echo "Testing database connectivity..."
    local result=$(make_request "health/readiness")
    local response_time=$(echo "$result" | cut -d'|' -f1)
    local http_code=$(echo "$result" | cut -d'|' -f2)
    
    echo -e "Database connectivity: ${YELLOW}${response_time}ms${NC} (HTTP $http_code)"
    
    if [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✅ Database is responsive${NC}"
    else
        echo -e "${RED}❌ Database connectivity issues${NC}"
    fi
}

# Function to test concurrent requests
test_concurrent_performance() {
    echo -e "\n${YELLOW}🔄 Testing Concurrent Request Performance${NC}"
    echo "==========================================="
    
    echo "Testing concurrent API requests..."
    
    local start_time=$(date +%s%N)
    
    # Run concurrent requests
    for i in $(seq 1 $CONCURRENT_REQUESTS); do
        make_request "health" "GET" "" "" &
    done
    
    wait # Wait for all background jobs to complete
    
    local end_time=$(date +%s%N)
    local total_time=$((($end_time - $start_time) / 1000000))
    
    echo -e "Concurrent requests completed in: ${YELLOW}${total_time}ms${NC}"
    echo "Concurrent requests: $CONCURRENT_REQUESTS"
    
    local avg_time=$((total_time / CONCURRENT_REQUESTS))
    echo -e "Average time per concurrent request: ${YELLOW}${avg_time}ms${NC}"
}

# Function to check system resources
check_system_resources() {
    echo -e "\n${YELLOW}🖥️  Checking System Resources${NC}"
    echo "==============================="
    
    # Check if services are running
    echo "Checking service status..."
    
    if curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000" | grep -q "200\|302"; then
        echo -e "Web server: ${GREEN}✅ Running${NC}"
    else
        echo -e "Web server: ${RED}❌ Not responding${NC}"
    fi
    
    # Check database connection
    if curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/health/readiness" | grep -q "200"; then
        echo -e "Database: ${GREEN}✅ Connected${NC}"
    else
        echo -e "Database: ${RED}❌ Connection failed${NC}"
    fi
    
    # Check Redis (if available)
    if command -v redis-cli &> /dev/null; then
        if redis-cli ping | grep -q "PONG"; then
            echo -e "Redis: ${GREEN}✅ Running${NC}"
        else
            echo -e "Redis: ${RED}❌ Not responding${NC}"
        fi
    fi
}

# Function to generate performance report
generate_report() {
    echo -e "\n${YELLOW}📊 Generating Performance Report${NC}"
    echo "=================================="
    
    local report_file="performance_report_$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$report_file" << EOF
RentHub Performance Test Report
Generated: $(date)
==============================

Test Configuration:
- Base URL: $BASE_URL
- Test Iterations: $TEST_ITERATIONS
- Concurrent Requests: $CONCURRENT_REQUESTS

System Status:
$(check_system_resources)

Recommendations:
1. If authentication response time > 1000ms, check database indexes and caching
2. If payment processing is slow, verify connection pooling is enabled
3. Monitor PHP-FPM and MySQL resource usage during peak loads
4. Consider implementing Redis clustering for high-traffic scenarios

Next Steps:
- Review Laravel logs for slow queries
- Check database connection pool statistics
- Monitor server resource utilization
- Consider implementing CDN for static assets

EOF
    
    echo -e "Performance report saved to: ${GREEN}$report_file${NC}"
}

# Main execution
main() {
    echo "Starting performance tests..."
    
    # Check if services are running first
    check_system_resources
    
    # Run performance tests
    test_auth_performance
    test_payment_performance
    test_database_performance
    test_concurrent_performance
    
    # Generate report
    generate_report
    
    echo -e "\n${GREEN}✅ Performance testing completed!${NC}"
    echo "Check the report file for detailed results and recommendations."
}

# Run main function
main "$@"
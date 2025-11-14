#!/bin/bash

# Production Debugging Script for RentHub
# This script helps identify and resolve issues in production deployment

echo "🔧 RentHub Production Debugging Script"
echo "======================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
API_URL="${API_URL:-https://api.yourdomain.com}"
FRONTEND_URL="${FRONTEND_URL:-https://yourdomain.com}"
HEALTH_CHECK_URL="${API_URL}/health/production"
LOGS_URL="${API_URL}/health/production/logs"

# Function to make API requests
api_request() {
    local endpoint=$1
    local method=${2:-GET}
    local data=$3
    
    echo -e "${BLUE}Testing: $endpoint${NC}"
    
    if [ "$method" = "POST" ]; then
        response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X POST "$API_URL$endpoint" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d "$data" 2>/dev/null || echo -e "\nHTTP_CODE:000")
    else
        response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X GET "$API_URL$endpoint" \
            -H "Accept: application/json" 2>/dev/null || echo -e "\nHTTP_CODE:000")
    fi
    
    http_code=$(echo "$response" | grep "HTTP_CODE:" | cut -d: -f2)
    body=$(echo "$response" | sed -n '1,/HTTP_CODE:/p' | sed '$d')
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ]; then
        echo -e "${GREEN}✅ Success (HTTP $http_code)${NC}"
        return 0
    elif [ "$http_code" = "000" ]; then
        echo -e "${RED}❌ Connection failed${NC}"
        return 1
    else
        echo -e "${RED}❌ Failed (HTTP $http_code)${NC}"
        echo "Response: $body"
        return 1
    fi
}

# Function to check health status
check_health() {
    echo -e "\n${YELLOW}🏥 Checking System Health${NC}"
    echo "=========================="
    
    echo -e "${BLUE}Fetching health status...${NC}"
    health_response=$(curl -s "$HEALTH_CHECK_URL" 2>/dev/null)
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Health check endpoint not responding${NC}"
        return 1
    fi
    
    # Parse health response (basic parsing)
    if echo "$health_response" | grep -q '"status":"healthy"'; then
        echo -e "${GREEN}✅ System is healthy${NC}"
    elif echo "$health_response" | grep -q '"status":"warning"'; then
        echo -e "${YELLOW}⚠️  System has warnings${NC}"
    else
        echo -e "${RED}❌ System has errors${NC}"
    fi
    
    # Show key metrics
    if echo "$health_response" | grep -q '"database"'; then
        db_status=$(echo "$health_response" | grep -o '"database":{[^}]*}' | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
        echo -e "Database: $([ "$db_status" = "healthy" ] && echo -e "${GREEN}✅${NC}" || echo -e "${RED}❌${NC}") $db_status"
    fi
    
    if echo "$health_response" | grep -q '"cache"'; then
        cache_status=$(echo "$health_response" | grep -o '"cache":{[^}]*}' | grep -o '"status":"[^"]*"' | cut -d'"' -f4)
        echo -e "Cache: $([ "$cache_status" = "healthy" ] && echo -e "${GREEN}✅${NC}" || echo -e "${RED}❌${NC}") $cache_status"
    fi
    
    # Save full health report
    echo "$health_response" > health_report_$(date +%Y%m%d_%H%M%S).json
    echo -e "${BLUE}Full health report saved to health_report_*.json${NC}"
}

# Function to check logs
check_logs() {
    echo -e "\n${YELLOW}📋 Checking Recent Logs${NC}"
    echo "======================="
    
    echo -e "${BLUE}Fetching recent error logs...${NC}"
    logs_response=$(curl -s "$LOGS_URL?level=error&lines=10" 2>/dev/null)
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Logs endpoint not responding${NC}"
        return 1
    fi
    
    # Check if there are errors
    if echo "$logs_response" | grep -q '"logs":\['; then
        error_count=$(echo "$logs_response" | grep -o '"logs":\[' | wc -l)
        if [ $error_count -gt 0 ]; then
            echo -e "${RED}⚠️  Found errors in logs${NC}"
            echo "$logs_response" | python3 -m json.tool 2>/dev/null | head -20
        else
            echo -e "${GREEN}✅ No recent errors in logs${NC}"
        fi
    else
        echo -e "${GREEN}✅ No logs found${NC}"
    fi
}

# Function to test API endpoints
test_api_endpoints() {
    echo -e "\n${YELLOW}🚀 Testing API Endpoints${NC}"
    echo "========================="
    
    # Basic endpoints
    echo -e "\n${BLUE}Testing basic endpoints:${NC}"
    api_request "/health"
    api_request "/health/status"
    api_request "/metrics"
    
    # Authentication endpoints
    echo -e "\n${BLUE}Testing authentication endpoints:${NC}"
    api_request "/login" "POST" '{"email":"test@example.com","password":"wrong"}'
    
    # Payment endpoints (without auth - should fail gracefully)
    echo -e "\n${BLUE}Testing payment endpoints:${NC}"
    api_request "/optimized/payments"
    api_request "/optimized/pool/payments"
}

# Function to check environment configuration
check_environment() {
    echo -e "\n${YELLOW}⚙️  Checking Environment Configuration${NC}"
    echo "====================================="
    
    echo -e "${BLUE}Environment variables to check:${NC}"
    echo "- APP_ENV: $(echo $APP_ENV 2>/dev/null || echo 'Not set')"
    echo "- APP_DEBUG: $(echo $APP_DEBUG 2>/dev/null || echo 'Not set')"
    echo "- DB_CONNECTION: $(echo $DB_CONNECTION 2>/dev/null || echo 'Not set')"
    echo "- CACHE_DRIVER: $(echo $CACHE_DRIVER 2>/dev/null || echo 'Not set')"
    echo "- QUEUE_CONNECTION: $(echo $QUEUE_CONNECTION 2>/dev/null || echo 'Not set')"
    echo "- SESSION_DRIVER: $(echo $SESSION_DRIVER 2>/dev/null || echo 'Not set')"
    
    # Check for common misconfigurations
    if [ "$APP_ENV" = "production" ] && [ "$APP_DEBUG" = "true" ]; then
        echo -e "${RED}⚠️  WARNING: APP_DEBUG is true in production${NC}"
    fi
    
    if [ -z "$DB_CONNECTION" ]; then
        echo -e "${RED}❌ ERROR: DB_CONNECTION not set${NC}"
    fi
}

# Function to check file permissions
check_permissions() {
    echo -e "\n${YELLOW}🔒 Checking File Permissions${NC}"
    echo "==========================="
    
    # Directories that need to be writable
    directories=(
        "storage"
        "storage/logs"
        "storage/app"
        "storage/framework"
        "storage/framework/cache"
        "storage/framework/sessions"
        "storage/framework/views"
        "bootstrap/cache"
    )
    
    for dir in "${directories[@]}"; do
        if [ -d "$dir" ]; then
            if [ -w "$dir" ]; then
                echo -e "${GREEN}✅ $dir is writable${NC}"
            else
                echo -e "${RED}❌ $dir is not writable${NC}"
            fi
        else
            echo -e "${RED}❌ $dir does not exist${NC}"
        fi
    done
}

# Function to check database connectivity
check_database() {
    echo -e "\n${YELLOW}🗄️  Checking Database Connectivity${NC}"
    echo "==================================="
    
    # This would need actual database connection test
    # For now, we'll check via API
    echo -e "${BLUE}Testing database via API...${NC}"
    api_request "/health/readiness"
}

# Function to generate debug report
generate_debug_report() {
    echo -e "\n${YELLOW}📊 Generating Debug Report${NC}"
    echo "============================="
    
    report_file="debug_report_$(date +%Y%m%d_%H%M%S).txt"
    
    cat > "$report_file" << EOF
RentHub Production Debug Report
Generated: $(date)
================================

Environment:
- API URL: $API_URL
- Frontend URL: $FRONTEND_URL
- Environment: $(echo $APP_ENV 2>/dev/null || echo 'Not set')
- Debug Mode: $(echo $APP_DEBUG 2>/dev/null || echo 'Not set')

System Information:
$(uname -a)
PHP Version: $(php -v | head -1)
Composer Version: $(composer --version 2>/dev/null || echo 'Not installed')
Node Version: $(node --version 2>/dev/null || echo 'Not installed')
NPM Version: $(npm --version 2>/dev/null || echo 'Not installed')

Health Check Results:
$(curl -s "$HEALTH_CHECK_URL" 2>/dev/null | python3 -m json.tool 2>/dev/null || echo "Health check failed")

Recent Errors:
$(curl -s "$LOGS_URL?level=error&lines=5" 2>/dev/null | python3 -m json.tool 2>/dev/null | head -20 || echo "Log retrieval failed")

Recommendations:
1. Check environment variables configuration
2. Verify database connectivity
3. Review file permissions
4. Monitor error logs regularly
5. Test API endpoints functionality
6. Check SSL certificates if applicable
7. Verify queue workers are running
8. Monitor server resources (CPU, memory, disk)

Next Steps:
- Review the generated health report JSON files
- Check Laravel logs on the server
- Verify Forge/Vercel deployment settings
- Test critical user workflows
- Monitor performance metrics
EOF
    
    echo -e "${GREEN}✅ Debug report saved to: $report_file${NC}"
}

# Function to check specific issues based on deployment
check_deployment_issues() {
    echo -e "\n${YELLOW}🔍 Checking Common Deployment Issues${NC}"
    echo "========================================"
    
    # Laravel Forge specific checks
    echo -e "${BLUE}Laravel Forge specific checks:${NC}"
    echo "- Check deployment script output in Forge"
    echo "- Verify environment variables in Forge panel"
    echo "- Check queue workers status in Forge"
    echo "- Review scheduled tasks configuration"
    echo "- Verify SSL certificate status"
    
    # Vercel specific checks
    echo -e "\n${BLUE}Vercel specific checks:${NC}"
    echo "- Check build logs in Vercel dashboard"
    echo "- Verify environment variables in Vercel"
    echo "- Review API endpoint configuration"
    echo "- Check for build errors or warnings"
    echo "- Verify frontend API URL configuration"
    
    # General checks
    echo -e "\n${BLUE}General deployment checks:${NC}"
    echo "- CORS configuration between frontend and backend"
    echo "- Database connection from production server"
    echo "- Redis/cache server connectivity"
    echo "- File upload permissions and limits"
    echo "- Email service configuration"
    echo "- Payment gateway integration"
}

# Main execution
main() {
    echo "Starting production debugging..."
    
    # Run all checks
    check_environment
    check_permissions
    check_database
    check_health
    check_logs
    test_api_endpoints
    check_deployment_issues
    
    # Generate comprehensive report
    generate_debug_report
    
    echo -e "\n${GREEN}✅ Debugging completed!${NC}"
    echo "Check the generated files and follow the recommendations above."
    echo ""
    echo -e "${YELLOW}Quick commands for further debugging:${NC}"
    echo "- View Laravel logs: tail -f storage/logs/laravel.log"
    echo "- Check PHP errors: tail -f /var/log/php-error.log"
    echo "- Test API manually: curl $API_URL/health"
    echo "- Check disk space: df -h"
    echo "- Check memory: free -h"
    echo "- Check processes: htop or top"
}

# Run main function
main "$@"
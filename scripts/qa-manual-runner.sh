#!/bin/bash

# RentHub QA Automation - Manual Test Runner
# This script allows manual execution of QA automation components

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Main menu
show_menu() {
    clear
    echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
    echo -e "${BLUE}║  RentHub QA Automation Test Runner    ║${NC}"
    echo -e "${BLUE}╔════════════════════════════════════════╗${NC}"
    echo ""
    echo "1) Run All Tests"
    echo "2) Run E2E Tests Only"
    echo "3) Run API Health Check"
    echo "4) Run Performance Audit"
    echo "5) Run Security Scan"
    echo "6) Check Code Quality"
    echo "7) Check Dependencies"
    echo "8) View QA Status"
    echo "9) Generate Health Report"
    echo "0) Exit"
    echo ""
    read -p "Select an option: " choice
}

# Test functions
run_all_tests() {
    print_header "Running All Tests"
    
    print_info "This will run E2E, API, performance, and security tests..."
    read -p "Continue? (y/n): " confirm
    
    if [ "$confirm" = "y" ]; then
        run_e2e_tests
        run_api_health_check
        run_performance_audit
        run_security_scan
        print_success "All tests completed!"
    fi
}

run_e2e_tests() {
    print_header "Running E2E Tests"
    
    cd frontend || exit
    
    print_info "Installing dependencies..."
    npm ci
    
    print_info "Installing Playwright browsers..."
    npx playwright install --with-deps
    
    print_info "Running E2E tests..."
    npm run e2e || print_error "E2E tests failed"
    
    cd ..
}

run_api_health_check() {
    print_header "Running API Health Check"
    
    print_info "Checking backend API health..."
    
    # Check if backend is running
    if ! curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
        print_warning "Backend is not running. Starting it now..."
        
        cd backend || exit
        
        # Setup test environment if needed
        if [ ! -f .env.testing ]; then
            cp .env.example .env.testing
            php artisan key:generate --env=testing
        fi
        
        # Start server in background
        php artisan serve --env=testing --port=8000 &
        SERVER_PID=$!
        echo $SERVER_PID > /tmp/renthub-server.pid
        
        sleep 5
        cd ..
    fi
    
    # Test health endpoint
    print_info "Testing health endpoint..."
    response=$(curl -s -w "\n%{http_code}" http://localhost:8000/api/health)
    status=$(echo "$response" | tail -n1)
    
    if [ "$status" = "200" ]; then
        print_success "Health endpoint: OK"
    else
        print_error "Health endpoint: FAILED ($status)"
    fi
    
    # Test other critical endpoints
    print_info "Testing critical endpoints..."
    
    endpoints=(
        "/api/v1/properties"
        "/api/v1/amenities"
    )
    
    for endpoint in "${endpoints[@]}"; do
        status=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000$endpoint")
        if [ "$status" = "200" ] || [ "$status" = "401" ]; then
            print_success "$endpoint: OK ($status)"
        else
            print_error "$endpoint: FAILED ($status)"
        fi
    done
}

run_performance_audit() {
    print_header "Running Performance Audit"
    
    cd frontend || exit
    
    print_info "Installing dependencies..."
    npm ci
    
    print_info "Building application..."
    npm run build
    
    print_info "Analyzing build size..."
    echo ""
    echo "Total build size:"
    du -sh .next
    echo ""
    echo "Static assets size:"
    du -sh .next/static
    echo ""
    echo "Largest bundles:"
    find .next -name "*.js" -type f -exec du -h {} + | sort -rh | head -10
    
    cd ..
}

run_security_scan() {
    print_header "Running Security Scan"
    
    print_info "Running npm audit..."
    cd frontend || exit
    npm audit || print_warning "Vulnerabilities found in frontend dependencies"
    cd ..
    
    print_info "Running composer audit..."
    cd backend || exit
    composer audit || print_warning "Vulnerabilities found in backend dependencies"
    cd ..
    
    print_info "Checking for secrets..."
    if command -v trufflehog &> /dev/null; then
        trufflehog filesystem . --json || print_warning "Potential secrets detected"
    else
        print_warning "TruffleHog not installed. Skipping secret detection."
        print_info "Install with: brew install trufflehog (macOS) or download from GitHub"
    fi
}

check_code_quality() {
    print_header "Checking Code Quality"
    
    # Frontend
    print_info "Checking frontend code quality..."
    cd frontend || exit
    
    print_info "Running ESLint..."
    npm run lint || print_error "ESLint found issues"
    
    print_info "Running TypeScript check..."
    npm run type-check || print_error "TypeScript errors found"
    
    cd ..
    
    # Backend
    print_info "Checking backend code quality..."
    cd backend || exit
    
    if [ -f vendor/bin/pint ]; then
        print_info "Running Laravel Pint..."
        vendor/bin/pint --test || print_error "Pint found issues"
    fi
    
    if [ -f vendor/bin/phpstan ]; then
        print_info "Running PHPStan..."
        vendor/bin/phpstan analyse --memory-limit=2G || print_error "PHPStan found issues"
    fi
    
    cd ..
}

check_dependencies() {
    print_header "Checking Dependencies"
    
    # Frontend
    print_info "Frontend outdated packages:"
    cd frontend || exit
    npm outdated || true
    cd ..
    
    echo ""
    
    # Backend
    print_info "Backend outdated packages:"
    cd backend || exit
    composer outdated --direct || true
    cd ..
}

view_qa_status() {
    print_header "QA Status"
    
    if [ -f QA_STATUS.md ]; then
        cat QA_STATUS.md
    else
        print_warning "QA_STATUS.md not found. Run automated workflows to generate it."
    fi
}

generate_health_report() {
    print_header "Generating Health Report"
    
    TIMESTAMP=$(date -u +"%Y-%m-%d %H:%M:%S UTC")
    REPORT_FILE="health-report-$(date +%Y%m%d-%H%M%S).md"
    
    {
        echo "# RentHub Health Report"
        echo ""
        echo "**Generated:** $TIMESTAMP"
        echo ""
        
        echo "## System Status"
        echo ""
        
        # Check if services are running
        if curl -s http://localhost:3000 > /dev/null 2>&1; then
            echo "- Frontend: 🟢 Running"
        else
            echo "- Frontend: 🔴 Not Running"
        fi
        
        if curl -s http://localhost:8000 > /dev/null 2>&1; then
            echo "- Backend: 🟢 Running"
        else
            echo "- Backend: 🔴 Not Running"
        fi
        
        echo ""
        echo "## Dependency Status"
        echo ""
        echo "### Frontend"
        echo "\`\`\`"
        cd frontend && npm outdated || true
        cd ..
        echo "\`\`\`"
        
        echo ""
        echo "### Backend"
        echo "\`\`\`"
        cd backend && composer outdated --direct || true
        cd ..
        echo "\`\`\`"
        
        echo ""
        echo "## Test Coverage"
        echo ""
        echo "Run automated tests to generate coverage reports."
        
        echo ""
        echo "---"
        echo "*Generated by RentHub QA Automation Test Runner*"
        
    } > "$REPORT_FILE"
    
    print_success "Health report generated: $REPORT_FILE"
    
    # Show preview
    cat "$REPORT_FILE"
}

cleanup() {
    print_info "Cleaning up..."
    
    # Stop backend server if we started it
    if [ -f /tmp/renthub-server.pid ]; then
        kill $(cat /tmp/renthub-server.pid) 2>/dev/null || true
        rm /tmp/renthub-server.pid
    fi
}

# Trap cleanup on exit
trap cleanup EXIT

# Main loop
while true; do
    show_menu
    
    case $choice in
        1) run_all_tests ;;
        2) run_e2e_tests ;;
        3) run_api_health_check ;;
        4) run_performance_audit ;;
        5) run_security_scan ;;
        6) check_code_quality ;;
        7) check_dependencies ;;
        8) view_qa_status ;;
        9) generate_health_report ;;
        0) 
            print_info "Exiting..."
            exit 0
            ;;
        *)
            print_error "Invalid option"
            sleep 2
            ;;
    esac
    
    echo ""
    read -p "Press Enter to continue..."
done

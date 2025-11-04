<#
API Endpoint Testing Script
Tests all implemented API endpoints
#>

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🔌 RentHub API Endpoint Testing" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$API_BASE = "http://localhost:8000/api"
$ErrorCount = 0
$SuccessCount = 0

function Test-Endpoint {
    param(
        [string]$Method,
        [string]$Endpoint,
        [string]$Description
    )
    
    Write-Host "Testing: " -NoNewline
    Write-Host "$Method $Endpoint" -ForegroundColor Yellow
    Write-Host "  Description: $Description" -ForegroundColor Gray
    
    try {
        $url = "$API_BASE$Endpoint"
        
        if ($Method -eq "GET") {
            $response = Invoke-WebRequest -Uri $url -Method Get -UseBasicParsing -ErrorAction Stop
        } elseif ($Method -eq "POST") {
            $response = Invoke-WebRequest -Uri $url -Method Post -UseBasicParsing -ErrorAction SilentlyContinue
        }
        
        if ($response.StatusCode -in @(200, 201, 401, 422)) {
            Write-Host "  ✅ Endpoint exists (Status: $($response.StatusCode))" -ForegroundColor Green
            $script:SuccessCount++
        } else {
            Write-Host "  ❌ Unexpected status: $($response.StatusCode)" -ForegroundColor Red
            $script:ErrorCount++
        }
    } catch {
        if ($_.Exception.Message -match "404") {
            Write-Host "  ❌ Endpoint not found (404)" -ForegroundColor Red
            $script:ErrorCount++
        } elseif ($_.Exception.Message -match "401") {
            Write-Host "  ✅ Endpoint exists (Requires auth)" -ForegroundColor Green
            $script:SuccessCount++
        } else {
            Write-Host "  ℹ️  Status: $($_.Exception.Message)" -ForegroundColor Yellow
            $script:SuccessCount++
        }
    }
    Write-Host ""
}

Write-Host "Checking if Laravel backend is running..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000" -UseBasicParsing -TimeoutSec 5
    Write-Host "✅ Backend is running!" -ForegroundColor Green
} catch {
    Write-Host "❌ Backend is not running on http://localhost:8000" -ForegroundColor Red
    Write-Host "Please start Laravel with: php artisan serve" -ForegroundColor Yellow
    exit 1
}
Write-Host ""

# Authentication Endpoints
Write-Host "=== Authentication ===" -ForegroundColor Magenta
Test-Endpoint "POST" "/register" "User registration"
Test-Endpoint "POST" "/login" "User login"
Test-Endpoint "POST" "/logout" "User logout"
Test-Endpoint "GET" "/user" "Get authenticated user"
Test-Endpoint "POST" "/forgot-password" "Forgot password"

# Property Endpoints
Write-Host "=== Properties ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/properties" "List all properties"
Test-Endpoint "GET" "/properties/1" "Get specific property"
Test-Endpoint "POST" "/properties" "Create property"
Test-Endpoint "GET" "/properties/search" "Search properties"
Test-Endpoint "GET" "/properties/featured" "Featured properties"

# Booking Endpoints
Write-Host "=== Bookings ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/bookings" "List bookings"
Test-Endpoint "POST" "/bookings" "Create booking"
Test-Endpoint "GET" "/bookings/1" "Get specific booking"
Test-Endpoint "POST" "/bookings/1/cancel" "Cancel booking"
Test-Endpoint "GET" "/bookings/availability" "Check availability"

# Payment Endpoints
Write-Host "=== Payments ===" -ForegroundColor Magenta
Test-Endpoint "POST" "/payments" "Create payment"
Test-Endpoint "GET" "/payments/1" "Get payment details"
Test-Endpoint "POST" "/payments/webhook" "Payment webhook"

# Review Endpoints
Write-Host "=== Reviews ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/reviews" "List reviews"
Test-Endpoint "POST" "/reviews" "Create review"
Test-Endpoint "GET" "/properties/1/reviews" "Property reviews"

# Messaging Endpoints
Write-Host "=== Messaging ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/messages" "List messages"
Test-Endpoint "POST" "/messages" "Send message"
Test-Endpoint "GET" "/conversations" "List conversations"

# Wishlist Endpoints
Write-Host "=== Wishlist ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/wishlist" "List wishlist items"
Test-Endpoint "POST" "/wishlist" "Add to wishlist"
Test-Endpoint "DELETE" "/wishlist/1" "Remove from wishlist"

# Advanced Features
Write-Host "=== Advanced Features ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/smart-pricing/1" "Smart pricing"
Test-Endpoint "GET" "/saved-searches" "Saved searches"
Test-Endpoint "POST" "/property-comparison" "Compare properties"
Test-Endpoint "GET" "/insurance/quotes" "Insurance quotes"
Test-Endpoint "GET" "/smart-locks" "Smart lock integration"
Test-Endpoint "GET" "/cleaning-schedules" "Cleaning schedules"
Test-Endpoint "GET" "/maintenance-requests" "Maintenance requests"
Test-Endpoint "GET" "/guest-screening/1" "Guest screening"
Test-Endpoint "GET" "/loyalty-points" "Loyalty points"
Test-Endpoint "GET" "/referrals" "Referral program"
Test-Endpoint "GET" "/dashboard/analytics" "Dashboard analytics"

# Admin Endpoints
Write-Host "=== Admin ===" -ForegroundColor Magenta
Test-Endpoint "GET" "/admin/users" "Admin: List users"
Test-Endpoint "GET" "/admin/properties" "Admin: List properties"
Test-Endpoint "GET" "/admin/bookings" "Admin: List bookings"
Test-Endpoint "GET" "/admin/statistics" "Admin: Statistics"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 RESULTS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Endpoints Tested: $($SuccessCount + $ErrorCount)" -ForegroundColor White
Write-Host "Available:        $SuccessCount" -ForegroundColor Green
Write-Host "Missing:          $ErrorCount" -ForegroundColor Red
Write-Host ""

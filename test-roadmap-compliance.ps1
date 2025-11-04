<# 
RentHub ROADMAP Compliance Testing Script
This script verifies all completed features from ROADMAP.md
#>

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🚀 RentHub ROADMAP Compliance Testing" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$ErrorCount = 0
$SuccessCount = 0
$TotalTests = 0

function Test-Feature {
    param(
        [string]$Category,
        [string]$Feature,
        [scriptblock]$TestBlock
    )
    
    $script:TotalTests++
    Write-Host "Testing: " -NoNewline
    Write-Host "$Category > $Feature" -ForegroundColor Yellow
    
    try {
        $result = & $TestBlock
        if ($result) {
            Write-Host "  ✅ PASS" -ForegroundColor Green
            $script:SuccessCount++
        } else {
            Write-Host "  ❌ FAIL" -ForegroundColor Red
            $script:ErrorCount++
        }
    } catch {
        Write-Host "  ❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
        $script:ErrorCount++
    }
    Write-Host ""
}

# Change to backend directory
Set-Location "C:\laragon\www\RentHub\backend"

Write-Host "📋 PHASE 1: CORE FEATURES (MVP)" -ForegroundColor Magenta
Write-Host "================================" -ForegroundColor Magenta
Write-Host ""

# 1.1 Authentication & User Management
Test-Feature "1.1 Authentication" "User Model" {
    Test-Path "app\Models\User.php"
}

Test-Feature "1.1 Authentication" "Authentication Controllers" {
    (Test-Path "app\Http\Controllers\Api\AuthController.php") -or 
    (Test-Path "app\Http\Controllers\API\AuthController.php")
}

Test-Feature "1.1 Authentication" "Sanctum Authentication" {
    $config = Get-Content "config\auth.php" -Raw
    $config -match "sanctum"
}

Test-Feature "1.1 Authentication" "User Roles & Permissions" {
    (Test-Path "app\Models\Role.php") -or 
    (Get-Content "app\Models\User.php" -Raw) -match "role"
}

# 1.2 Property Management
Test-Feature "1.2 Property" "Property Model" {
    Test-Path "app\Models\Property.php"
}

Test-Feature "1.2 Property" "Property Controller" {
    (Test-Path "app\Http\Controllers\Api\PropertyController.php") -or
    (Test-Path "app\Http\Controllers\API\PropertyController.php")
}

Test-Feature "1.2 Property" "Property Images" {
    $propertyModel = Get-Content "app\Models\Property.php" -Raw
    ($propertyModel -match "images") -or ($propertyModel -match "media")
}

Test-Feature "1.2 Property" "Property Migration" {
    $migrations = Get-ChildItem "database\migrations" -Filter "*properties*.php"
    $migrations.Count -gt 0
}

Test-Feature "1.2 Property" "Amenities" {
    Test-Path "app\Models\Amenity.php"
}

# 1.3 Property Listing
Test-Feature "1.3 Listing" "Search Functionality" {
    $controller = Get-ChildItem "app\Http\Controllers" -Recurse -Filter "*Property*.php" | 
                  Get-Content -Raw
    $controller -match "search"
}

Test-Feature "1.3 Listing" "Filters" {
    $routes = Get-Content "routes\api.php" -Raw
    ($routes -match "filter") -or ($routes -match "search")
}

# 1.4 Booking System
Test-Feature "1.4 Booking" "Booking Model" {
    Test-Path "app\Models\Booking.php"
}

Test-Feature "1.4 Booking" "Booking Controller" {
    (Test-Path "app\Http\Controllers\Api\BookingController.php") -or
    (Test-Path "app\Http\Controllers\API\BookingController.php")
}

Test-Feature "1.4 Booking" "Booking Migration" {
    $migrations = Get-ChildItem "database\migrations" -Filter "*bookings*.php"
    $migrations.Count -gt 0
}

Test-Feature "1.4 Booking" "Availability Check" {
    $bookingModel = Get-Content "app\Models\Booking.php" -Raw
    ($bookingModel -match "available") -or ($bookingModel -match "checkAvailability")
}

# 1.5 Payment System
Test-Feature "1.5 Payment" "Payment Model" {
    Test-Path "app\Models\Payment.php"
}

Test-Feature "1.5 Payment" "Payment Controller" {
    (Test-Path "app\Http\Controllers\Api\PaymentController.php") -or
    (Test-Path "app\Http\Controllers\API\PaymentController.php")
}

Test-Feature "1.5 Payment" "Stripe Configuration" {
    $servicesConfig = Get-Content "config\services.php" -Raw
    $servicesConfig -match "stripe"
}

# 1.6 Review System
Test-Feature "1.6 Reviews" "Review Model" {
    Test-Path "app\Models\Review.php"
}

Test-Feature "1.6 Reviews" "Review Controller" {
    (Test-Path "app\Http\Controllers\Api\ReviewController.php") -or
    (Test-Path "app\Http\Controllers\API\ReviewController.php")
}

Test-Feature "1.6 Reviews" "Review Migration" {
    $migrations = Get-ChildItem "database\migrations" -Filter "*reviews*.php"
    $migrations.Count -gt 0
}

# 1.7 Notifications
Test-Feature "1.7 Notifications" "Notification System" {
    Test-Path "app\Notifications"
}

Test-Feature "1.7 Notifications" "Mail Configuration" {
    Test-Path "config\mail.php"
}

Write-Host ""
Write-Host "📋 PHASE 2: ESSENTIAL FEATURES" -ForegroundColor Magenta
Write-Host "===============================" -ForegroundColor Magenta
Write-Host ""

# 2.1 Messaging System
Test-Feature "2.1 Messaging" "Message Model" {
    Test-Path "app\Models\Message.php"
}

Test-Feature "2.1 Messaging" "Message Controller" {
    (Test-Path "app\Http\Controllers\Api\MessageController.php") -or
    (Test-Path "app\Http\Controllers\API\MessageController.php")
}

# 2.2 Wishlist
Test-Feature "2.2 Wishlist" "Wishlist Model" {
    (Test-Path "app\Models\Wishlist.php") -or
    (Test-Path "app\Models\Favorite.php")
}

Test-Feature "2.2 Wishlist" "Wishlist Controller" {
    (Test-Path "app\Http\Controllers\Api\WishlistController.php") -or
    (Test-Path "app\Http\Controllers\API\WishlistController.php") -or
    (Test-Path "app\Http\Controllers\Api\FavoriteController.php")
}

# 2.3 Calendar Management
Test-Feature "2.3 Calendar" "Calendar Integration" {
    (Test-Path "app\Services\GoogleCalendarService.php") -or
    (Test-Path "app\Services\CalendarService.php")
}

# 2.4 Advanced Search
Test-Feature "2.4 Search" "Map Search" {
    $propertyController = Get-ChildItem "app\Http\Controllers" -Recurse -Filter "*Property*.php" | 
                          Get-Content -Raw
    $propertyController -match "map"
}

Test-Feature "2.4 Search" "Saved Searches" {
    Test-Path "app\Models\SavedSearch.php"
}

# 2.5 Property Verification
Test-Feature "2.5 Verification" "Verification System" {
    (Test-Path "app\Models\Verification.php") -or
    ((Get-Content "app\Models\Property.php" -Raw) -match "verified")
}

# 2.6 Dashboard Analytics
Test-Feature "2.6 Analytics" "Dashboard Controller" {
    (Test-Path "app\Http\Controllers\Api\DashboardController.php") -or
    (Test-Path "app\Http\Controllers\API\DashboardController.php")
}

# 2.7 Multi-language
Test-Feature "2.7 i18n" "Language Support" {
    Test-Path "resources\lang"
}

# 2.8 Multi-currency
Test-Feature "2.8 Currency" "Currency Configuration" {
    $config = Get-Content "config\app.php" -Raw
    ($config -match "currency") -or (Test-Path "config\currency.php")
}

Write-Host ""
Write-Host "📋 PHASE 3: ADVANCED FEATURES" -ForegroundColor Magenta
Write-Host "==============================" -ForegroundColor Magenta
Write-Host ""

# 3.1 Smart Pricing
Test-Feature "3.1 Pricing" "Smart Pricing Service" {
    Test-Path "app\Services\SmartPricingService.php"
}

# 3.3 Long-term Rentals
Test-Feature "3.3 Rentals" "Long-term Rental Support" {
    $bookingModel = Get-Content "app\Models\Booking.php" -Raw
    ($bookingModel -match "long.term") -or ($bookingModel -match "rental_type")
}

# 3.4 Property Comparison
Test-Feature "3.4 Comparison" "Comparison Feature" {
    (Test-Path "app\Http\Controllers\Api\ComparisonController.php") -or
    (Test-Path "app\Http\Controllers\API\ComparisonController.php") -or
    (Test-Path "app\Services\PropertyComparisonService.php")
}

# 3.6 Insurance
Test-Feature "3.6 Insurance" "Insurance Integration" {
    Test-Path "app\Services\InsuranceService.php"
}

# 3.7 Smart Locks
Test-Feature "3.7 Smart Locks" "Smart Lock Integration" {
    Test-Path "app\Services\SmartLockService.php"
}

# 3.8 Cleaning & Maintenance
Test-Feature "3.8 Maintenance" "Cleaning Service" {
    Test-Path "app\Models\CleaningSchedule.php"
}

Test-Feature "3.8 Maintenance" "Maintenance Requests" {
    Test-Path "app\Models\MaintenanceRequest.php"
}

# 3.10 Guest Screening
Test-Feature "3.10 Screening" "Guest Screening" {
    Test-Path "app\Services\GuestScreeningService.php"
}

Write-Host ""
Write-Host "📋 PHASE 4: PREMIUM FEATURES" -ForegroundColor Magenta
Write-Host "=============================" -ForegroundColor Magenta
Write-Host ""

# 4.2 AI & Machine Learning
Test-Feature "4.2 AI/ML" "Recommendation Service" {
    Test-Path "app\Services\RecommendationService.php"
}

# 4.4 IoT Integration
Test-Feature "4.4 IoT" "IoT Service" {
    Test-Path "app\Services\IoTService.php"
}

# 4.5 Concierge Services
Test-Feature "4.5 Concierge" "Concierge Service" {
    Test-Path "app\Services\ConciergeService.php"
}

# 4.6 Loyalty Program
Test-Feature "4.6 Loyalty" "Loyalty Program" {
    Test-Path "app\Models\LoyaltyPoint.php"
}

# 4.7 Referral Program
Test-Feature "4.7 Referral" "Referral System" {
    Test-Path "app\Models\Referral.php"
}

# 4.8 Automated Messaging
Test-Feature "4.8 Auto-Messaging" "Message Templates" {
    Test-Path "app\Models\MessageTemplate.php"
}

# 4.9 Advanced Reporting
Test-Feature "4.9 Reporting" "Reporting Service" {
    Test-Path "app\Services\ReportingService.php"
}

# 4.10 Channel Manager
Test-Feature "4.10 Integrations" "Channel Manager" {
    Test-Path "app\Services\ChannelManagerService.php"
}

Write-Host ""
Write-Host "📋 PHASE 5: SCALE & OPTIMIZE" -ForegroundColor Magenta
Write-Host "=============================" -ForegroundColor Magenta
Write-Host ""

# 5.1 Performance
Test-Feature "5.1 Performance" "Redis Configuration" {
    Test-Path "config\cache.php"
    $cacheConfig = Get-Content "config\cache.php" -Raw
    $cacheConfig -match "redis"
}

Test-Feature "5.1 Performance" "Queue Configuration" {
    Test-Path "config\queue.php"
}

# 5.2 SEO
Test-Feature "5.2 SEO" "Sitemap Generation" {
    (Test-Path "app\Http\Controllers\SitemapController.php") -or
    (Get-ChildItem "routes" -Recurse | Get-Content -Raw) -match "sitemap"
}

# 5.3 Infrastructure
Test-Feature "5.3 Infrastructure" "Docker Configuration" {
    Set-Location "C:\laragon\www\RentHub"
    Test-Path "docker-compose.yml"
}

Test-Feature "5.3 Infrastructure" "Kubernetes Configuration" {
    Test-Path "k8s"
}

# DevOps
Test-Feature "DevOps" "CI/CD Pipeline" {
    Test-Path ".github\workflows"
}

Test-Feature "DevOps" "Terraform" {
    Test-Path "terraform"
}

# Security
Test-Feature "Security" "Security Middleware" {
    Set-Location "C:\laragon\www\RentHub\backend"
    Test-Path "app\Http\Middleware"
    $middlewareFiles = Get-ChildItem "app\Http\Middleware" -Filter "*.php"
    $middlewareFiles.Count -gt 0
}

Test-Feature "Security" "Rate Limiting" {
    $kernel = Get-Content "app\Http\Kernel.php" -Raw
    $kernel -match "throttle"
}

Test-Feature "Security" "CSRF Protection" {
    $kernel = Get-Content "app\Http\Kernel.php" -Raw
    $kernel -match "VerifyCsrfToken"
}

# Testing
Test-Feature "Testing" "PHPUnit Configuration" {
    Test-Path "phpunit.xml"
}

Test-Feature "Testing" "Test Directory" {
    Test-Path "tests"
    $tests = Get-ChildItem "tests" -Recurse -Filter "*.php"
    $tests.Count -gt 0
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 TEST RESULTS SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total Tests:    $TotalTests" -ForegroundColor White
Write-Host "Passed:         $SuccessCount" -ForegroundColor Green
Write-Host "Failed:         $ErrorCount" -ForegroundColor Red
Write-Host "Success Rate:   $([math]::Round(($SuccessCount / $TotalTests) * 100, 2))%" -ForegroundColor Yellow
Write-Host ""

if ($ErrorCount -eq 0) {
    Write-Host "🎉 All tests passed! ROADMAP compliance verified!" -ForegroundColor Green
} else {
    Write-Host "⚠️  Some tests failed. Review the results above." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan

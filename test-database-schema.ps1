<#
Database Schema Verification Script
Verifies all tables and relationships
#>

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🗄️  RentHub Database Schema Testing" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Set-Location "C:\laragon\www\RentHub\backend"

$ErrorCount = 0
$SuccessCount = 0

function Test-Migration {
    param(
        [string]$Pattern,
        [string]$Description
    )
    
    Write-Host "Testing: $Description" -ForegroundColor Yellow
    
    $migrations = Get-ChildItem "database\migrations" -Filter "*$Pattern*.php" -ErrorAction SilentlyContinue
    
    if ($migrations.Count -gt 0) {
        Write-Host "  ✅ Found: " -NoNewline -ForegroundColor Green
        Write-Host "$($migrations.Count) migration(s)" -ForegroundColor White
        foreach ($migration in $migrations) {
            Write-Host "     - $($migration.Name)" -ForegroundColor Gray
        }
        $script:SuccessCount++
    } else {
        Write-Host "  ❌ No migrations found" -ForegroundColor Red
        $script:ErrorCount++
    }
    Write-Host ""
}

function Test-Model {
    param(
        [string]$ModelName,
        [string]$Description
    )
    
    Write-Host "Testing: $Description" -ForegroundColor Yellow
    
    $modelPath = "app\Models\$ModelName.php"
    
    if (Test-Path $modelPath) {
        $modelContent = Get-Content $modelPath -Raw
        Write-Host "  ✅ Model exists: $ModelName" -ForegroundColor Green
        
        # Check for relationships
        $relationships = @()
        if ($modelContent -match "hasMany") { $relationships += "hasMany" }
        if ($modelContent -match "belongsTo") { $relationships += "belongsTo" }
        if ($modelContent -match "hasOne") { $relationships += "hasOne" }
        if ($modelContent -match "belongsToMany") { $relationships += "belongsToMany" }
        
        if ($relationships.Count -gt 0) {
            Write-Host "     Relationships: $($relationships -join ', ')" -ForegroundColor Cyan
        }
        
        # Check for fillable
        if ($modelContent -match "fillable") {
            Write-Host "     ✓ Has fillable fields" -ForegroundColor Gray
        }
        
        $script:SuccessCount++
    } else {
        Write-Host "  ❌ Model not found: $ModelName" -ForegroundColor Red
        $script:ErrorCount++
    }
    Write-Host ""
}

Write-Host "📋 CORE TABLES" -ForegroundColor Magenta
Write-Host "===============" -ForegroundColor Magenta
Write-Host ""

Test-Migration "users" "Users table"
Test-Model "User" "User model"

Test-Migration "properties" "Properties table"
Test-Model "Property" "Property model"

Test-Migration "bookings" "Bookings table"
Test-Model "Booking" "Booking model"

Test-Migration "payments" "Payments table"
Test-Model "Payment" "Payment model"

Test-Migration "reviews" "Reviews table"
Test-Model "Review" "Review model"

Test-Migration "amenities" "Amenities table"
Test-Model "Amenity" "Amenity model"

Write-Host ""
Write-Host "📋 FEATURE TABLES" -ForegroundColor Magenta
Write-Host "==================" -ForegroundColor Magenta
Write-Host ""

Test-Migration "messages" "Messages table"
Test-Model "Message" "Message model"

Test-Migration "wishlist" "Wishlist table"
Test-Model "Wishlist" "Wishlist model"

Test-Migration "saved_search" "Saved searches table"
Test-Model "SavedSearch" "SavedSearch model"

Test-Migration "notification" "Notifications table"

Test-Migration "calendar" "Calendar table"

Test-Migration "maintenance" "Maintenance table"
Test-Model "MaintenanceRequest" "MaintenanceRequest model"

Test-Migration "cleaning" "Cleaning table"
Test-Model "CleaningSchedule" "CleaningSchedule model"

Test-Migration "referral" "Referrals table"
Test-Model "Referral" "Referral model"

Test-Migration "loyalty" "Loyalty points table"
Test-Model "LoyaltyPoint" "LoyaltyPoint model"

Test-Migration "template" "Message templates table"
Test-Model "MessageTemplate" "MessageTemplate model"

Write-Host ""
Write-Host "📋 PIVOT TABLES" -ForegroundColor Magenta
Write-Host "================" -ForegroundColor Magenta
Write-Host ""

Test-Migration "amenity_property" "Amenity-Property pivot"
Test-Migration "property_user" "Property-User pivot"

Write-Host ""
Write-Host "📋 CONTROLLERS" -ForegroundColor Magenta
Write-Host "===============" -ForegroundColor Magenta
Write-Host ""

$controllers = @(
    "AuthController",
    "PropertyController",
    "BookingController",
    "PaymentController",
    "ReviewController",
    "MessageController",
    "WishlistController",
    "DashboardController"
)

foreach ($controller in $controllers) {
    Write-Host "Testing: $controller" -ForegroundColor Yellow
    
    $found = $false
    $paths = @(
        "app\Http\Controllers\Api\$controller.php",
        "app\Http\Controllers\API\$controller.php",
        "app\Http\Controllers\$controller.php"
    )
    
    foreach ($path in $paths) {
        if (Test-Path $path) {
            Write-Host "  ✅ Found: $path" -ForegroundColor Green
            $script:SuccessCount++
            $found = $true
            break
        }
    }
    
    if (-not $found) {
        Write-Host "  ❌ Controller not found" -ForegroundColor Red
        $script:ErrorCount++
    }
    Write-Host ""
}

Write-Host ""
Write-Host "📋 API ROUTES" -ForegroundColor Magenta
Write-Host "==============" -ForegroundColor Magenta
Write-Host ""

if (Test-Path "routes\api.php") {
    $apiRoutes = Get-Content "routes\api.php" -Raw
    Write-Host "Analyzing API routes file..." -ForegroundColor Yellow
    Write-Host ""
    
    $routePatterns = @{
        "Authentication" = "register|login|logout"
        "Properties" = "properties"
        "Bookings" = "bookings"
        "Payments" = "payments"
        "Reviews" = "reviews"
        "Messages" = "messages"
        "Wishlist" = "wishlist|favorites"
        "Search" = "search|saved-searches"
        "Admin" = "admin"
    }
    
    foreach ($category in $routePatterns.Keys) {
        $pattern = $routePatterns[$category]
        if ($apiRoutes -match $pattern) {
            Write-Host "  ✅ $category routes found" -ForegroundColor Green
            $script:SuccessCount++
        } else {
            Write-Host "  ⚠️  $category routes not found" -ForegroundColor Yellow
        }
    }
} else {
    Write-Host "  ❌ api.php not found" -ForegroundColor Red
    $script:ErrorCount++
}

Write-Host ""
Write-Host "📋 SERVICES" -ForegroundColor Magenta
Write-Host "============" -ForegroundColor Magenta
Write-Host ""

$services = @(
    "GoogleCalendarService",
    "SmartPricingService",
    "InsuranceService",
    "SmartLockService",
    "GuestScreeningService",
    "RecommendationService",
    "IoTService",
    "ConciergeService",
    "ReportingService",
    "ChannelManagerService"
)

foreach ($service in $services) {
    Write-Host "Testing: $service" -ForegroundColor Yellow
    
    if (Test-Path "app\Services\$service.php") {
        Write-Host "  ✅ Service exists" -ForegroundColor Green
        $script:SuccessCount++
    } else {
        Write-Host "  ⚠️  Service not found (may not be implemented)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 RESULTS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Total Checks: $($SuccessCount + $ErrorCount)" -ForegroundColor White
Write-Host "Passed:       $SuccessCount" -ForegroundColor Green
Write-Host "Failed:       $ErrorCount" -ForegroundColor Red
Write-Host ""

if ($ErrorCount -eq 0) {
    Write-Host "🎉 All database schema checks passed!" -ForegroundColor Green
} else {
    Write-Host "⚠️  Some checks failed. Review above for details." -ForegroundColor Yellow
}

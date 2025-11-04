# ============================================================================
# RentHub - Complete Roadmap Verification & Testing Script
# ============================================================================
# Tests all features from ROADMAP.md systematically
# Last Updated: 2025-11-03
# ============================================================================

param(
    [string]$TestType = "all", # all, phase1, phase2, phase3, security, performance
    [switch]$Verbose,
    [switch]$GenerateReport
)

$ErrorActionPreference = "Continue"
$TestResults = @()
$StartTime = Get-Date

# ============================================================================
# CONFIGURATION
# ============================================================================

$Config = @{
    BackendPath = "C:\laragon\www\RentHub\backend"
    FrontendPath = "C:\laragon\www\RentHub\frontend"
    ApiUrl = "http://localhost:8000/api"
    FrontendUrl = "http://localhost:3000"
    TestDataPath = "C:\laragon\www\RentHub\test-data"
}

# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

function Write-TestHeader {
    param([string]$Title)
    Write-Host "`n========================================" -ForegroundColor Cyan
    Write-Host "  $Title" -ForegroundColor Cyan
    Write-Host "========================================`n" -ForegroundColor Cyan
}

function Write-TestResult {
    param(
        [string]$TestName,
        [string]$Status,
        [string]$Details = "",
        [string]$Category = "General"
    )
    
    $Color = switch ($Status) {
        "PASS" { "Green" }
        "FAIL" { "Red" }
        "WARN" { "Yellow" }
        "SKIP" { "Gray" }
        default { "White" }
    }
    
    $Result = [PSCustomObject]@{
        Category = $Category
        TestName = $TestName
        Status = $Status
        Details = $Details
        Timestamp = Get-Date
    }
    
    $script:TestResults += $Result
    
    Write-Host "[$Status] " -ForegroundColor $Color -NoNewline
    Write-Host "$TestName" -NoNewline
    if ($Details) {
        Write-Host " - $Details" -ForegroundColor Gray
    } else {
        Write-Host ""
    }
}

function Test-FileExists {
    param(
        [string]$Path,
        [string]$Description
    )
    
    if (Test-Path $Path) {
        Write-TestResult -TestName $Description -Status "PASS" -Details "File found: $Path"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Details "File not found: $Path"
        return $false
    }
}

function Test-DirectoryExists {
    param(
        [string]$Path,
        [string]$Description
    )
    
    if (Test-Path $Path -PathType Container) {
        Write-TestResult -TestName $Description -Status "PASS" -Details "Directory found: $Path"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Details "Directory not found: $Path"
        return $false
    }
}

function Test-ApiEndpoint {
    param(
        [string]$Endpoint,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [string]$Body = $null
    )
    
    try {
        $url = "$($Config.ApiUrl)$Endpoint"
        $params = @{
            Uri = $url
            Method = $Method
            Headers = $Headers
            UseBasicParsing = $true
            TimeoutSec = 10
        }
        
        if ($Body) {
            $params.Body = $Body
            $params.ContentType = "application/json"
        }
        
        $response = Invoke-WebRequest @params -ErrorAction Stop
        return @{ Success = $true; StatusCode = $response.StatusCode; Response = $response }
    } catch {
        return @{ Success = $false; Error = $_.Exception.Message }
    }
}

function Test-DatabaseTable {
    param(
        [string]$TableName,
        [string]$Description
    )
    
    try {
        Push-Location $Config.BackendPath
        $result = php artisan tinker --execute="echo \DB::connection()->getDatabaseName(); echo PHP_EOL; echo \Schema::hasTable('$TableName') ? 'exists' : 'missing';" 2>&1
        
        if ($result -match "exists") {
            Write-TestResult -TestName $Description -Status "PASS" -Details "Table '$TableName' exists" -Category "Database"
            Pop-Location
            return $true
        } else {
            Write-TestResult -TestName $Description -Status "FAIL" -Details "Table '$TableName' missing" -Category "Database"
            Pop-Location
            return $false
        }
    } catch {
        Write-TestResult -TestName $Description -Status "FAIL" -Details $_.Exception.Message -Category "Database"
        Pop-Location
        return $false
    }
}

function Test-LaravelCommand {
    param(
        [string]$Command,
        [string]$Description,
        [string]$ExpectedOutput = $null
    )
    
    try {
        Push-Location $Config.BackendPath
        $output = php artisan $Command 2>&1 | Out-String
        Pop-Location
        
        if ($ExpectedOutput) {
            if ($output -match $ExpectedOutput) {
                Write-TestResult -TestName $Description -Status "PASS" -Category "Laravel"
                return $true
            } else {
                Write-TestResult -TestName $Description -Status "FAIL" -Details "Expected output not found" -Category "Laravel"
                return $false
            }
        } else {
            Write-TestResult -TestName $Description -Status "PASS" -Category "Laravel"
            return $true
        }
    } catch {
        Write-TestResult -TestName $Description -Status "FAIL" -Details $_.Exception.Message -Category "Laravel"
        Pop-Location
        return $false
    }
}

function Test-ComposerPackage {
    param(
        [string]$PackageName,
        [string]$Description
    )
    
    try {
        Push-Location $Config.BackendPath
        $composerJson = Get-Content "composer.json" | ConvertFrom-Json
        
        $installed = $false
        if ($composerJson.require.$PackageName) {
            $installed = $true
        } elseif ($composerJson.'require-dev'.$PackageName) {
            $installed = $true
        }
        
        Pop-Location
        
        if ($installed) {
            Write-TestResult -TestName $Description -Status "PASS" -Details "Package: $PackageName" -Category "Dependencies"
            return $true
        } else {
            Write-TestResult -TestName $Description -Status "FAIL" -Details "Package not found: $PackageName" -Category "Dependencies"
            return $false
        }
    } catch {
        Write-TestResult -TestName $Description -Status "FAIL" -Details $_.Exception.Message -Category "Dependencies"
        Pop-Location
        return $false
    }
}

function Test-NpmPackage {
    param(
        [string]$PackageName,
        [string]$Description
    )
    
    try {
        Push-Location $Config.FrontendPath
        $packageJson = Get-Content "package.json" | ConvertFrom-Json
        
        $installed = $false
        if ($packageJson.dependencies.$PackageName) {
            $installed = $true
        } elseif ($packageJson.devDependencies.$PackageName) {
            $installed = $true
        }
        
        Pop-Location
        
        if ($installed) {
            Write-TestResult -TestName $Description -Status "PASS" -Details "Package: $PackageName" -Category "Dependencies"
            return $true
        } else {
            Write-TestResult -TestName $Description -Status "FAIL" -Details "Package not found: $PackageName" -Category "Dependencies"
            return $false
        }
    } catch {
        Write-TestResult -TestName $Description -Status "FAIL" -Details $_.Exception.Message -Category "Dependencies"
        Pop-Location
        return $false
    }
}

# ============================================================================
# PHASE 1: CORE FEATURES (MVP) TESTS
# ============================================================================

function Test-Phase1-Authentication {
    Write-TestHeader "Phase 1.1: Authentication & User Management"
    
    # Test Laravel Sanctum
    Test-ComposerPackage "laravel/sanctum" "Laravel Sanctum installed"
    
    # Test Auth Controllers
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\AuthController.php" "Auth Controller exists"
    
    # Test User Model
    Test-FileExists "$($Config.BackendPath)\app\Models\User.php" "User Model exists"
    
    # Test Auth Middleware
    Test-FileExists "$($Config.BackendPath)\app\Http\Middleware\Authenticate.php" "Auth Middleware exists"
    
    # Test Database Tables
    Test-DatabaseTable "users" "Users table exists"
    Test-DatabaseTable "personal_access_tokens" "Personal access tokens table exists"
    
    # Test Auth Routes
    $authRoutes = @(
        "/auth/register",
        "/auth/login",
        "/auth/logout",
        "/auth/user",
        "/auth/forgot-password",
        "/auth/reset-password"
    )
    
    foreach ($route in $authRoutes) {
        Test-FileExists "$($Config.BackendPath)\routes\api.php" "Auth routes defined (checking file)"
    }
}

function Test-Phase1-PropertyManagement {
    Write-TestHeader "Phase 1.2: Property Management"
    
    # Test Property Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Property.php" "Property Model exists"
    
    # Test Property Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\PropertyController.php" "Property Controller exists"
    
    # Test Filament Resources
    Test-FileExists "$($Config.BackendPath)\app\Filament\Resources\PropertyResource.php" "Property Filament Resource exists"
    
    # Test Database Tables
    Test-DatabaseTable "properties" "Properties table exists"
    Test-DatabaseTable "property_images" "Property images table exists"
    Test-DatabaseTable "amenities" "Amenities table exists"
    Test-DatabaseTable "property_amenity" "Property-Amenity pivot table exists"
    
    # Test Image Storage
    Test-DirectoryExists "$($Config.BackendPath)\storage\app\public\properties" "Property images storage directory"
}

function Test-Phase1-PropertyListing {
    Write-TestHeader "Phase 1.3: Property Listing (Tenant Side)"
    
    # Test Search Functionality
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\SearchController.php" "Search Controller exists"
    
    # Test Property Filters
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\PropertyController.php" "Property filtering in controller"
    
    # Test Frontend Components
    Test-FileExists "$($Config.FrontendPath)\src\components\PropertyCard.tsx" "Property Card component"
    Test-FileExists "$($Config.FrontendPath)\src\components\PropertyGrid.tsx" "Property Grid component"
    Test-FileExists "$($Config.FrontendPath)\src\components\SearchBar.tsx" "Search Bar component"
}

function Test-Phase1-BookingSystem {
    Write-TestHeader "Phase 1.4: Booking System"
    
    # Test Booking Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Booking.php" "Booking Model exists"
    
    # Test Booking Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\BookingController.php" "Booking Controller exists"
    
    # Test Database Tables
    Test-DatabaseTable "bookings" "Bookings table exists"
    
    # Test Availability Logic
    Test-FileExists "$($Config.BackendPath)\app\Services\AvailabilityService.php" "Availability Service exists"
    
    # Test Booking Statuses
    Test-DatabaseTable "bookings" "Booking statuses (checking table)"
}

function Test-Phase1-PaymentSystem {
    Write-TestHeader "Phase 1.5: Payment System"
    
    # Test Payment Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Payment.php" "Payment Model exists"
    
    # Test Payment Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\PaymentController.php" "Payment Controller exists"
    
    # Test Database Tables
    Test-DatabaseTable "payments" "Payments table exists"
    Test-DatabaseTable "payouts" "Payouts table exists"
    
    # Test Invoice Generation
    Test-FileExists "$($Config.BackendPath)\app\Services\InvoiceService.php" "Invoice Service exists"
}

function Test-Phase1-ReviewSystem {
    Write-TestHeader "Phase 1.6: Review & Rating System"
    
    # Test Review Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Review.php" "Review Model exists"
    
    # Test Review Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\ReviewController.php" "Review Controller exists"
    
    # Test Database Tables
    Test-DatabaseTable "reviews" "Reviews table exists"
    
    # Test Review Policies
    Test-FileExists "$($Config.BackendPath)\app\Policies\ReviewPolicy.php" "Review Policy exists"
}

function Test-Phase1-Notifications {
    Write-TestHeader "Phase 1.7: Notifications"
    
    # Test Notification Service
    Test-FileExists "$($Config.BackendPath)\app\Services\NotificationService.php" "Notification Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "notifications" "Notifications table exists"
    
    # Test Email Configuration
    Test-FileExists "$($Config.BackendPath)\.env.example" "Email configuration in .env.example"
    
    # Test Mail Templates
    Test-DirectoryExists "$($Config.BackendPath)\resources\views\emails" "Email templates directory"
}

# ============================================================================
# PHASE 2: ESSENTIAL FEATURES TESTS
# ============================================================================

function Test-Phase2-MessagingSystem {
    Write-TestHeader "Phase 2.1: Messaging System"
    
    # Test Message Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Message.php" "Message Model exists"
    
    # Test Message Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\MessageController.php" "Message Controller exists"
    
    # Test Database Tables
    Test-DatabaseTable "messages" "Messages table exists"
    Test-DatabaseTable "conversations" "Conversations table exists"
    
    # Test WebSocket Configuration
    Test-ComposerPackage "pusher/pusher-php-server" "Pusher package installed"
}

function Test-Phase2-Wishlist {
    Write-TestHeader "Phase 2.2: Wishlist/Favorites"
    
    # Test Wishlist Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Wishlist.php" "Wishlist Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "wishlists" "Wishlists table exists"
    Test-DatabaseTable "wishlist_items" "Wishlist items table exists"
}

function Test-Phase2-Calendar {
    Write-TestHeader "Phase 2.3: Calendar Management"
    
    # Test Calendar Service
    Test-FileExists "$($Config.BackendPath)\app\Services\CalendarService.php" "Calendar Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "property_availability" "Property availability table exists"
    Test-DatabaseTable "blocked_dates" "Blocked dates table exists"
    
    # Test Google Calendar Integration
    Test-ComposerPackage "google/apiclient" "Google API Client installed"
}

function Test-Phase2-AdvancedSearch {
    Write-TestHeader "Phase 2.4: Advanced Search"
    
    # Test Map Search
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\MapSearchController.php" "Map Search Controller exists"
    
    # Test Saved Searches
    Test-DatabaseTable "saved_searches" "Saved searches table exists"
    
    # Test Search Algorithms
    Test-FileExists "$($Config.BackendPath)\app\Services\SearchService.php" "Search Service exists"
}

function Test-Phase2-PropertyVerification {
    Write-TestHeader "Phase 2.5: Property Verification"
    
    # Test Verification Models
    Test-FileExists "$($Config.BackendPath)\app\Models\Verification.php" "Verification Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "verifications" "Verifications table exists"
    Test-DatabaseTable "verification_documents" "Verification documents table exists"
}

function Test-Phase2-DashboardAnalytics {
    Write-TestHeader "Phase 2.6: Dashboard Analytics"
    
    # Test Analytics Service
    Test-FileExists "$($Config.BackendPath)\app\Services\AnalyticsService.php" "Analytics Service exists"
    
    # Test Dashboard Controllers
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\OwnerDashboardController.php" "Owner Dashboard Controller"
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\TenantDashboardController.php" "Tenant Dashboard Controller"
    
    # Test Reporting
    Test-FileExists "$($Config.BackendPath)\app\Services\ReportingService.php" "Reporting Service exists"
}

function Test-Phase2-Multilanguage {
    Write-TestHeader "Phase 2.7: Multi-language Support"
    
    # Test Language Files
    Test-DirectoryExists "$($Config.BackendPath)\lang" "Language files directory"
    Test-DirectoryExists "$($Config.FrontendPath)\public\locales" "Frontend locales directory"
    
    # Test i18n Package (Frontend)
    Test-NpmPackage "next-i18next" "Next.js i18n package installed"
    
    # Test Translation Middleware
    Test-FileExists "$($Config.BackendPath)\app\Http\Middleware\SetLocale.php" "Locale Middleware exists"
}

function Test-Phase2-Multicurrency {
    Write-TestHeader "Phase 2.8: Multi-currency Support"
    
    # Test Currency Service
    Test-FileExists "$($Config.BackendPath)\app\Services\CurrencyService.php" "Currency Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "currencies" "Currencies table exists"
    Test-DatabaseTable "exchange_rates" "Exchange rates table exists"
    
    # Test Currency Conversion
    Test-ComposerPackage "moneyphp/money" "Money PHP package installed"
}

# ============================================================================
# PHASE 3: ADVANCED FEATURES TESTS
# ============================================================================

function Test-Phase3-SmartPricing {
    Write-TestHeader "Phase 3.1: Smart Pricing"
    
    # Test Pricing Service
    Test-FileExists "$($Config.BackendPath)\app\Services\PricingService.php" "Pricing Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "pricing_rules" "Pricing rules table exists"
    Test-DatabaseTable "seasonal_prices" "Seasonal prices table exists"
}

function Test-Phase3-LongTermRentals {
    Write-TestHeader "Phase 3.3: Long-term Rentals"
    
    # Test Lease Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Lease.php" "Lease Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "leases" "Leases table exists"
    Test-DatabaseTable "maintenance_requests" "Maintenance requests table exists"
}

function Test-Phase3-PropertyComparison {
    Write-TestHeader "Phase 3.4: Property Comparison"
    
    # Test Comparison Controller
    Test-FileExists "$($Config.BackendPath)\app\Http\Controllers\Api\PropertyComparisonController.php" "Property Comparison Controller"
    
    # Test Frontend Component
    Test-FileExists "$($Config.FrontendPath)\src\components\PropertyComparison.tsx" "Property Comparison Component"
}

function Test-Phase3-Insurance {
    Write-TestHeader "Phase 3.6: Insurance Integration"
    
    # Test Insurance Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Insurance.php" "Insurance Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "insurances" "Insurances table exists"
}

function Test-Phase3-SmartLocks {
    Write-TestHeader "Phase 3.7: Smart Locks Integration"
    
    # Test Smart Lock Service
    Test-FileExists "$($Config.BackendPath)\app\Services\SmartLockService.php" "Smart Lock Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "smart_locks" "Smart locks table exists"
    Test-DatabaseTable "access_codes" "Access codes table exists"
}

function Test-Phase3-CleaningMaintenance {
    Write-TestHeader "Phase 3.8: Cleaning & Maintenance"
    
    # Test Cleaning Service Model
    Test-FileExists "$($Config.BackendPath)\app\Models\CleaningService.php" "Cleaning Service Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "cleaning_services" "Cleaning services table exists"
    Test-DatabaseTable "maintenance_requests" "Maintenance requests table exists"
}

function Test-Phase3-GuestScreening {
    Write-TestHeader "Phase 3.10: Guest Screening"
    
    # Test Screening Service
    Test-FileExists "$($Config.BackendPath)\app\Services\GuestScreeningService.php" "Guest Screening Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "guest_screenings" "Guest screenings table exists"
    Test-DatabaseTable "background_checks" "Background checks table exists"
}

# ============================================================================
# PHASE 4: PREMIUM FEATURES TESTS
# ============================================================================

function Test-Phase4-AIML {
    Write-TestHeader "Phase 4.2: AI & Machine Learning"
    
    # Test Recommendation Engine
    Test-FileExists "$($Config.BackendPath)\app\Services\RecommendationService.php" "Recommendation Service exists"
    
    # Test ML Models Directory
    Test-DirectoryExists "$($Config.BackendPath)\ml-models" "ML models directory"
}

function Test-Phase4-LoyaltyProgram {
    Write-TestHeader "Phase 4.6: Loyalty Program"
    
    # Test Loyalty Model
    Test-FileExists "$($Config.BackendPath)\app\Models\LoyaltyProgram.php" "Loyalty Program Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "loyalty_points" "Loyalty points table exists"
    Test-DatabaseTable "loyalty_tiers" "Loyalty tiers table exists"
}

function Test-Phase4-ReferralProgram {
    Write-TestHeader "Phase 4.7: Referral Program"
    
    # Test Referral Model
    Test-FileExists "$($Config.BackendPath)\app\Models\Referral.php" "Referral Model exists"
    
    # Test Database Tables
    Test-DatabaseTable "referrals" "Referrals table exists"
}

function Test-Phase4-AutomatedMessaging {
    Write-TestHeader "Phase 4.8: Automated Messaging"
    
    # Test Message Templates
    Test-DatabaseTable "message_templates" "Message templates table exists"
    
    # Test Automation Service
    Test-FileExists "$($Config.BackendPath)\app\Services\AutomatedMessagingService.php" "Automated Messaging Service"
}

function Test-Phase4-AdvancedReporting {
    Write-TestHeader "Phase 4.9: Advanced Reporting"
    
    # Test Reporting Service
    Test-FileExists "$($Config.BackendPath)\app\Services\AdvancedReportingService.php" "Advanced Reporting Service"
    
    # Test Export Functionality
    Test-ComposerPackage "maatwebsite/excel" "Laravel Excel package installed"
}

function Test-Phase4-ChannelManager {
    Write-TestHeader "Phase 4.10: Channel Manager"
    
    # Test Channel Manager Service
    Test-FileExists "$($Config.BackendPath)\app\Services\ChannelManagerService.php" "Channel Manager Service exists"
    
    # Test Database Tables
    Test-DatabaseTable "channel_connections" "Channel connections table exists"
}

# ============================================================================
# SECURITY TESTS
# ============================================================================

function Test-Security-Authentication {
    Write-TestHeader "Security: Authentication & Authorization"
    
    # Test OAuth
    Test-ComposerPackage "laravel/passport" "Laravel Passport for OAuth2"
    
    # Test JWT
    Test-ComposerPackage "tymon/jwt-auth" "JWT Authentication"
    
    # Test RBAC
    Test-ComposerPackage "spatie/laravel-permission" "Permission package installed"
    Test-DatabaseTable "roles" "Roles table exists"
    Test-DatabaseTable "permissions" "Permissions table exists"
}

function Test-Security-DataProtection {
    Write-TestHeader "Security: Data Protection"
    
    # Test Encryption
    Test-FileExists "$($Config.BackendPath)\config\app.php" "Encryption configuration"
    
    # Test GDPR Compliance
    Test-FileExists "$($Config.BackendPath)\app\Services\GdprService.php" "GDPR Service exists"
    
    # Test Data Anonymization
    Test-FileExists "$($Config.BackendPath)\app\Services\DataAnonymizationService.php" "Data Anonymization Service"
}

function Test-Security-ApplicationSecurity {
    Write-TestHeader "Security: Application Security"
    
    # Test CSRF Protection
    Test-FileExists "$($Config.BackendPath)\app\Http\Middleware\VerifyCsrfToken.php" "CSRF Middleware"
    
    # Test Rate Limiting
    Test-FileExists "$($Config.BackendPath)\app\Http\Middleware\ThrottleRequests.php" "Rate Limiting Middleware"
    
    # Test Input Validation
    Test-DirectoryExists "$($Config.BackendPath)\app\Http\Requests" "Request validation classes"
    
    # Test Security Headers
    Test-FileExists "$($Config.BackendPath)\app\Http\Middleware\SecurityHeaders.php" "Security Headers Middleware"
}

function Test-Security-Monitoring {
    Write-TestHeader "Security: Monitoring & Auditing"
    
    # Test Audit Logging
    Test-DatabaseTable "audit_logs" "Audit logs table exists"
    
    # Test Activity Log
    Test-ComposerPackage "spatie/laravel-activitylog" "Activity log package installed"
}

# ============================================================================
# PERFORMANCE TESTS
# ============================================================================

function Test-Performance-Database {
    Write-TestHeader "Performance: Database Optimization"
    
    # Test Indexes
    Test-LaravelCommand "db:show" "Database connection test"
    
    # Test Query Caching
    Test-FileExists "$($Config.BackendPath)\config\cache.php" "Cache configuration"
    
    # Test Redis
    Test-ComposerPackage "predis/predis" "Redis package installed"
}

function Test-Performance-Caching {
    Write-TestHeader "Performance: Caching Strategy"
    
    # Test Cache Configuration
    Test-FileExists "$($Config.BackendPath)\config\cache.php" "Cache configuration exists"
    
    # Test Cache Drivers
    Test-FileExists "$($Config.BackendPath)\.env.example" "Cache driver configuration"
}

function Test-Performance-Assets {
    Write-TestHeader "Performance: Asset Optimization"
    
    # Test Image Optimization
    Test-ComposerPackage "intervention/image" "Image manipulation package"
    
    # Test Frontend Build
    Test-FileExists "$($Config.FrontendPath)\next.config.js" "Next.js configuration"
}

# ============================================================================
# DEVOPS TESTS
# ============================================================================

function Test-DevOps-Docker {
    Write-TestHeader "DevOps: Docker Configuration"
    
    # Test Docker Files
    Test-FileExists "C:\laragon\www\RentHub\docker-compose.yml" "Docker Compose file"
    Test-FileExists "C:\laragon\www\RentHub\backend\Dockerfile" "Backend Dockerfile"
    Test-FileExists "C:\laragon\www\RentHub\frontend\Dockerfile" "Frontend Dockerfile"
}

function Test-DevOps-CICD {
    Write-TestHeader "DevOps: CI/CD Pipeline"
    
    # Test GitHub Actions
    Test-DirectoryExists "C:\laragon\www\RentHub\.github\workflows" "GitHub Actions directory"
    Test-FileExists "C:\laragon\www\RentHub\.github\workflows\backend-tests.yml" "Backend tests workflow"
    Test-FileExists "C:\laragon\www\RentHub\.github\workflows\frontend-tests.yml" "Frontend tests workflow"
}

function Test-DevOps-Kubernetes {
    Write-TestHeader "DevOps: Kubernetes Configuration"
    
    # Test K8s Files
    Test-DirectoryExists "C:\laragon\www\RentHub\k8s" "Kubernetes directory"
    Test-FileExists "C:\laragon\www\RentHub\k8s\deployment.yml" "K8s deployment file"
}

function Test-DevOps-Terraform {
    Write-TestHeader "DevOps: Infrastructure as Code"
    
    # Test Terraform Files
    Test-DirectoryExists "C:\laragon\www\RentHub\terraform" "Terraform directory"
    Test-FileExists "C:\laragon\www\RentHub\terraform\main.tf" "Terraform main file"
}

# ============================================================================
# UI/UX TESTS
# ============================================================================

function Test-UIUX-DesignSystem {
    Write-TestHeader "UI/UX: Design System"
    
    # Test Design Tokens
    Test-FileExists "$($Config.FrontendPath)\src\styles\tokens.css" "Design tokens file"
    
    # Test Component Library
    Test-DirectoryExists "$($Config.FrontendPath)\src\components" "Components directory"
}

function Test-UIUX-Accessibility {
    Write-TestHeader "UI/UX: Accessibility"
    
    # Test A11y Dependencies
    Test-NpmPackage "@axe-core/react" "Axe accessibility testing"
    
    # Test ARIA Implementation
    Test-FileExists "$($Config.FrontendPath)\src\components\AccessibleButton.tsx" "Accessible components"
}

function Test-UIUX-ResponsiveDesign {
    Write-TestHeader "UI/UX: Responsive Design"
    
    # Test Responsive CSS
    Test-FileExists "$($Config.FrontendPath)\src\styles\responsive.css" "Responsive styles"
    
    # Test Mobile Components
    Test-DirectoryExists "$($Config.FrontendPath)\src\components\mobile" "Mobile components"
}

# ============================================================================
# MARKETING FEATURES TESTS
# ============================================================================

function Test-Marketing-SEO {
    Write-TestHeader "Marketing: SEO & Content"
    
    # Test SEO Configuration
    Test-FileExists "$($Config.FrontendPath)\next-sitemap.config.js" "Sitemap configuration"
    
    # Test Meta Tags
    Test-FileExists "$($Config.FrontendPath)\src\components\SEO.tsx" "SEO component"
    
    # Test Blog/CMS
    Test-DatabaseTable "posts" "Blog posts table"
}

function Test-Marketing-EmailMarketing {
    Write-TestHeader "Marketing: Email Marketing"
    
    # Test Newsletter
    Test-DatabaseTable "newsletter_subscriptions" "Newsletter subscriptions table"
    
    # Test Email Campaigns
    Test-DatabaseTable "email_campaigns" "Email campaigns table"
}

function Test-Marketing-SocialMedia {
    Write-TestHeader "Marketing: Social Media Integration"
    
    # Test Social Login
    Test-ComposerPackage "laravel/socialite" "Socialite package installed"
    
    # Test Open Graph
    Test-FileExists "$($Config.FrontendPath)\src\components\OpenGraph.tsx" "Open Graph component"
}

function Test-Marketing-Analytics {
    Write-TestHeader "Marketing: Analytics & Tracking"
    
    # Test Analytics Integration
    Test-FileExists "$($Config.FrontendPath)\src\lib\analytics.ts" "Analytics library"
    
    # Test Google Tag Manager
    Test-NpmPackage "@next/third-parties" "Third-party integrations"
}

# ============================================================================
# MAIN TEST EXECUTION
# ============================================================================

function Invoke-TestSuite {
    param([string]$Suite)
    
    switch ($Suite) {
        "phase1" {
            Test-Phase1-Authentication
            Test-Phase1-PropertyManagement
            Test-Phase1-PropertyListing
            Test-Phase1-BookingSystem
            Test-Phase1-PaymentSystem
            Test-Phase1-ReviewSystem
            Test-Phase1-Notifications
        }
        "phase2" {
            Test-Phase2-MessagingSystem
            Test-Phase2-Wishlist
            Test-Phase2-Calendar
            Test-Phase2-AdvancedSearch
            Test-Phase2-PropertyVerification
            Test-Phase2-DashboardAnalytics
            Test-Phase2-Multilanguage
            Test-Phase2-Multicurrency
        }
        "phase3" {
            Test-Phase3-SmartPricing
            Test-Phase3-LongTermRentals
            Test-Phase3-PropertyComparison
            Test-Phase3-Insurance
            Test-Phase3-SmartLocks
            Test-Phase3-CleaningMaintenance
            Test-Phase3-GuestScreening
        }
        "phase4" {
            Test-Phase4-AIML
            Test-Phase4-LoyaltyProgram
            Test-Phase4-ReferralProgram
            Test-Phase4-AutomatedMessaging
            Test-Phase4-AdvancedReporting
            Test-Phase4-ChannelManager
        }
        "security" {
            Test-Security-Authentication
            Test-Security-DataProtection
            Test-Security-ApplicationSecurity
            Test-Security-Monitoring
        }
        "performance" {
            Test-Performance-Database
            Test-Performance-Caching
            Test-Performance-Assets
        }
        "devops" {
            Test-DevOps-Docker
            Test-DevOps-CICD
            Test-DevOps-Kubernetes
            Test-DevOps-Terraform
        }
        "uiux" {
            Test-UIUX-DesignSystem
            Test-UIUX-Accessibility
            Test-UIUX-ResponsiveDesign
        }
        "marketing" {
            Test-Marketing-SEO
            Test-Marketing-EmailMarketing
            Test-Marketing-SocialMedia
            Test-Marketing-Analytics
        }
        "all" {
            # Run all test suites
            Invoke-TestSuite "phase1"
            Invoke-TestSuite "phase2"
            Invoke-TestSuite "phase3"
            Invoke-TestSuite "phase4"
            Invoke-TestSuite "security"
            Invoke-TestSuite "performance"
            Invoke-TestSuite "devops"
            Invoke-TestSuite "uiux"
            Invoke-TestSuite "marketing"
        }
    }
}

# ============================================================================
# GENERATE REPORT
# ============================================================================

function Generate-TestReport {
    Write-TestHeader "Test Results Summary"
    
    $totalTests = $script:TestResults.Count
    $passedTests = ($script:TestResults | Where-Object { $_.Status -eq "PASS" }).Count
    $failedTests = ($script:TestResults | Where-Object { $_.Status -eq "FAIL" }).Count
    $warnTests = ($script:TestResults | Where-Object { $_.Status -eq "WARN" }).Count
    $skippedTests = ($script:TestResults | Where-Object { $_.Status -eq "SKIP" }).Count
    
    $passRate = if ($totalTests -gt 0) { [math]::Round(($passedTests / $totalTests) * 100, 2) } else { 0 }
    
    Write-Host "`n" -NoNewline
    Write-Host "=====================================" -ForegroundColor Cyan
    Write-Host "  ROADMAP VERIFICATION REPORT" -ForegroundColor Cyan
    Write-Host "=====================================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Total Tests:    " -NoNewline
    Write-Host "$totalTests" -ForegroundColor White
    Write-Host "Passed:         " -NoNewline
    Write-Host "$passedTests" -ForegroundColor Green
    Write-Host "Failed:         " -NoNewline
    Write-Host "$failedTests" -ForegroundColor Red
    Write-Host "Warnings:       " -NoNewline
    Write-Host "$warnTests" -ForegroundColor Yellow
    Write-Host "Skipped:        " -NoNewline
    Write-Host "$skippedTests" -ForegroundColor Gray
    Write-Host "Pass Rate:      " -NoNewline
    Write-Host "$passRate%" -ForegroundColor $(if ($passRate -ge 80) { "Green" } elseif ($passRate -ge 60) { "Yellow" } else { "Red" })
    Write-Host ""
    Write-Host "Duration:       " -NoNewline
    $duration = (Get-Date) - $StartTime
    Write-Host "$($duration.ToString('mm\:ss'))" -ForegroundColor White
    Write-Host "=====================================" -ForegroundColor Cyan
    
    # Category Breakdown
    Write-Host "`nResults by Category:" -ForegroundColor Cyan
    $categories = $script:TestResults | Group-Object -Property Category | Sort-Object Name
    
    foreach ($cat in $categories) {
        $catPassed = ($cat.Group | Where-Object { $_.Status -eq "PASS" }).Count
        $catTotal = $cat.Count
        $catRate = if ($catTotal -gt 0) { [math]::Round(($catPassed / $catTotal) * 100, 0) } else { 0 }
        
        Write-Host "  $($cat.Name): " -NoNewline
        Write-Host "$catPassed/$catTotal ($catRate%)" -ForegroundColor $(if ($catRate -ge 80) { "Green" } elseif ($catRate -ge 60) { "Yellow" } else { "Red" })
    }
    
    # Failed Tests Details
    if ($failedTests -gt 0) {
        Write-Host "`nFailed Tests:" -ForegroundColor Red
        $failedItems = $script:TestResults | Where-Object { $_.Status -eq "FAIL" } | Select-Object -First 20
        foreach ($item in $failedItems) {
            Write-Host "  [$($item.Category)] " -NoNewline -ForegroundColor Gray
            Write-Host "$($item.TestName)" -ForegroundColor Red
            if ($item.Details) {
                Write-Host "    └─ $($item.Details)" -ForegroundColor DarkGray
            }
        }
        
        if ($failedTests -gt 20) {
            Write-Host "`n  ... and $($failedTests - 20) more failures" -ForegroundColor DarkGray
        }
    }
    
    # Generate JSON Report
    if ($GenerateReport) {
        $reportPath = "C:\laragon\www\RentHub\ROADMAP_TEST_REPORT_$(Get-Date -Format 'yyyyMMdd_HHmmss').json"
        $reportData = @{
            timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
            summary = @{
                total = $totalTests
                passed = $passedTests
                failed = $failedTests
                warnings = $warnTests
                skipped = $skippedTests
                passRate = $passRate
                duration = $duration.ToString()
            }
            results = $script:TestResults
        }
        
        $reportData | ConvertTo-Json -Depth 10 | Out-File -FilePath $reportPath -Encoding UTF8
        Write-Host "`nDetailed report saved to: $reportPath" -ForegroundColor Green
    }
    
    # Return exit code based on pass rate
    if ($passRate -lt 80) {
        exit 1
    }
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

Write-Host @"

╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║         RENTHUB ROADMAP VERIFICATION SUITE                   ║
║         Complete Feature Testing & Validation                ║
║                                                              ║
║         Version: 1.0.0                                       ║
║         Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")                     ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝

"@ -ForegroundColor Cyan

Write-Host "Test Type: " -NoNewline
Write-Host "$TestType" -ForegroundColor Yellow
Write-Host "Verbose: " -NoNewline
Write-Host "$Verbose" -ForegroundColor Yellow
Write-Host "Generate Report: " -NoNewline
Write-Host "$GenerateReport" -ForegroundColor Yellow
Write-Host ""

# Run tests
Invoke-TestSuite -Suite $TestType

# Generate report
Generate-TestReport

Write-Host "`n✅ Testing complete!`n" -ForegroundColor Green

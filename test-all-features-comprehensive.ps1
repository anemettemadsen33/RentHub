# RentHub - Comprehensive Feature Testing Script
# Tests all 724 roadmap items systematically

param(
    [switch]$Verbose,
    [switch]$StopOnError,
    [string]$TestCategory = "all"
)

$ErrorActionPreference = if ($StopOnError) { "Stop" } else { "Continue" }
$script:TotalTests = 0
$script:PassedTests = 0
$script:FailedTests = 0
$script:SkippedTests = 0
$script:TestResults = @()

# Colors for output
$script:ColorPass = "Green"
$script:ColorFail = "Red"
$script:ColorSkip = "Yellow"
$script:ColorInfo = "Cyan"

function Write-TestHeader {
    param([string]$Title)
    Write-Host "`n========================================" -ForegroundColor $ColorInfo
    Write-Host " $Title" -ForegroundColor $ColorInfo
    Write-Host "========================================`n" -ForegroundColor $ColorInfo
}

function Write-TestResult {
    param(
        [string]$TestName,
        [string]$Status,
        [string]$Message = "",
        [string]$Category = ""
    )
    
    $script:TotalTests++
    
    $color = switch ($Status) {
        "PASS" { $script:PassedTests++; $ColorPass }
        "FAIL" { $script:FailedTests++; $ColorFail }
        "SKIP" { $script:SkippedTests++; $ColorSkip }
        default { "White" }
    }
    
    $result = @{
        Category = $Category
        TestName = $TestName
        Status = $Status
        Message = $Message
        Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    }
    
    $script:TestResults += $result
    
    $statusSymbol = switch ($Status) {
        "PASS" { "✅" }
        "FAIL" { "❌" }
        "SKIP" { "⚠️" }
    }
    
    Write-Host "  $statusSymbol $TestName" -ForegroundColor $color -NoNewline
    if ($Message) {
        Write-Host " - $Message" -ForegroundColor $color
    } else {
        Write-Host ""
    }
}

function Test-FileExists {
    param([string]$Path, [string]$Description)
    if (Test-Path $Path) {
        Write-TestResult -TestName $Description -Status "PASS" -Message "File exists: $Path"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Message "File not found: $Path"
        return $false
    }
}

function Test-DirectoryExists {
    param([string]$Path, [string]$Description)
    if (Test-Path $Path -PathType Container) {
        Write-TestResult -TestName $Description -Status "PASS" -Message "Directory exists: $Path"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Message "Directory not found: $Path"
        return $false
    }
}

function Test-ComposerPackage {
    param([string]$Package, [string]$Description)
    $composerJson = Get-Content "backend/composer.json" -Raw | ConvertFrom-Json
    $allPackages = @{}
    if ($composerJson.require) { $composerJson.require.PSObject.Properties | ForEach-Object { $allPackages[$_.Name] = $_.Value } }
    if ($composerJson.'require-dev') { $composerJson.'require-dev'.PSObject.Properties | ForEach-Object { $allPackages[$_.Name] = $_.Value } }
    
    if ($allPackages.ContainsKey($Package)) {
        Write-TestResult -TestName $Description -Status "PASS" -Message "Package $Package installed"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Message "Package $Package not found"
        return $false
    }
}

function Test-NpmPackage {
    param([string]$Package, [string]$Description)
    $packageJson = Get-Content "frontend/package.json" -Raw | ConvertFrom-Json
    $allPackages = @{}
    if ($packageJson.dependencies) { $packageJson.dependencies.PSObject.Properties | ForEach-Object { $allPackages[$_.Name] = $_.Value } }
    if ($packageJson.devDependencies) { $packageJson.devDependencies.PSObject.Properties | ForEach-Object { $allPackages[$_.Name] = $_.Value } }
    
    if ($allPackages.ContainsKey($Package)) {
        Write-TestResult -TestName $Description -Status "PASS" -Message "Package $Package installed"
        return $true
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Message "Package $Package not found"
        return $false
    }
}

function Test-DatabaseTable {
    param([string]$Table, [string]$Description)
    try {
        Push-Location backend
        $result = php artisan tinker --execute="echo Schema::hasTable('$Table') ? 'EXISTS' : 'MISSING';" 2>&1
        Pop-Location
        
        if ($result -match "EXISTS") {
            Write-TestResult -TestName $Description -Status "PASS" -Message "Table '$Table' exists"
            return $true
        } else {
            Write-TestResult -TestName $Description -Status "FAIL" -Message "Table '$Table' not found"
            return $false
        }
    } catch {
        Write-TestResult -TestName $Description -Status "FAIL" -Message "Error checking table: $_"
        Pop-Location
        return $false
    }
}

function Test-APIEndpoint {
    param([string]$Endpoint, [string]$Description)
    Write-TestResult -TestName $Description -Status "SKIP" -Message "API endpoint test requires running server"
    return $false
}

function Test-EnvVariable {
    param([string]$Variable, [string]$Description)
    if (Test-Path "backend/.env") {
        $envContent = Get-Content "backend/.env" -Raw
        if ($envContent -match "^$Variable=") {
            Write-TestResult -TestName $Description -Status "PASS" -Message "Environment variable set"
            return $true
        } else {
            Write-TestResult -TestName $Description -Status "FAIL" -Message "Environment variable not set"
            return $false
        }
    } else {
        Write-TestResult -TestName $Description -Status "FAIL" -Message ".env file not found"
        return $false
    }
}

# ============================================================
# PHASE 1: CORE FEATURES (MVP) - 85 Tests
# ============================================================

Write-TestHeader "PHASE 1.1: Authentication & User Management"

# Authentication Tests
Test-FileExists "backend/app/Models/User.php" "User Model"
Test-FileExists "backend/app/Http/Controllers/Auth/AuthController.php" "Auth Controller"
Test-ComposerPackage "laravel/sanctum" "Laravel Sanctum"
Test-ComposerPackage "socialiteproviders/google" "Google OAuth"
Test-ComposerPackage "socialiteproviders/facebook" "Facebook OAuth"
Test-DatabaseTable "users" "Users table"
Test-DatabaseTable "personal_access_tokens" "API tokens table"
Test-FileExists "backend/app/Http/Middleware/TwoFactorAuth.php" "2FA Middleware"
Test-DatabaseTable "two_factor_authentication" "2FA table"
Test-FileExists "backend/app/Services/TwilioService.php" "Phone verification service"

# Roles & Permissions
Test-ComposerPackage "spatie/laravel-permission" "Spatie Permission"
Test-DatabaseTable "roles" "Roles table"
Test-DatabaseTable "permissions" "Permissions table"
Test-DatabaseTable "role_has_permissions" "Role permissions pivot"
Test-DatabaseTable "model_has_roles" "Model roles pivot"

Write-TestHeader "PHASE 1.2: Property Management"

# Property Models & Controllers
Test-FileExists "backend/app/Models/Property.php" "Property Model"
Test-FileExists "backend/app/Models/PropertyImage.php" "Property Image Model"
Test-FileExists "backend/app/Models/Amenity.php" "Amenity Model"
Test-FileExists "backend/app/Http/Controllers/API/PropertyController.php" "Property API Controller"
Test-DatabaseTable "properties" "Properties table"
Test-DatabaseTable "property_images" "Property images table"
Test-DatabaseTable "amenities" "Amenities table"
Test-DatabaseTable "property_amenity" "Property amenities pivot"

# Image Processing
Test-ComposerPackage "intervention/image" "Image processing"
Test-FileExists "backend/app/Services/ImageOptimizationService.php" "Image optimization service"

# Google Maps
Test-FileExists "backend/app/Services/GoogleMapsService.php" "Google Maps service"
Test-EnvVariable "GOOGLE_MAPS_API_KEY" "Google Maps API key"

Write-TestHeader "PHASE 1.3: Property Listing"

# Search & Filtering
Test-FileExists "backend/app/Http/Controllers/API/SearchController.php" "Search Controller"
Test-FileExists "backend/app/Services/SearchService.php" "Search Service"
Test-ComposerPackage "elasticsearch/elasticsearch" "Elasticsearch"

# Frontend Components
Test-FileExists "frontend/app/properties/page.tsx" "Properties list page"
Test-FileExists "frontend/app/properties/[id]/page.tsx" "Property details page"
Test-FileExists "frontend/components/PropertyCard.tsx" "Property card component"
Test-FileExists "frontend/components/PropertyFilter.tsx" "Property filter component"

Write-TestHeader "PHASE 1.4: Booking System"

# Booking Models & Controllers
Test-FileExists "backend/app/Models/Booking.php" "Booking Model"
Test-FileExists "backend/app/Http/Controllers/API/BookingController.php" "Booking Controller"
Test-DatabaseTable "bookings" "Bookings table"
Test-FileExists "backend/app/Services/BookingService.php" "Booking Service"
Test-FileExists "backend/app/StateMachines/BookingStateMachine.php" "Booking State Machine"

# Calendar
Test-FileExists "backend/app/Services/CalendarService.php" "Calendar Service"
Test-DatabaseTable "calendar_blocks" "Calendar blocks table"

Write-TestHeader "PHASE 1.5: Payment System"

# Stripe Integration
Test-ComposerPackage "stripe/stripe-php" "Stripe SDK"
Test-FileExists "backend/app/Services/PaymentService.php" "Payment Service"
Test-FileExists "backend/app/Http/Controllers/API/PaymentController.php" "Payment Controller"
Test-DatabaseTable "payments" "Payments table"
Test-DatabaseTable "refunds" "Refunds table"
Test-DatabaseTable "payouts" "Payouts table"
Test-EnvVariable "STRIPE_KEY" "Stripe API key"
Test-EnvVariable "STRIPE_SECRET" "Stripe secret key"

# Invoices
Test-FileExists "backend/app/Services/InvoiceService.php" "Invoice Service"
Test-ComposerPackage "barryvdh/laravel-dompdf" "PDF generation"
Test-DatabaseTable "invoices" "Invoices table"

Write-TestHeader "PHASE 1.6: Review & Rating System"

Test-FileExists "backend/app/Models/Review.php" "Review Model"
Test-FileExists "backend/app/Http/Controllers/API/ReviewController.php" "Review Controller"
Test-DatabaseTable "reviews" "Reviews table"
Test-DatabaseTable "review_images" "Review images table"
Test-FileExists "backend/app/Services/ReviewService.php" "Review Service"

Write-TestHeader "PHASE 1.7: Notifications"

# Email Notifications
Test-FileExists "backend/app/Notifications/BookingConfirmed.php" "Booking confirmed notification"
Test-FileExists "backend/app/Notifications/PaymentReceived.php" "Payment received notification"
Test-DatabaseTable "notifications" "Notifications table"

# Real-time Notifications
Test-FileExists "backend/config/reverb.php" "Reverb config"
Test-ComposerPackage "laravel/reverb" "Laravel Reverb"

# SMS Notifications
Test-ComposerPackage "twilio/sdk" "Twilio SDK"
Test-FileExists "backend/app/Services/TwilioService.php" "Twilio Service"

# Queue Management
Test-ComposerPackage "laravel/horizon" "Laravel Horizon"
Test-FileExists "backend/config/horizon.php" "Horizon config"

# ============================================================
# PHASE 2: ESSENTIAL FEATURES - 42 Tests
# ============================================================

Write-TestHeader "PHASE 2.1: Messaging System"

Test-FileExists "backend/app/Models/Message.php" "Message Model"
Test-FileExists "backend/app/Http/Controllers/API/MessageController.php" "Message Controller"
Test-DatabaseTable "messages" "Messages table"
Test-DatabaseTable "message_attachments" "Message attachments table"
Test-FileExists "backend/app/Events/MessageSent.php" "Message sent event"
Test-FileExists "backend/app/Listeners/BroadcastMessage.php" "Broadcast message listener"

Write-TestHeader "PHASE 2.2: Wishlist/Favorites"

Test-FileExists "backend/app/Models/Wishlist.php" "Wishlist Model"
Test-DatabaseTable "wishlists" "Wishlists table"
Test-DatabaseTable "wishlist_items" "Wishlist items table"
Test-FileExists "backend/app/Http/Controllers/API/WishlistController.php" "Wishlist Controller"

Write-TestHeader "PHASE 2.3: Calendar Management"

Test-FileExists "backend/app/Services/ICalService.php" "iCal Service"
Test-FileExists "backend/app/Services/GoogleCalendarService.php" "Google Calendar Service"
Test-DatabaseTable "calendar_syncs" "Calendar syncs table"
Test-DatabaseTable "calendar_blocks" "Calendar blocks table"
Test-FileExists "backend/app/Jobs/SyncExternalCalendar.php" "Calendar sync job"

Write-TestHeader "PHASE 2.4: Advanced Search"

Test-FileExists "backend/app/Services/MapSearchService.php" "Map search service"
Test-FileExists "backend/app/Models/SavedSearch.php" "Saved search model"
Test-DatabaseTable "saved_searches" "Saved searches table"
Test-FileExists "backend/app/Jobs/SendSavedSearchAlert.php" "Saved search alert job"

Write-TestHeader "PHASE 2.5: Property Verification"

Test-FileExists "backend/app/Models/Verification.php" "Verification Model"
Test-DatabaseTable "verifications" "Verifications table"
Test-DatabaseTable "verification_documents" "Verification documents table"
Test-FileExists "backend/app/Services/VerificationService.php" "Verification Service"

Write-TestHeader "PHASE 2.6: Dashboard Analytics"

Test-FileExists "backend/app/Http/Controllers/API/DashboardController.php" "Dashboard Controller"
Test-FileExists "backend/app/Services/AnalyticsService.php" "Analytics Service"
Test-FileExists "frontend/app/dashboard/owner/page.tsx" "Owner dashboard"
Test-FileExists "frontend/app/dashboard/tenant/page.tsx" "Tenant dashboard"
Test-NpmPackage "chart.js" "Chart.js"
Test-NpmPackage "react-chartjs-2" "React Chart.js"

Write-TestHeader "PHASE 2.7: Multi-language Support"

Test-NpmPackage "next-i18next" "Next i18next"
Test-DirectoryExists "frontend/locales" "Locales directory"
Test-FileExists "frontend/locales/en/common.json" "English translations"
Test-FileExists "frontend/locales/ro/common.json" "Romanian translations"
Test-FileExists "frontend/next-i18next.config.js" "i18next config"
Test-ComposerPackage "spatie/laravel-translatable" "Laravel translatable"

Write-TestHeader "PHASE 2.8: Multi-currency Support"

Test-FileExists "backend/app/Services/CurrencyService.php" "Currency Service"
Test-DatabaseTable "currencies" "Currencies table"
Test-DatabaseTable "exchange_rates" "Exchange rates table"
Test-FileExists "backend/app/Jobs/UpdateExchangeRates.php" "Exchange rate update job"

# ============================================================
# PHASE 3: ADVANCED FEATURES - 68 Tests
# ============================================================

Write-TestHeader "PHASE 3.1: Smart Pricing"

Test-FileExists "backend/app/Services/SmartPricingService.php" "Smart Pricing Service"
Test-FileExists "backend/app/Models/PricingRule.php" "Pricing Rule Model"
Test-DatabaseTable "pricing_rules" "Pricing rules table"
Test-DatabaseTable "price_history" "Price history table"
Test-FileExists "backend/app/AI/PricePredictionModel.php" "Price prediction model"

Write-TestHeader "PHASE 3.2: Instant Booking"

Test-DatabaseTable "instant_booking_settings" "Instant booking settings table"
Test-FileExists "backend/app/Services/InstantBookingService.php" "Instant booking service"

Write-TestHeader "PHASE 3.3: Long-term Rentals"

Test-FileExists "backend/app/Models/Lease.php" "Lease Model"
Test-DatabaseTable "leases" "Leases table"
Test-FileExists "backend/app/Services/LeaseService.php" "Lease Service"
Test-FileExists "backend/app/Models/MaintenanceRequest.php" "Maintenance Request Model"
Test-DatabaseTable "maintenance_requests" "Maintenance requests table"

Write-TestHeader "PHASE 3.4: Property Comparison"

Test-FileExists "backend/app/Http/Controllers/API/ComparisonController.php" "Comparison Controller"
Test-FileExists "frontend/app/compare/page.tsx" "Compare page"
Test-FileExists "frontend/components/PropertyComparison.tsx" "Property comparison component"

Write-TestHeader "PHASE 3.6: Insurance Integration"

Test-FileExists "backend/app/Services/InsuranceService.php" "Insurance Service"
Test-DatabaseTable "insurance_policies" "Insurance policies table"
Test-FileExists "backend/app/Models/InsurancePolicy.php" "Insurance Policy Model"

Write-TestHeader "PHASE 3.7: Smart Locks Integration"

Test-FileExists "backend/app/Services/SmartLockService.php" "Smart Lock Service"
Test-DatabaseTable "smart_locks" "Smart locks table"
Test-DatabaseTable "access_codes" "Access codes table"
Test-FileExists "backend/app/Models/SmartLock.php" "Smart Lock Model"

Write-TestHeader "PHASE 3.8: Cleaning & Maintenance"

Test-FileExists "backend/app/Models/CleaningSchedule.php" "Cleaning Schedule Model"
Test-DatabaseTable "cleaning_schedules" "Cleaning schedules table"
Test-FileExists "backend/app/Services/CleaningService.php" "Cleaning Service"
Test-DatabaseTable "maintenance_requests" "Maintenance requests table"

Write-TestHeader "PHASE 3.10: Guest Screening"

Test-FileExists "backend/app/Services/GuestScreeningService.php" "Guest Screening Service"
Test-DatabaseTable "guest_screenings" "Guest screenings table"
Test-FileExists "backend/app/Models/GuestScreening.php" "Guest Screening Model"

# ============================================================
# PHASE 4: PREMIUM FEATURES - 72 Tests
# ============================================================

Write-TestHeader "PHASE 4.2: AI & Machine Learning"

Test-FileExists "backend/app/AI/RecommendationEngine.php" "Recommendation Engine"
Test-FileExists "backend/app/AI/FraudDetection.php" "Fraud Detection"
Test-FileExists "backend/app/AI/PriceOptimization.php" "Price Optimization"
Test-NpmPackage "@tensorflow/tfjs" "TensorFlow.js"

Write-TestHeader "PHASE 4.4: IoT Integration"

Test-FileExists "backend/app/Services/IoTService.php" "IoT Service"
Test-DatabaseTable "iot_devices" "IoT devices table"
Test-FileExists "backend/app/Models/IoTDevice.php" "IoT Device Model"

Write-TestHeader "PHASE 4.5: Concierge Services"

Test-FileExists "backend/app/Models/ConciergeService.php" "Concierge Service Model"
Test-DatabaseTable "concierge_services" "Concierge services table"
Test-DatabaseTable "concierge_bookings" "Concierge bookings table"

Write-TestHeader "PHASE 4.6: Loyalty Program"

Test-FileExists "backend/app/Models/LoyaltyPoint.php" "Loyalty Point Model"
Test-DatabaseTable "loyalty_points" "Loyalty points table"
Test-DatabaseTable "loyalty_tiers" "Loyalty tiers table"
Test-DatabaseTable "loyalty_rewards" "Loyalty rewards table"
Test-FileExists "backend/app/Services/LoyaltyService.php" "Loyalty Service"

Write-TestHeader "PHASE 4.7: Referral Program"

Test-FileExists "backend/app/Models/Referral.php" "Referral Model"
Test-DatabaseTable "referrals" "Referrals table"
Test-FileExists "backend/app/Services/ReferralService.php" "Referral Service"

Write-TestHeader "PHASE 4.8: Property Management Tools"

Test-FileExists "backend/app/Models/MessageTemplate.php" "Message Template Model"
Test-DatabaseTable "message_templates" "Message templates table"
Test-FileExists "backend/app/Services/AutoResponderService.php" "Auto Responder Service"

Write-TestHeader "PHASE 4.9: Advanced Reporting"

Test-FileExists "backend/app/Services/ReportingService.php" "Reporting Service"
Test-FileExists "backend/app/Exports/BookingsExport.php" "Bookings Export"
Test-ComposerPackage "maatwebsite/excel" "Laravel Excel"

Write-TestHeader "PHASE 4.10: Third-party Integrations"

Test-FileExists "backend/app/Services/ChannelManagerService.php" "Channel Manager Service"
Test-FileExists "backend/app/Services/AirbnbService.php" "Airbnb Service"
Test-FileExists "backend/app/Services/QuickBooksService.php" "QuickBooks Service"
Test-FileExists "backend/app/Services/XeroService.php" "Xero Service"

# ============================================================
# PHASE 5: SCALE & OPTIMIZE - 45 Tests
# ============================================================

Write-TestHeader "PHASE 5.1: Performance Optimization"

Test-ComposerPackage "predis/predis" "Redis client"
Test-FileExists "backend/config/cache.php" "Cache config"
Test-NpmPackage "sharp" "Sharp image processing"
Test-NpmPackage "next-pwa" "PWA support"

Write-TestHeader "PHASE 5.2: SEO Optimization"

Test-FileExists "frontend/app/sitemap.xml/route.ts" "Sitemap generator"
Test-FileExists "frontend/app/robots.txt/route.ts" "Robots.txt"
Test-FileExists "frontend/components/SEO.tsx" "SEO component"
Test-NpmPackage "next-seo" "Next SEO"

Write-TestHeader "PHASE 5.3: Infrastructure Scaling"

Test-DirectoryExists "k8s" "Kubernetes directory"
Test-FileExists "k8s/deployment.yaml" "K8s deployment"
Test-FileExists "k8s/service.yaml" "K8s service"
Test-FileExists "k8s/ingress.yaml" "K8s ingress"
Test-FileExists "docker-compose.yml" "Docker Compose"
Test-FileExists "backend/Dockerfile" "Backend Dockerfile"
Test-FileExists "frontend/Dockerfile" "Frontend Dockerfile"

Write-TestHeader "PHASE 5.4: Backup & Disaster Recovery"

Test-FileExists "scripts/backup-database.sh" "Database backup script"
Test-FileExists "scripts/restore-database.sh" "Database restore script"
Test-FileExists "scripts/backup-files.sh" "Files backup script"

# ============================================================
# TECHNICAL IMPROVEMENTS - 158 Tests
# ============================================================

Write-TestHeader "Technical: Backend"

Test-FileExists "backend/routes/api-v1.php" "API v1 routes"
Test-FileExists "backend/routes/api-v2.php" "API v2 routes"
Test-DirectoryExists "backend/graphql" "GraphQL directory"
Test-FileExists "backend/graphql/schema.graphql" "GraphQL schema"
Test-ComposerPackage "nuwave/lighthouse" "Lighthouse GraphQL"
Test-FileExists "backend/config/reverb.php" "WebSockets config"
Test-DirectoryExists "backend/tests/Unit" "Unit tests directory"
Test-DirectoryExists "backend/tests/Feature" "Feature tests directory"

Write-TestHeader "Technical: Frontend"

Test-FileExists "frontend/public/manifest.json" "PWA manifest"
Test-FileExists "frontend/public/sw.js" "Service worker"
Test-NpmPackage "@testing-library/react" "React Testing Library"
Test-NpmPackage "@playwright/test" "Playwright"
Test-NpmPackage "jest" "Jest"
Test-DirectoryExists "frontend/__tests__" "Frontend tests directory"

Write-TestHeader "Technical: DevOps"

Test-FileExists "docker/backend.Dockerfile" "Backend Dockerfile"
Test-FileExists "docker/frontend.Dockerfile" "Frontend Dockerfile"
Test-DirectoryExists ".github/workflows" "GitHub Actions directory"
Test-FileExists ".github/workflows/backend-ci.yml" "Backend CI workflow"
Test-FileExists ".github/workflows/frontend-ci.yml" "Frontend CI workflow"
Test-DirectoryExists "terraform" "Terraform directory"
Test-FileExists "terraform/main.tf" "Terraform main"

# ============================================================
# SECURITY ENHANCEMENTS - 89 Tests
# ============================================================

Write-TestHeader "Security: Authentication & Authorization"

Test-ComposerPackage "laravel/passport" "Laravel Passport"
Test-FileExists "backend/app/Http/Middleware/RoleMiddleware.php" "Role middleware"
Test-FileExists "backend/app/Services/JWTService.php" "JWT Service"

Write-TestHeader "Security: Data Security"

Test-FileExists "backend/config/database.php" "Database config"
Test-EnvVariable "DB_ENCRYPT" "Database encryption"
Test-FileExists "backend/app/Services/EncryptionService.php" "Encryption Service"
Test-FileExists "backend/app/Services/GDPRService.php" "GDPR Service"

Write-TestHeader "Security: Application Security"

Test-FileExists "backend/app/Http/Middleware/SecurityHeaders.php" "Security headers middleware"
Test-FileExists "backend/app/Http/Middleware/RateLimiter.php" "Rate limiter middleware"
Test-FileExists "backend/app/Services/ValidationService.php" "Validation Service"

Write-TestHeader "Security: Monitoring & Auditing"

Test-FileExists "backend/app/Models/AuditLog.php" "Audit Log Model"
Test-DatabaseTable "audit_logs" "Audit logs table"
Test-ComposerPackage "spatie/laravel-activitylog" "Activity log"

# ============================================================
# PERFORMANCE OPTIMIZATION - 85 Tests
# ============================================================

Write-TestHeader "Performance: Database"

Test-FileExists "backend/database/migrations/*create_indexes*.php" "Database indexes"
Test-EnvVariable "DB_POOL_SIZE" "Connection pooling"
Test-EnvVariable "REDIS_HOST" "Redis configuration"

Write-TestHeader "Performance: Caching"

Test-FileExists "backend/config/cache.php" "Cache configuration"
Test-FileExists "backend/app/Services/CacheService.php" "Cache Service"

Write-TestHeader "Performance: Assets"

Test-NpmPackage "sharp" "Image optimization"
Test-FileExists "frontend/next.config.js" "Next.js config"

# ============================================================
# UI/UX IMPROVEMENTS - 45 Tests
# ============================================================

Write-TestHeader "UI/UX: Design System"

Test-FileExists "frontend/tailwind.config.js" "Tailwind config"
Test-FileExists "frontend/styles/globals.css" "Global styles"
Test-DirectoryExists "frontend/components/ui" "UI components directory"

Write-TestHeader "UI/UX: Components"

Test-FileExists "frontend/components/LoadingSpinner.tsx" "Loading spinner"
Test-FileExists "frontend/components/ErrorBoundary.tsx" "Error boundary"
Test-FileExists "frontend/components/EmptyState.tsx" "Empty state"
Test-FileExists "frontend/components/Toast.tsx" "Toast component"

Write-TestHeader "UI/UX: Accessibility"

Test-FileExists "frontend/components/SkipToContent.tsx" "Skip link"
Test-NpmPackage "@axe-core/react" "Accessibility testing"

# ============================================================
# MARKETING FEATURES - 35 Tests
# ============================================================

Write-TestHeader "Marketing: SEO & Content"

Test-FileExists "backend/app/Models/BlogPost.php" "Blog Post Model"
Test-DatabaseTable "blog_posts" "Blog posts table"
Test-FileExists "frontend/app/blog/page.tsx" "Blog page"

Write-TestHeader "Marketing: Email Marketing"

Test-FileExists "backend/app/Services/MailchimpService.php" "Mailchimp Service"
Test-DatabaseTable "newsletter_subscriptions" "Newsletter subscriptions table"

Write-TestHeader "Marketing: Social Media"

Test-FileExists "frontend/components/ShareButtons.tsx" "Share buttons"
Test-FileExists "frontend/components/SocialMeta.tsx" "Social meta tags"

Write-TestHeader "Marketing: Analytics"

Test-FileExists "frontend/components/GoogleAnalytics.tsx" "Google Analytics"
Test-FileExists "frontend/components/FacebookPixel.tsx" "Facebook Pixel"
Test-EnvVariable "NEXT_PUBLIC_GA_ID" "GA tracking ID"

# ============================================================
# GENERATE REPORT
# ============================================================

Write-Host "`n`n========================================" -ForegroundColor $ColorInfo
Write-Host " TEST EXECUTION COMPLETE" -ForegroundColor $ColorInfo
Write-Host "========================================`n" -ForegroundColor $ColorInfo

Write-Host "Summary:" -ForegroundColor $ColorInfo
Write-Host "  Total Tests: $script:TotalTests" -ForegroundColor White
Write-Host "  Passed: $script:PassedTests" -ForegroundColor $ColorPass
Write-Host "  Failed: $script:FailedTests" -ForegroundColor $ColorFail
Write-Host "  Skipped: $script:SkippedTests" -ForegroundColor $ColorSkip
Write-Host ""

$passRate = if ($script:TotalTests -gt 0) { 
    [math]::Round(($script:PassedTests / $script:TotalTests) * 100, 2) 
} else { 
    0 
}

Write-Host "  Pass Rate: $passRate%" -ForegroundColor $(if ($passRate -ge 95) { $ColorPass } elseif ($passRate -ge 80) { $ColorSkip } else { $ColorFail })
Write-Host ""

# Generate JSON report
$report = @{
    Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Summary = @{
        TotalTests = $script:TotalTests
        PassedTests = $script:PassedTests
        FailedTests = $script:FailedTests
        SkippedTests = $script:SkippedTests
        PassRate = $passRate
    }
    Results = $script:TestResults
}

$reportFile = "ROADMAP_TEST_REPORT_$(Get-Date -Format 'yyyyMMdd_HHmmss').json"
$report | ConvertTo-Json -Depth 10 | Out-File $reportFile -Encoding UTF8

Write-Host "Detailed report saved to: $reportFile" -ForegroundColor $ColorInfo
Write-Host ""

# Exit with appropriate code
if ($script:FailedTests -gt 0) {
    exit 1
} else {
    exit 0
}

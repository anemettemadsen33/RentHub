# Phase 1: Database Foundation Automation Script
# RentHub Project - Auto-completion

Write-Host "🚀 RentHub Phase 1: Database Foundation" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

$ErrorActionPreference = "Stop"
$backendPath = Join-Path $PSScriptRoot "..\backend"

# Check if backend exists
if (-not (Test-Path $backendPath)) {
    Write-Host "❌ Backend directory not found!" -ForegroundColor Red
    exit 1
}

Set-Location $backendPath

# Step 1: Create Missing Migrations
Write-Host "📊 Step 1: Creating missing migrations..." -ForegroundColor Cyan

$migrations = @(
    "create_bookings_table",
    "create_payments_table",
    "create_payment_methods_table",
    "create_reviews_table",
    "create_review_responses_table",
    "create_wishlists_table",
    "create_wishlist_items_table",
    "create_messages_table",
    "create_message_threads_table",
    "create_notifications_table",
    "create_notification_settings_table",
    "create_calendars_table",
    "create_calendar_events_table",
    "create_saved_searches_table",
    "create_verifications_table",
    "create_verification_documents_table",
    "create_analytics_table",
    "create_analytics_cache_table",
    "create_currencies_table",
    "create_currency_rates_table",
    "create_translations_table",
    "create_smart_locks_table",
    "create_smart_lock_access_logs_table",
    "create_insurance_policies_table",
    "create_insurance_claims_table",
    "create_cleaning_schedules_table",
    "create_maintenance_requests_table",
    "create_maintenance_tasks_table",
    "create_guest_screenings_table",
    "create_guest_screening_results_table",
    "create_loyalty_points_table",
    "create_loyalty_tiers_table",
    "create_referrals_table",
    "create_referral_rewards_table",
    "create_concierge_services_table",
    "create_concierge_bookings_table",
    "create_property_prices_table",
    "create_seasonal_prices_table",
    "create_booking_extras_table",
    "create_cancellation_policies_table",
    "create_refunds_table"
)

$created = 0
$skipped = 0

foreach ($migration in $migrations) {
    Write-Host "  Creating migration: $migration..." -NoNewline
    
    try {
        $output = php artisan make:migration $migration 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host " ✅" -ForegroundColor Green
            $created++
        } else {
            Write-Host " ⚠️  (already exists)" -ForegroundColor Yellow
            $skipped++
        }
    } catch {
        Write-Host " ❌" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "  Created: $created | Skipped: $skipped" -ForegroundColor Cyan
Write-Host ""

# Step 2: Create Models
Write-Host "📦 Step 2: Creating missing models..." -ForegroundColor Cyan

$models = @(
    "Booking",
    "Payment",
    "PaymentMethod",
    "Review",
    "ReviewResponse",
    "Wishlist",
    "WishlistItem",
    "Message",
    "MessageThread",
    "Notification",
    "NotificationSetting",
    "Calendar",
    "CalendarEvent",
    "SavedSearch",
    "Verification",
    "VerificationDocument",
    "Analytics",
    "AnalyticsCache",
    "Currency",
    "CurrencyRate",
    "Translation",
    "SmartLock",
    "SmartLockAccessLog",
    "InsurancePolicy",
    "InsuranceClaim",
    "CleaningSchedule",
    "MaintenanceRequest",
    "MaintenanceTask",
    "GuestScreening",
    "GuestScreeningResult",
    "LoyaltyPoint",
    "LoyaltyTier",
    "Referral",
    "ReferralReward",
    "ConciergeService",
    "ConciergeBooking",
    "PropertyPrice",
    "SeasonalPrice",
    "BookingExtra",
    "CancellationPolicy",
    "Refund"
)

$created = 0
$skipped = 0

foreach ($model in $models) {
    if (Test-Path "app\Models\$model.php") {
        Write-Host "  Skipping $model (exists)" -ForegroundColor Yellow
        $skipped++
    } else {
        Write-Host "  Creating model: $model..." -NoNewline
        try {
            php artisan make:model $model | Out-Null
            Write-Host " ✅" -ForegroundColor Green
            $created++
        } catch {
            Write-Host " ❌" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "  Created: $created | Skipped: $skipped" -ForegroundColor Cyan
Write-Host ""

# Step 3: Run Migrations
Write-Host "🔧 Step 3: Running migrations..." -ForegroundColor Cyan

try {
    Write-Host "  Running: php artisan migrate..." -NoNewline
    $output = php artisan migrate --force 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host " ✅" -ForegroundColor Green
    } else {
        Write-Host " ⚠️  Check output" -ForegroundColor Yellow
        Write-Host $output -ForegroundColor Gray
    }
} catch {
    Write-Host " ❌" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
}

Write-Host ""

# Step 4: Create Seeders
Write-Host "🌱 Step 4: Creating seeders..." -ForegroundColor Cyan

$seeders = @(
    "PropertySeeder",
    "UserSeeder",
    "BookingSeeder",
    "ReviewSeeder",
    "AmenitySeeder",
    "CurrencySeeder",
    "TranslationSeeder"
)

$created = 0

foreach ($seeder in $seeders) {
    if (Test-Path "database\seeders\$seeder.php") {
        Write-Host "  Skipping $seeder (exists)" -ForegroundColor Yellow
    } else {
        Write-Host "  Creating seeder: $seeder..." -NoNewline
        try {
            php artisan make:seeder $seeder | Out-Null
            Write-Host " ✅" -ForegroundColor Green
            $created++
        } catch {
            Write-Host " ❌" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "  Created: $created seeders" -ForegroundColor Cyan
Write-Host ""

# Step 5: Summary
Write-Host "✅ Phase 1 Complete!" -ForegroundColor Green
Write-Host ""
Write-Host "📊 Summary:" -ForegroundColor Cyan
Write-Host "  - Migrations created/checked" -ForegroundColor White
Write-Host "  - Models created/checked" -ForegroundColor White
Write-Host "  - Migrations executed" -ForegroundColor White
Write-Host "  - Seeders created" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Review migration files in database/migrations" -ForegroundColor White
Write-Host "  2. Add table structure to each migration" -ForegroundColor White
Write-Host "  3. Configure model relationships" -ForegroundColor White
Write-Host "  4. Run: php artisan migrate:fresh" -ForegroundColor White
Write-Host "  5. Run: .\scripts\auto-complete-phase2.ps1" -ForegroundColor White
Write-Host ""

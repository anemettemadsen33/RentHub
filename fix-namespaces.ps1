# Fix all namespace issues in API controllers
$files = @(
    "backend/app/Http/Controllers/Api/CurrencyController.php",
    "backend/app/Http/Controllers/Api/DashboardController.php",
    "backend/app/Http/Controllers/Api/DataPrivacyController.php",
    "backend/app/Http/Controllers/Api/ExternalCalendarController.php",
    "backend/app/Http/Controllers/Api/FavoriteController.php",
    "backend/app/Http/Controllers/Api/FraudDetectionController.php",
    "backend/app/Http/Controllers/Api/GDPRController.php",
    "backend/app/Http/Controllers/Api/GoogleCalendarController.php",
    "backend/app/Http/Controllers/Api/GuestReferenceController.php",
    "backend/app/Http/Controllers/Api/GuestScreeningController.php",
    "backend/app/Http/Controllers/Api/GuestVerificationController.php",
    "backend/app/Http/Controllers/Api/HealthCheckController.php",
    "backend/app/Http/Controllers/Api/InvoiceController.php",
    "backend/app/Http/Controllers/Api/IoTDeviceController.php",
    "backend/app/Http/Controllers/Api/LanguageController.php",
    "backend/app/Http/Controllers/Api/LongTermRentalController.php",
    "backend/app/Http/Controllers/Api/LoyaltyController.php",
    "backend/app/Http/Controllers/Api/MaintenanceRequestController.php",
    "backend/app/Http/Controllers/Api/MapSearchController.php",
    "backend/app/Http/Controllers/Api/MessageController.php",
    "backend/app/Http/Controllers/Api/MultiCurrencyController.php",
    "backend/app/Http/Controllers/Api/NotificationController.php",
    "backend/app/Http/Controllers/Api/OAuth2Controller.php",
    "backend/app/Http/Controllers/Api/OwnerDashboardController.php",
    "backend/app/Http/Controllers/Api/PaymentController.php",
    "backend/app/Http/Controllers/Api/PerformanceController.php",
    "backend/app/Http/Controllers/Api/PriceOptimizationController.php",
    "backend/app/Http/Controllers/Api/ProfileController.php",
    "backend/app/Http/Controllers/Api/PropertyAvailabilityController.php",
    "backend/app/Http/Controllers/Api/PropertyComparisonController.php",
    "backend/app/Http/Controllers/Api/PropertyVerificationController.php",
    "backend/app/Http/Controllers/Api/PushController.php",
    "backend/app/Http/Controllers/Api/QueueMonitorController.php",
    "backend/app/Http/Controllers/Api/ReferralController.php",
    "backend/app/Http/Controllers/Api/RentPaymentController.php",
    "backend/app/Http/Controllers/Api/ReviewController.php",
    "backend/app/Http/Controllers/Api/RoleController.php",
    "backend/app/Http/Controllers/Api/SavedSearchController.php",
    "backend/app/Http/Controllers/Api/SecurityAuditController.php",
    "backend/app/Http/Controllers/Api/SecurityController.php",
    "backend/app/Http/Controllers/Api/SeoController.php",
    "backend/app/Http/Controllers/Api/ServiceProviderController.php",
    "backend/app/Http/Controllers/Api/SettingsController.php",
    "backend/app/Http/Controllers/Api/SocialAuthController.php",
    "backend/app/Http/Controllers/Api/TranslationController.php",
    "backend/app/Http/Controllers/Api/TwoFactorAuthController.php",
    "backend/app/Http/Controllers/Api/UserVerificationController.php",
    "backend/app/Http/Controllers/Api/VerificationController.php",
    "backend/app/Http/Controllers/Api/WishlistController.php",
    "backend/app/Http/Controllers/Api/Security/GDPRController.php",
    "backend/app/Http/Controllers/Api/Security/OAuth2Controller.php",
    "backend/app/Http/Controllers/Api/Security/SecurityAuditController.php",
    "backend/app/Http/Controllers/Api/V1/AccessCodeController.php",
    "backend/app/Http/Controllers/Api/V1/AIRecommendationController.php",
    "backend/app/Http/Controllers/Api/V1/ConciergeBookingController.php",
    "backend/app/Http/Controllers/Api/V1/ConciergeServiceController.php",
    "backend/app/Http/Controllers/Api/V1/FraudDetectionController.php",
    "backend/app/Http/Controllers/Api/V1/InsuranceController.php",
    "backend/app/Http/Controllers/Api/V1/LoyaltyController.php",
    "backend/app/Http/Controllers/Api/V1/OwnerDashboardController.php",
    "backend/app/Http/Controllers/Api/V1/PriceOptimizationController.php",
    "backend/app/Http/Controllers/Api/V1/PriceSuggestionController.php",
    "backend/app/Http/Controllers/Api/V1/PricingRuleController.php",
    "backend/app/Http/Controllers/Api/V1/PropertyImportController.php",
    "backend/app/Http/Controllers/Api/V1/PropertyVerificationController.php",
    "backend/app/Http/Controllers/Api/V1/SmartLockController.php",
    "backend/app/Http/Controllers/Api/V1/TenantDashboardController.php",
    "backend/app/Http/Controllers/Api/V1/TranslationController.php",
    "backend/app/Http/Controllers/Api/V1/UserVerificationController.php",
    "backend/app/Http/Controllers/Api/V1/Admin/LoyaltyAdminController.php"
)

$fixedCount = 0
$totalFiles = $files.Count

foreach ($file in $files) {
    $fullPath = Join-Path $PSScriptRoot $file
    if (Test-Path $fullPath) {
        $content = Get-Content $fullPath -Raw
        
        # Fix Api namespace
        $newContent = $content -replace 'namespace App\\Http\\Controllers\\\\Api;', 'namespace App\Http\Controllers\Api;'
        
        # Fix Api\Security namespace
        $newContent = $newContent -replace 'namespace App\\Http\\Controllers\\\\Api\\Security;', 'namespace App\Http\Controllers\Api\Security;'
        
        # Fix Api\V1 namespace
        $newContent = $newContent -replace 'namespace App\\Http\\Controllers\\\\Api\\V1;', 'namespace App\Http\Controllers\Api\V1;'
        
        # Fix Api\V1\Admin namespace
        $newContent = $newContent -replace 'namespace App\\Http\\Controllers\\\\Api\\V1\\Admin;', 'namespace App\Http\Controllers\Api\V1\Admin;'
        
        if ($content -ne $newContent) {
            Set-Content $fullPath -Value $newContent -NoNewline
            $fixedCount++
            Write-Host "✓ Fixed: $file" -ForegroundColor Green
        }
    } else {
        Write-Host "⚠ Not found: $file" -ForegroundColor Yellow
    }
}

Write-Host "`n$fixedCount of $totalFiles files fixed!" -ForegroundColor Cyan

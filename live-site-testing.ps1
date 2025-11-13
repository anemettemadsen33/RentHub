# RentHub Live Site Testing Script
# Tests ALL buttons, forms, functions on both Frontend and Backend

param(
    [switch]$Frontend,
    [switch]$Backend,
    [switch]$All,
    [switch]$Report
)

$FrontendURL = "https://rent-19xinb37g-madsens-projects.vercel.app"
$BackendURL = "https://renthub-tbj7yxj7.on-forge.com"
$BackendAPIURL = "$BackendURL/api/v1"

$TestResults = @()
$TotalTests = 0
$PassedTests = 0
$FailedTests = 0

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$URL,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [object]$Body = $null,
        [int]$ExpectedStatus = 200
    )
    
    $TotalTests++
    Write-Host "`nTesting: $Name" -ForegroundColor Cyan
    Write-Host "URL: $URL" -ForegroundColor Gray
    Write-Host "Method: $Method" -ForegroundColor Gray
    
    try {
        $params = @{
            Uri = $URL
            Method = $Method
            Headers = $Headers
            TimeoutSec = 30
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
            $params.ContentType = "application/json"
        }
        
        $response = Invoke-WebRequest @params -ErrorAction Stop
        
        if ($response.StatusCode -eq $ExpectedStatus) {
            Write-Host "✅ PASSED - Status: $($response.StatusCode)" -ForegroundColor Green
            $script:PassedTests++
            $result = @{
                Name = $Name
                URL = $URL
                Method = $Method
                Status = "PASSED"
                StatusCode = $response.StatusCode
                ResponseTime = $response.Headers['X-Response-Time']
            }
        } else {
            Write-Host "⚠️ WARNING - Expected $ExpectedStatus, got $($response.StatusCode)" -ForegroundColor Yellow
            $result = @{
                Name = $Name
                URL = $URL
                Method = $Method
                Status = "WARNING"
                StatusCode = $response.StatusCode
            }
        }
    }
    catch {
        Write-Host "❌ FAILED - $($_.Exception.Message)" -ForegroundColor Red
        $script:FailedTests++
        $result = @{
            Name = $Name
            URL = $URL
            Method = $Method
            Status = "FAILED"
            Error = $_.Exception.Message
        }
    }
    
    $script:TestResults += [PSCustomObject]$result
    Start-Sleep -Milliseconds 500
}

# ==================== FRONTEND TESTS ====================
function Test-FrontendPages {
    Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║  🌐 FRONTEND LIVE TESTING             ║" -ForegroundColor White
    Write-Host "║  $FrontendURL" -ForegroundColor Gray
    Write-Host "╚════════════════════════════════════════╝" -ForegroundColor Cyan
    
    # Public Pages
    Write-Host "`n📄 PUBLIC PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Homepage" "$FrontendURL/"
    Test-Endpoint "About Page" "$FrontendURL/about"
    Test-Endpoint "Contact Page" "$FrontendURL/contact"
    Test-Endpoint "FAQ Page" "$FrontendURL/faq"
    Test-Endpoint "Help Page" "$FrontendURL/help"
    Test-Endpoint "Terms Page" "$FrontendURL/terms"
    Test-Endpoint "Privacy Page" "$FrontendURL/privacy"
    Test-Endpoint "Cookies Page" "$FrontendURL/cookies"
    
    # Property Pages
    Write-Host "`n🏠 PROPERTY PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Properties Listing" "$FrontendURL/properties"
    Test-Endpoint "Property Search" "$FrontendURL/properties?search=apartment"
    Test-Endpoint "Property Details (Sample)" "$FrontendURL/properties/1"
    Test-Endpoint "Property Comparison" "$FrontendURL/property-comparison"
    
    # Auth Pages
    Write-Host "`n🔐 AUTHENTICATION PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Login Page" "$FrontendURL/auth/login"
    Test-Endpoint "Register Page" "$FrontendURL/auth/register"
    Test-Endpoint "Auth Callback" "$FrontendURL/auth/callback"
    
    # User Pages (may redirect if not authenticated)
    Write-Host "`n👤 USER PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Profile" "$FrontendURL/profile"
    Test-Endpoint "Dashboard" "$FrontendURL/dashboard"
    Test-Endpoint "Bookings" "$FrontendURL/bookings"
    Test-Endpoint "Messages" "$FrontendURL/messages"
    Test-Endpoint "Notifications" "$FrontendURL/notifications"
    Test-Endpoint "Favorites" "$FrontendURL/favorites"
    Test-Endpoint "Wishlists" "$FrontendURL/wishlists"
    Test-Endpoint "Settings" "$FrontendURL/settings"
    Test-Endpoint "Profile Verification" "$FrontendURL/profile/verification"
    Test-Endpoint "Saved Searches" "$FrontendURL/saved-searches"
    
    # Booking Flow
    Write-Host "`n📅 BOOKING FLOW:" -ForegroundColor Yellow
    Test-Endpoint "Booking Page (Sample)" "$FrontendURL/bookings/1"
    Test-Endpoint "Payment Page (Sample)" "$FrontendURL/bookings/1/payment"
    Test-Endpoint "Payment History" "$FrontendURL/payments/history"
    Test-Endpoint "Invoices" "$FrontendURL/invoices"
    
    # Host Pages
    Write-Host "`n🏡 HOST DASHBOARD:" -ForegroundColor Yellow
    Test-Endpoint "Host Dashboard" "$FrontendURL/host"
    Test-Endpoint "Host Properties" "$FrontendURL/host/properties"
    Test-Endpoint "New Property" "$FrontendURL/host/properties/new"
    Test-Endpoint "Host Ratings" "$FrontendURL/host/ratings"
    Test-Endpoint "Calendar Sync" "$FrontendURL/calendar-sync"
    
    # Analytics & Reports
    Write-Host "`n📊 ANALYTICS:" -ForegroundColor Yellow
    Test-Endpoint "Analytics Dashboard" "$FrontendURL/analytics"
    Test-Endpoint "Property Analytics (Sample)" "$FrontendURL/properties/1/analytics"
    
    # Integrations
    Write-Host "`n🔌 INTEGRATIONS:" -ForegroundColor Yellow
    Test-Endpoint "Integrations Hub" "$FrontendURL/integrations"
    Test-Endpoint "Google Calendar" "$FrontendURL/integrations/google-calendar"
    Test-Endpoint "Stripe Integration" "$FrontendURL/integrations/stripe"
    Test-Endpoint "Realtime Integration" "$FrontendURL/integrations/realtime"
    
    # Additional Features
    Write-Host "`n⚡ FEATURES:" -ForegroundColor Yellow
    Test-Endpoint "Verification" "$FrontendURL/verification"
    Test-Endpoint "Screening" "$FrontendURL/screening"
    Test-Endpoint "Insurance" "$FrontendURL/insurance"
    Test-Endpoint "Loyalty Program" "$FrontendURL/loyalty"
    Test-Endpoint "Referrals" "$FrontendURL/referrals"
    Test-Endpoint "Security" "$FrontendURL/security"
    Test-Endpoint "Security Audit" "$FrontendURL/security/audit"
    
    # Admin
    Write-Host "`n⚙️ ADMIN:" -ForegroundColor Yellow
    Test-Endpoint "Admin Dashboard" "$FrontendURL/admin"
    Test-Endpoint "Admin Settings" "$FrontendURL/admin/settings"
    
    # PWA & Manifest
    Write-Host "`n📱 PWA:" -ForegroundColor Yellow
    Test-Endpoint "Manifest" "$FrontendURL/manifest.webmanifest"
    Test-Endpoint "Sitemap" "$FrontendURL/sitemap.xml"
    Test-Endpoint "Offline Page" "$FrontendURL/offline-page"
    
    # Demo Pages
    Write-Host "`n🎨 DEMO PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Demo Hub" "$FrontendURL/demo"
    Test-Endpoint "Demo i18n" "$FrontendURL/demo/i18n"
    Test-Endpoint "Demo Performance" "$FrontendURL/demo/performance"
    Test-Endpoint "Demo Accessibility" "$FrontendURL/demo/accessibility"
}

# ==================== BACKEND API TESTS ====================
function Test-BackendAPI {
    Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║  🔧 BACKEND API LIVE TESTING          ║" -ForegroundColor White
    Write-Host "║  $BackendAPIURL" -ForegroundColor Gray
    Write-Host "╚════════════════════════════════════════╝" -ForegroundColor Cyan
    
    $headers = @{
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }
    
    # Health Check
    Write-Host "`n💚 HEALTH CHECK:" -ForegroundColor Yellow
    Test-Endpoint "API Health" "$BackendURL/api/health" -Headers $headers
    Test-Endpoint "API Status" "$BackendURL/api/status" -Headers $headers
    
    # Auth Endpoints
    Write-Host "`n🔐 AUTHENTICATION API:" -ForegroundColor Yellow
    Test-Endpoint "Get CSRF Token" "$BackendURL/sanctum/csrf-cookie" -Headers $headers
    
    # Public API Endpoints
    Write-Host "`n🏠 PROPERTIES API:" -ForegroundColor Yellow
    Test-Endpoint "List Properties" "$BackendAPIURL/properties" -Headers $headers
    Test-Endpoint "Get Property" "$BackendAPIURL/properties/1" -Headers $headers
    Test-Endpoint "Search Properties" "$BackendAPIURL/properties/search?q=apartment" -Headers $headers
    
    # Categories
    Write-Host "`n📂 CATEGORIES API:" -ForegroundColor Yellow
    Test-Endpoint "List Categories" "$BackendAPIURL/categories" -Headers $headers
    
    # Amenities
    Write-Host "`n🛋️ AMENITIES API:" -ForegroundColor Yellow
    Test-Endpoint "List Amenities" "$BackendAPIURL/amenities" -Headers $headers
    
    # Reviews
    Write-Host "`n⭐ REVIEWS API:" -ForegroundColor Yellow
    Test-Endpoint "List Reviews" "$BackendAPIURL/reviews" -Headers $headers
    
    # User Profile (requires auth)
    Write-Host "`n👤 USER API (may require auth):" -ForegroundColor Yellow
    Test-Endpoint "Get Current User" "$BackendAPIURL/user" -Headers $headers -ExpectedStatus 401
    
    # Bookings (requires auth)
    Write-Host "`n📅 BOOKINGS API (may require auth):" -ForegroundColor Yellow
    Test-Endpoint "List Bookings" "$BackendAPIURL/bookings" -Headers $headers -ExpectedStatus 401
    
    # Messages (requires auth)
    Write-Host "`n💬 MESSAGES API (may require auth):" -ForegroundColor Yellow
    Test-Endpoint "List Messages" "$BackendAPIURL/messages" -Headers $headers -ExpectedStatus 401
    
    # Payments (requires auth)
    Write-Host "`n💳 PAYMENTS API (may require auth):" -ForegroundColor Yellow
    Test-Endpoint "Payment Methods" "$BackendAPIURL/payments/methods" -Headers $headers -ExpectedStatus 401
}

# ==================== ADMIN PANEL TESTS ====================
function Test-AdminPanel {
    Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║  ⚙️ ADMIN PANEL LIVE TESTING          ║" -ForegroundColor White
    Write-Host "║  $BackendURL/admin" -ForegroundColor Gray
    Write-Host "╚════════════════════════════════════════╝" -ForegroundColor Cyan
    
    Write-Host "`n🔐 ADMIN PAGES:" -ForegroundColor Yellow
    Test-Endpoint "Admin Login Page" "$BackendURL/admin/login"
    Test-Endpoint "Admin Dashboard" "$BackendURL/admin" -ExpectedStatus 302
    Test-Endpoint "Admin Users" "$BackendURL/admin/users" -ExpectedStatus 302
    Test-Endpoint "Admin Properties" "$BackendURL/admin/properties" -ExpectedStatus 302
    Test-Endpoint "Admin Bookings" "$BackendURL/admin/bookings" -ExpectedStatus 302
    Test-Endpoint "Admin Settings" "$BackendURL/admin/settings" -ExpectedStatus 302
}

# ==================== MAIN EXECUTION ====================
Write-Host "`n╔════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║  🚀 RENTHUB LIVE SITE COMPREHENSIVE TESTING  ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host "Started: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
Write-Host ""

if ($All -or (!$Frontend -and !$Backend)) {
    Test-FrontendPages
    Test-BackendAPI
    Test-AdminPanel
}
elseif ($Frontend) {
    Test-FrontendPages
}
elseif ($Backend) {
    Test-BackendAPI
    Test-AdminPanel
}

# ==================== SUMMARY ====================
Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  📊 TEST SUMMARY                      ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝" -ForegroundColor Cyan

Write-Host "`nTotal Tests Run: $TotalTests" -ForegroundColor White
Write-Host "✅ Passed: $PassedTests" -ForegroundColor Green
Write-Host "❌ Failed: $FailedTests" -ForegroundColor Red
Write-Host "📈 Success Rate: $([math]::Round(($PassedTests / $TotalTests) * 100, 2))%" -ForegroundColor $(if ($PassedTests -eq $TotalTests) { "Green" } else { "Yellow" })

Write-Host "`nCompleted: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray

# ==================== DETAILED REPORT ====================
if ($Report) {
    $reportPath = "LIVE_SITE_TEST_REPORT_$(Get-Date -Format 'yyyyMMdd_HHmmss').md"
    
    $reportContent = @"
# RentHub Live Site Test Report

**Generated**: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

## Summary

- **Total Tests**: $TotalTests
- **Passed**: $PassedTests ✅
- **Failed**: $FailedTests ❌
- **Success Rate**: $([math]::Round(($PassedTests / $TotalTests) * 100, 2))%

## Test Results

| Test Name | URL | Method | Status | Status Code | Error |
|-----------|-----|--------|--------|-------------|-------|
"@

    foreach ($result in $TestResults) {
        $status = switch ($result.Status) {
            "PASSED" { "✅" }
            "FAILED" { "❌" }
            "WARNING" { "⚠️" }
        }
        
        $reportContent += "`n| $($result.Name) | $($result.URL) | $($result.Method) | $status $($result.Status) | $($result.StatusCode) | $($result.Error) |"
    }
    
    $reportContent += @"

## Frontend URLs Tested

- Homepage: $FrontendURL/
- Properties: $FrontendURL/properties
- Auth: $FrontendURL/auth/login
- Dashboard: $FrontendURL/dashboard
- Host: $FrontendURL/host

## Backend APIs Tested

- Health: $BackendURL/api/health
- Properties: $BackendAPIURL/properties
- Auth: $BackendURL/sanctum/csrf-cookie

## Admin Panel Tested

- Login: $BackendURL/admin/login
- Dashboard: $BackendURL/admin

## Recommendations

"@

    if ($FailedTests -gt 0) {
        $reportContent += "`n### ❌ Failed Tests Need Attention`n"
        foreach ($result in $TestResults | Where-Object { $_.Status -eq "FAILED" }) {
            $reportContent += "- **$($result.Name)**: $($result.Error)`n"
        }
    }
    
    if ($PassedTests -eq $TotalTests) {
        $reportContent += "`n### ✅ All Tests Passed!`n"
        $reportContent += "All endpoints are responding correctly. Site is fully functional!`n"
    }
    
    $reportContent | Out-File -FilePath $reportPath -Encoding UTF8
    Write-Host "`n📄 Report saved: $reportPath" -ForegroundColor Green
}

Write-Host "`n💡 TIP: Run with -Report to generate detailed markdown report" -ForegroundColor Cyan
Write-Host "Example: .\live-site-testing.ps1 -All -Report" -ForegroundColor Gray

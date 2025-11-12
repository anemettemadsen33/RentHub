#!/usr/bin/env pwsh
# RentHub - Complete Page Verification Script
# Tests all 58 routes for existence and basic functionality

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  🔍 RentHub Page Verification Test" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "https://rent-hub-beta.vercel.app"
$passed = 0
$failed = 0
$warnings = 0

# Test routes
$routes = @(
    # Core Pages
    @{ Path = "/"; Name = "Homepage" },
    
    # Auth
    @{ Path = "/auth/login"; Name = "Login" },
    @{ Path = "/auth/register"; Name = "Register" },
    
    # Properties
    @{ Path = "/properties"; Name = "Properties Listing" },
    @{ Path = "/properties/1"; Name = "Property Details" },
    @{ Path = "/properties/1/reviews"; Name = "Property Reviews" },
    @{ Path = "/properties/1/analytics"; Name = "Property Analytics" },
    @{ Path = "/properties/1/calendar"; Name = "Property Calendar" },
    @{ Path = "/properties/1/maintenance"; Name = "Property Maintenance" },
    @{ Path = "/properties/1/smart-locks"; Name = "Smart Locks" },
    @{ Path = "/properties/1/access"; Name = "Property Access" },
    
    # Bookings
    @{ Path = "/bookings"; Name = "Bookings List" },
    @{ Path = "/bookings/1"; Name = "Booking Details" },
    @{ Path = "/bookings/1/payment"; Name = "Booking Payment" },
    
    # Dashboard
    @{ Path = "/dashboard"; Name = "Main Dashboard" },
    @{ Path = "/dashboard/owner"; Name = "Owner Dashboard" },
    @{ Path = "/dashboard/properties"; Name = "Dashboard Properties" },
    @{ Path = "/dashboard/properties/1"; Name = "Edit Property" },
    @{ Path = "/dashboard/properties/new"; Name = "New Property" },
    @{ Path = "/dashboard/settings"; Name = "Dashboard Settings" },
    @{ Path = "/dashboard-new"; Name = "New Dashboard" },
    
    # Messages & Notifications
    @{ Path = "/messages"; Name = "Messages" },
    @{ Path = "/messages/1"; Name = "Message Thread" },
    @{ Path = "/notifications"; Name = "Notifications" },
    
    # User Features
    @{ Path = "/profile"; Name = "Profile" },
    @{ Path = "/profile/verification"; Name = "Profile Verification" },
    @{ Path = "/favorites"; Name = "Favorites" },
    @{ Path = "/wishlists"; Name = "Wishlists" },
    @{ Path = "/saved-searches"; Name = "Saved Searches" },
    @{ Path = "/verification"; Name = "Verification" },
    
    # Payments & Finance
    @{ Path = "/payments"; Name = "Payments" },
    @{ Path = "/payments/history"; Name = "Payment History" },
    @{ Path = "/invoices"; Name = "Invoices" },
    
    # Analytics & Admin
    @{ Path = "/analytics"; Name = "Analytics" },
    @{ Path = "/admin/settings"; Name = "Admin Settings" },
    
    # Host Features
    @{ Path = "/host/properties"; Name = "Host Properties" },
    @{ Path = "/host/properties/new"; Name = "New Host Property" },
    @{ Path = "/host/ratings"; Name = "Host Ratings" },
    
    # Advanced Features
    @{ Path = "/property-comparison"; Name = "Property Comparison" },
    @{ Path = "/loyalty"; Name = "Loyalty Program" },
    @{ Path = "/referrals"; Name = "Referrals" },
    @{ Path = "/insurance"; Name = "Insurance" },
    @{ Path = "/screening"; Name = "Screening" },
    @{ Path = "/security/audit"; Name = "Security Audit" },
    @{ Path = "/calendar-sync"; Name = "Calendar Sync" },
    
    # Static Pages
    @{ Path = "/help"; Name = "Help" },
    @{ Path = "/faq"; Name = "FAQ" },
    @{ Path = "/contact"; Name = "Contact" },
    @{ Path = "/about"; Name = "About" },
    @{ Path = "/careers"; Name = "Careers" },
    @{ Path = "/press"; Name = "Press" },
    @{ Path = "/privacy"; Name = "Privacy Policy" },
    @{ Path = "/terms"; Name = "Terms of Service" },
    @{ Path = "/cookies"; Name = "Cookie Policy" },
    @{ Path = "/settings"; Name = "Settings" },
    
    # Demo Pages
    @{ Path = "/demo/accessibility"; Name = "Demo: Accessibility" },
    @{ Path = "/demo/form-validation"; Name = "Demo: Form Validation" },
    @{ Path = "/demo/i18n"; Name = "Demo: i18n" },
    @{ Path = "/demo/image-optimization"; Name = "Demo: Images" },
    @{ Path = "/demo/logger"; Name = "Demo: Logger" },
    @{ Path = "/demo/optimistic-ui"; Name = "Demo: Optimistic UI" },
    @{ Path = "/demo/performance"; Name = "Demo: Performance" },
    
    # Utility Pages
    @{ Path = "/offline-page"; Name = "Offline Page" }
)

Write-Host "Testing $($routes.Count) routes...`n" -ForegroundColor Yellow

foreach ($route in $routes) {
    $url = "$baseUrl$($route.Path)"
    try {
        $response = Invoke-WebRequest -Uri $url -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ " -ForegroundColor Green -NoNewline
            Write-Host "$($route.Name.PadRight(35)) " -NoNewline
            Write-Host "[$($route.Path)]" -ForegroundColor DarkGray
            $passed++
        } else {
            Write-Host "⚠️  " -ForegroundColor Yellow -NoNewline
            Write-Host "$($route.Name.PadRight(35)) " -NoNewline
            Write-Host "Status: $($response.StatusCode)" -ForegroundColor Yellow
            $warnings++
        }
    }
    catch {
        Write-Host "❌ " -ForegroundColor Red -NoNewline
        Write-Host "$($route.Name.PadRight(35)) " -NoNewline
        Write-Host "FAILED - $($_.Exception.Message)" -ForegroundColor Red
        $failed++
    }
    
    Start-Sleep -Milliseconds 100
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  📊 Test Results" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$total = $routes.Count
$passRate = [math]::Round(($passed / $total) * 100, 2)

Write-Host "Total Routes: $total" -ForegroundColor White
Write-Host "Passed:       $passed ($passRate%)" -ForegroundColor Green
Write-Host "Warnings:     $warnings" -ForegroundColor Yellow
Write-Host "Failed:       $failed" -ForegroundColor $(if ($failed -gt 0) { "Red" } else { "Green" })

if ($failed -eq 0) {
    Write-Host "`n🎉 ALL TESTS PASSED!" -ForegroundColor Green
} elseif ($failed -lt 5) {
    Write-Host "`n⚠️  MOSTLY WORKING - Minor issues found" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ MULTIPLE FAILURES - Review needed" -ForegroundColor Red
}

Write-Host "========================================`n" -ForegroundColor Cyan

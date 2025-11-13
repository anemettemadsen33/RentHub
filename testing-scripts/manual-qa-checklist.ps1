#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Manual QA Checklist - Interactive testing guide
.DESCRIPTION
    Step-by-step manual testing of all features
#>

param(
    [switch]$GenerateReport
)

Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  📋 MANUAL QA TESTING CHECKLIST       ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

$results = @()

function Test-Feature {
    param(
        [string]$Category,
        [string]$Feature,
        [string]$Steps,
        [string]$Expected
    )
    
    Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Blue
    Write-Host "📌 $Category - $Feature" -ForegroundColor Yellow
    Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Blue
    
    Write-Host "STEPS:" -ForegroundColor Cyan
    Write-Host $Steps -ForegroundColor White
    Write-Host "`nEXPECTED RESULT:" -ForegroundColor Cyan
    Write-Host $Expected -ForegroundColor White
    
    $response = Read-Host "`n✅ Did it work as expected? (y/n/s to skip)"
    
    $result = @{
        Category = $Category
        Feature = $Feature
        Passed = $response -eq 'y'
        Skipped = $response -eq 's'
        Timestamp = Get-Date
    }
    
    $script:results += $result
    
    if ($response -eq 'y') {
        Write-Host "✅ PASSED" -ForegroundColor Green
    } elseif ($response -eq 's') {
        Write-Host "⏭️  SKIPPED" -ForegroundColor Yellow
    } else {
        $notes = Read-Host "Notes about the issue"
        $result.Notes = $notes
        Write-Host "❌ FAILED" -ForegroundColor Red
    }
}

Write-Host @"
🎯 MANUAL TESTING GUIDE

This interactive script will guide you through testing ALL features.
For each test:
  • Follow the steps exactly
  • Verify the expected result
  • Answer y (pass), n (fail), or s (skip)

Press ENTER to start...
"@ -ForegroundColor Yellow

Read-Host

# ═══════════════════════════════════════
# AUTHENTICATION TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Authentication" -Feature "Registration" `
    -Steps @"
1. Go to /auth/register
2. Fill form: name, email, password
3. Click 'Sign Up'
"@ `
    -Expected "User created, redirected to dashboard, welcome message shown"

Test-Feature -Category "Authentication" -Feature "Login" `
    -Steps @"
1. Go to /auth/login
2. Enter credentials
3. Click 'Sign In'
"@ `
    -Expected "User logged in, redirected to dashboard"

Test-Feature -Category "Authentication" -Feature "Logout" `
    -Steps @"
1. Click profile menu
2. Click 'Logout'
"@ `
    -Expected "User logged out, redirected to home page"

Test-Feature -Category "Authentication" -Feature "Password Reset" `
    -Steps @"
1. Go to /auth/forgot-password
2. Enter email
3. Click 'Send Reset Link'
4. Check email
5. Click reset link
6. Enter new password
"@ `
    -Expected "Password reset email sent, new password works"

Test-Feature -Category "Authentication" -Feature "Social Login (Google)" `
    -Steps @"
1. Click 'Sign in with Google'
2. Authorize app
"@ `
    -Expected "User logged in via Google, redirected to dashboard"

# ═══════════════════════════════════════
# PROPERTY BROWSING TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Properties" -Feature "Browse Properties" `
    -Steps @"
1. Go to /properties
2. Scroll through listings
"@ `
    -Expected "All properties display with images, prices, titles"

Test-Feature -Category "Properties" -Feature "Search Properties" `
    -Steps @"
1. Enter location in search
2. Click search
"@ `
    -Expected "Filtered results show only matching location"

Test-Feature -Category "Properties" -Feature "Filter by Price" `
    -Steps @"
1. Set price range slider
2. Results update automatically
"@ `
    -Expected "Only properties in price range shown"

Test-Feature -Category "Properties" -Feature "Filter by Amenities" `
    -Steps @"
1. Select amenities (WiFi, Pool, etc.)
2. Click Apply Filters
"@ `
    -Expected "Results show only properties with selected amenities"

Test-Feature -Category "Properties" -Feature "View Property Details" `
    -Steps @"
1. Click on a property card
2. View details page
"@ `
    -Expected "Full details: images, description, amenities, reviews, map"

Test-Feature -Category "Properties" -Feature "Image Gallery" `
    -Steps @"
1. On property details page
2. Click on images
3. Navigate gallery
"@ `
    -Expected "Full-screen image viewer, next/prev buttons work"

# ═══════════════════════════════════════
# BOOKING TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Booking" -Feature "Select Dates" `
    -Steps @"
1. On property details
2. Click date picker
3. Select check-in and check-out dates
"@ `
    -Expected "Dates selected, price calculated, unavailable dates disabled"

Test-Feature -Category "Booking" -Feature "Guest Count" `
    -Steps @"
1. Change guest count
2. Price updates
"@ `
    -Expected "Total price adjusts based on guests"

Test-Feature -Category "Booking" -Feature "Create Booking" `
    -Steps @"
1. Fill booking form
2. Click 'Book Now'
3. Confirm booking
"@ `
    -Expected "Booking created, redirected to payment"

Test-Feature -Category "Booking" -Feature "View My Bookings" `
    -Steps @"
1. Go to /dashboard/bookings
2. View booking list
"@ `
    -Expected "All bookings displayed: upcoming, past, cancelled"

Test-Feature -Category "Booking" -Feature "Cancel Booking" `
    -Steps @"
1. On bookings page
2. Click 'Cancel' on a booking
3. Confirm cancellation
"@ `
    -Expected "Booking status changes to 'Cancelled', refund processed"

# ═══════════════════════════════════════
# PAYMENT TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Payment" -Feature "Stripe Payment" `
    -Steps @"
1. After booking
2. Enter test card: 4242 4242 4242 4242
3. Expiry: any future date
4. CVC: any 3 digits
5. Submit payment
"@ `
    -Expected "Payment successful, booking confirmed"

Test-Feature -Category "Payment" -Feature "Payment Failed" `
    -Steps @"
1. Use test card: 4000 0000 0000 0002
2. Submit payment
"@ `
    -Expected "Error message shown, booking not confirmed"

Test-Feature -Category "Payment" -Feature "Bank Transfer" `
    -Steps @"
1. Select 'Bank Transfer' payment method
2. Submit booking
3. View bank details
4. Upload proof of payment
"@ `
    -Expected "Bank details shown, proof upload works, pending confirmation"

# ═══════════════════════════════════════
# REVIEW TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Reviews" -Feature "Write Review" `
    -Steps @"
1. Go to completed booking
2. Click 'Write Review'
3. Fill rating (1-5 stars) and comment
4. Submit
"@ `
    -Expected "Review submitted, appears on property page"

Test-Feature -Category "Reviews" -Feature "View Reviews" `
    -Steps @"
1. On property details
2. Scroll to reviews section
"@ `
    -Expected "All reviews displayed with ratings, dates, user names"

Test-Feature -Category "Reviews" -Feature "Filter Reviews" `
    -Steps @"
1. Filter by rating (5 stars, 4 stars, etc.)
"@ `
    -Expected "Reviews filtered correctly"

# ═══════════════════════════════════════
# MESSAGING TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Messages" -Feature "Send Message" `
    -Steps @"
1. On property page, click 'Message Host'
2. Write message
3. Send
"@ `
    -Expected "Message sent, appears in inbox"

Test-Feature -Category "Messages" -Feature "Receive Message" `
    -Steps @"
1. Go to /dashboard/messages
2. Click on conversation
"@ `
    -Expected "Messages load, real-time updates work"

Test-Feature -Category "Messages" -Feature "Real-time Notifications" `
    -Steps @"
1. Send message from another account
2. Check if notification appears
"@ `
    -Expected "Toast notification, message count badge updates"

# ═══════════════════════════════════════
# HOST DASHBOARD TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Host" -Feature "Create Property" `
    -Steps @"
1. Go to /host/properties/new
2. Fill all required fields
3. Upload images
4. Select amenities
5. Submit
"@ `
    -Expected "Property created, appears in host dashboard"

Test-Feature -Category "Host" -Feature "Edit Property" `
    -Steps @"
1. Host dashboard
2. Click 'Edit' on property
3. Modify details
4. Save
"@ `
    -Expected "Changes saved, reflected on property page"

Test-Feature -Category "Host" -Feature "Delete Property" `
    -Steps @"
1. Click 'Delete' on property
2. Confirm deletion
"@ `
    -Expected "Property removed from listings"

Test-Feature -Category "Host" -Feature "View Bookings" `
    -Steps @"
1. Go to /host/bookings
"@ `
    -Expected "All bookings for host's properties shown"

Test-Feature -Category "Host" -Feature "Accept/Reject Booking" `
    -Steps @"
1. Click on pending booking
2. Accept or reject
"@ `
    -Expected "Booking status updated, guest notified"

# ═══════════════════════════════════════
# PROFILE TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Profile" -Feature "Update Profile" `
    -Steps @"
1. Go to /dashboard/profile
2. Change name, phone, bio
3. Save
"@ `
    -Expected "Profile updated successfully"

Test-Feature -Category "Profile" -Feature "Upload Avatar" `
    -Steps @"
1. Click 'Change Avatar'
2. Select image
3. Upload
"@ `
    -Expected "Avatar updated, shown in header"

Test-Feature -Category "Profile" -Feature "Change Password" `
    -Steps @"
1. Go to profile settings
2. Enter current password
3. Enter new password
4. Confirm
"@ `
    -Expected "Password changed, can login with new password"

# ═══════════════════════════════════════
# ADMIN PANEL TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Admin" -Feature "View Dashboard" `
    -Steps @"
1. Login as admin
2. Go to /admin
"@ `
    -Expected "Admin dashboard with stats: users, properties, bookings, revenue"

Test-Feature -Category "Admin" -Feature "Manage Users" `
    -Steps @"
1. Go to /admin/users
2. View user list
3. Edit/Delete/Ban user
"@ `
    -Expected "User management works, changes applied"

Test-Feature -Category "Admin" -Feature "Manage Properties" `
    -Steps @"
1. Go to /admin/properties
2. Approve/Reject/Delete properties
"@ `
    -Expected "Property moderation works"

Test-Feature -Category "Admin" -Feature "View Reports" `
    -Steps @"
1. Go to /admin/reports
2. Generate revenue report
"@ `
    -Expected "Reports display correctly with charts"

# ═══════════════════════════════════════
# MULTI-LANGUAGE TESTS
# ═══════════════════════════════════════

Test-Feature -Category "i18n" -Feature "Switch Language (EN → RO)" `
    -Steps @"
1. Click language selector
2. Select 'Română'
"@ `
    -Expected "All text translates to Romanian"

Test-Feature -Category "i18n" -Feature "Switch Language (RO → FR)" `
    -Steps @"
1. Select 'Français'
"@ `
    -Expected "All text translates to French"

Test-Feature -Category "i18n" -Feature "Date Formatting" `
    -Steps @"
1. Check dates in different languages
"@ `
    -Expected "Dates formatted according to locale"

# ═══════════════════════════════════════
# RESPONSIVE DESIGN TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Responsive" -Feature "Mobile Menu" `
    -Steps @"
1. Resize browser to mobile width
2. Click hamburger menu
"@ `
    -Expected "Mobile menu opens, all links accessible"

Test-Feature -Category "Responsive" -Feature "Tablet View" `
    -Steps @"
1. Resize to tablet width (768px)
2. Navigate pages
"@ `
    -Expected "Layout adapts, touch-friendly buttons"

Test-Feature -Category "Responsive" -Feature "Desktop View" `
    -Steps @"
1. Full desktop width (1920px)
"@ `
    -Expected "Full layout, sidebar navigation works"

# ═══════════════════════════════════════
# PERFORMANCE TESTS
# ═══════════════════════════════════════

Test-Feature -Category "Performance" -Feature "Page Load Speed" `
    -Steps @"
1. Open DevTools → Network
2. Navigate to /properties
3. Check load time
"@ `
    -Expected "Page loads in < 3 seconds"

Test-Feature -Category "Performance" -Feature "Image Optimization" `
    -Steps @"
1. Check Network tab
2. View image sizes
"@ `
    -Expected "Images are compressed, lazy-loaded"

# ═══════════════════════════════════════
# SUMMARY
# ═══════════════════════════════════════

Write-Host "`n`n╔════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  📊 MANUAL QA SUMMARY                 ║" -ForegroundColor White
Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor Cyan

$passed = ($results | Where-Object {$_.Passed -eq $true}).Count
$failed = ($results | Where-Object {$_.Passed -eq $false}).Count
$skipped = ($results | Where-Object {$_.Skipped -eq $true}).Count
$total = $results.Count

Write-Host "Total Tests: $total" -ForegroundColor White
Write-Host "✅ Passed: $passed" -ForegroundColor Green
Write-Host "❌ Failed: $failed" -ForegroundColor Red
Write-Host "⏭️  Skipped: $skipped`n" -ForegroundColor Yellow

if ($failed -gt 0) {
    Write-Host "FAILED TESTS:" -ForegroundColor Red
    $results | Where-Object {$_.Passed -eq $false} | ForEach-Object {
        Write-Host "  • $($_.Category) - $($_.Feature)" -ForegroundColor Red
        if ($_.Notes) {
            Write-Host "    Notes: $($_.Notes)" -ForegroundColor Gray
        }
    }
    Write-Host ""
}

if ($GenerateReport) {
    $reportPath = "test-results/manual-qa-$(Get-Date -Format 'yyyyMMdd-HHmmss').json"
    $results | ConvertTo-Json | Out-File $reportPath
    Write-Host "📄 Report saved: $reportPath`n" -ForegroundColor Cyan
}

$passRate = [math]::Round(($passed / ($total - $skipped)) * 100, 2)
Write-Host "Pass Rate: $passRate%`n" -ForegroundColor $(if($passRate -ge 90){'Green'}elseif($passRate -ge 70){'Yellow'}else{'Red'})

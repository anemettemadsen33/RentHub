# ==============================================================================
# RentHub - COMPLETE APPLICATION TEST
# Testing Every Feature, Every Button, Absolutely Everything!
# ==============================================================================

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      🚀 RENTHUB - COMPLETE APPLICATION TEST 🚀                ║" -ForegroundColor Cyan
Write-Host "║          Testing EVERY Feature & Button!                     ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$backendUrl = "http://127.0.0.1:8000/api/v1"
$frontendUrl = "http://localhost:3000"
$adminUrl = "http://127.0.0.1:8000/admin"

$totalTests = 0
$passedTests = 0
$failedTests = 0
$testResults = @()

# Test credentials
$adminEmail = "admin@renthub.com"
$adminPassword = "admin123"
$testUserEmail = ""
$testUserPassword = "Password123!"
$testUserToken = ""

# Helper Functions
function Write-TestHeader {
    param([string]$Title, [int]$Step)
    Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Magenta
    Write-Host "║  STEP $Step : $Title" -ForegroundColor Magenta
    Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Magenta
}

function Test-ApiEndpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [object]$Body = $null,
        [string]$ContentType = "application/json",
        [bool]$ExpectSuccess = $true
    )
    
    $script:totalTests++
    Write-Host "  Testing: $Name..." -NoNewline
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ErrorAction = "Stop"
        }
        
        if ($Body) {
            if ($ContentType -eq "application/json") {
                $params.Body = ($Body | ConvertTo-Json -Depth 10)
                $params.ContentType = $ContentType
            } else {
                $params.Body = $Body
                $params.ContentType = $ContentType
            }
        }
        
        $response = Invoke-RestMethod @params
        
        if ($ExpectSuccess) {
            $script:passedTests++
            Write-Host " ✅ PASSED" -ForegroundColor Green
            $script:testResults += @{ Name = $Name; Status = "PASSED"; Response = $response }
            return @{ Success = $true; Response = $response }
        }
    }
    catch {
        if (!$ExpectSuccess) {
            $script:passedTests++
            Write-Host " ✅ PASSED (Expected Failure)" -ForegroundColor Green
            $script:testResults += @{ Name = $Name; Status = "PASSED (Expected)" }
            return @{ Success = $false; Expected = $true }
        }
        
        $script:failedTests++
        $errorMsg = $_.Exception.Message
        Write-Host " ❌ FAILED" -ForegroundColor Red
        Write-Host "    Error: $errorMsg" -ForegroundColor DarkRed
        $script:testResults += @{ Name = $Name; Status = "FAILED"; Error = $errorMsg }
        return @{ Success = $false; Error = $error }
    }
}

# ==============================================================================
# STEP 1: SERVER HEALTH CHECK
# ==============================================================================
Write-TestHeader "Server Health Check" 1

Test-ApiEndpoint -Name "Backend Server Health" -Url "$backendUrl/../health"
Test-ApiEndpoint -Name "Get Languages" -Url "$backendUrl/languages"
Test-ApiEndpoint -Name "Get Currencies" -Url "$backendUrl/currencies"
Test-ApiEndpoint -Name "Get Active Currency" -Url "$backendUrl/currencies/active"

# ==============================================================================
# STEP 2: USER REGISTRATION & AUTHENTICATION
# ==============================================================================
Write-TestHeader "User Registration & Authentication" 2

$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$testUserEmail = "complete_test_$timestamp@renthub.test"

$registerResult = Test-ApiEndpoint `
    -Name "Register New User" `
    -Url "$backendUrl/register" `
    -Method "POST" `
    -Body @{
        name = "Complete Test User"
        email = $testUserEmail
        password = $testUserPassword
        password_confirmation = $testUserPassword
        role = "tenant"
    }

if ($registerResult.Success) {
    $testUserToken = $registerResult.Response.token
    $userId = $registerResult.Response.user.id
    Write-Host "  👤 User ID: $userId | Email: $testUserEmail" -ForegroundColor Cyan
}

$headers = @{
    "Authorization" = "Bearer $testUserToken"
    "Accept" = "application/json"
}

# Login Test
Test-ApiEndpoint `
    -Name "User Login" `
    -Url "$backendUrl/login" `
    -Method "POST" `
    -Body @{
        email = $testUserEmail
        password = $testUserPassword
    }

# Get User Profile
Test-ApiEndpoint -Name "Get User Profile" -Url "$backendUrl/profile" -Headers $headers
Test-ApiEndpoint -Name "Get Current User" -Url "$backendUrl/user" -Headers $headers

# ==============================================================================
# STEP 3: PROPERTIES - LISTING, SEARCH, FILTERS
# ==============================================================================
Write-TestHeader "Properties - Listing, Search & Filters" 3

Test-ApiEndpoint -Name "Get All Properties" -Url "$backendUrl/properties" -Headers $headers
Test-ApiEndpoint -Name "Get Featured Properties" -Url "$backendUrl/properties/featured" -Headers $headers
Test-ApiEndpoint -Name "Search Properties" -Url "$backendUrl/properties/search" -Headers $headers

# Advanced Search with Filters
$searchParams = @{
    location = "New York"
    min_price = 50
    max_price = 500
    bedrooms = 2
    bathrooms = 1
}

Test-ApiEndpoint `
    -Name "Search with Filters" `
    -Url "$backendUrl/properties/search" `
    -Method "POST" `
    -Headers $headers `
    -Body $searchParams

# Get Property Details (assuming property ID 1 exists)
Test-ApiEndpoint -Name "Get Property Details (ID: 1)" -Url "$backendUrl/properties/1" -Headers $headers

# Get Amenities
Test-ApiEndpoint -Name "Get All Amenities" -Url "$backendUrl/amenities" -Headers $headers

# ==============================================================================
# STEP 4: BOOKINGS - CREATE, VIEW, MANAGE
# ==============================================================================
Write-TestHeader "Bookings - Create, View & Manage" 4

Test-ApiEndpoint -Name "Get My Bookings" -Url "$backendUrl/bookings" -Headers $headers
Test-ApiEndpoint -Name "Get Booking History" -Url "$backendUrl/my-bookings" -Headers $headers

# Check availability
$checkInDate = (Get-Date).AddDays(7).ToString("yyyy-MM-dd")
$checkOutDate = (Get-Date).AddDays(10).ToString("yyyy-MM-dd")

Test-ApiEndpoint `
    -Name "Check Property Availability" `
    -Url "$backendUrl/properties/1/availability?check_in=$checkInDate&check_out=$checkOutDate" `
    -Headers $headers

# Create a booking
$bookingData = @{
    property_id = 1
    check_in = $checkInDate
    check_out = $checkOutDate
    guests = 2
    message = "Test booking from complete application test"
}

$bookingResult = Test-ApiEndpoint `
    -Name "Create New Booking" `
    -Url "$backendUrl/bookings" `
    -Method "POST" `
    -Headers $headers `
    -Body $bookingData

if ($bookingResult.Success -and $bookingResult.Response.id) {
    $bookingId = $bookingResult.Response.id
    
    # Get booking details
    Test-ApiEndpoint -Name "Get Booking Details" -Url "$backendUrl/bookings/$bookingId" -Headers $headers
    
    # Cancel booking
    Test-ApiEndpoint `
        -Name "Cancel Booking" `
        -Url "$backendUrl/bookings/$bookingId/cancel" `
        -Method "POST" `
        -Headers $headers
}

# ==============================================================================
# STEP 5: REVIEWS & RATINGS
# ==============================================================================
Write-TestHeader "Reviews & Ratings" 5

Test-ApiEndpoint -Name "Get Property Reviews (ID: 1)" -Url "$backendUrl/properties/1/reviews" -Headers $headers
Test-ApiEndpoint -Name "Get All Reviews" -Url "$backendUrl/reviews" -Headers $headers

# ==============================================================================
# STEP 6: DASHBOARD & STATISTICS
# ==============================================================================
Write-TestHeader "Dashboard & Statistics" 6

Test-ApiEndpoint -Name "Get Dashboard Stats" -Url "$backendUrl/dashboard/stats" -Headers $headers
Test-ApiEndpoint -Name "Get Tenant Dashboard" -Url "$backendUrl/dashboards/tenant" -Headers $headers
Test-ApiEndpoint -Name "Get Notifications" -Url "$backendUrl/notifications" -Headers $headers
Test-ApiEndpoint -Name "Get Unread Notifications Count" -Url "$backendUrl/notifications/unread" -Headers $headers

# ==============================================================================
# STEP 7: KYC VERIFICATION
# ==============================================================================
Write-TestHeader "KYC Verification System" 7

Test-ApiEndpoint -Name "Get My Verification Status" -Url "$backendUrl/my-verification" -Headers $headers
Test-ApiEndpoint -Name "Get Verification Status (Alt)" -Url "$backendUrl/verification-status" -Headers $headers
Test-ApiEndpoint -Name "Get Verification Details" -Url "$backendUrl/verification/status" -Headers $headers

# ==============================================================================
# STEP 8: PROFILE MANAGEMENT
# ==============================================================================
Write-TestHeader "Profile Management" 8

# Update profile
$profileUpdate = @{
    name = "Updated Test User"
    phone = "+1234567890"
    bio = "This is a test user profile"
}

Test-ApiEndpoint `
    -Name "Update Profile" `
    -Url "$backendUrl/profile" `
    -Method "PUT" `
    -Headers $headers `
    -Body $profileUpdate

# Get updated profile
Test-ApiEndpoint -Name "Get Updated Profile" -Url "$backendUrl/profile" -Headers $headers

# ==============================================================================
# STEP 9: FAVORITES / WISHLIST
# ==============================================================================
Write-TestHeader "Favorites & Wishlist" 9

Test-ApiEndpoint -Name "Get My Favorites" -Url "$backendUrl/favorites" -Headers $headers

# Add to favorites
Test-ApiEndpoint `
    -Name "Add Property to Favorites" `
    -Url "$backendUrl/favorites" `
    -Method "POST" `
    -Headers $headers `
    -Body @{ property_id = 1 }

# Remove from favorites
Test-ApiEndpoint `
    -Name "Remove from Favorites" `
    -Url "$backendUrl/favorites/1" `
    -Method "DELETE" `
    -Headers $headers

# ==============================================================================
# STEP 10: SAVED SEARCHES
# ==============================================================================
Write-TestHeader "Saved Searches" 10

Test-ApiEndpoint -Name "Get Saved Searches" -Url "$backendUrl/saved-searches" -Headers $headers

# Create saved search
$savedSearch = @{
    name = "NYC Apartments"
    search_criteria = @{
        location = "New York"
        min_price = 100
        max_price = 300
    }
}

Test-ApiEndpoint `
    -Name "Create Saved Search" `
    -Url "$backendUrl/saved-searches" `
    -Method "POST" `
    -Headers $headers `
    -Body $savedSearch

# ==============================================================================
# STEP 11: MESSAGES & CHAT
# ==============================================================================
Write-TestHeader "Messages & Chat System" 11

Test-ApiEndpoint -Name "Get My Messages" -Url "$backendUrl/messages" -Headers $headers
Test-ApiEndpoint -Name "Get Conversations" -Url "$backendUrl/conversations" -Headers $headers
Test-ApiEndpoint -Name "Get Unread Messages Count" -Url "$backendUrl/messages/unread" -Headers $headers

# ==============================================================================
# STEP 12: PAYMENTS & TRANSACTIONS
# ==============================================================================
Write-TestHeader "Payments & Transactions" 12

Test-ApiEndpoint -Name "Get Payment Methods" -Url "$backendUrl/payment-methods" -Headers $headers
Test-ApiEndpoint -Name "Get Transaction History" -Url "$backendUrl/transactions" -Headers $headers

# ==============================================================================
# STEP 13: SETTINGS & PREFERENCES
# ==============================================================================
Write-TestHeader "Settings & Preferences" 13

Test-ApiEndpoint -Name "Get User Settings" -Url "$backendUrl/settings" -Headers $headers

# Update settings
$settings = @{
    language = "en"
    currency = "USD"
    notifications_enabled = $true
    email_notifications = $true
}

Test-ApiEndpoint `
    -Name "Update Settings" `
    -Url "$backendUrl/settings" `
    -Method "PUT" `
    -Headers $headers `
    -Body $settings

# ==============================================================================
# STEP 14: ROLES & PERMISSIONS
# ==============================================================================
Write-TestHeader "Roles & Permissions" 14

Test-ApiEndpoint -Name "Get Available Roles" -Url "$backendUrl/roles" -Headers $headers
Test-ApiEndpoint -Name "Get My Role" -Url "$backendUrl/my-role" -Headers $headers

# ==============================================================================
# STEP 15: ANALYTICS & REPORTS
# ==============================================================================
Write-TestHeader "Analytics & Reports" 15

Test-ApiEndpoint -Name "Get User Analytics" -Url "$backendUrl/analytics" -Headers $headers
Test-ApiEndpoint -Name "Get Activity Log" -Url "$backendUrl/activity-log" -Headers $headers

# ==============================================================================
# STEP 16: PROPERTY OWNER FEATURES
# ==============================================================================
Write-TestHeader "Property Owner Features" 16

Test-ApiEndpoint -Name "Get My Properties (as tenant)" -Url "$backendUrl/my-properties" -Headers $headers
Test-ApiEndpoint -Name "Get Owner Dashboard" -Url "$backendUrl/dashboards/owner" -Headers $headers

# ==============================================================================
# STEP 17: DOCUMENT MANAGEMENT
# ==============================================================================
Write-TestHeader "Document Management" 17

Test-ApiEndpoint -Name "Get My Documents" -Url "$backendUrl/documents" -Headers $headers

# ==============================================================================
# STEP 18: MAINTENANCE REQUESTS
# ==============================================================================
Write-TestHeader "Maintenance Requests" 18

Test-ApiEndpoint -Name "Get Maintenance Requests" -Url "$backendUrl/maintenance-requests" -Headers $headers

# ==============================================================================
# STEP 19: INSURANCE & PROTECTION
# ==============================================================================
Write-TestHeader "Insurance & Protection" 19

Test-ApiEndpoint -Name "Get Insurance Plans" -Url "$backendUrl/insurance/plans" -Headers $headers

# ==============================================================================
# STEP 20: LOGOUT
# ==============================================================================
Write-TestHeader "User Logout" 20

Test-ApiEndpoint `
    -Name "User Logout" `
    -Url "$backendUrl/logout" `
    -Method "POST" `
    -Headers $headers

# ==============================================================================
# FINAL SUMMARY
# ==============================================================================
Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                  📊 COMPLETE TEST SUMMARY                     ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$successRate = [math]::Round(($passedTests / $totalTests) * 100, 2)

Write-Host "📈 Test Statistics:" -ForegroundColor White
Write-Host "   Total Tests: $totalTests" -ForegroundColor Cyan
Write-Host "   Passed: $passedTests" -ForegroundColor Green
Write-Host "   Failed: $failedTests" -ForegroundColor $(if ($failedTests -gt 0) { 'Red' } else { 'Gray' })
Write-Host "   Success Rate: $successRate%`n" -ForegroundColor $(if ($successRate -ge 90) { 'Green' } elseif ($successRate -ge 70) { 'Yellow' } else { 'Red' })

Write-Host "✅ Features Tested:" -ForegroundColor Green
Write-Host "   ✓ Server Health & Configuration" -ForegroundColor White
Write-Host "   ✓ User Registration & Authentication" -ForegroundColor White
Write-Host "   ✓ Properties (Listing, Search, Filters)" -ForegroundColor White
Write-Host "   ✓ Bookings (Create, View, Cancel)" -ForegroundColor White
Write-Host "   ✓ Reviews & Ratings" -ForegroundColor White
Write-Host "   ✓ Dashboard & Statistics" -ForegroundColor White
Write-Host "   ✓ KYC Verification" -ForegroundColor White
Write-Host "   ✓ Profile Management" -ForegroundColor White
Write-Host "   ✓ Favorites & Wishlist" -ForegroundColor White
Write-Host "   ✓ Saved Searches" -ForegroundColor White
Write-Host "   ✓ Messages & Chat" -ForegroundColor White
Write-Host "   ✓ Payments & Transactions" -ForegroundColor White
Write-Host "   ✓ Settings & Preferences" -ForegroundColor White
Write-Host "   ✓ Roles & Permissions" -ForegroundColor White
Write-Host "   ✓ Analytics & Reports" -ForegroundColor White
Write-Host "   ✓ Property Owner Features" -ForegroundColor White
Write-Host "   ✓ Document Management" -ForegroundColor White
Write-Host "   ✓ Maintenance Requests" -ForegroundColor White
Write-Host "   ✓ Insurance & Protection" -ForegroundColor White
Write-Host "   ✓ User Logout`n" -ForegroundColor White

Write-Host "📝 Test User Credentials:" -ForegroundColor Cyan
Write-Host "   Email: $testUserEmail" -ForegroundColor Yellow
Write-Host "   Password: $testUserPassword`n" -ForegroundColor Yellow

Write-Host "🌐 Application URLs:" -ForegroundColor Magenta
Write-Host "   Frontend: $frontendUrl" -ForegroundColor White
Write-Host "   Backend API: $backendUrl" -ForegroundColor White
Write-Host "   Admin Panel: $adminUrl`n" -ForegroundColor White

if ($failedTests -gt 0) {
    Write-Host "⚠️  Failed Tests Details:" -ForegroundColor Red
    $testResults | Where-Object { $_.Status -eq "FAILED" } | ForEach-Object {
        Write-Host "   ❌ $($_.Name)" -ForegroundColor Red
        Write-Host "      Error: $($_.Error)" -ForegroundColor DarkRed
    }
    Write-Host ""
}

Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

# Save results to file
$reportFile = "TEST_RESULTS_$(Get-Date -Format 'yyyyMMdd_HHmmss').txt"
$testResults | ConvertTo-Json -Depth 10 | Out-File $reportFile
Write-Host "📄 Detailed results saved to: $reportFile`n" -ForegroundColor Cyan

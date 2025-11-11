# Complete Booking Flow Test
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  RENTHUB - BOOKING FLOW TEST" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://127.0.0.1:8000/api/v1"
$headers = @{
    'Accept' = 'application/json'
    'Content-Type' = 'application/json'
}

# Step 1: Register a new user
Write-Host "[1] Creating test user..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$registerData = @{
    name = "Test Booking User"
    email = "booking_test_$timestamp@renthub.test"
    password = "TestBooking123!"
    password_confirmation = "TestBooking123!"
    role = "tenant"
} | ConvertTo-Json

try {
    $registerResponse = Invoke-RestMethod -Uri "$baseUrl/register" -Method Post -Headers $headers -Body $registerData
    $token = $registerResponse.token
    Write-Host "   ✓ User created: $($registerResponse.user.email)" -ForegroundColor Green
    Write-Host "   ✓ Token received" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Registration failed: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Update headers with auth token
$authHeaders = @{
    'Accept' = 'application/json'
    'Content-Type' = 'application/json'
    'Authorization' = "Bearer $token"
}

# Step 2: Get available properties
Write-Host "`n[2] Fetching available properties..." -ForegroundColor Yellow
try {
    $properties = Invoke-RestMethod -Uri "$baseUrl/properties" -Headers $authHeaders
    Write-Host "   ✓ Found $($properties.data.Count) properties" -ForegroundColor Green
    
    if ($properties.data.Count -eq 0) {
        Write-Host "   ✗ No properties available for booking" -ForegroundColor Red
        exit 1
    }
    
    # Use first property
    $property = $properties.data[0]
    Write-Host "   ✓ Selected: $($property.title) - $($property.price_per_night)/night" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Failed to fetch properties: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Step 3: Check availability
Write-Host "`n[3] Checking availability..." -ForegroundColor Yellow
$checkIn = (Get-Date).AddDays(7).ToString("yyyy-MM-dd")
$checkOut = (Get-Date).AddDays(10).ToString("yyyy-MM-dd")

$availabilityData = @{
    property_id = $property.id
    check_in = $checkIn
    check_out = $checkOut
} | ConvertTo-Json

try {
    $availability = Invoke-RestMethod -Uri "$baseUrl/check-availability" -Method Post -Headers $authHeaders -Body $availabilityData
    Write-Host "   ✓ Property is available" -ForegroundColor Green
    Write-Host "   ✓ Check-in: $checkIn" -ForegroundColor Green
    Write-Host "   ✓ Check-out: $checkOut" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Property not available: $($_.Exception.Message)" -ForegroundColor Red
}

# Step 4: Create booking
Write-Host "`n[4] Creating booking..." -ForegroundColor Yellow
$bookingData = @{
    property_id = $property.id
    check_in = $checkIn
    check_out = $checkOut
    guests = 2
    guest_name = "Test Booking User"
    guest_email = "booking_test_$timestamp@renthub.test"
    special_requests = "Non-smoking room please"
} | ConvertTo-Json

try {
    $booking = Invoke-RestMethod -Uri "$baseUrl/bookings" -Method Post -Headers $authHeaders -Body $bookingData
    Write-Host "   ✓ Booking created successfully!" -ForegroundColor Green
    Write-Host "   ✓ Booking ID: $($booking.id)" -ForegroundColor Green
    Write-Host "   ✓ Total: `$$($booking.total_amount)" -ForegroundColor Green
    Write-Host "   ✓ Nights: $($booking.nights)" -ForegroundColor Green
    Write-Host "   ✓ Status: $($booking.status)" -ForegroundColor Green
    
    $bookingId = $booking.id
} catch {
    Write-Host "   ✗ Booking creation failed" -ForegroundColor Red
    Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        $errorDetails = $_.ErrorDetails.Message | ConvertFrom-Json
        Write-Host "   Details: $($errorDetails.message)" -ForegroundColor Red
        if ($errorDetails.errors) {
            $errorDetails.errors.PSObject.Properties | ForEach-Object {
                Write-Host "   - $($_.Name): $($_.Value -join ', ')" -ForegroundColor Red
            }
        }
    }
    exit 1
}

# Step 5: Get booking details
Write-Host "`n[5] Fetching booking details..." -ForegroundColor Yellow
try {
    $bookingDetails = Invoke-RestMethod -Uri "$baseUrl/bookings/$bookingId" -Headers $authHeaders
    Write-Host "   ✓ Retrieved booking #$bookingId" -ForegroundColor Green
    Write-Host "   ✓ Property: $($bookingDetails.property.title)" -ForegroundColor Green
    Write-Host "   ✓ Guest: $($bookingDetails.guest_name)" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Failed to fetch booking: $($_.Exception.Message)" -ForegroundColor Red
}

# Step 6: List user bookings
Write-Host "`n[6] Listing user bookings..." -ForegroundColor Yellow
try {
    $userBookings = Invoke-RestMethod -Uri "$baseUrl/my-bookings" -Headers $authHeaders
    Write-Host "   ✓ User has $($userBookings.data.Count) booking(s)" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Failed to list bookings: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  ✓ BOOKING FLOW TEST COMPLETED!" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  - User created: booking_test_$timestamp@renthub.test" -ForegroundColor White
Write-Host "  - Property booked: $($property.title)" -ForegroundColor White
Write-Host "  - Booking ID: $bookingId" -ForegroundColor White
Write-Host "  - Check-in: $checkIn" -ForegroundColor White
Write-Host "  - Check-out: $checkOut" -ForegroundColor White
Write-Host "  - Total: `$$($booking.total_amount)" -ForegroundColor White
Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "  1. Login to admin panel: http://127.0.0.1:8000/admin" -ForegroundColor Yellow
Write-Host "  2. View booking in Filament admin" -ForegroundColor Yellow
Write-Host "  3. Login to frontend: http://localhost:3000/auth/login" -ForegroundColor Yellow
Write-Host "  4. View booking in user dashboard" -ForegroundColor Yellow

#!/usr/bin/env pwsh
# Complete API Testing Script for RentHub

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  RENTHUB - COMPLETE API TEST SUITE" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000/api/v1"
$headers = @{
    'Content-Type' = 'application/json'
    'Accept' = 'application/json'
}

# Test counter
$testsPassed = 0
$testsFailed = 0

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$Headers = $headers,
        [object]$Body = $null
    )
    
    Write-Host "Testing: $Name" -NoNewline
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ErrorAction = 'Stop'
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
        }
        
        $response = Invoke-WebRequest @params
        
        if ($response.StatusCode -ge 200 -and $response.StatusCode -lt 300) {
            Write-Host " ✓ PASS (Status: $($response.StatusCode))" -ForegroundColor Green
            $script:testsPassed++
            return $response
        } else {
            Write-Host " ✗ FAIL (Status: $($response.StatusCode))" -ForegroundColor Red
            $script:testsFailed++
            return $null
        }
    } catch {
        Write-Host " ✗ FAIL (Error: $($_.Exception.Message))" -ForegroundColor Red
        $script:testsFailed++
        return $null
    }
}

Write-Host "1. HEALTH & CONNECTIVITY TESTS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────────" -ForegroundColor DarkGray
Test-Endpoint "Health Check" "$baseUrl/health"
Test-Endpoint "CORS Check" "$baseUrl/properties" -Headers @{
    'Origin' = 'http://localhost:3000'
    'Accept' = 'application/json'
}

Write-Host "`n2. AUTHENTICATION TESTS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────────" -ForegroundColor DarkGray

# Generate unique email for testing
$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$testEmail = "test_$timestamp@example.com"

$registerBody = @{
    name = "Test User $timestamp"
    email = $testEmail
    password = "Password123!"
    password_confirmation = "Password123!"
}

$registerResponse = Test-Endpoint "User Registration" "$baseUrl/register" -Method POST -Body $registerBody

if ($registerResponse) {
    $registerData = $registerResponse.Content | ConvertFrom-Json
    $token = $registerData.token
    
    Write-Host "   Token received: $($token.Substring(0, 20))..." -ForegroundColor Gray
    
    # Test login
    $loginBody = @{
        email = $testEmail
        password = "Password123!"
    }
    
    $loginResponse = Test-Endpoint "User Login" "$baseUrl/login" -Method POST -Body $loginBody
    
    if ($loginResponse) {
        $loginData = $loginResponse.Content | ConvertFrom-Json
        $authToken = $loginData.token
        
        # Create authenticated headers
        $authHeaders = @{
            'Content-Type' = 'application/json'
            'Accept' = 'application/json'
            'Authorization' = "Bearer $authToken"
        }
        
        Write-Host "`n3. AUTHENTICATED ENDPOINTS" -ForegroundColor Yellow
        Write-Host "─────────────────────────────────────────" -ForegroundColor DarkGray
        
        Test-Endpoint "Get Current User" "$baseUrl/user" -Headers $authHeaders
        Test-Endpoint "Update Profile" "$baseUrl/user/profile" -Method PUT -Headers $authHeaders -Body @{
            phone = "+1234567890"
            bio = "Test bio"
        }
        Test-Endpoint "Logout" "$baseUrl/logout" -Method POST -Headers $authHeaders
    }
}

Write-Host "`n4. PUBLIC ENDPOINTS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────────" -ForegroundColor DarkGray

Test-Endpoint "Get Properties" "$baseUrl/properties"
Test-Endpoint "Get Properties (with filters)" "$baseUrl/properties?type=apartment&status=available"
Test-Endpoint "Get Amenities" "$baseUrl/amenities"
Test-Endpoint "Get Currencies" "$baseUrl/currencies"
Test-Endpoint "Get Languages" "$baseUrl/languages"

Write-Host "`n5. ERROR HANDLING TESTS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────────" -ForegroundColor DarkGray

# Test invalid endpoint
Write-Host "Testing: Invalid Endpoint" -NoNewline
try {
    Invoke-WebRequest -Uri "$baseUrl/invalid-endpoint-999" -Headers $headers -ErrorAction Stop
    Write-Host " ✗ FAIL (Should return 404)" -ForegroundColor Red
    $script:testsFailed++
} catch {
    if ($_.Exception.Response.StatusCode -eq 404) {
        Write-Host " ✓ PASS (404 as expected)" -ForegroundColor Green
        $script:testsPassed++
    } else {
        Write-Host " ✗ FAIL (Wrong error code)" -ForegroundColor Red
        $script:testsFailed++
    }
}

# Test invalid registration
Write-Host "Testing: Invalid Registration (missing fields)" -NoNewline
try {
    Invoke-WebRequest -Uri "$baseUrl/register" -Method POST -Headers $headers -Body (@{email="test@test.com"} | ConvertTo-Json) -ErrorAction Stop
    Write-Host " ✗ FAIL (Should return 422)" -ForegroundColor Red
    $script:testsFailed++
} catch {
    if ($_.Exception.Response.StatusCode -eq 422) {
        Write-Host " ✓ PASS (422 as expected)" -ForegroundColor Green
        $script:testsPassed++
    } else {
        Write-Host " ✗ FAIL (Wrong error code: $($_.Exception.Response.StatusCode))" -ForegroundColor Red
        $script:testsFailed++
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  TEST RESULTS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Tests Passed: $testsPassed" -ForegroundColor Green
Write-Host "Tests Failed: $testsFailed" -ForegroundColor Red
Write-Host "Total Tests: $($testsPassed + $testsFailed)" -ForegroundColor Cyan

if ($testsFailed -eq 0) {
    Write-Host "`n✓ ALL TESTS PASSED!" -ForegroundColor Green
    exit 0
} else {
    Write-Host "`n✗ SOME TESTS FAILED" -ForegroundColor Red
    exit 1
}

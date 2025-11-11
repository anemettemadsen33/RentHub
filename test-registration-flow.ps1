#!/usr/bin/env pwsh
# Complete Registration Test Script
# Tests the entire registration flow: CSRF cookie -> Register -> Verify

Write-Host "`n=== RentHub Registration Test ===" -ForegroundColor Cyan
Write-Host "Testing backend registration flow...`n" -ForegroundColor Cyan

$baseUrl = "http://localhost:8000"
$apiUrl = "$baseUrl/api/v1"

# Test 1: Get CSRF Cookie
Write-Host "[1/3] Getting CSRF cookie..." -ForegroundColor Yellow
try {
    $csrfResponse = Invoke-WebRequest -Uri "$baseUrl/sanctum/csrf-cookie" `
        -Method GET `
        -SessionVariable session `
        -UseBasicParsing
    Write-Host "✅ CSRF cookie obtained" -ForegroundColor Green
    Write-Host "Status: $($csrfResponse.StatusCode)" -ForegroundColor Gray
} catch {
    Write-Host "❌ Failed to get CSRF cookie" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

# Test 2: Register User
Write-Host "`n[2/3] Registering new user..." -ForegroundColor Yellow

$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$testUser = @{
    name = "Test User $timestamp"
    email = "test$timestamp@example.com"
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "tenant"
} | ConvertTo-Json

Write-Host "Request data:" -ForegroundColor Gray
Write-Host $testUser -ForegroundColor Gray

try {
    $registerResponse = Invoke-WebRequest -Uri "$apiUrl/register" `
        -Method POST `
        -Body $testUser `
        -ContentType "application/json" `
        -Headers @{
            "Accept" = "application/json"
            "X-Requested-With" = "XMLHttpRequest"
        } `
        -WebSession $session `
        -UseBasicParsing
    
    Write-Host "✅ Registration successful!" -ForegroundColor Green
    Write-Host "Status: $($registerResponse.StatusCode)" -ForegroundColor Gray
    
    $responseData = $registerResponse.Content | ConvertFrom-Json
    Write-Host "`nUser created:" -ForegroundColor Cyan
    Write-Host "  ID: $($responseData.user.id)" -ForegroundColor Gray
    Write-Host "  Name: $($responseData.user.name)" -ForegroundColor Gray
    Write-Host "  Email: $($responseData.user.email)" -ForegroundColor Gray
    Write-Host "  Role: $($responseData.user.role)" -ForegroundColor Gray
    Write-Host "`nToken: $($responseData.token.Substring(0, 20))..." -ForegroundColor Gray
    
} catch {
    Write-Host "❌ Registration failed" -ForegroundColor Red
    Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    
    if ($_.ErrorDetails.Message) {
        Write-Host "`nResponse:" -ForegroundColor Yellow
        Write-Host $_.ErrorDetails.Message -ForegroundColor Red
    }
    exit 1
}

# Test 3: Verify routes are accessible
Write-Host "`n[3/3] Verifying API routes..." -ForegroundColor Yellow
try {
    $routesResponse = Invoke-WebRequest -Uri "$apiUrl/health" `
        -Method GET `
        -Headers @{ "Accept" = "application/json" } `
        -UseBasicParsing -ErrorAction SilentlyContinue
    
    if ($routesResponse.StatusCode -eq 200) {
        Write-Host "✅ Health endpoint accessible" -ForegroundColor Green
    }
} catch {
    Write-Host "⚠️  Health endpoint not found (optional)" -ForegroundColor Yellow
}

Write-Host "`n=== All Tests Passed! ===" -ForegroundColor Green
Write-Host "Backend registration is working correctly.`n" -ForegroundColor Green

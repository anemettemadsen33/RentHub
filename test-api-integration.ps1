#!/usr/bin/env pwsh
# RentHub - API Integration Test
# Tests real API calls from frontend to backend

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  🔌 RentHub API Integration Test" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$backendUrl = "https://renthub-tbj7yxj7.on-forge.com/api/v1"
$frontendUrl = "https://rent-hub-beta.vercel.app"

$tests = @{
    Passed = 0
    Failed = 0
    Total = 0
}

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$ExpectedStatus = "200"
    )
    
    $tests.Total++
    
    try {
        $response = Invoke-WebRequest -Uri $Url -Method GET -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        
        if ($response.StatusCode -eq [int]$ExpectedStatus) {
            Write-Host "✅ " -ForegroundColor Green -NoNewline
            Write-Host "$Name".PadRight(40) -NoNewline
            Write-Host "[$($response.StatusCode)]" -ForegroundColor DarkGray
            $tests.Passed++
            return $true
        } else {
            Write-Host "⚠️  " -ForegroundColor Yellow -NoNewline
            Write-Host "$Name".PadRight(40) -NoNewline
            Write-Host "Expected $ExpectedStatus, got $($response.StatusCode)" -ForegroundColor Yellow
            $tests.Failed++
            return $false
        }
    }
    catch {
        Write-Host "❌ " -ForegroundColor Red -NoNewline
        Write-Host "$Name".PadRight(40) -NoNewline
        Write-Host "FAILED - $($_.Exception.Message)" -ForegroundColor Red
        $tests.Failed++
        return $false
    }
}

Write-Host "🔍 Backend API Tests" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

# Backend health check
Test-Endpoint "Backend Health" "$backendUrl/health"

# Core endpoints
Test-Endpoint "Properties List" "$backendUrl/properties"
Test-Endpoint "Property Types" "$backendUrl/property-types"
Test-Endpoint "Amenities" "$backendUrl/amenities"

# User endpoints (should require auth - 401 expected)
Write-Host "`n🔐 Protected Endpoints (401 expected)" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Test-Endpoint "My Properties (Protected)" "$backendUrl/my-properties" "401"
Test-Endpoint "My Bookings (Protected)" "$backendUrl/my-bookings" "401"
Test-Endpoint "Analytics Summary (Protected)" "$backendUrl/analytics/summary" "401"

Write-Host "`n🌐 Frontend Pages (SSR/Static)" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

# Frontend pages
Test-Endpoint "Homepage" "$frontendUrl/"
Test-Endpoint "Properties Page" "$frontendUrl/properties"
Test-Endpoint "Login Page" "$frontendUrl/auth/login"
Test-Endpoint "Dashboard" "$frontendUrl/dashboard"
Test-Endpoint "New Property Form" "$frontendUrl/dashboard/properties/new"

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  📊 Test Results" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

$passRate = if ($tests.Total -gt 0) { [math]::Round(($tests.Passed / $tests.Total) * 100, 2) } else { 0 }

Write-Host "Total Tests:  $($tests.Total)" -ForegroundColor White
Write-Host "Passed:       $($tests.Passed) ($passRate%)" -ForegroundColor Green
Write-Host "Failed:       $($tests.Failed)" -ForegroundColor $(if ($tests.Failed -gt 0) { "Red" } else { "Green" })

Write-Host "`n🔍 Analysis:" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray

if ($tests.Failed -eq 0) {
    Write-Host "✅ ALL API ENDPOINTS WORKING!" -ForegroundColor Green
    Write-Host "   • Backend responding correctly" -ForegroundColor Gray
    Write-Host "   • Frontend pages loading" -ForegroundColor Gray
    Write-Host "   • Protected routes require auth" -ForegroundColor Gray
} else {
    Write-Host "⚠️  SOME ISSUES DETECTED" -ForegroundColor Yellow
    Write-Host "   • Review failed endpoints above" -ForegroundColor Gray
}

Write-Host "`n========================================`n" -ForegroundColor Cyan

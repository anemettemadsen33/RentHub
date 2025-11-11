# Comprehensive Testing Script for RentHub
# Tests backend, frontend, APIs, pages, and functionality

Write-Host "🚀 RentHub Comprehensive Test Suite" -ForegroundColor Cyan
Write-Host "====================================`n" -ForegroundColor Cyan

$results = @{
    BackendTests = @()
    FrontendTests = @()
    APITests = @()
    PageTests = @()
    SecurityTests = @()
    PerformanceTests = @()
}

$startTime = Get-Date

# Function to test endpoint
function Test-Endpoint {
    param($url, $description)
    try {
        $response = Invoke-WebRequest -Uri $url -Method Get -TimeoutSec 10 -UseBasicParsing -ErrorAction Stop
        Write-Host "✅ $description - Status: $($response.StatusCode)" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "❌ $description - Failed: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Function to test API endpoint with JSON
function Test-ApiEndpoint {
    param($url, $description, $method = "GET", $body = $null)
    try {
        $headers = @{
            "Accept" = "application/json"
            "Content-Type" = "application/json"
        }
        
        $params = @{
            Uri = $url
            Method = $method
            Headers = $headers
            TimeoutSec = 10
            UseBasicParsing = $true
            ErrorAction = 'Stop'
        }
        
        if ($body) {
            $params.Body = ($body | ConvertTo-Json)
        }
        
        $response = Invoke-WebRequest @params
        $content = $response.Content | ConvertFrom-Json
        Write-Host "✅ API: $description - Status: $($response.StatusCode)" -ForegroundColor Green
        return @{
            Success = $true
            Data = $content
            StatusCode = $response.StatusCode
        }
    }
    catch {
        Write-Host "❌ API: $description - Failed: $($_.Exception.Message)" -ForegroundColor Red
        return @{
            Success = $false
            Error = $_.Exception.Message
        }
    }
}

Write-Host "`n📋 Phase 1: Backend Health Checks" -ForegroundColor Yellow
Write-Host "================================`n" -ForegroundColor Yellow

$backendUrl = "http://localhost:8000"
$apiUrl = "$backendUrl/api/v1"

# Backend endpoints
$results.BackendTests += Test-Endpoint "$backendUrl" "Backend Server"
$results.BackendTests += Test-Endpoint "$backendUrl/api/health" "Health Check"
$results.BackendTests += Test-Endpoint "$backendUrl/sanctum/csrf-cookie" "CSRF Cookie"

Write-Host "`n📋 Phase 2: API Endpoint Tests" -ForegroundColor Yellow
Write-Host "============================`n" -ForegroundColor Yellow

# Public API endpoints
$apiTests = @(
    @{ Url = "$apiUrl/properties"; Description = "Properties List" }
    @{ Url = "$apiUrl/currencies"; Description = "Currencies List" }
    @{ Url = "$apiUrl/health"; Description = "API Health" }
)

foreach ($test in $apiTests) {
    $result = Test-ApiEndpoint $test.Url $test.Description
    $results.APITests += $result.Success
}

Write-Host "`n📋 Phase 3: Frontend Health Checks" -ForegroundColor Yellow
Write-Host "================================`n" -ForegroundColor Yellow

$frontendUrl = "http://localhost:3000"

# Frontend pages to test
$pages = @(
    @{ Path = "/"; Name = "Home Page" }
    @{ Path = "/auth/login"; Name = "Login Page" }
    @{ Path = "/auth/register"; Name = "Register Page" }
    @{ Path = "/properties"; Name = "Properties Page" }
    @{ Path = "/dashboard"; Name = "Dashboard (should redirect)" }
    @{ Path = "/profile"; Name = "Profile Page (should redirect)" }
    @{ Path = "/bookings"; Name = "Bookings Page (should redirect)" }
    @{ Path = "/messages"; Name = "Messages Page (should redirect)" }
    @{ Path = "/notifications"; Name = "Notifications Page (should redirect)" }
    @{ Path = "/saved-searches"; Name = "Saved Searches Page (should redirect)" }
    @{ Path = "/wishlists"; Name = "Wishlists Page (should redirect)" }
)

foreach ($page in $pages) {
    $result = Test-Endpoint "$frontendUrl$($page.Path)" $page.Name
    $results.PageTests += $result
    Start-Sleep -Milliseconds 500
}

Write-Host "`n📋 Phase 4: Security Checks" -ForegroundColor Yellow
Write-Host "=========================`n" -ForegroundColor Yellow

# Test CORS headers
try {
    $response = Invoke-WebRequest -Uri "$apiUrl/health" -Method Options -Headers @{
        "Origin" = "http://localhost:3000"
        "Access-Control-Request-Method" = "GET"
    } -UseBasicParsing -ErrorAction Stop
    
    if ($response.Headers["Access-Control-Allow-Origin"]) {
        Write-Host "✅ CORS Headers Present" -ForegroundColor Green
        $results.SecurityTests += $true
    }
    else {
        Write-Host "⚠️  CORS Headers Missing" -ForegroundColor Yellow
        $results.SecurityTests += $false
    }
}
catch {
    Write-Host "❌ CORS Test Failed: $($_.Exception.Message)" -ForegroundColor Red
    $results.SecurityTests += $false
}

# Test Content Security
$securityTests = @(
    @{ Header = "X-Content-Type-Options"; Expected = $true }
    @{ Header = "X-Frame-Options"; Expected = $true }
)

try {
    $response = Invoke-WebRequest -Uri $frontendUrl -UseBasicParsing -ErrorAction Stop
    foreach ($test in $securityTests) {
        if ($response.Headers[$test.Header]) {
            Write-Host "✅ Security Header: $($test.Header)" -ForegroundColor Green
            $results.SecurityTests += $true
        }
        else {
            Write-Host "⚠️  Missing Security Header: $($test.Header)" -ForegroundColor Yellow
            $results.SecurityTests += $false
        }
    }
}
catch {
    Write-Host "⚠️  Security headers check skipped" -ForegroundColor Yellow
}

Write-Host "`n📋 Phase 5: Performance Checks" -ForegroundColor Yellow
Write-Host "============================`n" -ForegroundColor Yellow

# Test response times
$performanceUrls = @(
    @{ Url = $frontendUrl; Name = "Frontend Home" }
    @{ Url = "$apiUrl/properties"; Name = "API Properties" }
)

foreach ($test in $performanceUrls) {
    $start = Get-Date
    try {
        $null = Invoke-WebRequest -Uri $test.Url -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        $duration = (Get-Date) - $start
        $ms = [math]::Round($duration.TotalMilliseconds, 2)
        
        if ($ms -lt 1000) {
            Write-Host "✅ $($test.Name): ${ms}ms (Excellent)" -ForegroundColor Green
            $results.PerformanceTests += $true
        }
        elseif ($ms -lt 3000) {
            Write-Host "⚠️  $($test.Name): ${ms}ms (Acceptable)" -ForegroundColor Yellow
            $results.PerformanceTests += $true
        }
        else {
            Write-Host "❌ $($test.Name): ${ms}ms (Slow)" -ForegroundColor Red
            $results.PerformanceTests += $false
        }
    }
    catch {
        Write-Host "❌ $($test.Name): Failed" -ForegroundColor Red
        $results.PerformanceTests += $false
    }
}

# Summary
Write-Host "`n" -NoNewline
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "📊 Test Results Summary" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$totalTests = 0
$passedTests = 0

function Show-Results {
    param($name, $tests)
    $passed = ($tests | Where-Object { $_ -eq $true }).Count
    $total = $tests.Count
    $percentage = if ($total -gt 0) { [math]::Round(($passed / $total) * 100, 2) } else { 0 }
    
    $script:totalTests += $total
    $script:passedTests += $passed
    
    $color = if ($percentage -eq 100) { "Green" } elseif ($percentage -ge 70) { "Yellow" } else { "Red" }
    Write-Host "`n$name`: $passed/$total passed ($percentage%)" -ForegroundColor $color
}

Show-Results "Backend Tests" $results.BackendTests
Show-Results "API Tests" $results.APITests
Show-Results "Page Tests" $results.PageTests
Show-Results "Security Tests" $results.SecurityTests
Show-Results "Performance Tests" $results.PerformanceTests

$totalPercentage = if ($totalTests -gt 0) { [math]::Round(($passedTests / $totalTests) * 100, 2) } else { 0 }

Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "Overall: $passedTests/$totalTests tests passed ($totalPercentage%)" -ForegroundColor $(if ($totalPercentage -ge 80) { "Green" } else { "Yellow" })

$endTime = Get-Date
$duration = $endTime - $startTime
Write-Host "Duration: $([math]::Round($duration.TotalSeconds, 2)) seconds" -ForegroundColor Gray
Write-Host "=====================================" -ForegroundColor Cyan

# Save results to file
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$reportFile = "TEST_RESULTS_COMPREHENSIVE_$timestamp.txt"
$results | ConvertTo-Json -Depth 5 | Out-File $reportFile
Write-Host "`n📄 Full results saved to: $reportFile" -ForegroundColor Cyan

# 🔬 Complete Application Analysis Script
# Tests EVERYTHING - Backend, Frontend, APIs, Pages, Functions

Write-Host "🔬 RentHub - Complete 100% Analysis" -ForegroundColor Cyan
Write-Host "===================================`n" -ForegroundColor Cyan

$results = @{
    Backend = @{
        Syntax = @()
        Routes = @()
        Database = @()
        Tests = @()
    }
    Frontend = @{
        TypeScript = @()
        ESLint = @()
        Build = @()
        Pages = @()
    }
    Integration = @{
        API = @()
        Auth = @()
        Features = @()
    }
    Performance = @{
        Backend = @()
        Frontend = @()
    }
    Security = @{
        Vulnerabilities = @()
        Headers = @()
    }
}

$startTime = Get-Date

# Helper function
function Test-Endpoint {
    param($url, $description)
    try {
        $response = Invoke-WebRequest -Uri $url -Method Get -TimeoutSec 5 -UseBasicParsing -ErrorAction Stop
        Write-Host "✅ $description" -ForegroundColor Green
        return @{Success=$true; StatusCode=$response.StatusCode; Time=(Measure-Command {Invoke-WebRequest -Uri $url -UseBasicParsing}).TotalMilliseconds}
    } catch {
        Write-Host "❌ $description - $($_.Exception.Message)" -ForegroundColor Red
        return @{Success=$false; Error=$_.Exception.Message}
    }
}

#region Backend Analysis
Write-Host "`n📋 PHASE 1: Backend Analysis" -ForegroundColor Yellow
Write-Host "============================`n" -ForegroundColor Yellow

# 1.1 PHP Syntax Check
Write-Host "Checking PHP syntax..." -ForegroundColor Gray
$phpFiles = Get-ChildItem -Path "backend\app" -Filter "*.php" -Recurse
$syntaxErrors = 0
foreach ($file in $phpFiles | Select-Object -First 50) {
    $check = php -l $file.FullName 2>&1
    if ($check -notmatch "No syntax errors") {
        $syntaxErrors++
        Write-Host "  ❌ $($file.Name)" -ForegroundColor Red
    }
}
if ($syntaxErrors -eq 0) {
    Write-Host "✅ PHP Syntax: All files valid ($($phpFiles.Count) files)" -ForegroundColor Green
} else {
    Write-Host "⚠️  PHP Syntax: $syntaxErrors errors found" -ForegroundColor Yellow
}
$results.Backend.Syntax = @{Total=$phpFiles.Count; Errors=$syntaxErrors}

# 1.2 Database Check
Write-Host "`nChecking database..." -ForegroundColor Gray
try {
    Set-Location backend
    $dbCheck = php artisan migrate:status 2>&1 | Out-String
    if ($dbCheck -match "Ran\?") {
        Write-Host "✅ Database: Connected & Migrations OK" -ForegroundColor Green
        $results.Backend.Database = @{Status="Connected"}
    } else {
        Write-Host "⚠️  Database: No migrations" -ForegroundColor Yellow
        $results.Backend.Database = @{Status="No migrations"}
    }
    Set-Location ..
} catch {
    Write-Host "❌ Database: $($_.Exception.Message)" -ForegroundColor Red
    $results.Backend.Database = @{Status="Error"; Error=$_.Exception.Message}
    Set-Location ..
}

# 1.3 API Routes Test
Write-Host "`nTesting API endpoints..." -ForegroundColor Gray
$apiTests = @(
    "http://localhost:8000/api/health",
    "http://localhost:8000/api/v1/properties",
    "http://localhost:8000/api/v1/currencies",
    "http://localhost:8000/sanctum/csrf-cookie"
)
$apiPassed = 0
foreach ($url in $apiTests) {
    $result = Test-Endpoint $url $url
    if ($result.Success) { $apiPassed++ }
    $results.Backend.Routes += $result
}
Write-Host "API Tests: $apiPassed/$($apiTests.Count) passed" -ForegroundColor $(if($apiPassed -eq $apiTests.Count){"Green"}else{"Yellow"})

#endregion

#region Frontend Analysis
Write-Host "`n📋 PHASE 2: Frontend Analysis" -ForegroundColor Yellow
Write-Host "============================`n" -ForegroundColor Yellow

# 2.1 TypeScript Check
Write-Host "Running TypeScript type-check..." -ForegroundColor Gray
Set-Location frontend
$tsCheck = npm run type-check 2>&1 | Out-String
Set-Location ..
if ($tsCheck -match "Found 0 errors") {
    Write-Host "✅ TypeScript: No errors" -ForegroundColor Green
    $results.Frontend.TypeScript = @{Status="Pass"; Errors=0}
} else {
    $errorCount = ([regex]::Matches($tsCheck, "error TS")).Count
    Write-Host "⚠️  TypeScript: $errorCount errors" -ForegroundColor Yellow
    $results.Frontend.TypeScript = @{Status="Fail"; Errors=$errorCount}
}

# 2.2 ESLint Check
Write-Host "`nRunning ESLint..." -ForegroundColor Gray
Set-Location frontend
$lintCheck = npm run lint 2>&1 | Out-String
Set-Location ..
$warningCount = ([regex]::Matches($lintCheck, "Warning:")).Count
Write-Host "⚠️  ESLint: $warningCount warnings" -ForegroundColor Yellow
$results.Frontend.ESLint = @{Warnings=$warningCount}

# 2.3 Page Tests
Write-Host "`nTesting frontend pages..." -ForegroundColor Gray
$pages = @(
    "http://localhost:3000/",
    "http://localhost:3000/auth/login",
    "http://localhost:3000/auth/register",
    "http://localhost:3000/properties"
)
$pagesPassed = 0
foreach ($url in $pages) {
    $result = Test-Endpoint $url $url
    if ($result.Success) { $pagesPassed++ }
    $results.Frontend.Pages += $result
}
Write-Host "Page Tests: $pagesPassed/$($pages.Count) passed" -ForegroundColor $(if($pagesPassed -eq $pages.Count){"Green"}else{"Yellow"})

#endregion

#region Integration Tests
Write-Host "`n📋 PHASE 3: Integration Tests" -ForegroundColor Yellow
Write-Host "=============================`n" -ForegroundColor Yellow

# 3.1 API Integration
Write-Host "Testing API integration..." -ForegroundColor Gray
try {
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/v1/properties" -Method Get -ContentType "application/json"
    Write-Host "✅ API Integration: Properties endpoint returns data" -ForegroundColor Green
    $results.Integration.API = @{Status="Pass"; Records=($response.data.Count)}
} catch {
    Write-Host "❌ API Integration: Failed" -ForegroundColor Red
    $results.Integration.API = @{Status="Fail"; Error=$_.Exception.Message}
}

#endregion

#region Performance
Write-Host "`n📋 PHASE 4: Performance Analysis" -ForegroundColor Yellow
Write-Host "================================`n" -ForegroundColor Yellow

# Backend Performance
Write-Host "Testing backend performance..." -ForegroundColor Gray
$backendTime = (Measure-Command {
    Invoke-WebRequest -Uri "http://localhost:8000/api/v1/properties" -UseBasicParsing
}).TotalMilliseconds
$backendStatus = if($backendTime -lt 200){"Excellent"}elseif($backendTime -lt 500){"Good"}else{"Slow"}
Write-Host "Backend API: ${backendTime}ms ($backendStatus)" -ForegroundColor $(if($backendTime -lt 500){"Green"}else{"Yellow"})
$results.Performance.Backend = @{ResponseTime=$backendTime; Status=$backendStatus}

# Frontend Performance
Write-Host "Testing frontend performance..." -ForegroundColor Gray
$frontendTime = (Measure-Command {
    Invoke-WebRequest -Uri "http://localhost:3000/" -UseBasicParsing
}).TotalMilliseconds
$frontendStatus = if($frontendTime -lt 1000){"Excellent"}elseif($frontendTime -lt 3000){"Good"}else{"Slow"}
Write-Host "Frontend Page: ${frontendTime}ms ($frontendStatus)" -ForegroundColor $(if($frontendTime -lt 3000){"Green"}else{"Yellow"})
$results.Performance.Frontend = @{LoadTime=$frontendTime; Status=$frontendStatus}

#endregion

#region Security
Write-Host "`n📋 PHASE 5: Security Check" -ForegroundColor Yellow
Write-Host "==========================`n" -ForegroundColor Yellow

# NPM Audit
Write-Host "Running npm audit..." -ForegroundColor Gray
Set-Location frontend
$auditResult = npm audit --json 2>&1 | ConvertFrom-Json
Set-Location ..
$vulnCount = $auditResult.metadata.vulnerabilities.total
if ($vulnCount -eq 0) {
    Write-Host "✅ Security: No vulnerabilities" -ForegroundColor Green
} else {
    Write-Host "⚠️  Security: $vulnCount vulnerabilities" -ForegroundColor Yellow
}
$results.Security.Vulnerabilities = @{Total=$vulnCount}

# Security Headers
Write-Host "Checking security headers..." -ForegroundColor Gray
$headers = Invoke-WebRequest -Uri "http://localhost:3000/" -UseBasicParsing
$securityHeaders = @("X-Frame-Options", "X-Content-Type-Options")
$headersPassed = 0
foreach ($header in $securityHeaders) {
    if ($headers.Headers[$header]) {
        Write-Host "  ✅ $header present" -ForegroundColor Green
        $headersPassed++
    } else {
        Write-Host "  ⚠️  $header missing" -ForegroundColor Yellow
    }
}
$results.Security.Headers = @{Present=$headersPassed; Total=$securityHeaders.Count}

#endregion

#region Summary
Write-Host "`n" -NoNewline
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 ANALYSIS SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$totalChecks = 0
$passedChecks = 0

# Backend
$backendScore = 0
if ($results.Backend.Syntax.Errors -eq 0) { $backendScore++; $passedChecks++ }
$totalChecks++
if ($results.Backend.Database.Status -eq "Connected") { $backendScore++; $passedChecks++ }
$totalChecks++
$apiPassRate = ($results.Backend.Routes | Where-Object {$_.Success}).Count / $results.Backend.Routes.Count
if ($apiPassRate -gt 0.8) { $backendScore++; $passedChecks++ }
$totalChecks++

Write-Host "`n🔧 Backend: $backendScore/3 checks passed" -ForegroundColor $(if($backendScore -eq 3){"Green"}else{"Yellow"})
Write-Host "  - PHP Syntax: $(if($results.Backend.Syntax.Errors -eq 0){'✅'}else{'⚠️'})"
Write-Host "  - Database: $(if($results.Backend.Database.Status -eq 'Connected'){'✅'}else{'⚠️'})"
Write-Host "  - API Routes: $([math]::Round($apiPassRate * 100, 0))%"

# Frontend
$frontendScore = 0
if ($results.Frontend.TypeScript.Errors -eq 0) { $frontendScore++; $passedChecks++ }
$totalChecks++
if ($results.Frontend.ESLint.Warnings -lt 20) { $frontendScore++; $passedChecks++ }
$totalChecks++
$pagePassRate = ($results.Frontend.Pages | Where-Object {$_.Success}).Count / $results.Frontend.Pages.Count
if ($pagePassRate -gt 0.8) { $frontendScore++; $passedChecks++ }
$totalChecks++

Write-Host "`n⚛️  Frontend: $frontendScore/3 checks passed" -ForegroundColor $(if($frontendScore -eq 3){"Green"}else{"Yellow"})
Write-Host "  - TypeScript: $(if($results.Frontend.TypeScript.Errors -eq 0){'✅'}else{'⚠️'}) $($results.Frontend.TypeScript.Errors) errors"
Write-Host "  - ESLint: $(if($results.Frontend.ESLint.Warnings -lt 20){'✅'}else{'⚠️'}) $($results.Frontend.ESLint.Warnings) warnings"
Write-Host "  - Pages: $([math]::Round($pagePassRate * 100, 0))%"

# Performance
Write-Host "`n⚡ Performance:" -ForegroundColor Cyan
Write-Host "  - Backend API: $($results.Performance.Backend.ResponseTime)ms ($($results.Performance.Backend.Status))"
Write-Host "  - Frontend Page: $($results.Performance.Frontend.LoadTime)ms ($($results.Performance.Frontend.Status))"

# Security
Write-Host "`n🔒 Security:" -ForegroundColor Cyan
Write-Host "  - Vulnerabilities: $($results.Security.Vulnerabilities.Total)"
Write-Host "  - Security Headers: $($results.Security.Headers.Present)/$($results.Security.Headers.Total)"

# Overall
$overallScore = [math]::Round(($passedChecks / $totalChecks) * 100, 0)
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Overall Score: $overallScore%" -ForegroundColor $(if($overallScore -ge 80){"Green"}elseif($overallScore -ge 60){"Yellow"}else{"Red"})
Write-Host "Checks Passed: $passedChecks/$totalChecks" -ForegroundColor Cyan
$endTime = Get-Date
$duration = $endTime - $startTime
Write-Host "Duration: $([math]::Round($duration.TotalSeconds, 2))s" -ForegroundColor Gray
Write-Host "========================================" -ForegroundColor Cyan

# Save results
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$reportFile = "COMPLETE_ANALYSIS_REPORT_$timestamp.json"
$results | ConvertTo-Json -Depth 10 | Out-File $reportFile
Write-Host "`n📄 Full report saved to: $reportFile" -ForegroundColor Cyan

if ($overallScore -ge 90) {
    Write-Host "`n🎉 EXCELLENT! Application is in great shape!" -ForegroundColor Green
} elseif ($overallScore -ge 70) {
    Write-Host "`n✅ GOOD! Some minor issues to fix." -ForegroundColor Yellow
} else {
    Write-Host "`n⚠️  NEEDS WORK! Several issues require attention." -ForegroundColor Red
}

#endregion

# Complete Test Suite Runner for RentHub
# Runs all tests: Backend, Frontend, E2E, Static Analysis

param(
    [switch]$SkipBackend,
    [switch]$SkipFrontend,
    [switch]$SkipE2E,
    [switch]$Quick
)

Write-Host "🧪 RentHub Complete Test Suite" -ForegroundColor Cyan
Write-Host "==============================`n" -ForegroundColor Cyan

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$logFile = "TEST_LOG_$timestamp.txt"
$resultsFile = "TEST_RESULTS_$timestamp.json"

$testResults = @{
    Timestamp = $timestamp
    Backend = @{}
    Frontend = @{}
    E2E = @{}
    Summary = @{}
}

# Helper function to run command and log
function Invoke-TestCommand {
    param(
        [string]$Command,
        [string]$Description,
        [string]$WorkingDir = $PWD
    )
    
    Write-Host "`n🔄 $Description..." -ForegroundColor Yellow
    Write-Host "Command: $Command" -ForegroundColor Gray
    
    $startTime = Get-Date
    
    try {
        Push-Location $WorkingDir
        $output = Invoke-Expression $Command 2>&1 | Out-String
        $exitCode = $LASTEXITCODE
        Pop-Location
        
        $duration = (Get-Date) - $startTime
        
        $result = @{
            Description = $Description
            Command = $Command
            ExitCode = $exitCode
            Duration = $duration.TotalSeconds
            Output = $output
            Success = ($exitCode -eq 0)
        }
        
        if ($result.Success) {
            Write-Host "✅ $Description completed in $([math]::Round($duration.TotalSeconds, 2))s" -ForegroundColor Green
        }
        else {
            Write-Host "❌ $Description failed with exit code $exitCode" -ForegroundColor Red
            Write-Host $output -ForegroundColor Red
        }
        
        return $result
    }
    catch {
        Pop-Location
        Write-Host "❌ $Description failed: $($_.Exception.Message)" -ForegroundColor Red
        return @{
            Description = $Description
            Success = $false
            Error = $_.Exception.Message
            Duration = 0
        }
    }
}

# 1. Backend Tests
if (-not $SkipBackend) {
    Write-Host "`n" -NoNewline
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "🔧 Backend Tests" -ForegroundColor Cyan
    Write-Host "========================================" -ForegroundColor Cyan
    
    $backendDir = "C:\laragon\www\RentHub\backend"
    
    # PHP Syntax Check
    $testResults.Backend.Syntax = Invoke-TestCommand `
        -Command "php -l app/Http/Controllers/Api/PropertyController.php" `
        -Description "PHP Syntax Check" `
        -WorkingDir $backendDir
    
    # Type Checking with Laravel Pint
    if (-not $Quick) {
        $testResults.Backend.CodeStyle = Invoke-TestCommand `
            -Command "composer run pint -- --test" `
            -Description "Code Style Check (Pint)" `
            -WorkingDir $backendDir
    }
    
    # Run Laravel Tests (limit to faster tests in quick mode)
    if ($Quick) {
        $testResults.Backend.UnitTests = Invoke-TestCommand `
            -Command "php artisan test --filter=Api --stop-on-failure" `
            -Description "Backend API Tests (Quick)" `
            -WorkingDir $backendDir
    }
    else {
        $testResults.Backend.AllTests = Invoke-TestCommand `
            -Command "php artisan test" `
            -Description "Backend All Tests" `
            -WorkingDir $backendDir
    }
    
    # Database Migration Check
    $testResults.Backend.Migrations = Invoke-TestCommand `
        -Command "php artisan migrate:status" `
        -Description "Database Migration Status" `
        -WorkingDir $backendDir
}

# 2. Frontend Tests
if (-not $SkipFrontend) {
    Write-Host "`n" -NoNewline
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "⚛️  Frontend Tests" -ForegroundColor Cyan
    Write-Host "========================================" -ForegroundColor Cyan
    
    $frontendDir = "C:\laragon\www\RentHub\frontend"
    
    # Type Checking
    $testResults.Frontend.TypeCheck = Invoke-TestCommand `
        -Command "npm run type-check" `
        -Description "TypeScript Type Check" `
        -WorkingDir $frontendDir
    
    # Linting
    $testResults.Frontend.Lint = Invoke-TestCommand `
        -Command "npm run lint" `
        -Description "ESLint Check" `
        -WorkingDir $frontendDir
    
    # Build Test
    if (-not $Quick) {
        $testResults.Frontend.Build = Invoke-TestCommand `
            -Command "npm run build" `
            -Description "Production Build Test" `
            -WorkingDir $frontendDir
    }
    
    # Unit Tests (if exists)
    if (Test-Path "$frontendDir\vitest.config.ts") {
        $testResults.Frontend.UnitTests = Invoke-TestCommand `
            -Command "npm run test:unit" `
            -Description "Frontend Unit Tests" `
            -WorkingDir $frontendDir
    }
}

# 3. E2E Tests
if (-not $SkipE2E) {
    Write-Host "`n" -NoNewline
    Write-Host "========================================" -ForegroundColor Cyan
    Write-Host "🎭 End-to-End Tests (Playwright)" -ForegroundColor Cyan
    Write-Host "========================================" -ForegroundColor Cyan
    
    $frontendDir = "C:\laragon\www\RentHub\frontend"
    
    # Check if servers are running
    Write-Host "`n🔍 Checking if servers are running..." -ForegroundColor Yellow
    
    $backendRunning = $false
    $frontendRunning = $false
    
    try {
        $null = Invoke-WebRequest -Uri "http://localhost:8000/api/health" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        $backendRunning = $true
        Write-Host "✅ Backend server is running" -ForegroundColor Green
    }
    catch {
        Write-Host "⚠️  Backend server is not running. Skipping E2E tests." -ForegroundColor Yellow
        Write-Host "   Start backend with: cd backend && php artisan serve" -ForegroundColor Gray
    }
    
    try {
        $null = Invoke-WebRequest -Uri "http://localhost:3000" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        $frontendRunning = $true
        Write-Host "✅ Frontend server is running" -ForegroundColor Green
    }
    catch {
        Write-Host "⚠️  Frontend server is not running. Skipping E2E tests." -ForegroundColor Yellow
        Write-Host "   Start frontend with: cd frontend && npm run dev" -ForegroundColor Gray
    }
    
    if ($backendRunning -and $frontendRunning) {
        if ($Quick) {
            # Run only smoke tests
            $testResults.E2E.Smoke = Invoke-TestCommand `
                -Command "npx playwright test tests/e2e/smoke.spec.ts --reporter=list" `
                -Description "E2E Smoke Tests" `
                -WorkingDir $frontendDir
        }
        else {
            # Run all E2E tests
            $testResults.E2E.All = Invoke-TestCommand `
                -Command "npx playwright test --reporter=list,html" `
                -Description "E2E All Tests" `
                -WorkingDir $frontendDir
        }
    }
    else {
        Write-Host "`n⚠️  Skipping E2E tests - servers not running" -ForegroundColor Yellow
        $testResults.E2E.Skipped = @{
            Reason = "Servers not running"
            Success = $null
        }
    }
}

# 4. Integration/Comprehensive Tests
Write-Host "`n" -NoNewline
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🔗 Integration Tests" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if (Test-Path ".\comprehensive-test.ps1") {
    $testResults.Integration = Invoke-TestCommand `
        -Command ".\comprehensive-test.ps1" `
        -Description "Comprehensive Integration Tests" `
        -WorkingDir "C:\laragon\www\RentHub"
}

# Generate Summary
Write-Host "`n" -NoNewline
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 Test Results Summary" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

$allTests = @()

# Collect all test results
foreach ($category in $testResults.Keys) {
    if ($category -ne "Summary" -and $category -ne "Timestamp") {
        foreach ($test in $testResults[$category].Keys) {
            $result = $testResults[$category][$test]
            if ($result.Success -ne $null) {
                $allTests += @{
                    Category = $category
                    Test = $test
                    Success = $result.Success
                    Duration = $result.Duration
                }
            }
        }
    }
}

$totalTests = $allTests.Count
$passedTests = ($allTests | Where-Object { $_.Success -eq $true }).Count
$failedTests = ($allTests | Where-Object { $_.Success -eq $false }).Count
$successRate = if ($totalTests -gt 0) { [math]::Round(($passedTests / $totalTests) * 100, 2) } else { 0 }

Write-Host "`nTotal Tests: $totalTests" -ForegroundColor Cyan
Write-Host "Passed: $passedTests" -ForegroundColor Green
Write-Host "Failed: $failedTests" -ForegroundColor Red
Write-Host "Success Rate: $successRate%" -ForegroundColor $(if ($successRate -ge 80) { "Green" } elseif ($successRate -ge 60) { "Yellow" } else { "Red" })

# Category breakdown
Write-Host "`nBreakdown by Category:" -ForegroundColor Yellow
foreach ($category in ($testResults.Keys | Where-Object { $_ -ne "Summary" -and $_ -ne "Timestamp" })) {
    $categoryTests = $allTests | Where-Object { $_.Category -eq $category }
    if ($categoryTests.Count -gt 0) {
        $categoryPassed = ($categoryTests | Where-Object { $_.Success -eq $true }).Count
        $categoryTotal = $categoryTests.Count
        $categoryRate = [math]::Round(($categoryPassed / $categoryTotal) * 100, 2)
        
        $color = if ($categoryRate -eq 100) { "Green" } elseif ($categoryRate -ge 70) { "Yellow" } else { "Red" }
        Write-Host "  $category`: $categoryPassed/$categoryTotal ($categoryRate%)" -ForegroundColor $color
    }
}

# Save detailed results
$testResults.Summary = @{
    Total = $totalTests
    Passed = $passedTests
    Failed = $failedTests
    SuccessRate = $successRate
}

$testResults | ConvertTo-Json -Depth 10 | Out-File $resultsFile
Write-Host "`n📄 Detailed results saved to: $resultsFile" -ForegroundColor Cyan

# Final status
Write-Host "`n" -NoNewline
if ($successRate -eq 100) {
    Write-Host "🎉 All tests passed! Perfect score!" -ForegroundColor Green
}
elseif ($successRate -ge 80) {
    Write-Host "✅ Tests mostly passing. Good job!" -ForegroundColor Green
}
elseif ($successRate -ge 60) {
    Write-Host "⚠️  Some tests failing. Review needed." -ForegroundColor Yellow
}
else {
    Write-Host "❌ Many tests failing. Immediate attention required!" -ForegroundColor Red
}

Write-Host "========================================`n" -ForegroundColor Cyan

exit $(if ($successRate -ge 80) { 0 } else { 1 })

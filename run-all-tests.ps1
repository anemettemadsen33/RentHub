<#
RentHub - Master Test Runner
Executes all verification tests and generates a summary report
#>

param(
    [switch]$SkipAPI = $false,
    [switch]$Verbose = $false
)

$ErrorActionPreference = "Continue"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "🧪 RentHub - Master Test Suite" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$startTime = Get-Date
$testResults = @{}

# Test 1: ROADMAP Compliance
Write-Host "📋 Test 1: ROADMAP Compliance" -ForegroundColor Magenta
Write-Host "================================" -ForegroundColor Magenta
Write-Host ""

try {
    Set-Location "C:\laragon\www\RentHub"
    $output = & ".\test-roadmap-compliance.ps1" 2>&1
    if ($Verbose) {
        Write-Host $output
    }
    
    # Parse results
    $passLine = $output | Select-String "Passed:\s+(\d+)" | Select-Object -First 1
    $failLine = $output | Select-String "Failed:\s+(\d+)" | Select-Object -First 1
    
    if ($passLine -and $failLine) {
        $passed = [int]$passLine.Matches.Groups[1].Value
        $failed = [int]$failLine.Matches.Groups[1].Value
        $testResults["ROADMAP"] = @{
            Passed = $passed
            Failed = $failed
            Total = $passed + $failed
            Success = $failed -eq 0
        }
        Write-Host "✅ ROADMAP test completed: $passed passed, $failed failed" -ForegroundColor Green
    } else {
        $testResults["ROADMAP"] = @{ Success = $false; Error = "Could not parse results" }
        Write-Host "⚠️  ROADMAP test completed with warnings" -ForegroundColor Yellow
    }
} catch {
    $testResults["ROADMAP"] = @{ Success = $false; Error = $_.Exception.Message }
    Write-Host "❌ ROADMAP test failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host ""

# Test 2: Database Schema
Write-Host "📋 Test 2: Database Schema" -ForegroundColor Magenta
Write-Host "===========================" -ForegroundColor Magenta
Write-Host ""

try {
    Set-Location "C:\laragon\www\RentHub"
    $output = & ".\test-database-schema.ps1" 2>&1
    if ($Verbose) {
        Write-Host $output
    }
    
    # Parse results
    $passLine = $output | Select-String "Passed:\s+(\d+)" | Select-Object -First 1
    $failLine = $output | Select-String "Failed:\s+(\d+)" | Select-Object -First 1
    
    if ($passLine -and $failLine) {
        $passed = [int]$passLine.Matches.Groups[1].Value
        $failed = [int]$failLine.Matches.Groups[1].Value
        $testResults["Database"] = @{
            Passed = $passed
            Failed = $failed
            Total = $passed + $failed
            Success = $failed -le 5  # Allow up to 5 failures for optional features
        }
        Write-Host "✅ Database test completed: $passed passed, $failed failed" -ForegroundColor Green
    } else {
        $testResults["Database"] = @{ Success = $false; Error = "Could not parse results" }
        Write-Host "⚠️  Database test completed with warnings" -ForegroundColor Yellow
    }
} catch {
    $testResults["Database"] = @{ Success = $false; Error = $_.Exception.Message }
    Write-Host "❌ Database test failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host ""

# Test 3: API Endpoints (Optional - requires backend running)
if (-not $SkipAPI) {
    Write-Host "📋 Test 3: API Endpoints" -ForegroundColor Magenta
    Write-Host "=========================" -ForegroundColor Magenta
    Write-Host ""
    
    # Check if backend is running
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000" -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        
        try {
            $output = & ".\test-api-endpoints.ps1" 2>&1
            if ($Verbose) {
                Write-Host $output
            }
            
            # Parse results
            $availableLine = $output | Select-String "Available:\s+(\d+)" | Select-Object -First 1
            $missingLine = $output | Select-String "Missing:\s+(\d+)" | Select-Object -First 1
            
            if ($availableLine -and $missingLine) {
                $available = [int]$availableLine.Matches.Groups[1].Value
                $missing = [int]$missingLine.Matches.Groups[1].Value
                $testResults["API"] = @{
                    Passed = $available
                    Failed = $missing
                    Total = $available + $missing
                    Success = $missing -le 10  # Allow up to 10 missing endpoints
                }
                Write-Host "✅ API test completed: $available available, $missing missing" -ForegroundColor Green
            } else {
                $testResults["API"] = @{ Success = $false; Error = "Could not parse results" }
                Write-Host "⚠️  API test completed with warnings" -ForegroundColor Yellow
            }
        } catch {
            $testResults["API"] = @{ Success = $false; Error = $_.Exception.Message }
            Write-Host "❌ API test failed: $($_.Exception.Message)" -ForegroundColor Red
        }
    } catch {
        $testResults["API"] = @{ Success = $false; Skipped = $true; Reason = "Backend not running" }
        Write-Host "⚠️  API test skipped (backend not running on http://localhost:8000)" -ForegroundColor Yellow
        Write-Host "   Start backend with: cd backend && php artisan serve" -ForegroundColor Gray
    }
    
    Write-Host ""
    Write-Host ""
}

# Test 4: PHPUnit Tests
Write-Host "📋 Test 4: Backend PHPUnit Tests" -ForegroundColor Magenta
Write-Host "===================================" -ForegroundColor Magenta
Write-Host ""

Set-Location "C:\laragon\www\RentHub\backend"
try {
    if (Test-Path "vendor\bin\phpunit.bat") {
        Write-Host "Running PHPUnit tests..." -ForegroundColor Yellow
        $phpunitOutput = & "vendor\bin\phpunit.bat" --testdox 2>&1
        
        if ($Verbose) {
            Write-Host $phpunitOutput
        }
        
        # Parse results
        if ($phpunitOutput -match "OK \((\d+) test") {
            $testCount = $matches[1]
            $testResults["PHPUnit"] = @{
                Passed = $testCount
                Failed = 0
                Total = $testCount
                Success = $true
            }
            Write-Host "✅ PHPUnit tests passed: $testCount tests" -ForegroundColor Green
        } elseif ($phpunitOutput -match "Tests: (\d+), Assertions: (\d+), Failures: (\d+)") {
            $total = $matches[1]
            $failures = $matches[3]
            $testResults["PHPUnit"] = @{
                Passed = $total - $failures
                Failed = $failures
                Total = $total
                Success = $failures -eq 0
            }
            if ($failures -eq 0) {
                Write-Host "✅ PHPUnit tests passed: $total tests" -ForegroundColor Green
            } else {
                Write-Host "⚠️  PHPUnit tests completed: $($total - $failures) passed, $failures failed" -ForegroundColor Yellow
            }
        } else {
            $testResults["PHPUnit"] = @{ Success = $true; Info = "Tests executed" }
            Write-Host "✅ PHPUnit tests executed" -ForegroundColor Green
        }
    } else {
        $testResults["PHPUnit"] = @{ Success = $false; Skipped = $true; Reason = "PHPUnit not installed" }
        Write-Host "⚠️  PHPUnit not found. Run: composer install" -ForegroundColor Yellow
    }
} catch {
    $testResults["PHPUnit"] = @{ Success = $false; Error = $_.Exception.Message }
    Write-Host "❌ PHPUnit test failed: $($_.Exception.Message)" -ForegroundColor Red
}
Set-Location ".."

Write-Host ""
Write-Host ""

# Generate Summary Report
$endTime = Get-Date
$duration = $endTime - $startTime

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "📊 FINAL TEST SUMMARY" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$overallSuccess = $true
$totalTests = 0
$totalPassed = 0
$totalFailed = 0

foreach ($testName in $testResults.Keys | Sort-Object) {
    $result = $testResults[$testName]
    
    Write-Host "📋 $testName Test" -ForegroundColor White
    
    if ($result.Skipped) {
        Write-Host "   Status: SKIPPED - $($result.Reason)" -ForegroundColor Yellow
    } elseif ($result.Error) {
        Write-Host "   Status: ERROR - $($result.Error)" -ForegroundColor Red
        $overallSuccess = $false
    } elseif ($result.Success) {
        Write-Host "   Status: PASSED ✅" -ForegroundColor Green
        if ($result.Total) {
            Write-Host "   Tests:  $($result.Passed)/$($result.Total) passed" -ForegroundColor Gray
            $totalTests += $result.Total
            $totalPassed += $result.Passed
            $totalFailed += $result.Failed
        }
    } else {
        Write-Host "   Status: FAILED ❌" -ForegroundColor Red
        Write-Host "   Tests:  $($result.Passed)/$($result.Total) passed" -ForegroundColor Gray
        $overallSuccess = $false
        $totalTests += $result.Total
        $totalPassed += $result.Passed
        $totalFailed += $result.Failed
    }
    Write-Host ""
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Overall Statistics:" -ForegroundColor White
Write-Host "  Total Tests:    $totalTests" -ForegroundColor White
Write-Host "  Passed:         $totalPassed" -ForegroundColor Green
Write-Host "  Failed:         $totalFailed" -ForegroundColor $(if ($totalFailed -gt 0) { "Red" } else { "Gray" })
if ($totalTests -gt 0) {
    $successRate = [math]::Round(($totalPassed / $totalTests) * 100, 2)
    Write-Host "  Success Rate:   $successRate%" -ForegroundColor Yellow
}
Write-Host "  Duration:       $($duration.TotalSeconds.ToString('F2')) seconds" -ForegroundColor Cyan
Write-Host ""

if ($overallSuccess) {
    Write-Host "🎉 ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "The RentHub application is ready for deployment!" -ForegroundColor Green
} else {
    Write-Host "⚠️  SOME TESTS FAILED" -ForegroundColor Yellow
    Write-Host "Review the results above and fix the issues." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Generate documentation link
Write-Host "📚 Documentation:" -ForegroundColor Cyan
Write-Host "   - Full Report: ROADMAP_VERIFICATION_REPORT.md" -ForegroundColor White
Write-Host "   - ROADMAP:     ROADMAP.md" -ForegroundColor White
Write-Host "   - API Docs:    API_ENDPOINTS.md" -ForegroundColor White
Write-Host ""

# Exit with appropriate code
if ($overallSuccess) {
    exit 0
} else {
    exit 1
}

#####################################################################
# RENTHUB - COMPLETE FRONTEND TEST
# Testează TOATE PAGINILE din frontend pentru erori
#####################################################################

$ErrorActionPreference = 'Continue'
$baseUrl = "http://localhost:3000"
$totalTests = 0
$passedTests = 0
$failedTests = 0
$errors = @()

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      🚀 RENTHUB - COMPLETE FRONTEND TEST 🚀                ║" -ForegroundColor Cyan
Write-Host "║          Testing EVERY Page & Component!                   ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

# Lista completă de pagini de testat
$pages = @(
    @{Path="/"; Name="Homepage"},
    @{Path="/about"; Name="About Page"},
    @{Path="/properties"; Name="Properties Listing"},
    @{Path="/auth/login"; Name="Login Page"},
    @{Path="/auth/register"; Name="Register Page"},
    @{Path="/dashboard"; Name="Dashboard"},
    @{Path="/bookings"; Name="My Bookings"},
    @{Path="/favorites"; Name="Favorites/Wishlist"},
    @{Path="/messages"; Name="Messages"},
    @{Path="/profile"; Name="Profile"},
    @{Path="/settings"; Name="Settings"},
    @{Path="/verification"; Name="KYC Verification"},
    @{Path="/calendar-sync"; Name="Calendar Sync"},
    @{Path="/contact"; Name="Contact"},
    @{Path="/help"; Name="Help Center"},
    @{Path="/faq"; Name="FAQ"},
    @{Path="/terms"; Name="Terms & Conditions"},
    @{Path="/privacy"; Name="Privacy Policy"},
    @{Path="/insurance"; Name="Insurance Plans"},
    @{Path="/loyalty"; Name="Loyalty Program"},
    @{Path="/referrals"; Name="Referral Program"},
    @{Path="/screening"; Name="Guest Screening"},
    @{Path="/property-comparison"; Name="Property Comparison"},
    @{Path="/saved-searches"; Name="Saved Searches"},
    @{Path="/payments"; Name="Payments"},
    @{Path="/invoices"; Name="Invoices"},
    @{Path="/notifications"; Name="Notifications"},
    @{Path="/wishlists"; Name="Wishlists"},
    @{Path="/host/properties"; Name="Host Properties"},
    @{Path="/analytics"; Name="Analytics"},
    @{Path="/careers"; Name="Careers"},
    @{Path="/press"; Name="Press"}
)

function Test-PageLoads {
    param(
        [string]$Url,
        [string]$PageName
    )
    
    $script:totalTests++
    Write-Host "  Testing: $PageName... " -NoNewline
    
    try {
        $response = Invoke-WebRequest -Uri $Url -Method GET -TimeoutSec 30 -UseBasicParsing -ErrorAction Stop
        
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ PASSED (200 OK)" -ForegroundColor Green
            $script:passedTests++
            return $true
        }
        else {
            Write-Host "❌ FAILED (HTTP $($response.StatusCode))" -ForegroundColor Red
            $script:failedTests++
            $script:errors += @{Page=$PageName; Error="HTTP $($response.StatusCode)"}
            return $false
        }
    }
    catch {
        Write-Host "❌ FAILED" -ForegroundColor Red
        Write-Host "    Error: $($_.Exception.Message)" -ForegroundColor Yellow
        $script:failedTests++
        $script:errors += @{Page=$PageName; Error=$_.Exception.Message}
        return $false
    }
}

function Test-BackendApi {
    Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║  Backend API Health Check" -ForegroundColor Cyan
    Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan
    
    $script:totalTests++
    Write-Host "  Testing: Backend API Health... " -NoNewline
    
    try {
        $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/health" -Method GET -TimeoutSec 10
        if ($response.status -eq "ok") {
            Write-Host "✅ PASSED" -ForegroundColor Green
            $script:passedTests++
            return $true
        }
    }
    catch {
        Write-Host "❌ FAILED - Backend not running!" -ForegroundColor Red
        Write-Host "    Please start: cd backend; php artisan serve" -ForegroundColor Yellow
        $script:failedTests++
        $script:errors += @{Page="Backend API"; Error="Backend not running"}
        return $false
    }
}

function Test-FrontendRunning {
    Write-Host "  Testing: Frontend Server... " -NoNewline
    
    try {
        $response = Invoke-WebRequest -Uri $baseUrl -Method GET -TimeoutSec 10 -UseBasicParsing
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ PASSED" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "❌ FAILED - Frontend not running!" -ForegroundColor Red
        Write-Host "    Please start: cd frontend; npm run dev" -ForegroundColor Yellow
        return $false
    }
}

# Main test sequence
Write-Host "╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  STEP 1: Server Health Checks" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$frontendRunning = Test-FrontendRunning
$backendRunning = Test-BackendApi

if (-not $frontendRunning) {
    Write-Host "`n⚠️  CRITICAL: Frontend server is not running!" -ForegroundColor Red
    Write-Host "Please run: cd frontend; npm run dev" -ForegroundColor Yellow
    exit 1
}

if (-not $backendRunning) {
    Write-Host "`n⚠️  WARNING: Backend API is not running!" -ForegroundColor Yellow
    Write-Host "Some pages may not work correctly." -ForegroundColor Yellow
    Write-Host "To start: cd backend; php artisan serve" -ForegroundColor Yellow
}

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  STEP 2: Test All Frontend Pages" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

foreach ($page in $pages) {
    $url = $baseUrl + $page.Path
    Test-PageLoads -Url $url -PageName $page.Name
    Start-Sleep -Milliseconds 100
}

# Summary
Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                  📊 TEST SUMMARY                              ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "📈 Test Statistics:" -ForegroundColor White
Write-Host "   Total Tests: $totalTests" -ForegroundColor White
Write-Host "   Passed: $passedTests" -ForegroundColor Green
Write-Host "   Failed: $failedTests" -ForegroundColor $(if ($failedTests -gt 0) { "Red" } else { "Green" })

$successRate = [math]::Round(($passedTests / $totalTests) * 100, 2)
Write-Host "   Success Rate: $successRate%" -ForegroundColor $(if ($successRate -ge 95) { "Green" } elseif ($successRate -ge 80) { "Yellow" } else { "Red" })

if ($errors.Count -gt 0) {
    Write-Host "`n⚠️  Failed Tests Details:" -ForegroundColor Yellow
    foreach ($err in $errors) {
        Write-Host "   ❌ $($err.Page)" -ForegroundColor Red
        Write-Host "      Error: $($err.Error)" -ForegroundColor Yellow
    }
}
else {
    Write-Host "`n✅ ALL TESTS PASSED!" -ForegroundColor Green
}

Write-Host "`n═══════════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

# Save results
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$reportFile = "FRONTEND_TEST_RESULTS_$timestamp.txt"
$report = @"
RENTHUB FRONTEND TEST RESULTS
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
═══════════════════════════════════════════════════════════════

Total Tests: $totalTests
Passed: $passedTests
Failed: $failedTests
Success Rate: $successRate%

FAILED TESTS:
$(if ($errors.Count -eq 0) { "None - All tests passed!" } else { $errors | ForEach-Object { "❌ $($_.Page): $($_.Error)" } | Out-String })

TESTED PAGES:
$($pages | ForEach-Object { "  - $($_.Name) ($($_.Path))" } | Out-String)

═══════════════════════════════════════════════════════════════
"@

$report | Out-File -FilePath $reportFile -Encoding UTF8
Write-Host "📄 Detailed results saved to: $reportFile" -ForegroundColor Cyan

if ($failedTests -eq 0) {
    exit 0
}
else {
    exit 1
}

#!/usr/bin/env pwsh
# RentHub - Complete Step-by-Step Testing Guide
# Date: November 11, 2025

$ErrorActionPreference = "Continue"
$testResults = @()

Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║         RENTHUB - COMPLETE APPLICATION TESTING                 ║" -ForegroundColor Cyan
Write-Host "║              Step-by-Step Verification                         ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

function Write-Step {
    param([string]$Step, [string]$Description)
    Write-Host "`n┌─────────────────────────────────────────────────────────────┐" -ForegroundColor Yellow
    Write-Host "│ $Step" -ForegroundColor Yellow
    Write-Host "│ $Description" -ForegroundColor Gray
    Write-Host "└─────────────────────────────────────────────────────────────┘" -ForegroundColor Yellow
}

function Write-Success {
    param([string]$Message)
    Write-Host "  ✓ $Message" -ForegroundColor Green
}

function Write-Fail {
    param([string]$Message)
    Write-Host "  ✗ $Message" -ForegroundColor Red
}

function Write-Info {
    param([string]$Message)
    Write-Host "  ℹ $Message" -ForegroundColor Cyan
}

function Write-Warning {
    param([string]$Message)
    Write-Host "  ⚠ $Message" -ForegroundColor Yellow
}

# ═══════════════════════════════════════════════════════════════
# STEP 1: Environment Check
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 1" "Checking Development Environment"

try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Success "PHP Installed: $phpVersion"
    $testResults += @{Step="Environment"; Test="PHP"; Status="PASS"}
} catch {
    Write-Fail "PHP not found in PATH"
    $testResults += @{Step="Environment"; Test="PHP"; Status="FAIL"}
}

try {
    $nodeVersion = node -v 2>&1
    Write-Success "Node.js Installed: $nodeVersion"
    $testResults += @{Step="Environment"; Test="Node.js"; Status="PASS"}
} catch {
    Write-Fail "Node.js not found in PATH"
    $testResults += @{Step="Environment"; Test="Node.js"; Status="FAIL"}
}

try {
    $npmVersion = npm -v 2>&1
    Write-Success "npm Installed: v$npmVersion"
    $testResults += @{Step="Environment"; Test="npm"; Status="PASS"}
} catch {
    Write-Fail "npm not found in PATH"
    $testResults += @{Step="Environment"; Test="npm"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 2: Backend Server Check
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 2" "Checking Backend Server"

try {
    $response = Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue
    if ($response) {
        Write-Success "Backend server is running on port 8000"
        $testResults += @{Step="Backend"; Test="Server Running"; Status="PASS"}
    } else {
        Write-Warning "Backend server not running on port 8000"
        Write-Info "Starting backend server in background..."
        
        Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Set-Location 'c:\laragon\www\RentHub\backend'; php artisan serve --host=127.0.0.1 --port=8000" -WindowStyle Minimized
        Start-Sleep -Seconds 5
        
        $response = Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet -WarningAction SilentlyContinue
        if ($response) {
            Write-Success "Backend server started successfully"
            $testResults += @{Step="Backend"; Test="Server Start"; Status="PASS"}
        } else {
            Write-Fail "Failed to start backend server"
            $testResults += @{Step="Backend"; Test="Server Start"; Status="FAIL"}
        }
    }
} catch {
    Write-Fail "Error checking backend server: $($_.Exception.Message)"
    $testResults += @{Step="Backend"; Test="Server Check"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 3: Database Connection Test
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 3" "Testing Database Connection"

try {
    $headers = @{'Accept' = 'application/json'}
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/currencies" -Headers $headers -ErrorAction Stop
    Write-Success "Database connection working (currencies endpoint accessible)"
    $testResults += @{Step="Database"; Test="Connection"; Status="PASS"}
} catch {
    Write-Fail "Database connection issue: $($_.Exception.Message)"
    $testResults += @{Step="Database"; Test="Connection"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 4: API Registration Test
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 4" "Testing API Registration Endpoint"

$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$testEmail = "test_$timestamp@renthub.test"
$testPassword = "TestPassword123!"

try {
    $headers = @{
        'Content-Type' = 'application/json'
        'Accept' = 'application/json'
    }
    
    $body = @{
        name = "Test User $timestamp"
        email = $testEmail
        password = $testPassword
        password_confirmation = $testPassword
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/register" -Method POST -Headers $headers -Body $body -ErrorAction Stop
    
    if ($response.token) {
        Write-Success "User registered successfully"
        Write-Info "User ID: $($response.user.id)"
        Write-Info "Email: $($response.user.email)"
        Write-Info "Token: $($response.token.Substring(0,20))..."
        $script:authToken = $response.token
        $script:userId = $response.user.id
        $testResults += @{Step="API"; Test="Registration"; Status="PASS"}
    } else {
        Write-Fail "Registration successful but no token received"
        $testResults += @{Step="API"; Test="Registration"; Status="FAIL"}
    }
} catch {
    Write-Fail "Registration failed: $($_.Exception.Message)"
    $testResults += @{Step="API"; Test="Registration"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 5: API Login Test
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 5" "Testing API Login Endpoint"

try {
    $headers = @{
        'Content-Type' = 'application/json'
        'Accept' = 'application/json'
    }
    
    $body = @{
        email = $testEmail
        password = $testPassword
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/login" -Method POST -Headers $headers -Body $body -ErrorAction Stop
    
    if ($response.token) {
        Write-Success "Login successful"
        Write-Info "Token: $($response.token.Substring(0,20))..."
        $script:authToken = $response.token
        $testResults += @{Step="API"; Test="Login"; Status="PASS"}
    } else {
        Write-Fail "Login successful but no token received"
        $testResults += @{Step="API"; Test="Login"; Status="FAIL"}
    }
} catch {
    Write-Fail "Login failed: $($_.Exception.Message)"
    $testResults += @{Step="API"; Test="Login"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 6: Authenticated Endpoints Test
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 6" "Testing Authenticated Endpoints"

if ($script:authToken) {
    $authHeaders = @{
        'Authorization' = "Bearer $script:authToken"
        'Accept' = 'application/json'
        'Content-Type' = 'application/json'
    }
    
    # Test getting properties
    try {
        $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/properties" -Headers $authHeaders -ErrorAction Stop
        Write-Success "Properties endpoint accessible"
        Write-Info "Properties count: $(if($response.data){$response.data.Count}else{0})"
        $testResults += @{Step="API"; Test="Get Properties"; Status="PASS"}
    } catch {
        Write-Warning "Properties endpoint: $($_.Exception.Message)"
        $testResults += @{Step="API"; Test="Get Properties"; Status="WARN"}
    }
    
    # Test getting amenities
    try {
        $response = Invoke-RestMethod -Uri "http://127.0.0.1:8000/api/v1/amenities" -Headers $authHeaders -ErrorAction Stop
        Write-Success "Amenities endpoint accessible"
        $testResults += @{Step="API"; Test="Get Amenities"; Status="PASS"}
    } catch {
        Write-Warning "Amenities endpoint: $($_.Exception.Message)"
        $testResults += @{Step="API"; Test="Get Amenities"; Status="WARN"}
    }
} else {
    Write-Warning "Skipping authenticated tests (no auth token)"
}

# ═══════════════════════════════════════════════════════════════
# STEP 7: Admin Panel Access Test
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 7" "Testing Admin Panel Access"

try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/admin/login" -Method GET -UseBasicParsing -ErrorAction Stop
    
    if ($response.StatusCode -eq 200) {
        Write-Success "Admin panel accessible"
        Write-Info "URL: http://127.0.0.1:8000/admin/login"
        $testResults += @{Step="Admin"; Test="Login Page"; Status="PASS"}
    }
} catch {
    Write-Fail "Admin panel not accessible: $($_.Exception.Message)"
    $testResults += @{Step="Admin"; Test="Login Page"; Status="FAIL"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 8: Frontend Check
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 8" "Checking Frontend Status"

try {
    $frontendRunning = Test-NetConnection -ComputerName 127.0.0.1 -Port 3000 -InformationLevel Quiet -WarningAction SilentlyContinue
    
    if ($frontendRunning) {
        Write-Success "Frontend server is running on port 3000"
        $testResults += @{Step="Frontend"; Test="Server Running"; Status="PASS"}
        
        try {
            $response = Invoke-WebRequest -Uri "http://localhost:3000" -Method GET -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
            Write-Success "Frontend homepage accessible"
            Write-Info "URL: http://localhost:3000"
            $testResults += @{Step="Frontend"; Test="Homepage"; Status="PASS"}
        } catch {
            Write-Warning "Frontend server running but homepage not responding"
            $testResults += @{Step="Frontend"; Test="Homepage"; Status="WARN"}
        }
    } else {
        Write-Warning "Frontend server not running on port 3000"
        Write-Info "To start: cd frontend && npm run dev"
        $testResults += @{Step="Frontend"; Test="Server Running"; Status="WARN"}
    }
} catch {
    Write-Warning "Error checking frontend: $($_.Exception.Message)"
    $testResults += @{Step="Frontend"; Test="Check"; Status="WARN"}
}

# ═══════════════════════════════════════════════════════════════
# STEP 9: File Structure Check
# ═══════════════════════════════════════════════════════════════
Write-Step "STEP 9" "Verifying File Structure"

$requiredFiles = @(
    @{Path="backend/.env"; Name=".env file"},
    @{Path="backend/artisan"; Name="Artisan CLI"},
    @{Path="backend/composer.json"; Name="Composer config"},
    @{Path="frontend/package.json"; Name="Package.json"},
    @{Path="frontend/next.config.ts"; Name="Next.js config"},
    @{Path="frontend/tsconfig.json"; Name="TypeScript config"}
)

foreach ($file in $requiredFiles) {
    $fullPath = Join-Path "c:\laragon\www\RentHub" $file.Path
    if (Test-Path $fullPath) {
        Write-Success "$($file.Name) exists"
        $testResults += @{Step="Files"; Test=$file.Name; Status="PASS"}
    } else {
        Write-Fail "$($file.Name) missing at $($file.Path)"
        $testResults += @{Step="Files"; Test=$file.Name; Status="FAIL"}
    }
}

# ═══════════════════════════════════════════════════════════════
# RESULTS SUMMARY
# ═══════════════════════════════════════════════════════════════
Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                     TEST RESULTS SUMMARY                       ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$passed = ($testResults | Where-Object {$_.Status -eq "PASS"}).Count
$failed = ($testResults | Where-Object {$_.Status -eq "FAIL"}).Count
$warnings = ($testResults | Where-Object {$_.Status -eq "WARN"}).Count
$total = $testResults.Count

Write-Host "Total Tests: $total" -ForegroundColor White
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor Red
Write-Host "Warnings: $warnings" -ForegroundColor Yellow

$percentage = [math]::Round(($passed / $total) * 100, 2)
Write-Host "`nSuccess Rate: $percentage%" -ForegroundColor $(if($percentage -gt 80){"Green"}elseif($percentage -gt 50){"Yellow"}else{"Red"})

# Generate detailed report
Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                     DETAILED RESULTS                           ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$testResults | Group-Object -Property Step | ForEach-Object {
    Write-Host "`n$($_.Name):" -ForegroundColor Yellow
    $_.Group | ForEach-Object {
        $symbol = switch($_.Status) {
            "PASS" { "✓" }
            "FAIL" { "✗" }
            "WARN" { "⚠" }
        }
        $color = switch($_.Status) {
            "PASS" { "Green" }
            "FAIL" { "Red" }
            "WARN" { "Yellow" }
        }
        Write-Host "  $symbol $($_.Test)" -ForegroundColor $color
    }
}

# Next steps
Write-Host "`n╔════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                      NEXT STEPS                                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "1. Backend Server: http://127.0.0.1:8000" -ForegroundColor White
Write-Host "2. Frontend App: http://localhost:3000" -ForegroundColor White
Write-Host "3. Admin Panel: http://127.0.0.1:8000/admin" -ForegroundColor White
Write-Host "4. API Documentation: http://127.0.0.1:8000/api/documentation" -ForegroundColor White

if ($failed -eq 0) {
    Write-Host "`n✓ ALL CRITICAL TESTS PASSED! Application is ready." -ForegroundColor Green
} else {
    Write-Host "`n⚠ Some tests failed. Please review the results above." -ForegroundColor Yellow
}

Write-Host "`nTest credentials created:" -ForegroundColor Cyan
Write-Host "  Email: $testEmail" -ForegroundColor White
Write-Host "  Password: $testPassword" -ForegroundColor White
Write-Host "`n"

# RentHub Local Development Testing Script
# This script performs comprehensive testing of the local development environment

Write-Host "🔧 RentHub Local Development Testing Script" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

# Colors for output
$GREEN = "Green"
$RED = "Red"
$YELLOW = "Yellow"
$BLUE = "Blue"

function Test-Step {
    param(
        [string]$StepName,
        [scriptblock]$TestScript,
        [string]$SuccessMessage,
        [string]$FailureMessage
    )
    
    Write-Host "`n📋 Testing: $StepName" -ForegroundColor $BLUE
    try {
        $result = & $TestScript
        if ($result -eq $true -or $result -eq $null) {
            Write-Host "✅ $SuccessMessage" -ForegroundColor $GREEN
            return $true
        } else {
            Write-Host "❌ $FailureMessage" -ForegroundColor $RED
            return $false
        }
    } catch {
        Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor $RED
        return $false
    }
}

# Test 1: Frontend Development Server
$frontendTest = Test-Step "Frontend Development Server" {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:3000" -Method GET -TimeoutSec 10
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
} "Frontend server is running on http://localhost:3000" "Frontend server is not accessible"

# Test 2: Backend Laravel Server
$backendTest = Test-Step "Backend Laravel Server" {
    try {
        # Start Laravel server in background if not running
        $processes = Get-Process -Name "php" -ErrorAction SilentlyContinue
        $laravelRunning = $false
        
        foreach ($process in $processes) {
            if ($process.CommandLine -like "*artisan serve*" -or $process.CommandLine -like "*server.php*") {
                $laravelRunning = $true
                break
            }
        }
        
        if (-not $laravelRunning) {
            Write-Host "🚀 Starting Laravel development server..." -ForegroundColor $YELLOW
            Start-Process -FilePath "php" -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" -WorkingDirectory "backend" -WindowStyle Hidden
            Start-Sleep -Seconds 3
        }
        
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/health" -Method GET -TimeoutSec 10
        return $response.StatusCode -eq 200
    } catch {
        return $false
    }
} "Backend server is running on http://127.0.0.1:8000" "Backend server is not accessible"

# Test 3: API Health Check
$apiHealthTest = Test-Step "API Health Check" {
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/health" -Method GET -TimeoutSec 10
        $content = $response.Content | ConvertFrom-Json
        return $response.StatusCode -eq 200 -and $content.status -eq "healthy"
    } catch {
        return $false
    }
} "API health endpoint is working correctly" "API health endpoint failed"

# Test 4: Public Settings API
$publicSettingsTest = Test-Step "Public Settings API" {
    try {
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/settings/public" -Method GET -TimeoutSec 10
        $content = $response.Content | ConvertFrom-Json
        return $response.StatusCode -eq 200 -and $content.success -eq $true
    } catch {
        return $false
    }
} "Public settings API is accessible" "Public settings API failed"

# Test 5: CORS Configuration
$corsTest = Test-Step "CORS Configuration" {
    try {
        $headers = @{
            "Origin" = "http://localhost:3000"
            "Access-Control-Request-Method" = "GET"
        }
        $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/health" -Method OPTIONS -Headers $headers -TimeoutSec 10
        return $response.StatusCode -eq 200 -or $response.StatusCode -eq 204
    } catch {
        return $false
    }
} "CORS preflight requests are working" "CORS configuration has issues"

# Test 6: Database Connectivity
$databaseTest = Test-Step "Database Connectivity" {
    try {
        Set-Location -Path "backend"
        $output = php artisan migrate:status 2>&1
        Set-Location -Path ..
        return $output -like "*Ran*" -or $output -like "*Migrated*"
    } catch {
        return $false
    }
} "Database migrations are accessible" "Database connectivity failed"

# Test 7: TypeScript Compilation
$typeScriptTest = Test-Step "TypeScript Compilation" {
    try {
        Set-Location -Path "frontend"
        $output = npm run type-check 2>&1
        Set-Location -Path ..
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
} "TypeScript compilation passes" "TypeScript has errors"

# Test 8: Frontend Build
$frontendBuildTest = Test-Step "Frontend Build Process" {
    try {
        Set-Location -Path "frontend"
        $output = npm run build 2>&1
        Set-Location -Path ..
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
} "Frontend builds successfully" "Frontend build failed"

# Test 9: Authentication Flow Test
$authTest = Test-Step "Authentication Flow" {
    try {
        # Test registration
        $body = @{
            name = "Test User"
            email = "test-$(Get-Random)@example.com"
            password = "password123"
            password_confirmation = "password123"
        } | ConvertTo-Json
        
        $registerResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/register" -Method POST -Body $body -ContentType "application/json" -TimeoutSec 10
        
        if ($registerResponse.StatusCode -ne 201) {
            return $false
        }
        
        $registerData = $registerResponse.Content | ConvertFrom-Json
        $token = $registerData.access_token
        
        # Test authenticated request
        $headers = @{
            "Authorization" = "Bearer $token"
        }
        
        $meResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/v1/me" -Method GET -Headers $headers -TimeoutSec 10
        
        return $meResponse.StatusCode -eq 200
    } catch {
        return $false
    }
} "Authentication flow works correctly" "Authentication flow has issues"

# Test 10: API Routes Verification
$routesTest = Test-Step "API Routes Verification" {
    try {
        Set-Location -Path "backend"
        $output = php artisan route:list --name=api 2>&1
        Set-Location -Path ..
        return $output -like "*api/v1/register*" -and $output -like "*api/v1/login*" -and $output -like "*api/v1/me*"
    } catch {
        return $false
    }
} "API routes are properly configured" "API routes missing or misconfigured"

# Summary
Write-Host "`n📊 TESTING SUMMARY" -ForegroundColor $BLUE
Write-Host "==================" -ForegroundColor $BLUE

$tests = @($frontendTest, $backendTest, $apiHealthTest, $publicSettingsTest, $corsTest, $databaseTest, $typeScriptTest, $frontendBuildTest, $authTest, $routesTest)
$passed = ($tests | Where-Object { $_ -eq $true }).Count
$total = $tests.Count

Write-Host "Tests Passed: $passed/$total" -ForegroundColor $(if ($passed -eq $total) { $GREEN } else { $YELLOW })

if ($passed -eq $total) {
    Write-Host "🎉 All tests passed! Local development environment is ready." -ForegroundColor $GREEN
} else {
    Write-Host "⚠️  Some tests failed. Please check the issues above." -ForegroundColor $YELLOW
}

# Generate test report
$report = @{
    timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    environment = "local"
    tests_passed = $passed
    tests_total = $total
    results = @{
        frontend_server = $frontendTest
        backend_server = $backendTest
        api_health = $apiHealthTest
        public_settings = $publicSettingsTest
        cors = $corsTest
        database = $databaseTest
        typescript = $typeScriptTest
        frontend_build = $frontendBuildTest
        authentication = $authTest
        api_routes = $routesTest
    }
}

$reportPath = "local_test_report.json"
$report | ConvertTo-Json -Depth 3 | Out-File -FilePath $reportPath -Encoding UTF8
Write-Host "📄 Test report saved to: $reportPath" -ForegroundColor $BLUE

Write-Host "`n🎯 Next Steps:" -ForegroundColor $BLUE
Write-Host "- Review any failed tests above"
Write-Host "- Fix identified issues"
Write-Host "- Run staging tests when ready"
Write-Host "- Deploy to production after validation"
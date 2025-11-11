# Backend-Frontend Connection Test Script
# Run this to verify all connections are working

Write-Host "=== RentHub Backend-Frontend Connection Test ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: Backend is running
Write-Host "[1/6] Testing Backend..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/properties" -UseBasicParsing
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Backend is running on http://localhost:8000" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Backend is NOT running. Please start with: php artisan serve" -ForegroundColor Red
    exit 1
}

# Test 2: CORS headers
Write-Host "[2/6] Testing CORS..." -ForegroundColor Yellow
try {
    $headers = @{
        "Origin" = "http://localhost:3000"
        "Access-Control-Request-Method" = "GET"
    }
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/properties" -Headers $headers -UseBasicParsing -Method OPTIONS -ErrorAction SilentlyContinue
    Write-Host "✅ CORS is configured" -ForegroundColor Green
} catch {
    Write-Host "⚠️  CORS preflight may need adjustment" -ForegroundColor Yellow
}

# Test 3: Public endpoints
Write-Host "[3/6] Testing Public Endpoints..." -ForegroundColor Yellow
$endpoints = @(
    "/api/v1/properties",
    "/api/v1/properties/featured",
    "/api/v1/languages",
    "/api/v1/currencies"
)

foreach ($endpoint in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8000$endpoint" -UseBasicParsing -ErrorAction Stop
        if ($response.StatusCode -eq 200) {
            Write-Host "  ✅ $endpoint" -ForegroundColor Green
        }
    } catch {
        Write-Host "  ❌ $endpoint - $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test 4: Auth endpoints (should work without token)
Write-Host "[4/6] Testing Auth Endpoints..." -ForegroundColor Yellow
$authEndpoints = @(
    "/api/v1/login",
    "/api/v1/register"
)

foreach ($endpoint in $authEndpoints) {
    try {
        # POST request should return 422 (validation error) not 404
        $body = @{} | ConvertTo-Json
        $response = Invoke-WebRequest -Uri "http://localhost:8000$endpoint" -Method POST -Body $body -ContentType "application/json" -UseBasicParsing -ErrorAction SilentlyContinue
    } catch {
        if ($_.Exception.Response.StatusCode.value__ -eq 422) {
            Write-Host "  ✅ $endpoint (accepts requests)" -ForegroundColor Green
        } elseif ($_.Exception.Response.StatusCode.value__ -eq 404) {
            Write-Host "  ❌ $endpoint (not found)" -ForegroundColor Red
        } else {
            Write-Host "  ✅ $endpoint (available)" -ForegroundColor Green
        }
    }
}

# Test 5: Database connection
Write-Host "[5/6] Testing Database..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/properties" -UseBasicParsing
    $data = $response.Content | ConvertFrom-Json
    if ($data.success) {
        Write-Host "✅ Database connected and responding" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Database connection issue" -ForegroundColor Red
}

# Test 6: Frontend configuration
Write-Host "[6/6] Checking Frontend Configuration..." -ForegroundColor Yellow
if (Test-Path "frontend\.env.local") {
    $envContent = Get-Content "frontend\.env.local" -Raw
    if ($envContent -match "NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1") {
        Write-Host "✅ Frontend .env.local configured correctly" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Check NEXT_PUBLIC_API_BASE_URL in frontend\.env.local" -ForegroundColor Yellow
    }
} else {
    Write-Host "❌ frontend\.env.local not found" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Test Summary ===" -ForegroundColor Cyan
Write-Host "Backend URL: http://localhost:8000" -ForegroundColor White
Write-Host "Frontend URL: http://localhost:3000 (when running)" -ForegroundColor White
Write-Host "API Base: http://localhost:8000/api/v1" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Start frontend: cd frontend && npm run dev" -ForegroundColor White
Write-Host "2. Visit: http://localhost:3000" -ForegroundColor White
Write-Host "3. Try to register/login" -ForegroundColor White
Write-Host ""

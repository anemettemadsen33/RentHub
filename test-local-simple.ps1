# RentHub Local Development Testing Script
Write-Host "🔧 RentHub Local Development Testing" -ForegroundColor Green

# Test Frontend
Write-Host "Testing Frontend..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:3000" -Method GET -TimeoutSec 5
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Frontend running on http://localhost:3000" -ForegroundColor Green
    } else {
        Write-Host "❌ Frontend not responding" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Frontend not accessible: $($_.Exception.Message)" -ForegroundColor Red
}

# Test Backend
Write-Host "`nTesting Backend..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/health" -Method GET -TimeoutSec 5
    if ($response.StatusCode -eq 200) {
        Write-Host "✅ Backend API running on http://127.0.0.1:8000" -ForegroundColor Green
        $content = $response.Content | ConvertFrom-Json
        Write-Host "   Status: $($content.status)" -ForegroundColor Gray
    } else {
        Write-Host "❌ Backend API not responding" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Backend API not accessible: $($_.Exception.Message)" -ForegroundColor Red
}

# Test API Endpoints
Write-Host "`nTesting API Endpoints..." -ForegroundColor Yellow
$endpoints = @(
    @{url="http://127.0.0.1:8000/api/v1/settings/public"; name="Public Settings"},
    @{url="http://127.0.0.1:8000/api/v1/properties"; name="Properties"}
)

foreach ($endpoint in $endpoints) {
    try {
        $response = Invoke-WebRequest -Uri $endpoint.url -Method GET -TimeoutSec 5
        if ($response.StatusCode -eq 200) {
            Write-Host "✅ $($endpoint.name) - Working" -ForegroundColor Green
        } else {
            Write-Host "⚠️  $($endpoint.name) - Status: $($response.StatusCode)" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "❌ $($endpoint.name) - Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Test TypeScript
Write-Host "`nTesting TypeScript..." -ForegroundColor Yellow
Set-Location -Path "frontend"
$tsResult = npm run type-check 2>&1
Set-Location -Path ..
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ TypeScript compilation successful" -ForegroundColor Green
} else {
    Write-Host "❌ TypeScript errors found" -ForegroundColor Red
    Write-Host $tsResult -ForegroundColor Gray
}

Write-Host "`n🎯 Testing Complete!" -ForegroundColor Green
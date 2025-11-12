# 🔍 Quick Backend API Test Script

Write-Host "🔍 Testing RentHub Backend API..." -ForegroundColor Cyan
Write-Host ""

$baseUrl = "https://renthub-tbj7yxj7.on-forge.com"

# Test 1: Health Check / Base URL
Write-Host "1️⃣ Testing Base URL..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri $baseUrl -Method GET -UseBasicParsing
    Write-Host "   ✅ Status: $($response.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 2: API Endpoint
Write-Host "2️⃣ Testing API Endpoint..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api" -Method GET -UseBasicParsing
    Write-Host "   ✅ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "   📄 Response: $($response.Content.Substring(0, [Math]::Min(200, $response.Content.Length)))" -ForegroundColor Gray
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 3: Properties Endpoint
Write-Host "3️⃣ Testing Properties API..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/api/v1/properties" -Method GET -UseBasicParsing
    Write-Host "   ✅ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "   📄 Response: $($response.Content.Substring(0, [Math]::Min(200, $response.Content.Length)))" -ForegroundColor Gray
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}

Write-Host ""

# Test 4: CORS Headers
Write-Host "4️⃣ Testing CORS Headers..." -ForegroundColor Yellow
try {
    $headers = @{
        "Origin" = "https://rent-hub-beta.vercel.app"
    }
    $response = Invoke-WebRequest -Uri "$baseUrl/api/v1/properties" -Method OPTIONS -Headers $headers -UseBasicParsing
    Write-Host "   ✅ CORS Preflight: $($response.StatusCode)" -ForegroundColor Green
    
    # Check CORS headers
    $corsHeaders = $response.Headers | Where-Object { $_.Key -like "*Access-Control*" }
    if ($corsHeaders) {
        Write-Host "   📋 CORS Headers Found:" -ForegroundColor Green
        foreach ($header in $corsHeaders) {
            Write-Host "      - $($header.Key): $($header.Value)" -ForegroundColor Gray
        }
    } else {
        Write-Host "   ⚠️  No CORS headers found!" -ForegroundColor Yellow
    }
} catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ API Test Complete!" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Summary:" -ForegroundColor Cyan
Write-Host "   - Base URL: $baseUrl" -ForegroundColor Gray
Write-Host "   - Frontend URL: https://rent-hub-beta.vercel.app" -ForegroundColor Gray
Write-Host ""
Write-Host "🔧 Next Steps:" -ForegroundColor Yellow
Write-Host "   1. If API returns 500, check Laravel logs on Forge" -ForegroundColor Gray
Write-Host "   2. If CORS errors, update backend/config/cors.php" -ForegroundColor Gray
Write-Host "   3. Check EMERGENCY_FIX_DEPLOYMENT.md for detailed instructions" -ForegroundColor Gray

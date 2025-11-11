# Test CSRF + Register Flow
Write-Host "=== Testing CSRF Cookie + Register ===" -ForegroundColor Cyan

# Step 1: Get CSRF Cookie
Write-Host "`n1. Fetching CSRF cookie..." -ForegroundColor Yellow
try {
    $csrfResponse = Invoke-WebRequest `
        -Uri "http://127.0.0.1:8000/sanctum/csrf-cookie" `
        -Method GET `
        -SessionVariable 'session' `
        -UseBasicParsing
    
    Write-Host "✅ CSRF cookie fetched!" -ForegroundColor Green
    Write-Host "   Status: $($csrfResponse.StatusCode)" -ForegroundColor Gray
    
    # Extract XSRF-TOKEN cookie
    $xsrfToken = $session.Cookies.GetCookies("http://127.0.0.1:8000") | Where-Object { $_.Name -eq 'XSRF-TOKEN' } | Select-Object -ExpandProperty Value
    
    if ($xsrfToken) {
        Write-Host "   XSRF-TOKEN: $xsrfToken" -ForegroundColor Gray
    } else {
        Write-Host "   ⚠️  No XSRF-TOKEN found in cookies!" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Failed to fetch CSRF cookie" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

# Step 2: Register with CSRF token
Write-Host "`n2. Registering user with CSRF token..." -ForegroundColor Yellow

$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
    "X-XSRF-TOKEN" = [System.Web.HttpUtility]::UrlDecode($xsrfToken)
    "Referer" = "http://localhost:3000"
    "Origin" = "http://localhost:3000"
}

$body = @{
    name = "Test User"
    email = "test" + (Get-Random) + "@example.com"
    password = "Test1234!"
    password_confirmation = "Test1234!"
    role = "tenant"
} | ConvertTo-Json

try {
    $registerResponse = Invoke-WebRequest `
        -Uri "http://127.0.0.1:8000/api/v1/register" `
        -Method POST `
        -Headers $headers `
        -Body $body `
        -WebSession $session `
        -UseBasicParsing
    
    Write-Host "✅ Registration successful!" -ForegroundColor Green
    Write-Host "   Status: $($registerResponse.StatusCode)" -ForegroundColor Gray
    
    $responseData = $registerResponse.Content | ConvertFrom-Json
    Write-Host "`nUser created:" -ForegroundColor Green
    Write-Host "   ID: $($responseData.user.id)" -ForegroundColor Gray
    Write-Host "   Name: $($responseData.user.name)" -ForegroundColor Gray
    Write-Host "   Email: $($responseData.user.email)" -ForegroundColor Gray
    Write-Host "   Token: $($responseData.token.Substring(0, 20))..." -ForegroundColor Gray
    
} catch {
    Write-Host "❌ Registration failed!" -ForegroundColor Red
    Write-Host "   Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $responseBody = $reader.ReadToEnd()
        Write-Host "   Response: $responseBody" -ForegroundColor Red
    }
}

Write-Host "`n=== Test Complete ===" -ForegroundColor Cyan

# Test CORS Headers
$response = Invoke-WebRequest -Uri "http://localhost:8000/sanctum/csrf-cookie" -Method GET -Headers @{
    "Origin" = "http://localhost:3000"
    "Accept" = "application/json"
} -UseBasicParsing -SessionVariable session

Write-Host "`n=== CSRF Cookie Response ===" -ForegroundColor Green
Write-Host "Status Code: $($response.StatusCode)" -ForegroundColor Cyan
Write-Host "`nCORS Headers:" -ForegroundColor Yellow
$response.Headers.GetEnumerator() | Where-Object { $_.Key -like "*Access-Control*" } | ForEach-Object {
    Write-Host "  $($_.Key): $($_.Value)" -ForegroundColor White
}

# Now test registration with CSRF token
$csrfToken = $session.Cookies.GetCookies("http://localhost:8000") | Where-Object { $_.Name -eq "XSRF-TOKEN" } | Select-Object -ExpandProperty Value

$timestamp = Get-Date -Format "HHmmss"
$body = @{
    name = "Test User"
    email = "test$timestamp@example.com"
    password = "Password123!"
    password_confirmation = "Password123!"
} | ConvertTo-Json

Write-Host "`n=== Registration Request ===" -ForegroundColor Green
Write-Host "Email: test$timestamp@example.com" -ForegroundColor Cyan
Write-Host "CSRF Token: $($csrfToken.Substring(0, 20))..." -ForegroundColor Cyan

try {
    $registerResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/register" -Method POST -Headers @{
        "Origin" = "http://localhost:3000"
        "Accept" = "application/json"
        "Content-Type" = "application/json"
        "X-XSRF-TOKEN" = [System.Web.HttpUtility]::UrlDecode($csrfToken)
    } -Body $body -UseBasicParsing -WebSession $session

    Write-Host "`n=== Registration Response ===" -ForegroundColor Green
    Write-Host "Status Code: $($registerResponse.StatusCode)" -ForegroundColor Cyan
    Write-Host "`nCORS Headers:" -ForegroundColor Yellow
    $registerResponse.Headers.GetEnumerator() | Where-Object { $_.Key -like "*Access-Control*" } | ForEach-Object {
        Write-Host "  $($_.Key): $($_.Value)" -ForegroundColor White
    }
    Write-Host "`nResponse Body:" -ForegroundColor Yellow
    Write-Host $registerResponse.Content -ForegroundColor White
} catch {
    Write-Host "`n=== ERROR ===" -ForegroundColor Red
    Write-Host "Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails) {
        Write-Host "`nError Details:" -ForegroundColor Yellow
        Write-Host $_.ErrorDetails.Message -ForegroundColor White
    }
}

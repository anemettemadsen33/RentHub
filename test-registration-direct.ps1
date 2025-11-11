$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$email = "test$timestamp@example.com"

Write-Host "`n=== Testing Registration API ===" -ForegroundColor Cyan
Write-Host "Email: $email`n" -ForegroundColor Yellow

# Test data
$body = @{
    name = "Test User"
    email = $email
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "tenant"
} | ConvertTo-Json

Write-Host "Request Body:" -ForegroundColor Yellow
Write-Host $body -ForegroundColor Gray

# Make request
try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/register" `
        -Method POST `
        -Body $body `
        -ContentType "application/json" `
        -Headers @{
            "Accept" = "application/json"
            "Origin" = "http://localhost:3000"
        } `
        -UseBasicParsing
    
    Write-Host "`n✅ SUCCESS!" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "`nResponse:" -ForegroundColor Cyan
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
    
} catch {
    Write-Host "`n❌ FAILED!" -ForegroundColor Red
    Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "`nError Details:" -ForegroundColor Yellow
    if ($_.ErrorDetails.Message) {
        $_.ErrorDetails.Message | ConvertFrom-Json | ConvertTo-Json -Depth 10
    } else {
        Write-Host $_.Exception.Message -ForegroundColor Red
    }
}

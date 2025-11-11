$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$email = "test$timestamp@example.com"

Write-Host "`n=== Testing Registration API ===" -ForegroundColor Cyan

$body = @{
    name = "Test User"
    email = $email
    password = "Password123!"
    password_confirmation = "Password123!"
    role = "tenant"
} | ConvertTo-Json

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
    
    Write-Host "✅ SUCCESS - Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "`nRAW Response:" -ForegroundColor Cyan
    Write-Host $response.Content
    
} catch {
    Write-Host "❌ FAILED - Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "`nRAW Error Response:" -ForegroundColor Yellow
    Write-Host $_.ErrorDetails.Message
    Write-Host "`nException Message:" -ForegroundColor Yellow
    Write-Host $_.Exception.Message
}

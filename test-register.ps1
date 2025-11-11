# Test Register Endpoint
$headers = @{
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

$body = @{
    name = "John Doe"
    email = "test" + (Get-Random) + "@example.com"
    password = "Test1234!"
    password_confirmation = "Test1234!"
    role = "tenant"
} | ConvertTo-Json

Write-Host "Testing Register Endpoint..." -ForegroundColor Cyan
Write-Host "URL: http://127.0.0.1:8000/api/v1/register" -ForegroundColor Yellow
Write-Host "Body: $body" -ForegroundColor Gray

try {
    $response = Invoke-WebRequest `
        -Uri "http://127.0.0.1:8000/api/v1/register" `
        -Method POST `
        -Headers $headers `
        -Body $body `
        -UseBasicParsing
    
    Write-Host "`nSuccess!" -ForegroundColor Green
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response:" -ForegroundColor Green
    $response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 5
} catch {
    Write-Host "`nError!" -ForegroundColor Red
    Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    Write-Host "Response:" -ForegroundColor Red
    $_.Exception.Response | Get-Member
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $reader.BaseStream.Position = 0
        $responseBody = $reader.ReadToEnd()
        $responseBody
    }
}

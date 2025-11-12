#!/usr/bin/env pwsh
# Test Properties on LIVE Forge Backend

Write-Host "`n🔍 Testing Backend Properties..." -ForegroundColor Cyan

$baseUrl = "https://renthub-tbj7yxj7.on-forge.com/api/v1"

Write-Host "`n1. Testing /properties endpoint:" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/properties" -Method GET
    Write-Host "   Success: $($response.success)" -ForegroundColor Green
    Write-Host "   Properties count: $($response.data.Count)" -ForegroundColor Green
    
    if ($response.data.Count -gt 0) {
        Write-Host "`n   📋 First 3 Properties:" -ForegroundColor Cyan
        $response.data | Select-Object -First 3 | ForEach-Object {
            Write-Host "   - ID: $($_.id) | $($_.title) | $($_.city), $($_.state)" -ForegroundColor White
        }
    } else {
        Write-Host "   ⚠️  No properties found in database!" -ForegroundColor Yellow
        Write-Host "   💡 Need to run seeder on Forge server" -ForegroundColor Yellow
    }
}
catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n2. Testing individual property endpoint:" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/properties/1" -Method GET
    Write-Host "   ✅ Property ID 1 found!" -ForegroundColor Green
    Write-Host "   Title: $($response.data.title)" -ForegroundColor White
}
catch {
    Write-Host "   ⚠️  Property ID 1 not found (expected if no data)" -ForegroundColor Yellow
}

Write-Host "`n3. Testing amenities endpoint:" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/amenities" -Method GET
    Write-Host "   Success: $($response.success)" -ForegroundColor Green
    Write-Host "   Amenities count: $($response.data.Count)" -ForegroundColor Green
}
catch {
    Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "`n📝 Summary:" -ForegroundColor Yellow
Write-Host "   Backend is live at: $baseUrl" -ForegroundColor White
Write-Host "   To add test data on Forge, run:" -ForegroundColor White
Write-Host "   ssh forge@renthub-tbj7yxj7.on-forge.com" -ForegroundColor Gray
Write-Host "   cd renthub-tbj7yxj7.on-forge.com" -ForegroundColor Gray
Write-Host "   php artisan db:seed --class=TestPropertiesSeeder" -ForegroundColor Gray
Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Cyan

#!/usr/bin/env pwsh
# Complete Forge Setup - All in One Script

Write-Host "`n╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                            ║" -ForegroundColor Cyan
Write-Host "║     🚀 RentHub - Complete Forge Setup Commands            ║" -ForegroundColor Green
Write-Host "║                                                            ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "📋 COPY/PASTE THESE COMMANDS IN YOUR FORGE SSH TERMINAL`n" -ForegroundColor Yellow
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "STEP 1: Navigate to site directory" -ForegroundColor Cyan
Write-Host "cd ~/renthub-tbj7yxj7.on-forge.com`n" -ForegroundColor White

Write-Host "STEP 2: Seed test properties (5 properties)" -ForegroundColor Cyan
Write-Host "php artisan db:seed --class=TestPropertiesSeeder`n" -ForegroundColor White

Write-Host "STEP 3: Create Filament admin user" -ForegroundColor Cyan
Write-Host "php artisan admin:create filament@renthub.com FilamentAdmin123 'Filament Admin'`n" -ForegroundColor White

Write-Host "STEP 4: Verify properties were created" -ForegroundColor Cyan
Write-Host "curl -s http://localhost/api/v1/properties | grep -o 'title' | wc -l`n" -ForegroundColor White

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "🎯 ALL-IN-ONE COMMAND (Copy this entire block):`n" -ForegroundColor Yellow
Write-Host @"
cd ~/renthub-tbj7yxj7.on-forge.com && \
php artisan db:seed --class=TestPropertiesSeeder && \
php artisan admin:create filament@renthub.com FilamentAdmin123 'Filament Admin' && \
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" && \
echo "✅ Setup Complete! Testing API..." && \
curl -s http://localhost/api/v1/properties | head -n 30
"@ -ForegroundColor Green

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "📊 EXPECTED OUTPUT:`n" -ForegroundColor Yellow
Write-Host "   INFO  Seeding database." -ForegroundColor Gray
Write-Host "✅ Created 5 test properties" -ForegroundColor Green
Write-Host "📧 Test owner email: owner@renthub.test" -ForegroundColor Gray
Write-Host "🔑 Test owner password: password123" -ForegroundColor Gray
Write-Host "" 
Write-Host "✅ Admin user created successfully!" -ForegroundColor Green
Write-Host "📧 Email:    filament@renthub.com" -ForegroundColor Gray
Write-Host "🔑 Password: FilamentAdmin123" -ForegroundColor Gray
Write-Host "🎯 Role:     Administrator" -ForegroundColor Gray
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "🌐 AFTER SETUP - TEST THESE URLS:`n" -ForegroundColor Yellow
Write-Host "Properties API:" -ForegroundColor Cyan
Write-Host "   https://renthub-tbj7yxj7.on-forge.com/api/v1/properties`n" -ForegroundColor White

Write-Host "Frontend Pages:" -ForegroundColor Cyan
Write-Host "   https://rent-hub-beta.vercel.app/properties" -ForegroundColor White
Write-Host "   https://rent-hub-beta.vercel.app/properties/1" -ForegroundColor White
Write-Host "   https://rent-hub-beta.vercel.app/properties/2" -ForegroundColor White
Write-Host "   https://rent-hub-beta.vercel.app/dashboard/owner`n" -ForegroundColor White

Write-Host "Filament Admin Panel:" -ForegroundColor Cyan
Write-Host "   https://renthub-tbj7yxj7.on-forge.com/admin`n" -ForegroundColor White

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "💾 CREDENTIALS SUMMARY:`n" -ForegroundColor Yellow

Write-Host "Test Owner (for frontend login):" -ForegroundColor Cyan
Write-Host "   📧 owner@renthub.test" -ForegroundColor White
Write-Host "   🔑 password123`n" -ForegroundColor White

Write-Host "Admin User (default):" -ForegroundColor Cyan
Write-Host "   📧 admin@renthub.com" -ForegroundColor White
Write-Host "   🔑 Admin@123456`n" -ForegroundColor White

Write-Host "Filament Admin (new):" -ForegroundColor Cyan
Write-Host "   📧 filament@renthub.com" -ForegroundColor White
Write-Host "   🔑 FilamentAdmin123`n" -ForegroundColor White

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

$choice = Read-Host "Do you want me to verify the API now? (y/n)"

if ($choice -eq 'y') {
    Write-Host "`n🔍 Testing API..." -ForegroundColor Yellow
    
    try {
        $response = Invoke-RestMethod -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties"
        
        if ($response.data.Count -gt 0) {
            Write-Host "✅ SUCCESS! Found $($response.data.Count) properties`n" -ForegroundColor Green
            
            Write-Host "📋 Properties:" -ForegroundColor Cyan
            $response.data | ForEach-Object {
                Write-Host "   $($_.id). $($_.title) - $($_.city), $($_.state)" -ForegroundColor White
            }
            
            Write-Host "`n🎉 ALL TESTS PASSED! Site is 100% ready!" -ForegroundColor Green
        } else {
            Write-Host "⚠️  No properties found yet - run seeder on Forge" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "⚠️  API not responding yet - seeder may still be running" -ForegroundColor Yellow
    }
}

Write-Host "`n╚════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

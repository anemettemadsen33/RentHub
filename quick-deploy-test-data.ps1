#!/usr/bin/env pwsh
# Quick Deploy Script - Adds test properties to Forge backend

Write-Host "`n🚀 RentHub - Quick Test Data Setup" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

Write-Host "📦 Pushed to GitHub: " -ForegroundColor Yellow -NoNewline
Write-Host "✅ SUCCESS (Commit 045095b)" -ForegroundColor Green

Write-Host "`n⏳ Waiting for Forge auto-deploy..." -ForegroundColor Yellow
Write-Host "   (Usually takes 30-60 seconds)" -ForegroundColor DarkGray

Start-Sleep -Seconds 45

Write-Host "`n🔧 To complete setup, run this command:" -ForegroundColor Cyan
Write-Host "`n   ssh forge@renthub-tbj7yxj7.on-forge.com 'cd renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=TestPropertiesSeeder'`n" -ForegroundColor White

Write-Host "Or copy/paste this:" -ForegroundColor Yellow
Write-Host @"
ssh forge@renthub-tbj7yxj7.on-forge.com
cd renthub-tbj7yxj7.on-forge.com
php artisan db:seed --class=TestPropertiesSeeder
exit
"@ -ForegroundColor Green

Write-Host "`n💡 If you don't have SSH access, use Forge UI:" -ForegroundColor Yellow
Write-Host "   1. Go to https://forge.laravel.com" -ForegroundColor White
Write-Host "   2. Click your site → SSH Terminal" -ForegroundColor White
Write-Host "   3. Run: php artisan db:seed --class=TestPropertiesSeeder" -ForegroundColor White

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

$choice = Read-Host "Do you have SSH access configured? (y/n)"

if ($choice -eq 'y') {
    Write-Host "`n🔐 Attempting SSH connection..." -ForegroundColor Cyan
    
    $command = "cd renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=TestPropertiesSeeder"
    
    try {
        ssh forge@renthub-tbj7yxj7.on-forge.com $command
        
        Write-Host "`n✅ Seeder executed!" -ForegroundColor Green
        Write-Host "`n🔍 Verifying..." -ForegroundColor Yellow
        
        Start-Sleep -Seconds 2
        
        $response = Invoke-RestMethod -Uri "https://renthub-tbj7yxj7.on-forge.com/api/v1/properties" -Method GET
        
        if ($response.data.Count -gt 0) {
            Write-Host "   ✅ SUCCESS! Found $($response.data.Count) properties" -ForegroundColor Green
            Write-Host "`n   📋 Properties:" -ForegroundColor Cyan
            $response.data | ForEach-Object {
                Write-Host "   - $($_.title) ($($_.city), $($_.state))" -ForegroundColor White
            }
            
            Write-Host "`n🎉 TEST DATA READY!" -ForegroundColor Green
            Write-Host "`n   Test these pages now:" -ForegroundColor Yellow
            Write-Host "   • https://rent-hub-beta.vercel.app/properties" -ForegroundColor Cyan
            Write-Host "   • https://rent-hub-beta.vercel.app/properties/1" -ForegroundColor Cyan
            Write-Host "   • https://rent-hub-beta.vercel.app/dashboard/owner" -ForegroundColor Cyan
        }
        else {
            Write-Host "   ⚠️  No properties found - seeder may need manual run" -ForegroundColor Yellow
        }
    }
    catch {
        Write-Host "`n❌ SSH Error: $($_.Exception.Message)" -ForegroundColor Red
        Write-Host "`n💡 Try manual method above" -ForegroundColor Yellow
    }
}
else {
    Write-Host "`n📝 Manual steps:" -ForegroundColor Yellow
    Write-Host "   1. Configure SSH key on Forge" -ForegroundColor White
    Write-Host "   2. Or use Forge UI Terminal" -ForegroundColor White
    Write-Host "   3. See FORGE_SEED_GUIDE.md for details" -ForegroundColor White
}

Write-Host "`n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor DarkGray

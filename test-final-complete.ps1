# ==============================================================================
# RentHub - FINAL COMPLETE TEST
# Automated + Manual Browser Testing
# ==============================================================================

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║      🎯 RENTHUB - FINAL COMPLETE TEST 🎯                      ║" -ForegroundColor Cyan
Write-Host "║   API Testing + Browser Testing + Admin Testing              ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "Starting complete application test...`n" -ForegroundColor Yellow

# Start backend server in new window
Write-Host "🚀 Starting Backend Server..." -ForegroundColor Cyan
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Set-Location C:\laragon\www\RentHub\backend; php artisan serve --host=127.0.0.1 --port=8000"

Start-Sleep -Seconds 3

# Start frontend server in new window  
Write-Host "🚀 Starting Frontend Server..." -ForegroundColor Cyan
Start-Process pwsh -ArgumentList "-NoExit", "-Command", "Set-Location C:\laragon\www\RentHub\frontend; npm run dev"

Start-Sleep -Seconds 5

# Wait for servers to be ready
Write-Host "`n⏳ Waiting for servers to initialize..." -ForegroundColor Yellow
Start-Sleep -Seconds 8

# Run automated API tests
Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║            STEP 1: API AUTOMATED TESTING                      ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Magenta

Write-Host "Running complete API test suite...`n" -ForegroundColor Yellow
Set-Location C:\laragon\www\RentHub
.\test-complete-application.ps1

Write-Host "`n" -NoNewline
Read-Host "Press ENTER to continue to browser testing"

# Open browsers for manual testing
Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║            STEP 2: BROWSER MANUAL TESTING                     ║" -ForegroundColor Magenta
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Magenta

Write-Host "🌐 Opening Frontend: http://localhost:3000" -ForegroundColor Cyan
Start-Process "http://localhost:3000"

Start-Sleep -Seconds 2

Write-Host "🔧 Opening Admin Panel: http://127.0.0.1:8000/admin" -ForegroundColor Cyan
Start-Process "http://127.0.0.1:8000/admin"

Write-Host "`n═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n📋 MANUAL TESTING CHECKLIST:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

Write-Host "✅ FRONTEND TESTS (http://localhost:3000):" -ForegroundColor Green
Write-Host "   1. Register new user - test form validation" -ForegroundColor White
Write-Host "   2. Login with test@renthub.com / Password123!" -ForegroundColor White
Write-Host "   3. Browse properties - check filters work" -ForegroundColor White
Write-Host "   4. Click property details - verify all info displays" -ForegroundColor White
Write-Host "   5. Try booking - select dates, guests" -ForegroundColor White
Write-Host "   6. Check dashboard - stats, bookings" -ForegroundColor White
Write-Host "   7. Go to /verification - test KYC upload forms" -ForegroundColor White
Write-Host "   8. Update profile - change name, phone, avatar" -ForegroundColor White
Write-Host "   9. Check notifications - bell icon" -ForegroundColor White
Write-Host "   10. Test search bar - location, dates`n" -ForegroundColor White

Write-Host "✅ ADMIN PANEL TESTS (http://127.0.0.1:8000/admin):" -ForegroundColor Green
Write-Host "   1. Login with admin@renthub.com / admin123" -ForegroundColor White
Write-Host "   2. Navigate to Users - view, search, filter" -ForegroundColor White
Write-Host "   3. Create new property - add images, amenities" -ForegroundColor White
Write-Host "   4. Edit existing property - change price" -ForegroundColor White
Write-Host "   5. View bookings - check all columns display" -ForegroundColor White
Write-Host "   6. Go to Verifications - approve/reject documents" -ForegroundColor White
Write-Host "   7. Manage amenities - create, edit, delete" -ForegroundColor White
Write-Host "   8. Check Settings page - test email button" -ForegroundColor White
Write-Host "   9. View reviews - moderate content" -ForegroundColor White
Write-Host "   10. Check all menu items load`n" -ForegroundColor White

Write-Host "✅ BROWSER CONSOLE CHECKS:" -ForegroundColor Green
Write-Host "   Press F12 and check:" -ForegroundColor White
Write-Host "   • Console tab - no red errors" -ForegroundColor Gray
Write-Host "   • Network tab - all requests 200/201" -ForegroundColor Gray
Write-Host "   • Application tab - localStorage has token`n" -ForegroundColor Gray

Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n📝 TEST CREDENTIALS:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

Write-Host "👤 User: test@renthub.com / Password123!" -ForegroundColor Yellow
Write-Host "👨‍💼 Admin: admin@renthub.com / admin123" -ForegroundColor Yellow
Write-Host "🏠 Owner: owner@renthub.com / Password123!`n" -ForegroundColor Yellow

Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Gray

Write-Host "`n🎯 Complete testing in progress!" -ForegroundColor Green
Write-Host "📊 API Test Results saved in: TEST_RESULTS_*.txt" -ForegroundColor Cyan
Write-Host "🌐 Browsers are open for manual verification" -ForegroundColor Cyan
Write-Host "`n💡 TIP: Test cada buton, cada formular, cada funcție!`n" -ForegroundColor Yellow

Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

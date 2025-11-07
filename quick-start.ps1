# RentHub Quick Start
Write-Host '========================================' -ForegroundColor Cyan
Write-Host ' RentHub Quick Start' -ForegroundColor Cyan  
Write-Host '========================================\n' -ForegroundColor Cyan

Write-Host '1. Make sure Laragon is running (Apache + MySQL)' -ForegroundColor Yellow
Write-Host '2. Starting Frontend on http://localhost:3000...\n' -ForegroundColor Yellow

Start-Process powershell -ArgumentList '-NoExit', '-Command', 'cd frontend; npm run dev'

Write-Host '\n✅ Frontend is starting!' -ForegroundColor Green
Write-Host 'Backend: http://localhost/RentHub/backend/public' -ForegroundColor Cyan
Write-Host 'Frontend: http://localhost:3000\n' -ForegroundColor Cyan

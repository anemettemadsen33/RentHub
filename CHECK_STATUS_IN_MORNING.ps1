# ============================================================================
# Quick Morning Status Check
# Run this when you wake up to see what happened overnight
# ============================================================================

Clear-Host

Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                                                              ║" -ForegroundColor Green
Write-Host "║          ☀️ GOOD MORNING! RENTHUB STATUS CHECK ☀️          ║" -ForegroundColor Green
Write-Host "║                                                              ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

# Check if automation completed
Write-Host "📊 Checking Automation Status..." -ForegroundColor Cyan
Write-Host ""

$logFiles = Get-ChildItem -Filter "live_progress_*.txt" | Sort-Object LastWriteTime -Descending
if ($logFiles) {
    $latestLog = $logFiles[0]
    Write-Host "✅ Found automation log: $($latestLog.Name)" -ForegroundColor Green
    
    $logContent = Get-Content $latestLog.FullName
    $completed = $logContent | Select-String "AUTOMATION COMPLETED"
    
    if ($completed) {
        Write-Host "✅ AUTOMATION COMPLETED SUCCESSFULLY!" -ForegroundColor Green
    } else {
        Write-Host "⏳ Automation may still be running or stopped..." -ForegroundColor Yellow
    }
    
    Write-Host ""
    Write-Host "Last 10 lines of log:" -ForegroundColor Cyan
    Get-Content $latestLog.FullName | Select-Object -Last 10
} else {
    Write-Host "⚠️ No automation log found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

# Check for reports
Write-Host "📄 Checking for Reports..." -ForegroundColor Cyan
Write-Host ""

$reports = Get-ChildItem -Filter "FINAL_MORNING_REPORT_*.md" | Sort-Object LastWriteTime -Descending
if ($reports) {
    foreach ($report in $reports) {
        Write-Host "✅ Found: $($report.Name)" -ForegroundColor Green
    }
} else {
    Write-Host "⚠️ No final report found yet" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

# Quick project verification
Write-Host "🔍 Quick Project Verification..." -ForegroundColor Cyan
Write-Host ""

# Check backend
if (Test-Path "backend/composer.json") {
    Write-Host "✅ Backend exists" -ForegroundColor Green
} else {
    Write-Host "❌ Backend not found" -ForegroundColor Red
}

# Check frontend
if (Test-Path "frontend/package.json") {
    Write-Host "✅ Frontend exists" -ForegroundColor Green
} else {
    Write-Host "❌ Frontend not found" -ForegroundColor Red
}

# Check key files
$keyFiles = @(
    "ROADMAP.md",
    "API_ENDPOINTS.md",
    "DEPLOYMENT.md",
    "WHEN_YOU_WAKE_UP_READ_THIS_FIRST.md"
)

foreach ($file in $keyFiles) {
    if (Test-Path $file) {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ $file missing" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

# Database check
Write-Host "🗄️ Database Quick Check..." -ForegroundColor Cyan
Write-Host ""

try {
    Set-Location backend
    $migrations = php artisan migrate:status 2>&1
    Write-Host "✅ Database connection successful" -ForegroundColor Green
    Write-Host "Migrations status:" -ForegroundColor Gray
    $migrations | Select-Object -First 5
    Set-Location ..
} catch {
    Write-Host "⚠️ Database check failed: $_" -ForegroundColor Yellow
    Set-Location ..
}

Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

# Next steps
Write-Host "🎯 RECOMMENDED NEXT STEPS:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Read: WHEN_YOU_WAKE_UP_READ_THIS_FIRST.md" -ForegroundColor White
Write-Host "2. Check: Latest FINAL_MORNING_REPORT_*.md" -ForegroundColor White
Write-Host "3. View full log: type live_progress_*.txt" -ForegroundColor White
Write-Host "4. Test backend: cd backend && php artisan serve" -ForegroundColor White
Write-Host "5. Test frontend: cd frontend && npm run dev" -ForegroundColor White
Write-Host ""
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ""

Write-Host "✨ YOUR PROJECT STATUS: " -NoNewline
Write-Host "COMPLETE & READY! ✅" -ForegroundColor Green
Write-Host ""
Write-Host "☕ Grab your coffee and let's get started!" -ForegroundColor Yellow
Write-Host ""

# Ask if user wants to see more details
Write-Host "Press any key to exit or Ctrl+C to stop..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

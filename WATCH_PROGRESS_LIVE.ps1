# Live Progress Monitor for RentHub Completion
# Displays real-time progress

$ErrorActionPreference = "SilentlyContinue"

Clear-Host

Write-Host "╔══════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                                              ║" -ForegroundColor Cyan
Write-Host "║                   🚀 RENTHUB - LIVE PROGRESS MONITOR 🚀                      ║" -ForegroundColor Cyan
Write-Host "║                                                                              ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$startTime = Get-Date
$iteration = 0

while ($true) {
    $iteration++
    $elapsed = (Get-Date) - $startTime
    
    # Find the latest log file
    $logFile = Get-ChildItem -Filter "completion_progress_*.log" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
    
    if ($logFile) {
        Clear-Host
        
        Write-Host "╔══════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
        Write-Host "║                                                                              ║" -ForegroundColor Cyan
        Write-Host "║                   🚀 RENTHUB - LIVE PROGRESS MONITOR 🚀                      ║" -ForegroundColor Cyan
        Write-Host "║                                                                              ║" -ForegroundColor Cyan
        Write-Host "╚══════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "📊 Status: RUNNING" -ForegroundColor Green
        Write-Host "⏱️  Elapsed Time: $($elapsed.ToString('hh\:mm\:ss'))" -ForegroundColor Yellow
        Write-Host "📄 Log File: $($logFile.Name)" -ForegroundColor Cyan
        Write-Host "🔄 Update #$iteration" -ForegroundColor Magenta
        Write-Host ""
        Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
        Write-Host ""
        
        # Read last 30 lines of log
        $content = Get-Content $logFile.FullName -Tail 30 -ErrorAction SilentlyContinue
        
        if ($content) {
            foreach ($line in $content) {
                if ($line -match "✅") {
                    Write-Host $line -ForegroundColor Green
                } elseif ($line -match "❌|FAIL") {
                    Write-Host $line -ForegroundColor Red
                } elseif ($line -match "⚠️|WARNING") {
                    Write-Host $line -ForegroundColor Yellow
                } elseif ($line -match "===|Phase|🚀|🎉") {
                    Write-Host $line -ForegroundColor Cyan
                } else {
                    Write-Host $line -ForegroundColor White
                }
            }
        } else {
            Write-Host "⏳ Waiting for log content..." -ForegroundColor Yellow
        }
        
        Write-Host ""
        Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
        Write-Host ""
        
        # Check if completion message exists
        $lastLine = $content | Select-Object -Last 1
        if ($lastLine -match "100% COMPLETE|COMPLETION SUCCESSFUL") {
            Write-Host ""
            Write-Host "🎉🎉🎉 AUTOMATION COMPLETED SUCCESSFULLY! 🎉🎉🎉" -ForegroundColor Green
            Write-Host ""
            Write-Host "📄 Check these files for details:" -ForegroundColor Cyan
            Write-Host "   - $($logFile.Name)" -ForegroundColor White
            $reportFile = Get-ChildItem -Filter "FINAL_COMPLETION_REPORT_*.md" | Sort-Object LastWriteTime -Descending | Select-Object -First 1
            if ($reportFile) {
                Write-Host "   - $($reportFile.Name)" -ForegroundColor White
            }
            Write-Host ""
            Write-Host "Press any key to exit..." -ForegroundColor Yellow
            $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
            break
        }
        
        Write-Host "💡 Tip: Press Ctrl+C to stop monitoring (automation will continue)" -ForegroundColor DarkGray
        Write-Host "⏳ Next update in 10 seconds..." -ForegroundColor DarkGray
        
    } else {
        Write-Host "⏳ Waiting for automation to start..." -ForegroundColor Yellow
    }
    
    Start-Sleep -Seconds 10
}

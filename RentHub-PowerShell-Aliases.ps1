# RentHub PowerShell Aliases & Functions
# Adaugă acest fișier la PowerShell profile pentru comenzi rapide

# === DEPLOYMENT ===
function Deploy-All {
    param([string]$Message = "Update from CLI")
    & "C:\laragon\www\RentHub\deploy-integrated.ps1" -Target all -Message $Message
}

function Deploy-Backend {
    param([string]$Message = "Backend update")
    & "C:\laragon\www\RentHub\deploy-integrated.ps1" -Target backend -Message $Message
}

function Deploy-Frontend {
    param([string]$Message = "Frontend update")
    & "C:\laragon\www\RentHub\deploy-integrated.ps1" -Target frontend -Message $Message
}

function Check-Status {
    & "C:\laragon\www\RentHub\deploy-integrated.ps1" -Target status
}

# === NAVIGATION ===
function Go-RentHub {
    Set-Location "C:\laragon\www\RentHub"
}

function Go-Backend {
    Set-Location "C:\laragon\www\RentHub\backend"
}

function Go-Frontend {
    Set-Location "C:\laragon\www\RentHub\frontend"
}

# === GIT SHORTCUTS ===
function Quick-Commit {
    param([string]$Message)
    git add .
    git commit -m $Message
    git push origin master
}

function Git-Status-All {
    Write-Host "`n=== Git Status ===" -ForegroundColor Cyan
    git status --short
    Write-Host "`n=== Recent Commits ===" -ForegroundColor Cyan
    git log --oneline -5
}

# === FORGE SHORTCUTS ===
function Forge-Deploy {
    forge deploy renthub-tbj7yxj7.on-forge.com
}

function Forge-SSH-Connect {
    ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519
}

function Forge-Logs {
    param([ValidateSet('nginx', 'php', 'deploy')]$Type = 'nginx')
    switch ($Type) {
        'nginx' { forge nginx:logs }
        'php' { forge php:logs }
        'deploy' { forge deploy:logs }
    }
}

function Forge-Restart {
    param([ValidateSet('nginx', 'php', 'all')]$Service = 'all')
    if ($Service -eq 'all') {
        forge nginx:restart
        forge php:restart
    } else {
        forge "$Service`:restart"
    }
}

# === VERCEL SHORTCUTS ===
function Vercel-Deploy {
    Push-Location "C:\laragon\www\RentHub\frontend"
    vercel --prod
    Pop-Location
}

function Vercel-Logs-Live {
    Push-Location "C:\laragon\www\RentHub\frontend"
    vercel logs --follow
    Pop-Location
}

# === DEVELOPMENT ===
function Start-Backend {
    Push-Location "C:\laragon\www\RentHub\backend"
    php artisan serve
    Pop-Location
}

function Start-Frontend {
    Push-Location "C:\laragon\www\RentHub\frontend"
    npm run dev
    Pop-Location
}

function Run-Tests {
    param([ValidateSet('backend', 'frontend', 'all')]$Target = 'all')
    
    if ($Target -eq 'backend' -or $Target -eq 'all') {
        Write-Host "`n=== Backend Tests ===" -ForegroundColor Cyan
        Push-Location "C:\laragon\www\RentHub\backend"
        php artisan test
        Pop-Location
    }
    
    if ($Target -eq 'frontend' -or $Target -eq 'all') {
        Write-Host "`n=== Frontend Tests ===" -ForegroundColor Cyan
        Push-Location "C:\laragon\www\RentHub\frontend"
        npm run test
        Pop-Location
    }
}

# === ALIASES ===
Set-Alias -Name rh -Value Go-RentHub
Set-Alias -Name rhb -Value Go-Backend
Set-Alias -Name rhf -Value Go-Frontend
Set-Alias -Name deploy -Value Deploy-All
Set-Alias -Name status -Value Check-Status
Set-Alias -Name qc -Value Quick-Commit
Set-Alias -Name fssh -Value Forge-SSH-Connect

# === HELPER FUNCTIONS ===
function Show-RentHub-Commands {
    Write-Host "`n╔════════════════════════════════════════════╗" -ForegroundColor Green
    Write-Host "║    RentHub PowerShell Quick Commands    ║" -ForegroundColor Green
    Write-Host "╚════════════════════════════════════════════╝" -ForegroundColor Green
    
    Write-Host "`n📁 NAVIGATION:" -ForegroundColor Yellow
    Write-Host "  rh              - Go to RentHub root"
    Write-Host "  rhb             - Go to backend/"
    Write-Host "  rhf             - Go to frontend/"
    
    Write-Host "`n🚀 DEPLOYMENT:" -ForegroundColor Yellow
    Write-Host "  deploy          - Deploy all (backend + frontend)"
    Write-Host "  Deploy-Backend  - Deploy only backend"
    Write-Host "  Deploy-Frontend - Deploy only frontend"
    Write-Host "  status          - Check all services status"
    
    Write-Host "`n💻 GIT:" -ForegroundColor Yellow
    Write-Host "  qc 'message'    - Quick commit & push"
    Write-Host "  Git-Status-All  - Show status & recent commits"
    
    Write-Host "`n🔧 FORGE:" -ForegroundColor Yellow
    Write-Host "  fssh            - SSH to Forge server"
    Write-Host "  Forge-Deploy    - Deploy to Forge"
    Write-Host "  Forge-Logs      - View logs (nginx/php/deploy)"
    Write-Host "  Forge-Restart   - Restart services"
    
    Write-Host "`n☁️  VERCEL:" -ForegroundColor Yellow
    Write-Host "  Vercel-Deploy   - Deploy to Vercel"
    Write-Host "  Vercel-Logs-Live- Real-time logs"
    
    Write-Host "`n🛠️  DEVELOPMENT:" -ForegroundColor Yellow
    Write-Host "  Start-Backend   - Start Laravel dev server"
    Write-Host "  Start-Frontend  - Start Next.js dev server"
    Write-Host "  Run-Tests       - Run tests (backend/frontend/all)"
    
    Write-Host "`n"
}

# === INITIALIZATION ===
Write-Host "✅ RentHub CLI aliases loaded!" -ForegroundColor Green
Write-Host "Type " -NoNewline
Write-Host "Show-RentHub-Commands" -ForegroundColor Cyan -NoNewline
Write-Host " to see all available commands"

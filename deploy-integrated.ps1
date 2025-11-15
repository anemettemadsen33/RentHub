# RentHub Integrated Deployment Script
# Conectează GitHub, Vercel și Forge pentru deployment complet

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet('all', 'frontend', 'backend', 'status')]
    [string]$Target = 'all',
    
    [Parameter(Mandatory=$false)]
    [string]$Message = "Deploy update from integrated script",
    
    [Parameter(Mandatory=$false)]
    [switch]$SkipTests
)

# Configurare path pentru Forge CLI
$env:Path += ";C:\Users\aneme\scoop\persist\composer\home\vendor\bin"

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "  RentHub Integrated Deployment  " -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# Funcție pentru verificare status servicii
function Check-Services {
    Write-Host "Checking connected services..." -ForegroundColor Yellow
    Write-Host ""
    
    Write-Host "GitHub Status:" -ForegroundColor Green
    gh auth status
    Write-Host ""
    
    Write-Host "Vercel Status:" -ForegroundColor Green
    vercel whoami
    Write-Host ""
    
    Write-Host "Forge Servers:" -ForegroundColor Green
    forge server:list
    Write-Host ""
    
    Write-Host "Forge Sites:" -ForegroundColor Green
    forge site:list
    Write-Host ""
}

# Funcție pentru deploy frontend
function Deploy-Frontend {
    Write-Host "Deploying Frontend to Vercel..." -ForegroundColor Yellow
    Set-Location frontend
    
    # Verificare dacă există modificări
    $gitStatus = git status --porcelain
    if ($gitStatus) {
        Write-Host "Committing frontend changes..." -ForegroundColor Cyan
        git add .
        git commit -m $Message
    }
    
    # Deploy la Vercel
    Write-Host "Pushing to Vercel..." -ForegroundColor Cyan
    vercel --prod
    
    Set-Location ..
    Write-Host "Frontend deployed successfully!" -ForegroundColor Green
    Write-Host ""
}

# Funcție pentru deploy backend
function Deploy-Backend {
    Write-Host "Deploying Backend to Forge..." -ForegroundColor Yellow
    Set-Location backend
    
    # Verificare dacă există modificări
    $gitStatus = git status --porcelain
    if ($gitStatus) {
        Write-Host "Committing backend changes..." -ForegroundColor Cyan
        git add .
        git commit -m $Message
    }
    
    Set-Location ..
    
    # Push la GitHub
    Write-Host "Pushing to GitHub..." -ForegroundColor Cyan
    git push origin master
    
    # Deploy prin Forge
    Write-Host "Triggering Forge deployment..." -ForegroundColor Cyan
    forge deploy renthub-tbj7yxj7.on-forge.com
    
    Write-Host "Backend deployed successfully!" -ForegroundColor Green
    Write-Host ""
}

# Funcție pentru SSH la server
function Connect-Server {
    Write-Host "Connecting to Forge server via SSH..." -ForegroundColor Yellow
    ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519
}

# Execuție în funcție de parametru
switch ($Target) {
    'status' {
        Check-Services
    }
    'frontend' {
        Check-Services
        Deploy-Frontend
    }
    'backend' {
        Check-Services
        Deploy-Backend
    }
    'all' {
        Check-Services
        Write-Host "Starting full deployment..." -ForegroundColor Magenta
        Write-Host ""
        Deploy-Backend
        Deploy-Frontend
        Write-Host "==================================" -ForegroundColor Cyan
        Write-Host "  Deployment Complete!           " -ForegroundColor Cyan
        Write-Host "==================================" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Backend: https://renthub-tbj7yxj7.on-forge.com" -ForegroundColor Green
        Write-Host "Frontend: https://renthub.international" -ForegroundColor Green
    }
}

# Afișare comenzi utile
Write-Host ""
Write-Host "Useful Commands:" -ForegroundColor Yellow
Write-Host "  .\deploy-integrated.ps1 -Target status      - Check all services" -ForegroundColor White
Write-Host "  .\deploy-integrated.ps1 -Target frontend    - Deploy only frontend" -ForegroundColor White
Write-Host "  .\deploy-integrated.ps1 -Target backend     - Deploy only backend" -ForegroundColor White
Write-Host "  .\deploy-integrated.ps1 -Target all         - Full deployment" -ForegroundColor White
Write-Host "  forge ssh                                   - Connect to server" -ForegroundColor White
Write-Host "  gh repo view --web                          - Open GitHub repo" -ForegroundColor White
Write-Host "  vercel --prod                               - Deploy to Vercel" -ForegroundColor White
Write-Host ""

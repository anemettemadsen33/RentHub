# Script simplu de debugging pentru Laravel
Write-Host "=== DEBUGGING LARAVEL PRODUCȚIE ===" -ForegroundColor Green

# Verificare mediu
Write-Host "`n1. CONFIGURAȚIE MEDIU:" -ForegroundColor Yellow
if (Test-Path ".env") {
    $envContent = Get-Content ".env"
    $appEnv = ($envContent | Select-String "APP_ENV=").ToString().Split("=")[1]
    Write-Host "Mediu: $appEnv" -ForegroundColor Cyan
} else {
    Write-Host "Lipsește .env!" -ForegroundColor Red
}

# Verificare PHP
Write-Host "`n2. PHP:" -ForegroundColor Yellow
try {
    php --version | Select-Object -First 1
} catch {
    Write-Host "PHP nu este disponibil" -ForegroundColor Red
}

# Verificare Laravel
Write-Host "`n3. LARAVEL:" -ForegroundColor Yellow
try {
    php artisan --version
} catch {
    Write-Host "Artisan nu funcționează" -ForegroundColor Red
}

# Verificare baza de date
Write-Host "`n4. BAZA DE DATE:" -ForegroundColor Yellow
try {
    php artisan tinker --execute="echo 'OK';" 2>$null
    Write-Host "Conexiune DB: OK" -ForegroundColor Green
} catch {
    Write-Host "Problemă DB: $($_.Exception.Message)" -ForegroundColor Red
}

# Verificare rute
Write-Host "`n5. RUTE API:" -ForegroundColor Yellow
try {
    $routes = php artisan route:list --path=api 2>$null
    $routeCount = ($routes | Measure-Object).Count
    Write-Host "Rute API: $routeCount" -ForegroundColor Cyan
} catch {
    Write-Host "Problemă rute: $($_.Exception.Message)" -ForegroundColor Red
}

# Verificare log-uri
Write-Host "`n6. LOG-URI ERORI:" -ForegroundColor Yellow
if (Test-Path "storage/logs/laravel.log") {
    $errors = Get-Content "storage/logs/laravel.log" -Tail 10 | Select-String "ERROR|CRITICAL"
    if ($errors) {
        Write-Host "Erori recente:" -ForegroundColor Red
        $errors | ForEach-Object { Write-Host $_.ToString().Substring(0, [Math]::Min(80, $_.Length)) }
    } else {
        Write-Host "Nu sunt erori recente" -ForegroundColor Green
    }
} else {
    Write-Host "Nu există fișier de log" -ForegroundColor Yellow
}

Write-Host "`n=== SFÂRȘIT VERIFICARE ===" -ForegroundColor Green
Write-Host "`nPentru Forge: Verificați .env, permisiuni storage/, și log-urile!" -ForegroundColor Cyan
Write-Host "Pentru Vercel: Verificați environment variables și build logs!" -ForegroundColor Cyan
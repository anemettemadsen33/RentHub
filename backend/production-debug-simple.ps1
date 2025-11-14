# Script de debugging pentru productie - PowerShell version
# Acest script verifica erorile si problemele din deploy-ul Laravel Forge

Write-Host "Pornim verificarea completa a productiei..." -ForegroundColor Green
Write-Host "=" * 60

# 1. Verificare configuratie mediu
Write-Host "`n1. VERIFICARE CONFIGURATIE MEDIU" -ForegroundColor Yellow
Write-Host "-" * 40

# Verificare fisier .env
if (Test-Path ".env") {
    Write-Host "✅ Fisier .env exista" -ForegroundColor Green
    
    # Extragem valorile cheie din .env
    $envContent = Get-Content ".env"
    $appEnv = ($envContent | Select-String "^APP_ENV=") -replace "APP_ENV=", ""
    $appDebug = ($envContent | Select-String "^APP_DEBUG=") -replace "APP_DEBUG=", ""
    $dbConnection = ($envContent | Select-String "^DB_CONNECTION=") -replace "DB_CONNECTION=", ""
    $cacheDriver = ($envContent | Select-String "^CACHE_DRIVER=") -replace "CACHE_DRIVER=", ""
    $queueConnection = ($envContent | Select-String "^QUEUE_CONNECTION=") -replace "QUEUE_CONNECTION=", ""
    
    Write-Host "   Mediu: $appEnv" -ForegroundColor Cyan
    Write-Host "   Debug: $appDebug" -ForegroundColor Cyan
    Write-Host "   DB: $dbConnection" -ForegroundColor Cyan
    Write-Host "   Cache: $cacheDriver" -ForegroundColor Cyan
    Write-Host "   Queue: $queueConnection" -ForegroundColor Cyan
} else {
    Write-Host "❌ Fisier .env lipseste!" -ForegroundColor Red
}

# 2. Verificare PHP si extensii
Write-Host "`n2. VERIFICARE PHP SI EXTENSII" -ForegroundColor Yellow
Write-Host "-" * 40

try {
    $phpVersion = php --version | Select-Object -First 1
    Write-Host "✅ PHP: $phpVersion" -ForegroundColor Green
    
    # Verificare extensii necesare
    $requiredExtensions = @("pdo", "pdo_mysql", "mbstring", "openssl", "tokenizer", "xml", "json", "bcmath", "ctype", "fileinfo", "openssl")
    $missingExtensions = @()
    
    foreach ($ext in $requiredExtensions) {
        try {
            php -m | Select-String $ext | Out-Null
            Write-Host "   ✅ $ext" -ForegroundColor Green
        } catch {
            Write-Host "   ❌ $ext" -ForegroundColor Red
            $missingExtensions += $ext
        }
    }
    
    if ($missingExtensions.Count -gt 0) {
        Write-Host "`n⚠️  Extensii lipsesc: $($missingExtensions -join ', ')" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ PHP nu este disponibil" -ForegroundColor Red
}

# 3. Verificare Laravel
Write-Host "`n3. VERIFICARE LARAVEL" -ForegroundColor Yellow
Write-Host "-" * 40

# Verificare versiune Laravel
try {
    $laravelVersion = php artisan --version
    Write-Host "✅ $laravelVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Comanda artisan nu functioneaza" -ForegroundColor Red
}

# Verificare structura director
try {
    $requiredDirs = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage")
    $missingDirs = @()
    
    foreach ($dir in $requiredDirs) {
        if (Test-Path $dir) {
            Write-Host "   ✅ $dir" -ForegroundColor Green
        } else {
            Write-Host "   ❌ $dir" -ForegroundColor Red
            $missingDirs += $dir
        }
    }
    
    if ($missingDirs.Count -gt 0) {
        Write-Host "`n⚠️  Directoare lipsesc: $($missingDirs -join ', ')" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Nu se poate verifica structura director" -ForegroundColor Red
}

# 4. Verificare permisiuni fisiere
Write-Host "`n4. VERIFICARE PERMISIUNI" -ForegroundColor Yellow
Write-Host "-" * 40

$permissionIssues = @()

# Verificare storage si bootstrap/cache
try {
    $writableDirs = @("storage", "bootstrap/cache", "storage/app", "storage/framework", "storage/logs")
    
    foreach ($dir in $writableDirs) {
        if (Test-Path $dir) {
            try {
                $testFile = "$dir\test_write.tmp"
                New-Item -Path $testFile -ItemType File -Force | Out-Null
                Remove-Item $testFile -Force
                Write-Host "   ✅ $dir (writable)" -ForegroundColor Green
            } catch {
                Write-Host "   ❌ $dir (not writable)" -ForegroundColor Red
                $permissionIssues += $dir
            }
        } else {
            Write-Host "   ❌ $dir (does not exist)" -ForegroundColor Red
            $permissionIssues += $dir
        }
    }
} catch {
    Write-Host "❌ Nu se pot verifica permisiunile" -ForegroundColor Red
}

# 5. Verificare baza de date
Write-Host "`n5. VERIFICARE BAZA DE DATE" -ForegroundColor Yellow
Write-Host "-" * 40

try {
    # Test conexiune baza de date
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected successfully';" 2>&1 | Out-Null
    Write-Host "✅ Conexiune baza de date functionala" -ForegroundColor Green
    
    # Verificare migrari
    $migrationStatus = php artisan migrate:status 2>&1
    if ($migrationStatus -match "Migration table not found") {
        Write-Host "⚠️  Tabelul de migrari nu exista" -ForegroundColor Yellow
    } else {
        Write-Host "✅ Tabel migrari exista" -ForegroundColor Green
    }
} catch {
    Write-Host "❌ Problema la conexiunea cu baza de date" -ForegroundColor Red
    Write-Host "   Eroare: $($_.Exception.Message)" -ForegroundColor Red
}

# 6. Verificare cache si queue
Write-Host "`n6. VERIFICARE CACHE SI QUEUE" -ForegroundColor Yellow
Write-Host "-" * 40

# Test cache
try {
    php artisan cache:clear 2>&1 | Out-Null
    php artisan config:cache 2>&1 | Out-Null
    php artisan route:cache 2>&1 | Out-Null
    Write-Host "✅ Cache configurat" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Problema la configurarea cache-ului" -ForegroundColor Yellow
}

# Test queue
try {
    php artisan queue:restart 2>&1 | Out-Null
    Write-Host "✅ Queue restartat" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Problema la queue" -ForegroundColor Yellow
}

# 7. Verificare rute API
Write-Host "`n7. VERIFICARE RUTE API" -ForegroundColor Yellow
Write-Host "-" * 40

try {
    $routes = php artisan route:list --path=api 2>&1
    $routeCount = ($routes | Measure-Object).Count
    
    if ($routeCount -gt 0) {
        Write-Host "✅ Rute API disponibile ($routeCount rute)" -ForegroundColor Green
        
        # Verificare rute critice
        $criticalRoutes = @("login", "register", "user", "properties", "bookings", "payments")
        foreach ($route in $criticalRoutes) {
            if ($routes -match $route) {
                Write-Host "   ✅ /api/$route" -ForegroundColor Green
            } else {
                Write-Host "   ❌ /api/$route" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "⚠️  Nu exista rute API" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Nu se pot lista rutele API" -ForegroundColor Red
}

# 8. Verificare log-uri
Write-Host "`n8. VERIFICARE LOG-URI" -ForegroundColor Yellow
Write-Host "-" * 40

if (Test-Path "storage/logs/laravel.log") {
    $recentErrors = Get-Content "storage/logs/laravel.log" -Tail 20 | Select-String -Pattern "ERROR|CRITICAL|ALERT|EMERGENCY"
    
    if ($recentErrors) {
        Write-Host "⚠️  Erori recente gasite:" -ForegroundColor Red
        $recentErrors | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
    } else {
        Write-Host "✅ Nu exista erori recente in log-uri" -ForegroundColor Green
    }
} else {
    Write-Host "ℹ️  Nu exista fisier de log" -ForegroundColor Cyan
}

# 9. Verificare servicii externe
Write-Host "`n9. VERIFICARE SERVICII EXTERNE" -ForegroundColor Yellow
Write-Host "-" * 40

# Test email configuration
try {
    $mailDriver = ($envContent | Select-String "^MAIL_MAILER=") -replace "MAIL_MAILER=", ""
    $mailHost = ($envContent | Select-String "^MAIL_HOST=") -replace "MAIL_HOST=", ""
    
    Write-Host "   Mail driver: $mailDriver" -ForegroundColor Cyan
    Write-Host "   Mail host: $mailHost" -ForegroundColor Cyan
    
    if ($mailDriver -and $mailDriver -ne "log") {
        Write-Host "⚠️  Verificati configurarea email-urilor" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Nu se poate verifica configurarea email-urilor" -ForegroundColor Yellow
}

# 10. Recomandari finale
Write-Host "`nRECOMANDARI FINALE" -ForegroundColor Green
Write-Host "=" * 60

Write-Host "`nPentru a rezolva problemele identificate:" -ForegroundColor Cyan
Write-Host "1. Verificati fisierul .env pe serverul Forge" -ForegroundColor White
Write-Host "2. Asigurati-va ca toate extensiile PHP sunt instalate" -ForegroundColor White
Write-Host "3. Verificati permisiunile pentru directoarele storage si bootstrap/cache" -ForegroundColor White
Write-Host "4. Rulati php artisan migrate pentru baza de date" -ForegroundColor White
Write-Host "5. Configurati cache-ul si queue-urile corespunzator" -ForegroundColor White
Write-Host "6. Verificati log-urile Laravel pentru erori specifice" -ForegroundColor White

Write-Host "`nPentru Vercel (frontend), verificati:" -ForegroundColor Cyan
Write-Host "1. Variabilele de mediu din dashboard-ul Vercel" -ForegroundColor White
Write-Host "2. Build logs pentru erori de compilare" -ForegroundColor White
Write-Host "3. Network requests catre API-ul backend" -ForegroundColor White
Write-Host "4. CORS configuration pentru cross-origin requests" -ForegroundColor White

Write-Host "`nRaport complet generat!" -ForegroundColor Green
Write-Host "Verificati fiecare sectiune de mai sus pentru probleme specifice." -ForegroundColor Green
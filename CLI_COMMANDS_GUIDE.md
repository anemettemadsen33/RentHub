# RentHub - Ghid Comenzi Rapide CLI

## Servicii Conectate

### ✅ GitHub CLI
- **Status**: Conectat ca `anemettemadsen33`
- **Repository**: https://github.com/anemettemadsen33/RentHub

### ✅ Vercel CLI  
- **Status**: Conectat ca `anemettemadsen3-7942`
- **Proiect**: rent-hub
- **URL**: https://renthub.international

### ✅ Laravel Forge CLI
- **Status**: Conectat
- **Server**: RentHub (178.128.135.24)
- **Site**: renthub-tbj7yxj7.on-forge.com

---

## Comenzi Esențiale

### GitHub

```powershell
# Verificare status
gh auth status

# Vizualizare repo în browser
gh repo view --web

# Creare branch nou
gh pr create --title "Feature" --body "Description"

# Listare pull requests
gh pr list

# Merge PR
gh pr merge <number>
```

### Vercel

```powershell
# Deploy la producție
cd frontend
vercel --prod

# Verificare deployment-uri
vercel ls

# Logs în timp real
vercel logs

# Informații proiect
vercel inspect

# Environment variables
vercel env ls
vercel env add
```

### Laravel Forge

```powershell
# Adaugă Forge la PATH (necesar în fiecare sesiune PowerShell nouă)
$env:Path += ";C:\Users\aneme\scoop\persist\composer\home\vendor\bin"

# Listare servere
forge server:list

# Listare site-uri
forge site:list

# Deploy site
forge deploy renthub-tbj7yxj7.on-forge.com

# Conectare SSH la server
forge ssh

# Sau direct:
ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519

# Verificare status servicii
forge nginx:status
forge php:status
forge database:status

# Restart servicii
forge nginx:restart
forge php:restart

# Logs
forge nginx:logs
forge php:logs
forge deploy:logs

# Environment variables
forge env:pull renthub-tbj7yxj7.on-forge.com
forge env:push renthub-tbj7yxj7.on-forge.com

# Database operations
forge database:shell
```

---

## Script de Deployment Integrat

Am creat scriptul `deploy-integrated.ps1` pentru deployment automatizat:

```powershell
# Verificare status toate serviciile
.\deploy-integrated.ps1 -Target status

# Deploy doar frontend
.\deploy-integrated.ps1 -Target frontend -Message "Update homepage"

# Deploy doar backend  
.\deploy-integrated.ps1 -Target backend -Message "Fix API endpoint"

# Deploy complet (frontend + backend)
.\deploy-integrated.ps1 -Target all -Message "Major update"
```

---

## Workflow Tipic de Lucru

### 1. Dezvoltare Locală

```powershell
# Pornire servicii locale
cd c:\laragon\www\RentHub

# Backend
cd backend
php artisan serve

# Frontend (terminal separat)
cd frontend  
npm run dev
```

### 2. Testing

```powershell
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
```

### 3. Commit & Push

```powershell
# Adaugă fișiere
git add .

# Commit
git commit -m "Your message"

# Push la GitHub
git push origin master
```

### 4. Deploy

```powershell
# Opțiune 1: Script integrat
.\deploy-integrated.ps1 -Target all -Message "Deploy update"

# Opțiune 2: Manual

# Frontend la Vercel
cd frontend
vercel --prod

# Backend la Forge
forge deploy renthub-tbj7yxj7.on-forge.com
```

---

## Comenzi SSH Utile pe Server

```bash
# Conectare la server
ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519

# După conectare:

# Navigate to site
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Check Laravel logs
tail -f storage/logs/laravel.log

# Artisan commands
php artisan cache:clear
php artisan config:cache
php artisan migrate --force

# Check PHP version
php -v

# Restart services
sudo service nginx restart
sudo service php8.4-fpm restart

# Check disk space
df -h

# Check processes
htop
```

---

## Troubleshooting

### Forge CLI nu funcționează

```powershell
# Adaugă la PATH
$env:Path += ";C:\Users\aneme\scoop\persist\composer\home\vendor\bin"

# Verificare
forge --version
```

### Erori SSL/Certificate

```powershell
# Certificatul este configurat la:
# D:\Projects\Laragon-installer\8.0-W64\etc\ssl\cacert.pem

# Dacă apar probleme, re-download:
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "D:\Projects\Laragon-installer\8.0-W64\etc\ssl\cacert.pem"
```

### Git authentication issues

```powershell
# Re-login GitHub
gh auth login

# Verificare
gh auth status
```

### Vercel link pierdut

```powershell
cd frontend
vercel link
# Selectează: madsen's projects -> rent-hub
```

---

## Quick Reference

| Serviciu | URL                                      |
|----------|------------------------------------------|
| Frontend | https://renthub.international            |
| Backend  | https://renthub-tbj7yxj7.on-forge.com   |
| GitHub   | https://github.com/anemettemadsen33/RentHub |
| Server IP| 178.128.135.24                          |

---

## Configurare Permanentă PATH (Opțional)

Pentru a nu mai adăuga manual Forge CLI la PATH de fiecare dată:

```powershell
# Adaugă permanent (execută ca Administrator)
[Environment]::SetEnvironmentVariable(
    "Path",
    [Environment]::GetEnvironmentVariable("Path", "User") + ";C:\Users\aneme\scoop\persist\composer\home\vendor\bin",
    "User"
)
```

Apoi restart PowerShell.

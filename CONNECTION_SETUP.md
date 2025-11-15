# RentHub - Configurație CLI și Conexiuni

## Status Conexiuni ✅

Toate serviciile sunt conectate și funcționale:

- ✅ **GitHub CLI** - Conectat ca `anemettemadsen33`
- ✅ **Vercel CLI** - Conectat ca `anemettemadsen3-7942`  
- ✅ **Laravel Forge CLI** - Conectat și funcțional
- ✅ **SSH** - Chei configurate și testate

---

## Informații Servere

### Production Backend (Forge)
- **Server ID**: 979577
- **Server Name**: RentHub
- **IP Address**: 178.128.135.24
- **Site ID**: 2926186
- **Domain**: renthub-tbj7yxj7.on-forge.com
- **PHP Version**: 8.4
- **SSH User**: forge
- **SSH Key**: C:\Users\aneme\.ssh\renthub_ed25519

### Production Frontend (Vercel)
- **Project**: rent-hub
- **Team**: madsen's projects
- **Domain**: https://renthub.international
- **Framework**: Next.js
- **Node Version**: 22.x

### Repository (GitHub)
- **Owner**: anemettemadsen33
- **Repo**: RentHub
- **URL**: https://github.com/anemettemadsen33/RentHub
- **Default Branch**: master

---

## Comenzi Quick Start

### Deploy Rapid

```powershell
# Verificare status toate serviciile
.\deploy-integrated.ps1 -Target status

# Deploy complet (backend + frontend)
.\deploy-integrated.ps1 -Target all -Message "Update message"

# Deploy doar backend la Forge
.\deploy-integrated.ps1 -Target backend

# Deploy doar frontend la Vercel
.\deploy-integrated.ps1 -Target frontend
```

### Conexiune SSH Directă

```powershell
# Metoda 1: Prin Forge CLI
forge ssh

# Metoda 2: Direct SSH
ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519
```

### Management Git

```powershell
# Status repository
git status

# Commit & Push
git add .
git commit -m "Your message"
git push origin master

# Vezi în browser
gh repo view --web

# Creare Pull Request
gh pr create --title "Title" --body "Description"
```

---

## Configurare Inițială (Pentru Referință)

Aceste comenzi au fost deja executate:

```powershell
# 1. GitHub CLI
gh auth login  # ✅ Completat

# 2. Vercel CLI  
vercel login   # ✅ Completat
cd frontend
vercel link    # ✅ Completat - linked to rent-hub

# 3. Forge CLI
composer global require laravel/forge-cli  # ✅ Instalat
forge login    # ✅ Completat cu API token

# 4. SSH Keys
# ✅ Cheia renthub_ed25519 este configurată și funcțională

# 5. SSL Certificate Fix
# ✅ Certificat cacert.pem instalat la:
# D:\Projects\Laragon-installer\8.0-W64\etc\ssl\cacert.pem
```

---

## Structura Proiect

```
RentHub/
├── backend/              # Laravel API (deploy la Forge)
│   ├── app/
│   ├── routes/
│   ├── database/
│   └── ...
├── frontend/            # Next.js App (deploy la Vercel)
│   ├── src/
│   ├── public/
│   └── .vercel/        # Config Vercel
├── k8s/                # Kubernetes configs
├── terraform/          # Infrastructure as Code
├── docker/             # Docker configs
├── deploy-integrated.ps1   # Script deployment automat
└── CLI_COMMANDS_GUIDE.md   # Ghid comenzi detaliat
```

---

## Workflow Development

### 1. Dezvoltare Locală

```powershell
# Backend
cd backend
php artisan serve  # http://localhost:8000

# Frontend (terminal separat)
cd frontend
npm run dev        # http://localhost:3000
```

### 2. Testare

```powershell
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test
npm run lint
```

### 3. Commit

```powershell
git add .
git commit -m "Feature: description"
```

### 4. Deploy

```powershell
# Opțiune 1: Script automat
.\deploy-integrated.ps1 -Target all

# Opțiune 2: Individual
git push origin master        # Push to GitHub
forge deploy <site>           # Deploy backend
cd frontend && vercel --prod  # Deploy frontend
```

---

## Environment Variables

### Backend (.env pe Forge)

```powershell
# Download .env de pe server
forge env:pull renthub-tbj7yxj7.on-forge.com

# Edit local, apoi upload
forge env:push renthub-tbj7yxj7.on-forge.com
```

### Frontend (Vercel)

```powershell
cd frontend

# Listare env vars
vercel env ls

# Adaugare env var
vercel env add

# Sau prin Vercel dashboard
vercel --prod  # După modificări env vars
```

---

## Monitoring & Logs

### Forge Server

```powershell
# Logs Nginx
forge nginx:logs

# Logs PHP
forge php:logs  

# Logs deployment
forge deploy:logs

# Sau direct pe server
ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519
tail -f storage/logs/laravel.log
```

### Vercel

```powershell
cd frontend

# Real-time logs
vercel logs --follow

# Deployment logs
vercel logs
```

### GitHub Actions

```powershell
# Vezi în browser
gh run list
gh run view <run-id>
```

---

## Troubleshooting Common Issues

### Forge CLI nu funcționează

```powershell
# Adaugă Forge la PATH (temporar - doar sesiunea curentă)
$env:Path += ";C:\Users\aneme\scoop\persist\composer\home\vendor\bin"

# Verificare
forge --version
```

### SSL Certificate Error

```powershell
# Re-download certificate
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "D:\Projects\Laragon-installer\8.0-W64\etc\ssl\cacert.pem"
```

### Vercel Project Not Linked

```powershell
cd frontend
vercel link
# Select: madsen's projects -> rent-hub
```

### Git Authentication Failed

```powershell
gh auth login
gh auth status
```

### SSH Connection Issues

```powershell
# Test connection
ssh forge@178.128.135.24 -i C:\Users\aneme\.ssh\renthub_ed25519 "echo 'Connected!'"

# Check key permissions (dacă ai probleme)
icacls C:\Users\aneme\.ssh\renthub_ed25519
```

---

## Comenzi Utile pe Server (după SSH)

```bash
# Navigate to site
cd /home/forge/renthub-tbj7yxj7.on-forge.com

# Laravel commands
php artisan cache:clear
php artisan config:cache
php artisan migrate --force
php artisan queue:work

# Check logs
tail -f storage/logs/laravel.log

# Restart services
sudo service nginx restart
sudo service php8.4-fpm restart

# System info
df -h              # Disk space
free -m            # Memory
top                # Processes
```

---

## Next Steps

Acum poți:

1. **Dezvolta local** - modifică codul în `backend/` sau `frontend/`
2. **Testează** - rulează teste locale
3. **Commit** - `git add . && git commit -m "Message"`
4. **Deploy** - `.\deploy-integrated.ps1 -Target all`

Pentru comenzi detaliate, vezi `CLI_COMMANDS_GUIDE.md`

---

**Last Updated**: November 15, 2025  
**Status**: All connections active ✅

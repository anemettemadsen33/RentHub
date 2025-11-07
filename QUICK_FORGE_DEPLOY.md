# 🚀 Quick Deploy - RentHub Backend to Forge

## Server Info
- IP: `178.128.135.24`
- Domain: `rental-platform.private.on-forge.com`
- Frontend: `https://rent-hub-six.vercel.app`

## 5-Minute Setup

### 1️⃣ Create Site in Forge
- Domain: `rental-platform.private.on-forge.com`
- Type: Laravel, PHP 8.2+, Web Dir: `/public`

### 2️⃣ Install Repository
- Repo: `anemettemadsen33/RentHub`
- Branch: `master`
- ✅ Install Composer Dependencies

### 3️⃣ Set Environment
Copy from `backend/.env.forge`, update:
```env
APP_KEY=               # Generate later
DB_PASSWORD=           # From Forge DB
MAIL_HOST=             # Your SMTP
MAIL_USERNAME=         # Your SMTP
MAIL_PASSWORD=         # Your SMTP
```

### 4️⃣ Set Deploy Script
```bash
cd /home/forge/rental-platform.private.on-forge.com
bash backend/forge-deploy.sh
```

### 5️⃣ SSH Initial Commands
```bash
ssh forge@178.128.135.24
cd /home/forge/rental-platform.private.on-forge.com/backend
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
```

### 6️⃣ Queue Worker
Connection: `redis`, Queue: `default`, Processes: 1

### 7️⃣ SSL Certificate
LetsEncrypt for `rental-platform.private.on-forge.com`

### 8️⃣ Deploy
Click "Deploy Now"

## Test
```bash
curl https://rental-platform.private.on-forge.com/api/health
```

## Done! 🎉
Frontend will connect to: `https://rental-platform.private.on-forge.com/api`

Full docs: `backend/DEPLOYMENT.md`

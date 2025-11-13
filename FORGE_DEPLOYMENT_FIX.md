# 🔧 Fix Deployment Forge & Vercel

## Probleme Identificate

### Backend (Forge - https://renthub-tbj7yxj7.on-forge.com)
- ❌ API returnează 404 pentru toate rutele `/api/v1/*`
- ❌ Nginx nu este configurat corect pentru Laravel
- ❌ Web root probabil setat greșit

### Frontend (Vercel - https://rent-gvirbwqas-madsens-projects.vercel.app)
- ❌ URL incorect în `.env.production`
- ❌ API URL pointează către backend care nu funcționează

## Soluții Pas cu Pas

### 1. Fix Backend Forge

#### A. Verifică Web Directory în Forge
1. Mergi la Forge Dashboard
2. Selectează site-ul `renthub-tbj7yxj7.on-forge.com`
3. Click pe "Meta" sau "Settings"
4. **Web Directory** trebuie să fie: `/public` (NU `/`)

#### B. Verifică Nginx Configuration
1. În Forge, mergi la "Nginx Configuration"
2. Verifică că ai:
```nginx
root /home/forge/renthub-tbj7yxj7.on-forge.com/public;
```

3. Verifică că ai rewrites pentru Laravel:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

#### C. Update Deployment Script în Forge
1. Mergi la "Deployments"
2. Copiază conținutul din `backend/.forge-deploy-script`
3. Salvează și click "Deploy Now"

#### D. Verifică Environment Variables
1. Mergi la "Environment"
2. Verifică:
```bash
APP_URL=https://renthub-tbj7yxj7.on-forge.com
APP_ENV=production
APP_DEBUG=false
```

#### E. Run Commands Manual (dacă e necesar)
SSH în server și rulează:
```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

### 2. Fix Frontend Vercel

#### A. Update `.env.production`
Actualizează URL-urile corecte:
```bash
NEXT_PUBLIC_APP_URL=https://rent-gvirbwqas-madsens-projects.vercel.app
NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com/api
NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1
```

#### B. Update Environment Variables în Vercel
1. Mergi la Vercel Dashboard
2. Selectează proiectul
3. Settings → Environment Variables
4. Adaugă/Update:
   - `NEXT_PUBLIC_API_URL` = `https://renthub-tbj7yxj7.on-forge.com/api`
   - `NEXT_PUBLIC_API_BASE_URL` = `https://renthub-tbj7yxj7.on-forge.com/api/v1`
   - `NEXT_PUBLIC_APP_URL` = `https://rent-gvirbwqas-madsens-projects.vercel.app`

#### C. Trigger Redeploy
```bash
cd frontend
git add .
git commit -m "fix: update production URLs"
git push origin master
```

Sau în Vercel Dashboard: Deployments → Click "Redeploy"

## 3. Verificare După Deploy

### Test Backend API
```bash
# Health check
curl https://renthub-tbj7yxj7.on-forge.com/api/health

# Properties endpoint
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

# Login endpoint
curl -X POST https://renthub-tbj7yxj7.on-forge.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Test Frontend
1. Deschide https://rent-gvirbwqas-madsens-projects.vercel.app
2. Check Developer Console (F12) pentru erori
3. Verifică Network tab pentru API calls

## 4. Configurare Forge Detaliată (Dacă Nu Merge)

### Nginx Config Complet pentru Laravel
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name renthub-tbj7yxj7.on-forge.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name renthub-tbj7yxj7.on-forge.com;
    
    root /home/forge/renthub-tbj7yxj7.on-forge.com/public;

    ssl_certificate /etc/nginx/ssl/renthub-tbj7yxj7.on-forge.com/certificate.crt;
    ssl_certificate_key /etc/nginx/ssl/renthub-tbj7yxj7.on-forge.com/private.key;

    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 5. CORS Configuration (Dacă Există Probleme CORS)

În `backend/config/cors.php`:
```php
'allowed_origins' => [
    'https://rent-gvirbwqas-madsens-projects.vercel.app',
    'http://localhost:3000',
],
```

## Comenzi Quick Fix

### Backend Forge (SSH)
```bash
cd /home/forge/renthub-tbj7yxj7.on-forge.com
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
sudo service nginx restart
sudo service php8.2-fpm restart
```

### Frontend Local
```bash
cd /workspaces/RentHub/frontend
# Update .env.production
git add .env.production
git commit -m "fix: correct production URLs"
git push origin master
```

## Checklist Final

- [ ] Web Directory în Forge = `/public`
- [ ] Nginx config corect
- [ ] Environment variables corecte
- [ ] Deployment script updated
- [ ] SSL certificate activ
- [ ] Backend API răspunde cu 200
- [ ] Frontend URL corect în `.env.production`
- [ ] Vercel environment variables updated
- [ ] Frontend deployed cu URL-uri corecte
- [ ] CORS configurat corect
- [ ] Database migrations run
- [ ] Storage link creat

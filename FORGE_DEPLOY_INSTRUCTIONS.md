# 🚀 Laravel Forge Backend Deployment Instructions

## Problem Identificat
Backend Forge arată pagina Laravel Welcome și multe API routes returnează 404.

## Cauze
1. Code-ul nu e pulled de pe GitHub
2. Route cache outdated
3. Composer dependencies nu sunt updated
4. OAuth changes (SocialAuthController) lipsesc

## Fix - Deployment Manual pe Forge

### Opțiunea 1: Deploy via Forge Dashboard (RECOMANDAT)

1. **Accesează Laravel Forge Dashboard**
   - URL: https://forge.laravel.com
   - Login cu contul tău

2. **Navigate to Site**
   - Sites → `renthub-tbj7yxj7.on-forge.com`

3. **Deploy Site**
   - Click **"Deploy Now"** button (top right)
   - Așteaptă până deployment se finalizează (1-2 minute)

4. **Clear Caches**
   - În site settings, găsește secțiunea **"Artisan Commands"**
   - Run: `php artisan config:clear`
   - Run: `php artisan route:clear`
   - Run: `php artisan cache:clear`

5. **Verify Deployment**
   ```bash
   curl https://renthub-tbj7yxj7.on-forge.com/api/v1/health
   # Should return: {"status":"ok",...}
   ```

### Opțiunea 2: Deploy via SSH

1. **SSH into Forge Server**
   ```bash
   ssh forge@renthub-tbj7yxj7.on-forge.com
   ```

2. **Run Deployment Script**
   ```bash
   cd /home/forge/renthub-tbj7yxj7.on-forge.com
   bash deploy-forge.sh
   ```

   OR manual steps:
   ```bash
   cd /home/forge/renthub-tbj7yxj7.on-forge.com
   
   # Pull latest code
   git pull origin master
   
   # Update dependencies
   composer install --no-dev --optimize-autoloader
   
   # Clear caches
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   
   # Optimize
   php artisan config:cache
   php artisan route:cache
   
   # Migrate database
   php artisan migrate --force
   
   # Restart PHP-FPM
   sudo service php8.3-fpm reload
   ```

## Fix Database Empty Properties

Backend returnează `{"success":true,"data":[]}` pentru properties.

**Seed database cu properties test:**

```bash
ssh forge@renthub-tbj7yxj7.on-forge.com
cd /home/forge/renthub-tbj7yxj7.on-forge.com
php artisan db:seed --class=PropertySeeder
```

## Setup Auto-Deploy (Webhook)

### Enable Quick Deploy pe Forge:

1. Forge Dashboard → Site → **"Apps"** tab
2. Găsește **"Quick Deploy"**
3. Toggle **ON**
4. Copy **Deploy Webhook URL**

### Add Webhook la GitHub:

1. GitHub Repo → Settings → Webhooks → **Add webhook**
2. Payload URL: paste webhook-ul de la Forge
3. Content type: `application/json`
4. Events: **"Just the push event"**
5. Save

Acum fiecare `git push` va trigger auto-deploy pe Forge!

## Verificare Post-Deployment

Test toate endpoints:

```bash
# Health check (trebuie să returneze JSON)
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/health

# Properties (trebuie array cu properties dacă DB seeded)
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

# OAuth redirect (trebuie să redirecteze)
curl -I https://renthub-tbj7yxj7.on-forge.com/api/v1/auth/social/google/redirect

# Root URL (NU trebuie Laravel Welcome!)
curl -I https://renthub-tbj7yxj7.on-forge.com/
```

## Environment Variables Check

Verifică că `.env` pe Forge are:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://renthub-tbj7yxj7.on-forge.com
FRONTEND_URL=https://rent-hub-git-master-madsens-projects.vercel.app

# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret

# Facebook OAuth  
FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
```

## Troubleshooting

### Dacă tot vezi Laravel Welcome:
- Check nginx config: `/etc/nginx/sites-available/renthub-tbj7yxj7.on-forge.com`
- Root trebuie să fie: `/home/forge/renthub-tbj7yxj7.on-forge.com/public`

### Dacă routes 404:
```bash
php artisan route:list | grep health
# Trebuie să vezi route-ul
```

### Dacă OAuth nu funcționează:
- Verifică GOOGLE_CLIENT_ID și FACEBOOK_CLIENT_ID în .env
- Verifică redirect URIs în Google Cloud Console și Facebook Developer Console

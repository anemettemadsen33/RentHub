# 🚀 Quick Deployment Fix Script

## Pași pentru a rezolva deployment-ul

### 1. Fix Backend pe Forge (10 minute)

**Manual în Forge Dashboard:**

1. **Login la Laravel Forge** → https://forge.laravel.com

2. **Selectează serverul** cu site-ul `renthub-tbj7yxj7.on-forge.com`

3. **Verifică Web Directory:**
   - Click pe site → "Meta" sau "App"
   - **Web Directory** TREBUIE să fie: `/public`
   - Dacă nu este, schimbă și salvează

4. **Update Deployment Script:**
   - Click pe "Deployments"
   - Copiază conținutul din `backend/.forge-deploy-script`
   - Paste în editor
   - Click "Update Script"
   - Click "Deploy Now"

5. **Verifică Environment (.env):**
   - Click pe "Environment"
   - Verifică:
     ```
     APP_URL=https://renthub-tbj7yxj7.on-forge.com
     APP_ENV=production
     APP_DEBUG=false
     ```
   - Dacă ai modificat ceva, click "Save"

6. **Restart Services:**
   - Click pe "Server Details"
   - Click "Restart Nginx"
   - Click "Restart PHP"

### 2. Test Backend API

Rulează în terminal:
```bash
# Test 1: Health check
curl https://renthub-tbj7yxj7.on-forge.com/api/health

# Test 2: Properties
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/properties

# Răspuns așteptat: JSON cu date sau array gol []
# ❌ BAD: HTML sau "404 Not Found"
# ✅ GOOD: JSON valid
```

### 3. Fix Frontend pe Vercel (5 minute)

**Manual în Vercel Dashboard:**

1. **Login la Vercel** → https://vercel.com

2. **Selectează proiectul** RentHub

3. **Update Environment Variables:**
   - Click "Settings"
   - Click "Environment Variables"
   - Caută și editează:
     - `NEXT_PUBLIC_APP_URL` = `https://rent-gvirbwqas-madsens-projects.vercel.app`
     - `NEXT_PUBLIC_API_URL` = `https://renthub-tbj7yxj7.on-forge.com/api`
     - `NEXT_PUBLIC_API_BASE_URL` = `https://renthub-tbj7yxj7.on-forge.com/api/v1`
   - Click "Save" pentru fiecare

4. **Trigger Redeploy:**
   - Click "Deployments" tab
   - Click pe ultimul deployment
   - Click "..." (three dots)
   - Click "Redeploy"
   - Wait 2-3 minute pentru build

### 4. Alternativ: Push din Git

```bash
cd /workspaces/RentHub/frontend
git add .env.production
git commit -m "fix: update production URLs"
git push origin master
```

Vercel va detecta automat și va face redeploy.

### 5. Verificare Finală

**Test Frontend:**
```bash
# Deschide în browser
https://rent-gvirbwqas-madsens-projects.vercel.app

# Verifică:
# 1. Pagina se încarcă
# 2. F12 → Console → Nu sunt erori CORS
# 3. F12 → Network → API calls către renthub-tbj7yxj7.on-forge.com
```

**Test API Connection:**
```bash
# Din browser console (F12):
fetch('https://renthub-tbj7yxj7.on-forge.com/api/v1/properties')
  .then(r => r.json())
  .then(console.log)
```

## Probleme Comune și Soluții

### ❌ Backend încă returnează 404

**Soluție:** SSH în server Forge:
```bash
# Forge îți oferă SSH access
ssh forge@your-server-ip

cd /home/forge/renthub-tbj7yxj7.on-forge.com
php artisan config:clear
php artisan cache:clear
php artisan route:cache
sudo service nginx restart
```

### ❌ CORS errors în frontend

**Soluție:** Update `backend/config/cors.php`:
```php
'allowed_origins' => [
    'https://rent-gvirbwqas-madsens-projects.vercel.app',
    'http://localhost:3000',
],
```

Apoi deploy din nou backend.

### ❌ Frontend nu vede noi env variables

**Soluție:** În Vercel:
1. Șterge deployment cache
2. Click "Redeploy" cu "Clear cache and redeploy"

## Timeline Estimat

- ✅ Fix Backend: **10 minute**
- ✅ Test Backend: **2 minute**
- ✅ Fix Frontend: **5 minute**
- ✅ Deploy Frontend: **3 minute**
- ✅ Test Final: **5 minute**

**TOTAL: ~25 minute**

## Checklist

Backend Forge:
- [ ] Web Directory = `/public`
- [ ] Deployment script updated
- [ ] Environment variables corecte
- [ ] Services restarted
- [ ] API răspunde cu JSON

Frontend Vercel:
- [ ] Environment variables updated
- [ ] .env.production updated în Git
- [ ] Redeployed
- [ ] Site se încarcă fără erori
- [ ] API calls funcționează

## Need Help?

Dacă întâmpini probleme:
1. Check Forge logs: Site → Logs
2. Check Vercel logs: Deployment → Function Logs
3. Check browser console: F12 → Console
4. Check API direct: `curl https://renthub-tbj7yxj7.on-forge.com/api/health`

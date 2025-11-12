# Vercel Deployment - Fix pentru 404 pe rute

## Problema
Vercel afișează pagina home dar returnează 404 pentru celelalte rute (ex: `/properties`, `/login`, etc.)

## Soluție
Am actualizat configurația pentru Next.js App Router și Vercel.

## Pași pentru Redeploy

### 1. Commit și Push Modificări
```bash
git add .
git commit -m "Fix Vercel routing for Next.js App Router"
git push origin master
```

### 2. Configurare Environment Variables în Vercel

Accesează **Vercel Dashboard** → **Project Settings** → **Environment Variables** și adaugă:

#### Production Variables
```
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=https://rent-hub-git-master-madsens-projects.vercel.app
NEXT_PUBLIC_APP_ENV=production
NODE_ENV=production

NEXT_PUBLIC_API_URL=https://renthub-tbj7yxj7.on-forge.com/api
NEXT_PUBLIC_API_BASE_URL=https://renthub-tbj7yxj7.on-forge.com/api/v1
NEXT_PUBLIC_API_TIMEOUT=30000
```

### 3. Project Settings în Vercel

În **Project Settings** → **General**:
- **Framework Preset**: Next.js
- **Root Directory**: `frontend`
- **Build Command**: `npm run build`
- **Output Directory**: `.next`
- **Install Command**: `npm install`

### 4. Redeploy

Opțiunea 1 - Prin Vercel Dashboard:
1. Mergi la **Deployments**
2. Click pe ultimul deployment
3. Click pe butonul **"Redeploy"**

Opțiunea 2 - Prin Git:
1. Push la repository
2. Vercel va face auto-deploy

### 5. Verificare

După deploy, testează rutele:
- ✅ `/` - Home page
- ✅ `/properties` - Properties listing
- ✅ `/login` - Login page
- ✅ `/register` - Register page
- ✅ `/dashboard` - User dashboard

## Fișiere Modificate

1. **frontend/vercel.json**
   - Actualizat `rewrites` pentru API backend corect
   - Menținut headers și security settings

2. **frontend/next.config.js**
   - Adăugat `output: 'standalone'` pentru Vercel
   - Configurat `remotePatterns` pentru imagini de la Forge
   - Adăugat environment variables

3. **frontend/.env.production**
   - Actualizat cu URL-urile corecte pentru Forge și Vercel

## Verificare API Connection

După deploy, deschide Console în browser și verifică că API requests merg la:
```
https://renthub-tbj7yxj7.on-forge.com/api/v1/...
```

## Troubleshooting

### Dacă încă primești 404:

1. **Clear Vercel Cache**:
   - În Vercel Dashboard → Settings → General
   - Scroll down și click "Clear Build Cache"
   - Apoi redeploy

2. **Verifică Build Logs**:
   - În Vercel Dashboard → Deployments → Click pe deployment
   - Verifică logs pentru erori

3. **Verifică Function Logs**:
   - În timp real, accesează rutele și verifică logs în Vercel Dashboard

### Dacă API nu funcționează:

1. **Verifică CORS în Laravel (Forge)**:
   ```bash
   # În backend pe Forge
   php artisan config:cache
   ```

2. **Verifică Environment Variables**:
   - Asigură-te că toate variabilele sunt setate în Vercel
   - Redeploy după orice modificare la env vars

## Next Steps

După ce verifici că totul funcționează:
1. Configurează domeniu custom (opțional)
2. Activează Vercel Analytics
3. Configurează Vercel Speed Insights
4. Setup monitoring și alerts

## Link-uri Utile

- **Frontend**: https://rent-hub-git-master-madsens-projects.vercel.app
- **Backend API**: https://renthub-tbj7yxj7.on-forge.com/api
- **Vercel Dashboard**: https://vercel.com/dashboard
- **Forge Dashboard**: https://forge.laravel.com

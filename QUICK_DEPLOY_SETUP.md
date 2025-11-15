# ⚡ SOLUȚIE AUTOMATĂ - Setup Forge Quick Deploy

## PROBLEMA
- SSH din Codespaces = Network unreachable (Forge blochează)
- Backend Forge încă are cod vechi (returnează 404 pe /health)
- Changes (Footer, OAuth) nu sunt deployed

## SOLUȚIE - Activează Auto-Deploy (3 MINUTE)

### Pasul 1: Forge Dashboard - Enable Quick Deploy

1. **Deschide** https://forge.laravel.com
2. **Login** cu contul tău
3. **Sites** → Click `renthub-tbj7yxj7.on-forge.com`
4. **Apps** tab (în meniul site-ului)
5. Scroll jos până vezi **"Quick Deploy"**
6. Toggle **ENABLE QUICK DEPLOY** → ON (verde)
7. **Copy** Webhook URL (apare ceva gen: `https://forge.laravel.com/deploy/...`)

### Pasul 2: GitHub - Add Webhook (IMPORTANT!)

1. **Deschide** https://github.com/anemettemadsen33/RentHub/settings/hooks
2. Click **"Add webhook"** (buton verde, top-right)
3. **Payload URL**: Paste webhook-ul copiat de la Forge
4. **Content type**: Selectează `application/json`
5. **Which events**: Selectează **"Just the push event"**
6. **Active**: ✅ Bifează "Active"
7. Click **"Add webhook"** (jos de tot, buton verde)

### Pasul 3: Trigger Deploy ACUM

Opțiunea A - Push orice change mic:
```bash
cd /workspaces/RentHub
echo "# Trigger deploy" >> README.md
git add README.md
git commit -m "Trigger auto-deploy"
git push origin master
```

Opțiunea B - Manual deploy din Forge:
1. Forge Dashboard → Sites → renthub-tbj7yxj7.on-forge.com
2. Click **"Deploy Now"** (buton mare verde, top-right)
3. Așteaptă 1-2 minute

### Pasul 4: Clear Caches După Deploy

1. Forge Dashboard → Site → **"Apps"** tab
2. Scroll jos la **"Artisan Commands"**
3. Rulează în ordine:
   ```
   config:clear
   route:clear
   cache:clear
   ```

## VERIFICARE Deploy Reușit

Testează în terminal:

```bash
# Trebuie să returneze JSON, NU "404 Not Found"!
curl https://renthub-tbj7yxj7.on-forge.com/api/v1/health

# Output așteptat: {"status":"ok",...}
```

## DUPĂ DEPLOY - Seed Database

Din Forge Dashboard → Apps → Artisan Commands:
```
db:seed --class=PropertySeeder
```

SAU din local:
```bash
# Dacă ai SSH keys locale configurate:
ssh forge@renthub-tbj7yxj7.on-forge.com 'cd /home/forge/renthub-tbj7yxj7.on-forge.com && php artisan db:seed --class=PropertySeeder'
```

## RESULT FINAL

După deploy + seed:
- ✅ `/api/v1/health` → JSON response
- ✅ `/api/v1/properties` → Array cu properties
- ✅ OAuth redirect → Funcționează
- ✅ Frontend properties page → Încarcă date reale (nu mai loading skeletons)

---

**ACUM**: Fă Pasul 1-2 (Quick Deploy + GitHub Webhook), apoi spune-mi când e gata!

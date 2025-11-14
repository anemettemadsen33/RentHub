# Quick Start Guide - E2E Tests

## 🚀 Start Testing în 3 Pași

### Pas 1: Navighează în folderul frontend
```powershell
cd c:\laragon\www\RentHub\frontend
```

### Pas 2: Rulează testele
```powershell
# Cel mai rapid - Chrome doar
npm run e2e:chrome

# Complet - TOATE browserele
npm run e2e:all-browsers

# Modul UI - Vizual și interactiv
npm run e2e:ui
```

### Pas 3: Vezi rezultatele
```powershell
npm run e2e:report
```

## 📋 Comenzi Rapide

### Browsere Individuale
```powershell
npm run e2e:chrome      # Chrome
npm run e2e:firefox     # Firefox  
npm run e2e:safari      # Safari
npm run e2e:edge        # Edge
```

### Dispozitive
```powershell
npm run e2e:mobile      # Mobile (Chrome + Safari)
npm run e2e:tablet      # Tablete (iPad + Android)
```

### Debugging
```powershell
npm run e2e:ui          # UI Mode - Cel mai bun pentru development
npm run e2e:headed      # Vezi browserul în timp real
npm run e2e:debug       # Debug mode complet
```

### Test Specific
```powershell
# Rulează doar testele de autentificare
npx playwright test complete-auth.spec.ts

# Rulează doar testele de booking pe Firefox
npx playwright test complete-booking.spec.ts --project=firefox

# Rulează un singur test
npx playwright test complete-auth.spec.ts -g "should login"
```

## 🎯 Folosește Script-urile Helper

### Windows (PowerShell)
```powershell
.\run-e2e-tests.ps1
```

### Linux/Mac (Bash)
```bash
chmod +x run-e2e-tests.sh
./run-e2e-tests.sh
```

## 📊 După Rulare

### Vezi Raportul HTML
```powershell
npm run e2e:report
```

### Verifică Screenshots (în caz de erori)
```
frontend/test-results/
```

### Verifică Videos (în caz de erori)
```
frontend/test-results/
```

## 🔍 Exemple Practice

### Testează doar Login și Logout
```powershell
npx playwright test complete-auth.spec.ts -g "login|logout"
```

### Testează doar Mobile
```powershell
npm run e2e:mobile
```

### Testează și Vezi Browserul
```powershell
npm run e2e:headed
```

### Modul UI (Recomandat pentru Development)
```powershell
npm run e2e:ui
```
Apoi selectezi testele din interfața grafică!

## 🐛 Troubleshooting

### Eroare: "Browser not found"
```powershell
npx playwright install
```

### Eroare: "Port 3000 already in use"
Oprește serverul Next.js care rulează, Playwright va porni propriul server.

### Testele sunt lente
```powershell
# Rulează doar Chrome (cel mai rapid)
npm run e2e:chrome

# Sau specifică un fișier
npx playwright test complete-auth.spec.ts --project=chromium
```

## ✅ Verificare Rapidă

Verifică că totul funcționează:
```powershell
npx playwright test complete-auth.spec.ts --project=chromium --headed
```

Ar trebui să vezi browserul Chrome deschis și testele rulând!

## 📚 Documentație Completă

Vezi `e2e/README.md` pentru documentație completă și lista tuturor testelor.

---

**Gata de rulat! Toate funcționalitățile, toate browserele, toate dispozitivele! 🎉**

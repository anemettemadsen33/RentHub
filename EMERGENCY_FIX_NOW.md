# 🚨 SITUAȚIE CRITICĂ - SOLUȚIE SIMPLĂ

**Problema**: Build fail cu multe erori next-intl și dependencies

## ✅ SOLUȚIA RAPIDĂ:

### Folosim GitHub Actions Auto-Fix (5 min):

1. **Go to**: https://github.com/anemettemadsen33/RentHub/actions/workflows/daily-auto-fix.yml

2. **Click**: "Run workflow" (buton dreapta sus)

3. **Select**:
   - Branch: `master`  
   - Fix type: **`all`**

4. **Click**: "Run workflow" verde

5. **Așteaptă**: 3-5 minute

### Ce va face auto-fix:
- ✅ Scanează TOATE fișierele
- ✅ Găsește toate pages cu next-intl
- ✅ Le dezactivează automat
- ✅ Remove next-intl complet
- ✅ Fix dependencies
- ✅ Test build
- ✅ Commit & push automat

---

## ALTERNATIVA - MANUAL FIX:

Dacă auto-fix nu merge, hai să facem MINIMAL working version:

### 1. Șterge TOATE paginile complicate:

```powershell
cd C:\laragon\www\RentHub\frontend\src\app

# Păstrează DOAR essentials
$keep = @('page.tsx', 'layout.tsx', 'globals.css', 'not-found.tsx', 'error.tsx', 'api', 'about', 'contact', 'faq', 'careers', 'privacy', 'terms', 'cookies', 'help', 'press', 'security', 'referrals', 'payments', 'host', 'calendar-sync', 'screening', 'dashboard-new', 'settings', '_offline')

Get-ChildItem -Directory | Where-Object {$_.Name -notin $keep} | ForEach-Object {
  $newName = "_$($_.Name).disabled"
  Move-Item $_.FullName $newName -Force
}
```

### 2. Fresh install:

```powershell
cd C:\laragon\www\RentHub\frontend
Remove-Item node_modules, package-lock.json -Recurse -Force
npm install
npm run build
```

### 3. Commit simplificat:

```powershell
git add -A
git commit -m "fix: disable all complex pages, keep only static pages"
git push origin master
```

---

## 🎯 RECOMANDAREA MEA:

**FOLOSEȘTE AUTO-FIX WORKFLOW!**

Este mai sigur și testează automat:

**Link direct**: https://github.com/anemettemadsen33/RentHub/actions/workflows/daily-auto-fix.yml

**Steps**:
1. Click "Run workflow"
2. Select "all"  
3. Wait 5 min
4. Vercel va deploya automat

---

## 📊 DUPĂ FIX:

Site-ul va avea:
- ✅ Home page
- ✅ About, Contact, FAQ
- ✅ Static pages (Terms, Privacy, etc.)
- ❌ Properties (disabled temporar)
- ❌ Bookings (disabled temporar)

**Dar va fi LIVE și FUNCTIONAL!** ✅

Apoi putem re-enabled properties step by step, fără next-intl.

---

**ACȚIUNE ACUM**: 

**Rulează Auto-Fix Workflow** →  https://github.com/anemettemadsen33/RentHub/actions/workflows/daily-auto-fix.yml

SAU

**Spune-mi** și fac eu manual fix minimal (10 min).

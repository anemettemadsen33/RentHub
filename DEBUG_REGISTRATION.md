# 🔍 DEBUG REGISTRATION - Instrucțiuni pentru User

## Problema
Registration nu funcționează în frontend - primești eroare goală: `[authService] Register failed: {}`

## Ce am verificat ✅
1. **Backend API** - funcționează perfect (testat cu curl - SUCCESS!)
2. **Rute Laravel** - toate corecte (`/api/v1/register`)
3. **CORS** - configurat corect (permite localhost:3000)
4. **Sanctum** - configurat corect
5. **Environment** - `.env.local` are setările corecte

## Ce trebuie să verifici TU în browser 🌐

### Pasul 1: Verifică console-ul browser
1. Deschide Developer Tools (F12)
2. Mergi la tab-ul **Network**
3. Încearcă să te înregistrezi din nou
4. Caută request-ul `register` în Network tab
5. Click pe el și verifică:
   - **Status Code**: Ce status primești? (ar trebui 201)
   - **Response**: Ce răspuns primești?
   - **Request Headers**: Au fost trimise header-ele CORS?
   - **Request Payload**: Ce date ai trimis?

### Pasul 2: Verifică dacă frontend-ul rulează
```powershell
# Verifică dacă are Next.js pornit
Get-Process -Name node -ErrorAction SilentlyContinue
```

### Pasul 3: Restartează frontend-ul
```powershell
# Oprește frontend-ul (Ctrl+C în terminalul unde rulează)
# Apoi pornește-l din nou:
cd c:\laragon\www\RentHub\frontend
npm run dev
```

### Pasul 4: Testează din nou
1. Deschide http://localhost:3000/auth/register
2. Cu Developer Tools DESCHIS (Network tab):
3. Completează formularul cu date noi:
   - Name: Test User
   - Email: **testNOU@example.com** (IMPORTANT: email nou!)
   - Password: Password123!
   - Confirm Password: Password123!
4. Submit și URMĂREȘTE în Network tab ce se întâmplă

## Ce să cauți în Network tab 🔎

### Request la `/sanctum/csrf-cookie`
- **Status**: 204 (No Content) = ✅ OK
- Ar trebui să se facă ÎNAINTEA request-ului `/register`

### Request la `/api/v1/register`
- **Status**: 
  - 201 = ✅ SUCCESS (registration OK!)
  - 422 = ⚠️ Validation errors (vezi Response)
  - 419 = ⚠️ CSRF token missing (vezi Headers)
  - 500 = ❌ Server error (vezi Laravel logs)
  - 0 = ❌ CORS blocked sau backend oprit

- **Response Headers** trebuie să aibă:
  ```
  Access-Control-Allow-Origin: http://localhost:3000
  Access-Control-Allow-Credentials: true
  ```

- **Response Body** (dacă SUCCESS):
  ```json
  {
    "user": { "id": ..., "name": "...", "email": "..." },
    "token": "1|...",
    "message": "Registration successful!"
  }
  ```

## Erori comune și soluții 🛠️

### Eroare: Status 0 (Failed to fetch)
**Cauză**: Backend-ul nu rulează sau CORS blochează
**Soluție**:
```powershell
# Verifică dacă Laravel rulează
cd c:\laragon\www\RentHub\backend
php artisan serve --port=8000
```

### Eroare: 422 (Validation Error)
**Cauză**: Datele nu sunt trimise corect
**Soluție**: Verifică Request Payload în Network tab - toate câmpurile au valori?

### Eroare: 419 (CSRF Token Mismatch)
**Cauză**: CSRF cookie nu se setează
**Soluție**: Verifică dacă `/sanctum/csrf-cookie` returnează Set-Cookie header

### Eroare: Email already exists
**Cauză**: Ai folosit același email de mai multe ori
**Soluție**: Folosește un email complet NOU de fiecare dată!

## Test manual rapid (fără frontend) ✨

Dacă vrei să testezi doar backend-ul:

```powershell
cd c:\laragon\www\RentHub
.\test-registration-flow.ps1
```

Sau cu curl:
```powershell
curl -X POST http://localhost:8000/api/v1/register `
  -H "Content-Type: application/json" `
  -H "Accept: application/json" `
  -d "@test-register.json"
```

## După ce găsești problema 📋

Trimite-mi screenshot sau copy-paste din:
1. **Console tab** - toate log-urile `[authService]` și `[AuthContext]`
2. **Network tab** - Status, Headers și Response pentru request-ul `/register`
3. Rezultatul comenzii: `php artisan serve --port=8000` (e pornit sau nu?)

---

**TL;DR**: Deschide DevTools (F12) → Network tab → Încearcă register → Trimite-mi ce status code primești la request-ul `/api/v1/register`

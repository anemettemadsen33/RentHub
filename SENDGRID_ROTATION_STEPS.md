# 🔴 URGENT: SendGrid API Key Rotation

**Status:** CRITICAL - Key leaked in repository  
**Deadline:** Înainte de deployment Luni  
**Estimated Time:** 15 minutes

---

## 📋 Checklist Complet

### Pas 1: Creează Nou API Key în SendGrid (5 min)

1. **Login SendGrid Dashboard**
   - URL: https://app.sendgrid.com/
   - Account: team@renthub.com (sau contul tău)

2. **Navighează la API Keys**
   - Click: Settings → API Keys (stânga jos)
   - URL direct: https://app.sendgrid.com/settings/api_keys

3. **Creează Nou Key**
   - Click: "Create API Key" (buton albastru)
   - **Name:** `RentHub-Production-Nov-2025`
   - **Permissions:** 
     - ✅ Mail Send → Full Access
     - ❌ Disable toate celelalte (security best practice)
   - Click: "Create & View"

4. **IMPORTANT: Copiază Key-ul ACUM**
   - ⚠️ Va fi afișat O SINGURĂ DATĂ!
   - Format: `SG.xxxxxxxxxxxxxxxxxxxxxxxx`
   - Salvează temporar în clipboard/notepad

---

### Pas 2: Update Backend .env (2 min)

**Local Development:**
```bash
cd c:\laragon\www\RentHub\backend
```

Editează `.env`:
```env
# OLD (COMPROMISED):
# MAIL_PASSWORD=SG.4p9fVE7TRxS...

# NEW (FROM SENDGRID):
MAIL_PASSWORD=SG.NEW_KEY_HERE_PASTE_FROM_CLIPBOARD
```

**Production (Laravel Forge):**
1. Login: https://forge.laravel.com/
2. Servers → renthub-tbj7yxj7
3. Sites → renthub-tbj7yxj7.on-forge.com
4. Environment → Edit Environment
5. Find: `MAIL_PASSWORD=`
6. Replace cu noul key
7. Click: "Save"

---

### Pas 3: Update Frontend .env (1 min)

**Frontend folosește backend pentru email**, dar verifică dacă există configurări:

```bash
cd c:\laragon\www\RentHub\frontend
```

Editează `.env.production`:
```env
# Email-ul se trimite prin backend API, nu direct din frontend
# Dar verifică dacă există:
NEXT_PUBLIC_CONTACT_EMAIL=contact@renthub.com
```

---

### Pas 4: Testează Noul Key (5 min)

**Backend Test:**
```bash
cd c:\laragon\www\RentHub\backend
php artisan tinker
```

În Tinker:
```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Test email from RentHub', function ($message) {
    $message->to('your-email@example.com')
            ->subject('SendGrid Test - New API Key');
});
// Should return: null (success) or error
```

**Alternative - Test via Artisan:**
```bash
php artisan queue:work --once
# Trimite un test notification
```

---

### Pas 5: Revocă Vechiul Key (2 min)

**CRITICAL - Previne abuse:**

1. Înapoi în SendGrid Dashboard
2. Settings → API Keys
3. Găsește vechiul key: `RentHub-Production-*` (sau unnamed)
4. Click trei puncte (⋮) → **Delete**
5. Confirmă deletion

**Keys de șters:**
- ❌ `SG.4p9fVE7TRxS...` (leaked în repository)
- ❌ Orice alte keys vechi/nefolosite

---

### Pas 6: Update Documentation (1 min)

Editează `PRODUCTION_SECRETS_CHECKLIST.md`:
```markdown
### ✅ Mail (SendGrid) - ROTATED
- [x] **MAIL_PASSWORD** (SendGrid API Key)
  - Status: ✅ Rotated on 2025-11-15
  - New key: Active
  - Old key: Revoked
```

---

## 🔒 Security Best Practices

### Ce NU trebuie făcut:
- ❌ NU commit-a key-ul în Git
- ❌ NU partaja key-ul via email/Slack
- ❌ NU folosi același key pentru dev & production

### Ce trebuie făcut:
- ✅ Folosește .env pentru local
- ✅ Folosește Forge Environment Variables pentru production
- ✅ Rotează key-urile la 90 zile
- ✅ Monitorizează SendGrid Dashboard pentru activitate suspectă

---

## 📊 Verification Checklist

După finalizare, verifică:

- [ ] Noul SendGrid key funcționează (test email trimis)
- [ ] Vechiul key a fost revocat în SendGrid
- [ ] Backend .env actualizat (local)
- [ ] Forge Environment actualizat (production)
- [ ] Documentation updated
- [ ] Nu există keys în Git history (dacă da, contact GitHub Support)

---

## 🆘 Troubleshooting

### Error: "Authentication failed"
- **Cauză:** Key greșit sau permissions insuficiente
- **Fix:** Verifică că ai copiat întreg key-ul (inclusiv `SG.` prefix)

### Error: "Rate limit exceeded"
- **Cauză:** Prea multe emailuri într-un interval scurt
- **Fix:** Așteaptă 5 minute, apoi retry

### Emailuri nu ajung
- **Verifică:** 
  1. SendGrid Dashboard → Activity Feed
  2. Spam folder
  3. DNS records (SPF, DKIM, DMARC)

---

## 📅 Schedule de Rotație

**Recomandări:**
- 🔄 SendGrid API Key: **la fiecare 90 zile**
- 🔄 După orice security incident: **IMEDIAT**
- 🔄 La schimbarea team members: **24h**

**Next Rotation:** 2026-02-15 (90 days from now)

---

## ✅ Task Complete

Când toate checkbox-urile sunt bifate, marchează task-ul ca:
```
[x] Task 1: URGENT - Rotație SendGrid API Key - COMPLETED
```

**Time Invested:** ~15 minutes  
**Security Impact:** 🔴 CRITICAL → 🟢 SECURE

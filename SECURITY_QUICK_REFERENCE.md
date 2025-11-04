# 🔐 Security Quick Reference - RentHub

## 🚀 Quick Commands

### Enable 2FA (TOTP)
```bash
curl -X POST /api/security/2fa/enable \
  -H "Authorization: Bearer TOKEN" \
  -d '{"method":"totp","password":"PASSWORD"}'
```

### Export User Data (GDPR)
```bash
curl -X POST /api/privacy/export \
  -H "Authorization: Bearer TOKEN" \
  -d '{"format":"json"}'
```

### Request Data Deletion
```bash
curl -X POST /api/privacy/delete \
  -H "Authorization: Bearer TOKEN" \
  -d '{"categories":["all"]}'
```

### CCPA Opt-Out
```bash
curl -X POST /api/privacy/ccpa/opt-out \
  -H "Authorization: Bearer TOKEN"
```

---

## 🔑 Middleware Reference

### Apply to Routes

```php
// Rate limiting
Route::middleware(['rate_limit:api'])->get('/endpoint', ...);

// DDoS protection
Route::middleware(['ddos'])->get('/endpoint', ...);

// XSS protection
Route::middleware(['xss'])->post('/endpoint', ...);

// SQL injection protection
Route::middleware(['sql_injection'])->get('/endpoint', ...);

// Permission check
Route::middleware(['permission:resource.action'])->get('/endpoint', ...);

// Role check
Route::middleware(['role:admin,landlord'])->get('/endpoint', ...);
```

---

## 📝 Service Usage

### Audit Logging

```php
use App\Services\Security\AuditLogService;

$audit = app(AuditLogService::class);

// Log events
$audit->logAuthentication($user, 'login', true);
$audit->logDataAccess($user, 'Property', 123);
$audit->logSuspiciousActivity('brute_force', 5);
```

### Input Validation

```php
use App\Services\Security\InputValidationService;

$validator = app(InputValidationService::class);

// Sanitize
$clean = $validator->sanitizeString($input);
$safeHtml = $validator->sanitizeHtml($html);
$email = $validator->sanitizeEmail($email);

// Validate
$fileResult = $validator->validateFileUpload($file);
$isValid = $validator->validateSqlInput($query);
$safe = $validator->preventXss($input);
```

### Two-Factor Auth

```php
use App\Services\Security\TwoFactorAuthService;

$twoFactor = app(TwoFactorAuthService::class);

// Manage 2FA
$result = $twoFactor->enable($user, 'totp');
$valid = $twoFactor->verify($user, $code);
$sent = $twoFactor->sendCode($user);
$disabled = $twoFactor->disable($user);

// Backup codes
$codes = $twoFactor->generateBackupCodes($user);
$valid = $twoFactor->verifyBackupCode($user, $code);
```

### GDPR Service

```php
use App\Services\Security\GDPRService;

$gdpr = app(GDPRService::class);

// Consent
$consent = $gdpr->recordConsent($user, $data);
$withdrawn = $gdpr->withdrawConsent($user);

// Data export
$data = $gdpr->exportUserData($user, 'json');

// Data deletion
$result = $gdpr->requestDataDeletion($user);
```

### CCPA Service

```php
use App\Services\Security\CCPAService;

$ccpa = app(CCPAService::class);

// Consent
$consent = $ccpa->recordConsent($user, $data);
$optedOut = $ccpa->optOutOfDataSale($user);

// Disclosure
$disclosure = $ccpa->requestDataDisclosure($user);

// Data portability
$data = $ccpa->exportUserData($user, 'json');
```

---

## 🔒 Security Headers

### Required Headers (Auto-Applied)

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; ...
```

---

## ⚙️ Configuration

### Rate Limits by Role

| Role | Requests/Min |
|------|--------------|
| Guest | 60 |
| Tenant | 120 |
| Landlord | 300 |
| Admin | 1000 |

### Password Policy

- Min length: **8 characters**
- Must contain: **uppercase, lowercase, numbers, symbols**
- Check compromised: **Yes**
- Expiry: **90 days**
- Prevent reuse: **Last 5 passwords**

### Data Retention

- Audit logs: **365 days**
- GDPR data: **7 years** (2555 days)
- Deletion grace period: **30 days**

---

## 🚨 Common Error Codes

| Code | Error | Solution |
|------|-------|----------|
| 400 | Invalid input detected | Check input validation |
| 401 | Unauthorized | Verify authentication token |
| 403 | Forbidden | Check user permissions |
| 419 | CSRF token mismatch | Refresh CSRF token |
| 429 | Too many requests | Wait for rate limit reset |

---

## 🔍 Testing Endpoints

### Security Overview
```
GET /api/security/overview
```

### Audit Logs
```
GET /api/security/audit-logs?event_type=authentication
```

### 2FA Status
```
GET /api/security/2fa/status
```

### Privacy Settings
```
GET /api/privacy/settings
```

---

## 🛠️ Maintenance Commands

```bash
# Clean up expired tokens
php artisan tokens:cleanup

# Clean up audit logs
php artisan audit:cleanup

# Run migrations
php artisan migrate

# Seed roles & permissions
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 📊 Security Score

Your security score is based on:

| Feature | Points |
|---------|--------|
| 2FA Enabled | 30 |
| Password Fresh (<90d) | 20 |
| Email Verified | 15 |
| GDPR Consent | 15 |
| No Failed Logins | 20 |

**Maximum Score:** 100

---

## 🔐 Environment Variables

### Essential Security Settings

```env
# Core Security
ENCRYPT_DATA_AT_REST=true
FORCE_TLS=true
APP_KEY=base64:...

# JWT
JWT_SECRET=your_secret_key

# Two-Factor
2FA_ENABLED=true

# Rate Limiting
RATE_LIMITING_ENABLED=true
RATE_LIMITER_DRIVER=redis

# DDoS Protection
DDOS_PROTECTION_ENABLED=true
DDOS_WHITELIST_IPS=127.0.0.1,::1

# Compliance
GDPR_ENABLED=true
GDPR_RETENTION_DAYS=2555
CCPA_ENABLED=true

# Monitoring
AUDIT_LOGGING_ENABLED=true
SECURITY_MONITORING_ENABLED=true
```

---

## 📞 Emergency Contacts

### Security Issues
- **Email:** security@renthub.com
- **Response Time:** < 4 hours

### Data Privacy Requests
- **Email:** privacy@renthub.com
- **Response Time:** < 30 days

---

**Last Updated:** November 3, 2024

# 🔐 Comprehensive Security Implementation Guide - RentHub

## 📋 Overview

Complete security implementation covering Authentication, Authorization, Data Protection, Application Security, and Compliance (GDPR/CCPA).

## 🎯 Implemented Security Features

### ✅ Authentication & Authorization
- [x] OAuth 2.0 (Google, Facebook, GitHub)
- [x] JWT tokens (access & refresh)
- [x] Two-Factor Authentication (TOTP, SMS, Email)
- [x] Role-Based Access Control (RBAC)
- [x] API Key Management
- [x] Session Management

### ✅ Data Security
- [x] Data encryption at rest
- [x] Data encryption in transit (TLS 1.3)
- [x] PII data anonymization
- [x] GDPR compliance
- [x] CCPA compliance
- [x] Data retention policies
- [x] Right to be forgotten

### ✅ Application Security
- [x] SQL injection prevention
- [x] XSS protection
- [x] CSRF protection
- [x] Rate limiting (per-user & global)
- [x] DDoS protection
- [x] Security headers (CSP, HSTS, etc.)
- [x] Input validation & sanitization
- [x] File upload security

### ✅ Monitoring & Audit
- [x] Comprehensive audit logging
- [x] Security event monitoring
- [x] Suspicious activity detection
- [x] Failed login tracking
- [x] Admin action logging

---

## 🚀 Quick Start

### 1. Environment Configuration

Add to your `.env` file:

```env
# Security Configuration
ENCRYPT_DATA_AT_REST=true
FORCE_TLS=true
RATE_LIMITING_ENABLED=true
DDOS_PROTECTION_ENABLED=true
AUDIT_LOGGING_ENABLED=true
SECURITY_MONITORING_ENABLED=true

# Two-Factor Authentication
2FA_ENABLED=true

# GDPR Configuration
GDPR_ENABLED=true
GDPR_RETENTION_DAYS=2555

# CCPA Configuration
CCPA_ENABLED=true

# Rate Limiting
RATE_LIMITER_DRIVER=redis

# DDoS Protection
DDOS_WHITELIST_IPS=127.0.0.1,::1
DDOS_BLACKLIST_IPS=

# JWT Configuration (from existing setup)
JWT_SECRET=your_jwt_secret
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Register Middleware

Update `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... existing middleware
    \App\Http\Middleware\SecurityHeaders::class,
    \App\Http\Middleware\TLSEnforcement::class,
    \App\Http\Middleware\DDoSProtectionMiddleware::class,
];

protected $middlewareGroups = [
    'api' => [
        // ... existing middleware
        \App\Http\Middleware\XssProtectionMiddleware::class,
        \App\Http\Middleware\SqlInjectionProtectionMiddleware::class,
        \App\Http\Middleware\RateLimitMiddleware::class,
    ],
];

protected $middlewareAliases = [
    // ... existing aliases
    'rate_limit' => \App\Http\Middleware\RateLimitMiddleware::class,
    'ddos' => \App\Http\Middleware\DDoSProtectionMiddleware::class,
    'xss' => \App\Http\Middleware\XssProtectionMiddleware::class,
    'sql_injection' => \App\Http\Middleware\SqlInjectionProtectionMiddleware::class,
    'csrf_custom' => \App\Http\Middleware\CsrfProtectionMiddleware::class,
];
```

### 4. Add Routes

Create/Update `routes/api.php`:

```php
// Security Management Routes
Route::middleware(['auth:api'])->prefix('security')->group(function () {
    Route::get('/overview', [SecurityController::class, 'overview']);
    Route::get('/audit-logs', [SecurityController::class, 'auditLogs']);
    
    // Two-Factor Authentication
    Route::prefix('2fa')->group(function () {
        Route::post('/enable', [TwoFactorAuthController::class, 'enable']);
        Route::post('/verify', [TwoFactorAuthController::class, 'verify']);
        Route::post('/send-code', [TwoFactorAuthController::class, 'sendCode']);
        Route::post('/disable', [TwoFactorAuthController::class, 'disable']);
        Route::post('/regenerate-backup-codes', [TwoFactorAuthController::class, 'regenerateBackupCodes']);
        Route::get('/status', [TwoFactorAuthController::class, 'status']);
    });
});

// Data Privacy Routes
Route::middleware(['auth:api'])->prefix('privacy')->group(function () {
    Route::post('/gdpr/consent', [DataPrivacyController::class, 'giveGdprConsent']);
    Route::delete('/gdpr/consent', [DataPrivacyController::class, 'withdrawGdprConsent']);
    Route::post('/ccpa/opt-out', [DataPrivacyController::class, 'ccpaOptOut']);
    Route::get('/ccpa/disclosure', [DataPrivacyController::class, 'requestDataDisclosure']);
    Route::post('/export', [DataPrivacyController::class, 'exportData']);
    Route::get('/export/{filename}', [DataPrivacyController::class, 'downloadExport'])->name('api.privacy.download-export');
    Route::post('/delete', [DataPrivacyController::class, 'requestDataDeletion']);
    Route::delete('/delete/cancel', [DataPrivacyController::class, 'cancelDataDeletion']);
    Route::get('/settings', [DataPrivacyController::class, 'getPrivacySettings']);
});
```

---

## 📖 Detailed Feature Documentation

## 1. Two-Factor Authentication (2FA)

### Enable 2FA

**Endpoint:** `POST /api/security/2fa/enable`

**Request:**
```json
{
  "method": "totp",
  "password": "user_password"
}
```

**Response:**
```json
{
  "message": "2FA enabled successfully",
  "data": {
    "secret": "BASE32SECRET",
    "qr_code_url": "otpauth://totp/...",
    "backup_codes": [
      "abcd1234-efgh5678",
      "ijkl9012-mnop3456"
    ]
  }
}
```

**Supported Methods:**
- `totp` - Time-based One-Time Password (Google Authenticator, Authy)
- `sms` - SMS verification code
- `email` - Email verification code

### Verify 2FA Setup

**Endpoint:** `POST /api/security/2fa/verify`

**Request:**
```json
{
  "code": "123456"
}
```

### Disable 2FA

**Endpoint:** `POST /api/security/2fa/disable`

**Request:**
```json
{
  "password": "user_password"
}
```

---

## 2. Data Privacy & Compliance

### GDPR - Give Consent

**Endpoint:** `POST /api/privacy/gdpr/consent`

**Request:**
```json
{
  "consent_text": "I agree to the processing of my personal data...",
  "purposes": [
    "service_provision",
    "communication",
    "analytics"
  ]
}
```

### GDPR - Export Data

**Endpoint:** `POST /api/privacy/export`

**Request:**
```json
{
  "format": "json"
}
```

**Response:**
```json
{
  "message": "Data export completed successfully",
  "download_url": "/api/privacy/export/data-export-123-2024-11-03-150000.json",
  "expires_at": "2024-11-10T15:00:00Z"
}
```

### GDPR - Request Deletion (Right to be Forgotten)

**Endpoint:** `POST /api/privacy/delete`

**Request:**
```json
{
  "categories": ["all"],
  "reason": "No longer need the service"
}
```

**Response:**
```json
{
  "success": true,
  "request_id": 123,
  "scheduled_for": "2024-12-03T15:00:00Z",
  "grace_period_days": 30
}
```

### CCPA - Opt Out of Data Sale

**Endpoint:** `POST /api/privacy/ccpa/opt-out`

**Response:**
```json
{
  "message": "Successfully opted out of data sale"
}
```

### CCPA - Request Data Disclosure

**Endpoint:** `GET /api/privacy/ccpa/disclosure`

**Response:**
```json
{
  "categories_collected": [
    {
      "category": "Identifiers",
      "examples": ["Email address", "Phone number", "Name"]
    }
  ],
  "sources": {
    "Directly from you": "Account registration, profile updates",
    "Automatically collected": "Cookies, web beacons"
  },
  "business_purposes": {
    "Service provision": "To provide and maintain our services"
  },
  "third_parties": [
    {
      "category": "Payment Processors",
      "purpose": "To process payments",
      "examples": ["Stripe", "PayPal"]
    }
  ],
  "sold_or_shared": {
    "sold": false,
    "shared_for_business_purposes": true,
    "do_not_sell_status": false
  }
}
```

---

## 3. Rate Limiting

### Apply Rate Limiting to Routes

```php
// Global API rate limit (60 requests per minute)
Route::middleware(['rate_limit:api'])->group(function () {
    Route::get('/properties', [PropertyController::class, 'index']);
});

// Authentication rate limit (5 requests per 15 minutes)
Route::middleware(['rate_limit:auth'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Upload rate limit (10 requests per hour)
Route::middleware(['rate_limit:uploads'])->group(function () {
    Route::post('/upload', [FileController::class, 'upload']);
});
```

### Per-User Rate Limits

Rate limits automatically adjust based on user role:

| Role | Rate Limit |
|------|------------|
| **Guest** | 60 requests/min |
| **Tenant** | 120 requests/min |
| **Landlord** | 300 requests/min |
| **Admin** | 1000 requests/min |

### Response Headers

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699012345
```

### Rate Limit Exceeded Response

```json
{
  "error": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

**Status Code:** `429 Too Many Requests`

---

## 4. DDoS Protection

### Configuration

DDoS protection is automatically enabled with the following defaults:

- Max requests per second: **10**
- Ban duration: **60 minutes**
- Challenge suspicious traffic: **Enabled**

### IP Whitelisting

Add trusted IPs to `.env`:

```env
DDOS_WHITELIST_IPS=192.168.1.1,10.0.0.1,172.16.0.0/24
```

### IP Blacklisting

Add blocked IPs to `.env`:

```env
DDOS_BLACKLIST_IPS=1.2.3.4,5.6.7.8
```

### Banned IP Response

```json
{
  "error": "Your IP has been temporarily banned due to suspicious activity",
  "retry_after": 3600
}
```

**Status Code:** `429 Too Many Requests`

---

## 5. Input Validation & Sanitization

### Using InputValidationService

```php
use App\Services\Security\InputValidationService;

$validationService = app(InputValidationService::class);

// Sanitize string
$clean = $validationService->sanitizeString($_POST['name']);

// Sanitize HTML
$cleanHtml = $validationService->sanitizeHtml($_POST['description'], ['p', 'strong', 'em']);

// Validate email
$email = $validationService->sanitizeEmail($_POST['email']);

// Validate URL
$url = $validationService->sanitizeUrl($_POST['website']);

// Sanitize filename
$filename = $validationService->sanitizeFilename($_FILES['document']['name']);

// Validate file upload
$result = $validationService->validateFileUpload($request->file('document'));

if ($result['valid']) {
    // Process upload
    $file->storeAs('uploads', $result['sanitized_name']);
}

// Prevent XSS
$safe = $validationService->preventXss($userInput);

// Check for malicious content
if ($validationService->containsMaliciousContent($input)) {
    abort(400, 'Invalid input detected');
}
```

### File Upload Security

**Configuration:** `config/security.php`

```php
'file_upload' => [
    'scan_for_viruses' => true,
    'validate_mime_type' => true,
    'randomize_filenames' => true,
    'max_size' => 10485760, // 10MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
    'forbidden_extensions' => ['php', 'exe', 'sh', 'bat'],
],
```

---

## 6. Security Headers

All responses include comprehensive security headers:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(self), microphone=(), camera=()
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; ...
```

### Custom CSP Configuration

Update `config/security.php`:

```php
'headers' => [
    'Content-Security-Policy' => implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.example.com",
        "style-src 'self' 'unsafe-inline'",
        "img-src 'self' data: https:",
        "connect-src 'self' https://api.example.com",
    ]),
],
```

---

## 7. Audit Logging

### Automatic Logging

The following events are automatically logged:

- ✅ Authentication attempts (success/failure)
- ✅ Authorization failures
- ✅ Data access (viewing records)
- ✅ Data modifications (create/update/delete)
- ✅ Admin actions
- ✅ Security events (2FA, password changes)
- ✅ Suspicious activity

### Manual Logging

```php
use App\Services\Security\AuditLogService;

$auditLog = app(AuditLogService::class);

// Log authentication
$auditLog->logAuthentication($user, 'login', true);

// Log authorization failure
$auditLog->logAuthorizationFailure($user, 'Property', 'update', [
    'property_id' => 123,
]);

// Log data access
$auditLog->logDataAccess($user, 'Property', 123, 'view');

// Log data modification
$auditLog->logDataModification($user, 'Property', 123, 'update', 
    ['price' => 100], 
    ['price' => 150]
);

// Log admin action
$auditLog->logAdminAction($admin, 'user_deleted', [
    'deleted_user_id' => 456,
]);

// Log security event
$auditLog->logSecurityEvent('password_changed', true, [
    'method' => 'manual',
]);

// Log suspicious activity
$auditLog->logSuspiciousActivity('multiple_failed_logins', 3, [
    'attempts' => 5,
    'time_window' => '5 minutes',
]);
```

### Query Audit Logs

**Endpoint:** `GET /api/security/audit-logs`

**Query Parameters:**
- `event_type` - Filter by event type
- `from_date` - Filter from date (ISO 8601)
- `to_date` - Filter to date (ISO 8601)

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "event_type": "authentication",
      "event_name": "login",
      "success": true,
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "metadata": {
        "method": "email",
        "timestamp": "2024-11-03T15:00:00Z"
      },
      "created_at": "2024-11-03T15:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 1250
  }
}
```

### Cleanup Old Logs

```bash
php artisan audit:cleanup
```

Or programmatically:

```php
$auditLog = app(AuditLogService::class);
$deleted = $auditLog->cleanupOldLogs();
```

Logs older than the retention period (default: 365 days) will be deleted.

---

## 8. Security Monitoring & Alerts

### Configuration

```php
'monitoring' => [
    'enabled' => true,
    'alert_on_suspicious_activity' => true,
    'alert_channels' => ['email', 'slack', 'sms'],
    'thresholds' => [
        'failed_logins' => 5,
        'rate_limit_violations' => 10,
        'unauthorized_access_attempts' => 3,
    ],
],
```

### Alert Triggers

Alerts are automatically sent when:

- **Failed logins** exceed threshold
- **Rate limit** violations occur
- **Unauthorized access** attempts detected
- **Suspicious activity** identified
- **DDoS attack** detected

### Alert Channels

- **Email** - Sent to security team
- **Slack** - Posted to #security channel
- **SMS** - Sent to on-call engineer

---

## 9. Password Policy

### Configuration

```php
'password' => [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_symbols' => true,
    'check_compromised' => true,
    'expiry_days' => 90,
    'prevent_reuse_count' => 5,
],
```

### Validation

```php
use Illuminate\Validation\Rules\Password;

$request->validate([
    'password' => ['required', Password::min(8)
        ->mixedCase()
        ->numbers()
        ->symbols()
        ->uncompromised()
    ],
]);
```

---

## 10. API Security Best Practices

### 1. Always Use HTTPS

```env
FORCE_TLS=true
APP_URL=https://yourdomain.com
```

### 2. Implement Authentication

```php
Route::middleware(['auth:api'])->group(function () {
    // Your protected routes
});
```

### 3. Use Rate Limiting

```php
Route::middleware(['rate_limit:api'])->group(function () {
    // Your API routes
});
```

### 4. Validate All Input

```php
$validated = $request->validate([
    'email' => 'required|email|max:255',
    'name' => 'required|string|max:100',
]);
```

### 5. Sanitize Output

```php
$safe = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### 6. Use Prepared Statements

Laravel Eloquent automatically uses prepared statements:

```php
// ✅ Safe
Property::where('id', $id)->first();

// ❌ Never do this
DB::raw("SELECT * FROM properties WHERE id = $id");
```

### 7. Implement CSRF Protection

```php
Route::middleware(['csrf_custom'])->post('/action', function () {
    // CSRF protected
});
```

---

## 🧪 Testing Security Features

### Test 2FA

```bash
curl -X POST http://localhost:8000/api/security/2fa/enable \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"method":"totp","password":"your_password"}'
```

### Test Rate Limiting

```bash
# Send multiple requests rapidly
for i in {1..70}; do
  curl http://localhost:8000/api/properties
done
```

### Test XSS Protection

```bash
curl -X POST http://localhost:8000/api/properties \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"<script>alert(\"XSS\")</script>Test Property"}'
```

### Test SQL Injection Protection

```bash
curl -X GET "http://localhost:8000/api/properties?search=1' OR '1'='1"
```

---

## 📊 Security Dashboard

### Get Security Overview

**Endpoint:** `GET /api/security/overview`

**Response:**
```json
{
  "two_factor": {
    "enabled": true,
    "enforced": false
  },
  "data_protection": {
    "gdpr_consent": true,
    "ccpa_do_not_sell": false
  },
  "security_score": 85,
  "recommendations": [
    {
      "type": "password",
      "priority": "medium",
      "message": "Change your password regularly (recommended every 90 days)"
    }
  ]
}
```

### Security Score Calculation

| Feature | Points |
|---------|--------|
| 2FA Enabled | 30 |
| Password Updated (< 90 days) | 20 |
| Email Verified | 15 |
| GDPR Consent Given | 15 |
| No Failed Logins | 20 |
| **Total** | **100** |

---

## 🔧 Maintenance Tasks

### Daily Tasks

```bash
# Cleanup expired tokens
php artisan tokens:cleanup

# Cleanup expired API keys
php artisan api-keys:cleanup
```

### Weekly Tasks

```bash
# Review audit logs
php artisan audit:review

# Check for security updates
composer update
```

### Monthly Tasks

```bash
# Cleanup old audit logs
php artisan audit:cleanup

# Review security settings
php artisan security:review
```

---

## 🎓 Security Checklist

### Authentication ✅
- [x] OAuth 2.0 implementation
- [x] JWT tokens with refresh
- [x] Two-factor authentication
- [x] Password policy enforcement
- [x] Account lockout after failed attempts

### Authorization ✅
- [x] Role-based access control
- [x] Permission-based authorization
- [x] API key management
- [x] Session management

### Data Protection ✅
- [x] Encryption at rest
- [x] Encryption in transit
- [x] PII anonymization
- [x] GDPR compliance
- [x] CCPA compliance
- [x] Data retention policies
- [x] Right to be forgotten

### Application Security ✅
- [x] SQL injection prevention
- [x] XSS protection
- [x] CSRF protection
- [x] Rate limiting
- [x] DDoS protection
- [x] Security headers
- [x] Input validation
- [x] File upload security

### Monitoring ✅
- [x] Audit logging
- [x] Security monitoring
- [x] Suspicious activity detection
- [x] Alert system

---

## 📞 Support & Resources

### Documentation
- [Security Guide](./SECURITY_GUIDE.md)
- [Authentication Setup](./AUTHENTICATION_SETUP.md)
- [API Documentation](./API_ENDPOINTS.md)

### Security Contact
- Email: security@renthub.com
- Bug Bounty: https://bugcrowd.com/renthub

### Compliance
- GDPR: https://renthub.com/gdpr
- CCPA: https://renthub.com/ccpa
- Privacy Policy: https://renthub.com/privacy

---

**Last Updated:** November 3, 2024  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

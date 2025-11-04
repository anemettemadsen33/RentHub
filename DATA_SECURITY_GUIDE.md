# 🔒 Data Security Guide - RentHub

## Overview

Comprehensive data security implementation including encryption at rest and in transit, PII anonymization, GDPR/CCPA compliance, data retention policies, and right to be forgotten for RentHub platform.

## 📋 Table of Contents

- [Data Encryption](#data-encryption)
- [TLS 1.3 Configuration](#tls-13-configuration)
- [PII Data Anonymization](#pii-data-anonymization)
- [GDPR Compliance](#gdpr-compliance)
- [CCPA Compliance](#ccpa-compliance)
- [Data Retention Policies](#data-retention-policies)
- [Right to be Forgotten](#right-to-be-forgotten)
- [Security Headers](#security-headers)

## 🔐 Data Encryption

### Encryption at Rest

**Laravel Encryption:**
```php
use App\Services\Security\EncryptionService;

$encryptionService = app(EncryptionService::class);

// Encrypt sensitive data
$encrypted = $encryptionService->encrypt($sensitiveData);

// Decrypt data
$decrypted = $encryptionService->decrypt($encrypted);

// One-way hash
$hash = $encryptionService->hash($data);
```

**Automatic Encryption in Models:**
```php
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Automatically encrypt these attributes
    protected $casts = [
        'ssn' => 'encrypted',
        'bank_account' => 'encrypted',
        'credit_card' => 'encrypted',
    ];
}
```

**File Encryption:**
```php
$encryptionService = app(EncryptionService::class);

// Encrypt file
$encryptionService->encryptFile($filePath);

// Decrypt file
$encryptionService->decryptFile($encryptedPath, $outputPath);
```

### Database Encryption

**Enable MySQL Encryption:**
```sql
-- Enable encryption for table
ALTER TABLE users ENCRYPTION='Y';

-- Enable encryption for tablespace
ALTER TABLESPACE innodb_system ENCRYPTION='Y';
```

**Environment Configuration:**
```env
# Encryption Key (auto-generated)
APP_KEY=base64:your_32_byte_key_here

# Database encryption
DB_ENCRYPTION=true
```

### Backup Encryption

All backups should be encrypted:
```bash
# Encrypted database backup
mysqldump --single-transaction renthub | \
  openssl enc -aes-256-cbc -salt -pbkdf2 -out backup.sql.enc

# Decrypt backup
openssl enc -aes-256-cbc -d -pbkdf2 -in backup.sql.enc -out backup.sql
```

## 🌐 TLS 1.3 Configuration

### Nginx Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name renthub.com;

    # TLS 1.3 only
    ssl_protocols TLSv1.3;
    
    # Modern cipher suites
    ssl_ciphers TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256;
    ssl_prefer_server_ciphers off;

    # SSL certificates
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_trusted_certificate /path/to/chain.crt;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Laravel TLS Enforcement

**Middleware:**
```php
// Applied automatically via SecurityHeaders middleware
Route::middleware(['tls'])->group(function () {
    // All routes here require HTTPS
});
```

**Force HTTPS in production:**
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true),

// config/sanctum.php
'secure' => env('SANCTUM_SECURE_COOKIE', true),
```

### Certificate Management

**Let's Encrypt (Certbot):**
```bash
# Install certbot
sudo apt-get install certbot python3-certbot-nginx

# Get certificate
sudo certbot --nginx -d renthub.com -d www.renthub.com

# Auto-renewal
sudo certbot renew --dry-run
```

## 👤 PII Data Anonymization

### Anonymization Service

```php
use App\Services\Security\AnonymizationService;

$anonymizationService = app(AnonymizationService::class);

// Anonymize email
$email = 'john.doe@example.com';
$anonymized = $anonymizationService->anonymizeEmail($email);
// Output: jo*******@example.com

// Anonymize phone
$phone = '+1234567890';
$anonymized = $anonymizationService->anonymizePhone($phone);
// Output: ******7890

// Anonymize name
$name = 'John Doe';
$anonymized = $anonymizationService->anonymizeName($name);
// Output: J*** D**

// Anonymize address
$address = '123 Main St, New York, NY 10001';
$anonymized = $anonymizationService->anonymizeAddress($address);
// Output: [REDACTED], New York, NY 10001

// Anonymize IP
$ip = '192.168.1.100';
$anonymized = $anonymizationService->anonymizeIP($ip);
// Output: 192.168.1.0

// Mask credit card
$card = '4532123456789012';
$masked = $anonymizationService->maskCreditCard($card);
// Output: ************9012
```

### Automatic Anonymization

**In Models:**
```php
class User extends Model
{
    public function getAnonymizedEmailAttribute()
    {
        return app(AnonymizationService::class)->anonymizeEmail($this->email);
    }
}
```

**In API Responses:**
```php
return response()->json([
    'users' => $users->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->anonymized_name,
            'email' => $user->anonymized_email,
        ];
    }),
]);
```

### Redact Sensitive Data

```php
$text = "Contact me at john@example.com or call 555-123-4567";
$redacted = $anonymizationService->redactSensitiveData($text);
// Output: "Contact me at [EMAIL] or call [PHONE]"
```

## 📜 GDPR Compliance

### User Rights Implementation

**1. Right to Access:**
```bash
GET /api/gdpr/data
```

```javascript
const response = await fetch('/api/gdpr/data', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const data = await response.json();
```

**2. Right to Data Portability:**
```bash
GET /api/gdpr/export
```

Downloads ZIP file containing all user data in JSON format plus uploaded files.

**3. Right to Rectification:**
```bash
PATCH /api/user/profile
{
  "name": "Updated Name",
  "email": "newemail@example.com"
}
```

**4. Right to be Forgotten:**
```bash
POST /api/gdpr/delete
{
  "reason": "I want to delete my account",
  "confirm": true
}
```

**5. Right to Restrict Processing:**
```bash
PATCH /api/gdpr/consent
{
  "data_processing": false,
  "marketing": false
}
```

**6. Right to Object:**
```bash
PATCH /api/gdpr/consent
{
  "marketing": false
}
```

### Consent Management

**Update Consent:**
```php
use App\Services\Security\GDPRService;

$gdprService = app(GDPRService::class);

$gdprService->updateConsent($user, [
    'terms' => true,
    'privacy' => true,
    'marketing' => false,
    'data_processing' => true,
]);
```

**Check Consent Status:**
```php
$consent = $gdprService->getConsentStatus($user);
/*
[
    'terms_accepted' => true,
    'privacy_accepted' => true,
    'marketing_consent' => false,
    'data_processing_consent' => true,
    'last_consent_update' => '2024-11-03 10:30:00',
]
*/
```

### Data Access Logging

```php
$gdprService->recordDataAccess($user, 'export', 'User requested data export');
```

**Access Types:**
- `view` - User viewed their data
- `export` - User exported their data
- `modify` - User modified their data
- `delete` - User requested deletion
- `delete_request` - Deletion request logged

### GDPR Compliance Report

```bash
php artisan gdpr:report
```

**Output:**
```
GDPR Compliance Report
======================

Total Users: 1,250
Users with Consent: 1,180
Data Export Requests (30d): 15
Deletion Requests (30d): 3
Anonymized Users: 25
Retention Policy Compliant: Yes
Last Check: 2024-11-03 14:30:00
```

## 🇺🇸 CCPA Compliance

### CCPA vs GDPR Mapping

| CCPA Right | GDPR Equivalent | Implementation |
|------------|-----------------|----------------|
| Right to Know | Right to Access | `/api/gdpr/data` |
| Right to Delete | Right to Erasure | `/api/gdpr/delete` |
| Right to Opt-Out | Right to Object | `/api/gdpr/consent` |
| Right to Non-Discrimination | - | No price changes for opting out |

### "Do Not Sell" Implementation

```php
// Update user preferences
$user->update([
    'do_not_sell' => true,
    'marketing_consent' => false,
]);
```

**Frontend:**
```javascript
// Add "Do Not Sell My Personal Information" link
<a href="/api/gdpr/consent?do_not_sell=true">
  Do Not Sell My Personal Information
</a>
```

### CCPA Disclosure

Required disclosure on privacy policy:
- Categories of personal information collected
- Categories of personal information sold
- Categories of third parties with whom information is shared
- Right to opt-out of sale
- Right to non-discrimination

## 📅 Data Retention Policies

### Retention Periods

| Data Type | Retention Period | Legal Requirement |
|-----------|------------------|-------------------|
| User Profiles | Indefinite (until deletion) | - |
| Bookings | 7 years after completion | Tax law |
| Payments | 10 years | Tax law |
| Messages | 2 years | - |
| Activity Logs | 1 year | - |
| API Logs | 90 days | - |
| Audit Logs | 7 years | Legal compliance |
| Temporary Files | 1 day | - |
| Soft Deleted Users | 30 days grace period | GDPR |

### Automatic Cleanup

**Schedule Cleanup:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Daily cleanup of expired data
    $schedule->command('data:cleanup')->daily();
    
    // Weekly GDPR report
    $schedule->command('gdpr:report')->weekly();
}
```

**Manual Cleanup:**
```bash
# Dry run (preview)
php artisan data:cleanup --dry-run

# Actual cleanup
php artisan data:cleanup
```

**Programmatic Cleanup:**
```php
use App\Services\Security\DataRetentionService;

$retentionService = app(DataRetentionService::class);
$results = $retentionService->cleanupExpiredData();
```

### Archive Before Delete

```php
// Archive old data before deleting
$retentionService->archiveData('activity_logs', 365);
```

### Retention Statistics

```bash
# Get retention stats
$stats = $retentionService->getRetentionStats();

# Estimate storage recovery
$estimates = $retentionService->estimateStorageRecovery();
```

## 🗑️ Right to be Forgotten

### Deletion Process

**1. User Requests Deletion:**
```bash
POST /api/gdpr/delete
{
  "reason": "Privacy concerns",
  "confirm": true
}
```

**2. Grace Period (30 days):**
```json
{
  "message": "Account deletion scheduled",
  "deletion_date": "2024-12-03"
}
```

**3. Cancel Deletion (within grace period):**
```bash
POST /api/gdpr/cancel-deletion
```

**4. Automatic Deletion After Grace Period:**
```php
// Scheduled daily
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        $users = User::whereNotNull('deletion_requested_at')
            ->where('deletion_requested_at', '<', now()->subDays(30))
            ->get();
        
        foreach ($users as $user) {
            app(GDPRService::class)->deleteUserData($user, anonymize: true);
        }
    })->daily();
}
```

### Deletion Types

**1. Anonymization (Recommended):**
```php
$gdprService->deleteUserData($user, anonymize: true);
```

Keeps records for legal/audit purposes but anonymizes PII:
- Name → "Deleted User"
- Email → "deleted_abc123@anonymized.local"
- Phone → null
- Address → null
- All personal data → null

**2. Hard Delete:**
```php
$gdprService->deleteUserData($user, anonymize: false);
```

Completely removes user and related data (except legally required records like payments).

### What Gets Deleted/Anonymized

✅ **Deleted:**
- Profile information
- OAuth providers
- API keys
- Refresh tokens
- Roles and permissions
- Messages
- Reviews
- Activity logs
- Uploaded files

⚠️ **Kept (Anonymized):**
- Bookings (legal requirement)
- Payment records (tax law)
- Audit logs (compliance)

## 🛡️ Security Headers

All responses include security headers via `SecurityHeaders` middleware:

```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
Content-Security-Policy: default-src 'self'; ...
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

## 🔧 Configuration

### Environment Variables

```env
# Encryption
APP_KEY=base64:your_32_byte_key_here
DB_ENCRYPTION=true

# TLS/HTTPS
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
SANCTUM_SECURE_COOKIE=true

# Data Retention
DATA_RETENTION_DAYS=365
TEMP_FILES_RETENTION_DAYS=1

# GDPR
GDPR_DELETION_GRACE_PERIOD=30
GDPR_EXPORT_EXPIRY_HOURS=24
```

### Service Providers

```php
// config/app.php
'providers' => [
    // ...
    App\Providers\SecurityServiceProvider::class,
],
```

## 📊 Monitoring & Auditing

### Key Metrics

1. **Data Access Requests**
2. **Data Export Requests**
3. **Deletion Requests**
4. **Consent Updates**
5. **Failed Encryption/Decryption**
6. **TLS Connection Failures**

### Audit Trail

All GDPR-related actions are logged in `data_access_logs` table:
```sql
SELECT * FROM data_access_logs 
WHERE user_id = 123 
ORDER BY created_at DESC;
```

## ✅ Compliance Checklist

### GDPR Compliance
- [x] Right to access (data export)
- [x] Right to rectification (profile updates)
- [x] Right to erasure (account deletion)
- [x] Right to restrict processing (consent management)
- [x] Right to data portability (JSON/ZIP export)
- [x] Right to object (opt-out)
- [x] Consent management
- [x] Data retention policies
- [x] Audit logging
- [x] Privacy by design

### CCPA Compliance
- [x] Right to know
- [x] Right to delete
- [x] Right to opt-out
- [x] Right to non-discrimination
- [x] Privacy policy disclosure

### Security Compliance
- [x] Data encryption at rest
- [x] Data encryption in transit (TLS 1.3)
- [x] PII anonymization
- [x] Secure headers
- [x] Backup encryption
- [x] Access logging

---

**Compliance Status**: ✅ GDPR & CCPA Ready  
**Last Updated**: November 3, 2024

For compliance questions, contact legal@renthub.com

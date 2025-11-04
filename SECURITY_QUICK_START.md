# 🔐 Security Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 1: Run Migrations
```bash
cd C:\laragon\www\RentHub\backend
php artisan migrate
```

### Step 2: Seed Security Data
```bash
php artisan db:seed --class=SecuritySeeder
```

### Step 3: Register Security Routes
Add to `routes/api.php`:
```php
require __DIR__.'/security.php';
```

### Step 4: Configure Environment
Add to `.env`:
```env
# Security Configuration
ENCRYPT_DATA_AT_REST=true
FORCE_TLS=true
RATE_LIMITING_ENABLED=true
DDOS_PROTECTION_ENABLED=true
AUDIT_LOGGING_ENABLED=true
SECURITY_MONITORING_ENABLED=true

# GDPR/CCPA Compliance
GDPR_ENABLED=true
GDPR_RETENTION_DAYS=2555
CCPA_ENABLED=true

# Two-Factor Authentication
2FA_ENABLED=true

# Rate Limiting
RATE_LIMITER_DRIVER=redis
```

---

## 🧪 Quick Testing

### Test 1: Generate API Key
```bash
curl -X POST http://localhost/api/api-keys \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test API Key",
    "scopes": ["read", "write"],
    "expires_in_days": 90
  }'
```

**Expected Response:**
```json
{
  "message": "API key created successfully",
  "data": {
    "id": 1,
    "key": "rh_xxxxxxxxxxxxxxxxxxxxxxxx",
    "name": "Test API Key",
    "scopes": ["read", "write"],
    "created_at": "2025-01-03T17:00:00Z",
    "expires_at": "2025-04-03T17:00:00Z"
  }
}
```

---

### Test 2: OAuth 2.0 Authorization
```bash
# Step 1: Get Authorization Code
curl -X POST http://localhost/api/oauth/authorize \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "your_client_id",
    "redirect_uri": "https://yourapp.com/callback",
    "response_type": "code",
    "scope": "read write"
  }'

# Step 2: Exchange Code for Tokens
curl -X POST http://localhost/api/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "authorization_code",
    "code": "AUTHORIZATION_CODE_FROM_STEP_1",
    "client_id": "your_client_id",
    "client_secret": "your_client_secret",
    "redirect_uri": "https://yourapp.com/callback"
  }'
```

---

### Test 3: GDPR Data Export
```bash
curl -X POST http://localhost/api/gdpr/export \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "format": "json"
  }'
```

**Expected Response:**
```json
{
  "message": "Data export initiated",
  "download_url": "https://api.renthub.com/storage/gdpr/exports/user_data_123_1234567890.json"
}
```

---

### Test 4: Grant GDPR Consent
```bash
curl -X POST http://localhost/api/gdpr/consents \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "marketing",
    "details": {
      "email": true,
      "sms": false
    }
  }'
```

---

### Test 5: View Audit Trail
```bash
curl -X GET "http://localhost/api/security/audit-trail?days=30" \
  -H "Authorization: Bearer YOUR_USER_TOKEN"
```

---

### Test 6: Run Vulnerability Scan (Admin Only)
```bash
curl -X POST http://localhost/api/security/scan \
  -H "Authorization: Bearer ADMIN_TOKEN"
```

**Expected Response:**
```json
{
  "data": {
    "scan_date": "2025-01-03T17:00:00Z",
    "total_vulnerabilities": 0,
    "critical": 0,
    "high": 0,
    "medium": 0,
    "low": 0,
    "vulnerabilities": []
  }
}
```

---

## 🔧 Command-Line Tools

### Run Security Scan
```bash
php artisan security:scan

# With detailed report
php artisan security:scan --report

# JSON output
php artisan security:scan --json
```

### Clean Expired Data
```bash
# Clean expired API keys
php artisan security:clean --tokens

# Clean old audit logs
php artisan security:clean --logs

# Clean old data per retention policy
php artisan security:clean --data

# Clean everything
php artisan security:clean --all
```

---

## 🔑 Common Use Cases

### Use Case 1: Create OAuth Client
```php
use App\Models\OAuthClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

$client = OAuthClient::create([
    'client_id' => Str::random(32),
    'client_secret' => Hash::make($secret = Str::random(64)),
    'name' => 'My Application',
    'redirect_uri' => 'https://myapp.com/callback',
    'is_confidential' => true,
    'is_active' => true,
]);

// Save $secret securely - it won't be shown again
```

---

### Use Case 2: Check User Permissions
```php
use App\Services\Security\RBACService;

$rbac = app(RBACService::class);

// Check single permission
if ($rbac->hasPermission($user, 'property.create')) {
    // User can create properties
}

// Check any permission
if ($rbac->hasAnyPermission($user, ['property.edit', 'property.delete'])) {
    // User can edit OR delete properties
}

// Check all permissions
if ($rbac->hasAllPermissions($user, ['property.view', 'property.edit'])) {
    // User can view AND edit properties
}

// Get all user permissions
$permissions = $rbac->getUserPermissions($user);
```

---

### Use Case 3: Log Security Events
```php
use App\Services\Security\SecurityAuditService;

$audit = app(SecurityAuditService::class);

// Log successful login
$audit->logAuthentication($user, 'login', true);

// Log failed login
$audit->logAuthentication($user, 'login', false, [
    'reason' => 'invalid_password',
]);

// Log authorization failure
$audit->logAuthorizationFailure($user, 'property', 'delete');

// Log sensitive data access
$audit->logDataAccess($user, 'user_documents', 'download');

// Log data modification
$audit->logDataModification($user, 'user_profile', 'update', $oldData, $newData);
```

---

### Use Case 4: Encrypt Sensitive Data
```php
use App\Services\Security\EncryptionService;

$encryption = app(EncryptionService::class);

// Encrypt data
$encrypted = $encryption->encryptData($sensitiveData);

// Decrypt data
$decrypted = $encryption->decryptData($encrypted);

// Anonymize PII
$maskedEmail = $encryption->anonymizePII('user@example.com', 'mask');
// Output: "us**@ex****e.com"

$hashedSSN = $encryption->anonymizePII('123-45-6789', 'hash');
// Output: "a1b2c3d4e5f6g7h8"
```

---

### Use Case 5: Handle GDPR Requests
```php
use App\Services\Security\GDPRService;

$gdpr = app(GDPRService::class);

// Export user data
$downloadUrl = $gdpr->exportUserData($user, 'json');

// Request deletion (30-day grace period)
$gdpr->deleteUserData($user, gracePeriod: true);

// Immediate deletion (admin only)
$gdpr->deleteUserData($user, gracePeriod: false);

// Record consent
$gdpr->recordConsent($user, 'marketing', [
    'email' => true,
    'sms' => false,
]);

// Check consent
if ($gdpr->hasConsent($user, 'marketing')) {
    // Send marketing email
}

// Revoke consent
$gdpr->revokeConsent($user, 'marketing');
```

---

## 📊 Monitoring Dashboard Queries

### Get Failed Login Attempts (Last 24 Hours)
```sql
SELECT user_id, COUNT(*) as attempts, MAX(created_at) as last_attempt
FROM security_audit_logs
WHERE category = 'authentication'
  AND event = 'login'
  AND successful = false
  AND created_at > NOW() - INTERVAL 24 HOUR
GROUP BY user_id
HAVING attempts >= 3
ORDER BY attempts DESC;
```

### Get Active Security Incidents
```sql
SELECT type, severity, COUNT(*) as count
FROM security_incidents
WHERE status IN ('open', 'investigating')
GROUP BY type, severity
ORDER BY 
  FIELD(severity, 'critical', 'high', 'medium', 'low'),
  count DESC;
```

### Get Top API Key Users
```sql
SELECT u.email, ak.name, ak.usage_count, ak.last_used_at
FROM api_keys ak
JOIN users u ON ak.user_id = u.id
WHERE ak.is_active = true
ORDER BY ak.usage_count DESC
LIMIT 10;
```

### Get GDPR Requests Status
```sql
SELECT type, status, COUNT(*) as count
FROM gdpr_requests
WHERE created_at > NOW() - INTERVAL 30 DAY
GROUP BY type, status;
```

---

## 🔒 Security Best Practices

### 1. Password Requirements
- Minimum 8 characters
- Include uppercase and lowercase letters
- Include numbers and symbols
- Check against compromised password databases
- Rotate every 90 days
- Don't reuse last 5 passwords

### 2. API Security
- Always use HTTPS (TLS 1.3)
- Implement rate limiting
- Use API keys or OAuth tokens
- Validate and sanitize all inputs
- Log all API access

### 3. Data Protection
- Encrypt data at rest (AES-256-GCM)
- Encrypt data in transit (TLS 1.3)
- Anonymize PII when possible
- Implement data retention policies
- Support GDPR/CCPA compliance

### 4. Access Control
- Implement RBAC
- Follow principle of least privilege
- Regularly audit permissions
- Use multi-factor authentication for admins
- Monitor failed authorization attempts

### 5. Monitoring & Auditing
- Log all authentication events
- Log authorization failures
- Monitor for suspicious activity
- Set up security alerts
- Run regular vulnerability scans

---

## 🆘 Troubleshooting

### Issue: API Key Not Working
**Solution:**
```bash
# Check if key is expired
SELECT * FROM api_keys WHERE key = 'YOUR_KEY_HASH' AND expires_at > NOW();

# Check if key is active
SELECT * FROM api_keys WHERE key = 'YOUR_KEY_HASH' AND is_active = true;
```

### Issue: OAuth Token Expired
**Solution:**
```bash
# Use refresh token to get new access token
curl -X POST http://localhost/api/oauth/token \
  -d "grant_type=refresh_token" \
  -d "refresh_token=YOUR_REFRESH_TOKEN" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "client_secret=YOUR_CLIENT_SECRET"
```

### Issue: Rate Limit Exceeded
**Solution:**
```php
// Check rate limit status
$remaining = RateLimiter::remaining('api-' . $user->id, 60);

// Clear rate limit (admin only)
RateLimiter::clear('api-' . $user->id);
```

### Issue: Account Locked After Failed Logins
**Solution:**
```php
// Unlock user account
$user->update(['locked_until' => null, 'failed_login_attempts' => 0]);
```

---

## 📚 Additional Resources

- [Full Documentation](COMPREHENSIVE_SECURITY_IMPLEMENTATION.md)
- [API Reference](API_ENDPOINTS.md)
- [Security Configuration](backend/config/security.php)
- [Testing Guide](TESTING_GUIDE.md)

---

## ✅ Checklist

Before going to production:

- [ ] Run migrations
- [ ] Seed security data
- [ ] Configure environment variables
- [ ] Enable HTTPS (TLS 1.3)
- [ ] Set up Redis for rate limiting
- [ ] Configure email for security alerts
- [ ] Run vulnerability scan
- [ ] Review security audit logs
- [ ] Test OAuth flow
- [ ] Test API key generation
- [ ] Test GDPR compliance features
- [ ] Set up monitoring dashboard
- [ ] Document security policies
- [ ] Train team on security procedures

---

**Last Updated:** January 3, 2025  
**Version:** 1.0.0

# 🚀 Security Implementation - Deployment Guide

## 📋 Pre-Deployment Checklist

### System Requirements
- [ ] PHP 8.1 or higher
- [ ] Laravel 11.x
- [ ] MySQL 8.0 or PostgreSQL 13+
- [ ] Redis (for rate limiting & caching)
- [ ] Composer 2.x
- [ ] HTTPS/TLS 1.3 certificate

### Environment Setup
- [ ] Production server configured
- [ ] SSL/TLS certificate installed
- [ ] Redis server running
- [ ] Database server accessible
- [ ] Backup system configured
- [ ] Monitoring tools ready

---

## 🔧 Step-by-Step Deployment

### Step 1: Backup Current System
```bash
# Backup database
mysqldump -u root -p renthub > backup_before_security_$(date +%Y%m%d_%H%M%S).sql

# Backup application
cd C:\laragon\www\RentHub
git commit -am "Pre-security implementation backup"
git tag pre-security-v1.0
```

### Step 2: Pull Security Implementation
```bash
cd C:\laragon\www\RentHub\backend
```

### Step 3: Install Dependencies (if needed)
```bash
composer require firebase/php-jwt
composer require pragmarx/google2fa-laravel
```

### Step 4: Configure Environment
Add to `.env`:
```env
# === Security Configuration ===

# Encryption
ENCRYPT_DATA_AT_REST=true
FORCE_TLS=true

# Rate Limiting
RATE_LIMITING_ENABLED=true
RATE_LIMITER_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# DDoS Protection
DDOS_PROTECTION_ENABLED=true
DDOS_WHITELIST_IPS=
DDOS_BLACKLIST_IPS=

# Audit & Monitoring
AUDIT_LOGGING_ENABLED=true
SECURITY_MONITORING_ENABLED=true

# GDPR/CCPA Compliance
GDPR_ENABLED=true
GDPR_RETENTION_DAYS=2555
CCPA_ENABLED=true

# Two-Factor Authentication
2FA_ENABLED=true

# Security Alerts
SECURITY_ALERT_EMAIL=security@yourdomain.com
SECURITY_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Session Security
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

### Step 5: Update API Routes
Edit `backend/routes/api.php` and add at the end:
```php
// Security Routes
require __DIR__.'/security.php';
```

### Step 6: Run Database Migrations
```bash
cd C:\laragon\www\RentHub\backend

# Check migration status
php artisan migrate:status

# Run migrations
php artisan migrate --force

# Verify tables created
php artisan db:show
```

Expected new tables:
- oauth_clients
- oauth_access_tokens
- oauth_refresh_tokens
- api_keys
- roles
- permissions
- role_user
- permission_role
- permission_user
- security_audit_logs
- security_incidents
- gdpr_requests
- data_consents
- password_histories
- login_attempts

### Step 7: Seed Security Data
```bash
# Seed roles and permissions
php artisan db:seed --class=SecuritySeeder

# Verify seeding
php artisan tinker
>>> Role::count()  # Should return 5
>>> Permission::count()  # Should return 35+
```

### Step 8: Assign Roles to Existing Users
```bash
php artisan tinker

# Assign admin role to yourself
>>> use App\Services\Security\RBACService;
>>> $rbac = app(RBACService::class);
>>> $user = User::where('email', 'admin@yourdomain.com')->first();
>>> $rbac->assignRole($user, 'admin');

# Assign landlord role to existing landlords
>>> User::where('role', 'landlord')->each(function($user) use ($rbac) {
...     $rbac->assignRole($user, 'landlord');
... });

# Assign tenant role to existing tenants
>>> User::where('role', 'tenant')->each(function($user) use ($rbac) {
...     $rbac->assignRole($user, 'tenant');
... });
```

### Step 9: Register Middleware
Edit `backend/app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ... existing middleware
        \App\Http\Middleware\SecurityHeaders::class,
        \App\Http\Middleware\RateLimitMiddleware::class,
        \App\Http\Middleware\DDoSProtectionMiddleware::class,
        \App\Http\Middleware\SqlInjectionProtectionMiddleware::class,
        \App\Http\Middleware\XssProtectionMiddleware::class,
    ],
];

protected $routeMiddleware = [
    // ... existing middleware
    'api.key' => \App\Http\Middleware\CheckAPIKey::class,
    'permission' => \App\Http\Middleware\CheckPermission::class,
    'role' => \App\Http\Middleware\CheckRole::class,
    'rbac' => \App\Http\Middleware\AdvancedRBACMiddleware::class,
];
```

### Step 10: Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Regenerate optimized files
php artisan config:cache
php artisan route:cache
php artisan optimize
```

### Step 11: Run Security Scan
```bash
php artisan security:scan --report
```

Expected output:
```
Starting security vulnerability scan...

+------------------------+-------+
| Metric                 | Count |
+------------------------+-------+
| Total Vulnerabilities  | 0     |
| Critical               | 0     |
| High                   | 0     |
| Medium                 | 0     |
| Low                    | 0     |
+------------------------+-------+

✓ No vulnerabilities found!
```

### Step 12: Test Security Endpoints

#### Test 1: Health Check
```bash
curl http://localhost/api/health
```

#### Test 2: Generate API Key
```bash
curl -X POST http://localhost/api/api-keys \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Key","scopes":["read","write"],"expires_in_days":90}'
```

#### Test 3: Security Headers
```bash
curl -I http://localhost/api/properties
```

Verify headers:
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- Strict-Transport-Security: ...
- Content-Security-Policy: ...

#### Test 4: Rate Limiting
```bash
# Run multiple requests quickly
for i in {1..70}; do
  curl -s http://localhost/api/properties > /dev/null
  echo "Request $i"
done
```

Should see 429 (Too Many Requests) after limit exceeded.

### Step 13: Set Up Scheduled Tasks
Edit `backend/app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // ... existing schedules

    // Security maintenance tasks
    $schedule->command('security:clean --tokens')->daily();
    $schedule->command('security:clean --logs')->weekly();
    $schedule->command('security:scan')->weekly();
}
```

Update crontab:
```bash
* * * * * cd /path/to/renthub/backend && php artisan schedule:run >> /dev/null 2>&1
```

### Step 14: Configure Logging
Edit `backend/config/logging.php`:

```php
'channels' => [
    // ... existing channels

    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 365,
    ],

    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 365,
    ],
],
```

### Step 15: Set Up Monitoring
```bash
# Create monitoring dashboard
php artisan make:filament-page SecurityDashboard

# Or use existing monitoring tools
# Configure Prometheus/Grafana metrics
# Set up alerting rules
```

### Step 16: Create OAuth Client (Optional)
```bash
php artisan tinker

>>> use App\Models\OAuthClient;
>>> use Illuminate\Support\Str;
>>> use Illuminate\Support\Facades\Hash;

>>> $clientId = Str::random(32);
>>> $clientSecret = Str::random(64);
>>> OAuthClient::create([
...     'client_id' => $clientId,
...     'client_secret' => Hash::make($clientSecret),
...     'name' => 'RentHub Mobile App',
...     'redirect_uri' => 'https://app.renthub.com/callback',
...     'is_confidential' => true,
...     'is_active' => true,
... ]);

# Save $clientId and $clientSecret securely!
```

### Step 17: Final Verification
```bash
# Check database
php artisan db:show

# Check routes
php artisan route:list | grep security

# Check configuration
php artisan config:show security

# Run tests
php artisan test --filter=Security
```

---

## 🔍 Post-Deployment Verification

### Verify OAuth 2.0
```bash
# Test authorization endpoint
curl -X POST http://localhost/api/oauth/authorize \
  -H "Authorization: Bearer USER_TOKEN" \
  -d "client_id=YOUR_CLIENT_ID" \
  -d "redirect_uri=https://example.com/callback" \
  -d "response_type=code"
```

### Verify RBAC
```bash
php artisan tinker

>>> $user = User::first();
>>> $rbac = app(\App\Services\Security\RBACService::class);
>>> $rbac->getUserPermissions($user);
>>> $rbac->getUserRoles($user);
```

### Verify Audit Logging
```bash
# Check if logs are being created
tail -f storage/logs/audit.log
tail -f storage/logs/security.log

# Check database
mysql -u root -p renthub
> SELECT * FROM security_audit_logs ORDER BY created_at DESC LIMIT 10;
```

### Verify Rate Limiting
```bash
# Check Redis
redis-cli
> KEYS rate_limit:*
> TTL rate_limit:api:USER_ID
```

---

## 📊 Monitoring Setup

### Key Metrics to Monitor

1. **Authentication Events**
   - Failed login attempts
   - Account lockouts
   - Password resets
   - 2FA activations

2. **Authorization Events**
   - Permission denied
   - Role changes
   - Privilege escalation attempts

3. **Security Incidents**
   - Critical incidents
   - High severity incidents
   - Open incidents
   - Response times

4. **API Usage**
   - Rate limit hits
   - API key usage
   - OAuth token generation
   - Invalid requests

5. **GDPR Requests**
   - Data exports
   - Deletion requests
   - Consent changes

### Sample Monitoring Queries

```sql
-- Failed logins in last 24 hours
SELECT user_id, COUNT(*) as attempts
FROM security_audit_logs
WHERE category = 'authentication'
  AND successful = false
  AND created_at > NOW() - INTERVAL 24 HOUR
GROUP BY user_id
HAVING attempts >= 3;

-- Active security incidents
SELECT type, severity, COUNT(*) as count
FROM security_incidents
WHERE status = 'open'
GROUP BY type, severity;

-- Top API consumers
SELECT u.email, COUNT(*) as request_count
FROM security_audit_logs sal
JOIN users u ON sal.user_id = u.id
WHERE sal.created_at > NOW() - INTERVAL 1 DAY
GROUP BY u.id
ORDER BY request_count DESC
LIMIT 10;
```

---

## 🆘 Rollback Plan

If issues occur during deployment:

### Quick Rollback
```bash
# 1. Revert code
git checkout pre-security-v1.0

# 2. Restore database
mysql -u root -p renthub < backup_before_security_TIMESTAMP.sql

# 3. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 4. Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

### Partial Rollback (Keep database, disable features)
```env
# Disable security features in .env
RATE_LIMITING_ENABLED=false
DDOS_PROTECTION_ENABLED=false
AUDIT_LOGGING_ENABLED=false
SECURITY_MONITORING_ENABLED=false
```

---

## 🎓 Training Team

### For Developers
- Review `COMPREHENSIVE_SECURITY_IMPLEMENTATION.md`
- Understand OAuth 2.0 flow
- Learn RBAC system
- Practice using security services

### For Support Team
- How to handle security incidents
- How to unlock user accounts
- How to generate API keys for users
- GDPR request procedures

### For Admins
- Security dashboard usage
- Running vulnerability scans
- Reviewing audit logs
- Managing roles and permissions

---

## 📞 Support Contacts

- **Security Issues:** security@yourdomain.com
- **Emergency:** +1-XXX-XXX-XXXX
- **Slack:** #security-alerts

---

## ✅ Deployment Checklist

### Pre-Deployment
- [ ] Code reviewed
- [ ] Tests passed
- [ ] Database backed up
- [ ] Team notified
- [ ] Maintenance window scheduled

### During Deployment
- [ ] Environment configured
- [ ] Routes registered
- [ ] Migrations run
- [ ] Data seeded
- [ ] Middleware registered
- [ ] Caches cleared
- [ ] Security scan passed

### Post-Deployment
- [ ] Endpoints tested
- [ ] Monitoring configured
- [ ] Logs verified
- [ ] Performance checked
- [ ] Documentation updated
- [ ] Team trained

### Verification
- [ ] OAuth 2.0 working
- [ ] API keys working
- [ ] RBAC functional
- [ ] GDPR features working
- [ ] Rate limiting active
- [ ] Audit logs recording
- [ ] Security headers present
- [ ] Encryption working

---

## 🎉 Success Criteria

Deployment is successful when:
- ✅ All migrations completed
- ✅ No security vulnerabilities found
- ✅ All tests passing
- ✅ Monitoring active
- ✅ No errors in logs
- ✅ Performance acceptable
- ✅ Team trained
- ✅ Documentation complete

---

**Deployment Date:** _____________  
**Deployed By:** _____________  
**Verified By:** _____________  
**Status:** _____________

---

**Version:** 1.0.0  
**Last Updated:** January 3, 2025

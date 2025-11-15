# Sentry Integration Guide - RentHub

## 📋 Setup Instructions

### Step 1: Create Sentry Projects

#### Backend Project (Laravel)
1. Visit https://sentry.io/organizations/
2. Click "Create Project"
3. Platform: **Laravel**
4. Project name: **RentHub Backend**
5. Set alert frequency: **On every new issue**
6. Copy the DSN (will look like: `https://PUBLIC_KEY@o123456.ingest.sentry.io/PROJECT_ID`)

#### Frontend Project (Next.js)
1. Create second project
2. Platform: **Next.js**
3. Project name: **RentHub Frontend**
4. Copy the DSN

### Step 2: Configure Backend (Laravel)

#### Add to Forge Environment Variables:
```bash
SENTRY_LARAVEL_DSN=https://YOUR_PUBLIC_KEY@o123456.ingest.sentry.io/YOUR_PROJECT_ID
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.05
SENTRY_ENVIRONMENT=production
```

#### Update config/logging.php (already configured):
```php
'sentry' => [
    'driver' => 'sentry',
    'level' => env('LOG_LEVEL', 'error'),
],
```

#### Test Error Capture:
```bash
php artisan tinker
throw new \Exception('Sentry test error from backend');
```

### Step 3: Configure Frontend (Next.js)

#### Add to Vercel Environment Variables:
```bash
NEXT_PUBLIC_SENTRY_DSN=https://YOUR_PUBLIC_KEY@o123456.ingest.sentry.io/YOUR_PROJECT_ID
SENTRY_DSN=https://YOUR_PUBLIC_KEY@o123456.ingest.sentry.io/YOUR_PROJECT_ID
NEXT_PUBLIC_SENTRY_ENVIRONMENT=production
NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE=0.2
NEXT_PUBLIC_SENTRY_PROFILES_SAMPLE_RATE=0.1
NEXT_PUBLIC_SENTRY_REPLAYS_SESSION_SAMPLE_RATE=0.05
NEXT_PUBLIC_SENTRY_REPLAYS_ON_ERROR_SAMPLE_RATE=1.0
```

#### Sentry Config Files (already exist):
- `frontend/instrumentation.ts` - Server-side Sentry init
- `frontend/instrumentation.client.ts` - Client-side Sentry init
- `frontend/sentry.client.config.ts` - Client configuration
- `frontend/sentry.server.config.ts` - Server configuration
- `frontend/sentry.edge.config.ts` - Edge runtime configuration

#### Test Error Capture:
```javascript
// Add to any page temporarily
throw new Error('Sentry test error from frontend');
```

### Step 4: Configure Sampling Rates

#### Traces Sampling (Performance Monitoring)
- **Backend:** `0.1` (10% of transactions)
- **Frontend:** `0.2` (20% of transactions)
- **Rationale:** Higher rate for frontend to catch user-facing issues

#### Profiles Sampling (CPU/Memory Profiling)
- **Backend:** `0.05` (5% of traced transactions)
- **Frontend:** `0.1` (10% of traced transactions)
- **Rationale:** Expensive operation, keep low

#### Session Replays (Video-like recordings)
- **Normal Sessions:** `0.05` (5% of sessions)
- **Error Sessions:** `1.0` (100% of sessions with errors)
- **Rationale:** Capture all errors, sample normal usage

### Step 5: Set Up Alerts

#### Recommended Alerts:
1. **Error Spike Alert**
   - Condition: >10 errors in 1 minute
   - Action: Email + Slack notification

2. **Performance Degradation**
   - Condition: P95 response time >2 seconds
   - Action: Email notification

3. **High Error Rate**
   - Condition: Error rate >5% of traffic
   - Action: Email + PagerDuty (if using)

4. **New Issue Alert**
   - Condition: First occurrence of new error
   - Action: Email notification

### Step 6: Integration Features

#### Enable Features:
- ✅ **Performance Monitoring** - Track slow API calls
- ✅ **Session Replay** - Video-like error reproduction
- ✅ **Profiling** - CPU/memory usage insights
- ✅ **Cron Monitoring** - Track scheduled tasks
- ✅ **Release Tracking** - Associate errors with deployments

#### Add Release Tracking:
```bash
# In your deployment script
sentry-cli releases new "renthub@$(git rev-parse HEAD)"
sentry-cli releases set-commits "renthub@$(git rev-parse HEAD)" --auto
sentry-cli releases finalize "renthub@$(git rev-parse HEAD)"
```

### Step 7: Custom Context & Breadcrumbs

#### Backend - Add User Context:
```php
// In app/Http/Middleware/Authenticate.php or similar
\Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($user) {
    $scope->setUser([
        'id' => $user->id,
        'email' => $user->email,
        'username' => $user->name,
    ]);
});
```

#### Frontend - Add User Context:
```typescript
// In src/app/layout.tsx or auth hook
import * as Sentry from '@sentry/nextjs';

Sentry.setUser({
  id: user.id,
  email: user.email,
  username: user.name,
});
```

#### Add Custom Breadcrumbs:
```php
// Backend
\Sentry\addBreadcrumb(
    new \Sentry\Breadcrumb(
        \Sentry\Breadcrumb::LEVEL_INFO,
        \Sentry\Breadcrumb::TYPE_DEFAULT,
        'booking',
        'User created booking #123'
    )
);
```

```typescript
// Frontend
Sentry.addBreadcrumb({
  category: 'booking',
  message: 'User clicked "Book Now"',
  level: 'info',
});
```

### Step 8: Verification Checklist

- [ ] Backend DSN configured in Forge
- [ ] Frontend DSN configured in Vercel
- [ ] Test error captured in Sentry dashboard (backend)
- [ ] Test error captured in Sentry dashboard (frontend)
- [ ] Performance traces visible
- [ ] Session replays recording
- [ ] User context attached to events
- [ ] Alert rules configured
- [ ] Team members added to Sentry organization
- [ ] Integration with Slack/email working

### Step 9: Best Practices

#### Error Filtering:
```php
// config/sentry.php - Already configured
'before_send' => function (\Sentry\Event $event): ?\Sentry\Event {
    // Don't send validation errors
    if ($event->getLevel() === \Sentry\Severity::warning()) {
        return null;
    }
    return $event;
},
```

#### Performance Tips:
- Don't log sensitive data (passwords, tokens, credit cards)
- Use custom fingerprinting for grouped errors
- Set appropriate sample rates (adjust based on traffic)
- Use breadcrumbs sparingly (max 100 per event)

#### Budget Awareness:
- **Free tier:** 5,000 errors/month, 10,000 transactions
- Monitor quota usage in Sentry dashboard
- Adjust sampling rates if approaching limits
- Consider upgrading for production apps with high traffic

### Step 10: Monitoring Dashboard

#### Key Metrics to Watch:
1. **Error Rate** - Should stay <1%
2. **P95 Response Time** - Target <500ms for API, <2s for pages
3. **Apdex Score** - Target >0.9 (satisfied users)
4. **Session Crashes** - Target <0.1%

#### Weekly Review:
- Check top 10 errors
- Review performance trends
- Identify slow endpoints
- Analyze user feedback with session replays

---

## 🚀 Quick Start Commands

```bash
# Backend - Test Sentry
ssh forge@178.128.135.24
cd /home/forge/renthub-tbj7yxj7.on-forge.com
php artisan tinker --execute="throw new Exception('Test Sentry Backend');"

# Frontend - Test in browser console
throw new Error('Test Sentry Frontend');

# Check Sentry dashboard
open https://sentry.io/organizations/YOUR_ORG/issues/
```

---

## 📞 Support

- **Sentry Docs:** https://docs.sentry.io/
- **Laravel Integration:** https://docs.sentry.io/platforms/php/guides/laravel/
- **Next.js Integration:** https://docs.sentry.io/platforms/javascript/guides/nextjs/
- **Support:** https://sentry.io/support/

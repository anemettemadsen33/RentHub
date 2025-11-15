# Production Secrets & Keys Checklist

**Status:** Pre-Production Audit  
**Last Updated:** November 15, 2025  
**Priority:** 🔴 CRITICAL - Must complete before Monday deployment

---

## 🔐 Backend Secrets (Laravel Forge Environment Variables)

### ✅ Application Core
- [ ] **APP_KEY** - Generate: `php artisan key:generate --show`
  - Current: ❌ Not set in production
  - Action: Run on Forge server, add to `.env`
  
### ✅ Database
- [x] **DB_PASSWORD** - Already configured by Forge
- [x] **DB_DATABASE** - forge (default)
- [x] **DB_USERNAME** - forge (default)

### 🔴 Cache & Redis
- [ ] **REDIS_PASSWORD** - Generate strong password
  - Current: ❌ Default "secret" (INSECURE!)
  - Action: Rotate via Forge Redis config
  - Command: Add to Forge Environment tab

### 🔴 Mail (SendGrid)
- [ ] **MAIL_PASSWORD** (SendGrid API Key)
  - Current: ⚠️ Previously leaked (in setup-forge-production.sh)
  - Status: **MUST ROTATE IMMEDIATELY**
  - Steps:
    1. Login to SendGrid Dashboard
    2. API Keys → Create API Key
    3. Set permissions: Mail Send (Full Access)
    4. Copy key (shown only once!)
    5. Add to Forge Environment: `MAIL_PASSWORD=SG.new_key_here`
    6. Revoke old key: `SG.4p9fVE7...` (leaked key)

### 🔴 AWS S3
- [ ] **AWS_ACCESS_KEY_ID**
- [ ] **AWS_SECRET_ACCESS_KEY**
  - Current: ❌ Not configured
  - Action:
    1. Create IAM user: `renthub-production-s3`
    2. Attach policy: `AmazonS3FullAccess` (or custom bucket-only policy)
    3. Generate access keys
    4. Store in Forge Environment Variables

### 🔴 Search (Meilisearch)
- [ ] **MEILISEARCH_KEY**
  - Current: ❌ Default "masterKey" (INSECURE!)
  - Action: Generate random 64-char key
  - Command: `openssl rand -base64 48`

### 🔴 Broadcasting (Reverb)
- [ ] **REVERB_APP_KEY** - Random 32 chars
- [ ] **REVERB_APP_SECRET** - Random 64 chars
  - Generate: `openssl rand -base64 32` and `openssl rand -base64 48`

### 🔴 Pusher Beams (Web Push)
- [ ] **PUSHER_BEAMS_INSTANCE_ID**
- [ ] **PUSHER_BEAMS_SECRET_KEY**
  - Get from: https://dashboard.pusher.com/beams
  - Create instance: "RentHub Production"

### 🔴 Payments (Stripe)
- [ ] **STRIPE_KEY** (pk_live_...)
- [ ] **STRIPE_SECRET** (sk_live_...)
- [ ] **STRIPE_WEBHOOK_SECRET** (whsec_...)
  - Current: ❌ Using test keys
  - Action:
    1. Complete Stripe account verification
    2. Enable production mode
    3. Get live keys from Dashboard → Developers → API keys
    4. Create webhook endpoint: `https://renthub-tbj7yxj7.on-forge.com/webhook/stripe`
    5. Events to subscribe: `payment_intent.*`, `charge.*`, `customer.*`

### 🔴 SMS (Twilio)
- [ ] **TWILIO_SID**
- [ ] **TWILIO_AUTH_TOKEN**
- [ ] **TWILIO_PHONE_NUMBER**
  - Current: ❌ Not configured
  - Action: Purchase production phone number, get credentials

### 🔴 OAuth (Social Login)
- [ ] **GOOGLE_CLIENT_ID**
- [ ] **GOOGLE_CLIENT_SECRET**
  - Redirect URI: `https://renthub-tbj7yxj7.on-forge.com/auth/google/callback`
  
- [ ] **FACEBOOK_CLIENT_ID**
- [ ] **FACEBOOK_CLIENT_SECRET**
  - Redirect URI: `https://renthub-tbj7yxj7.on-forge.com/auth/facebook/callback`

### 🔴 Monitoring (Sentry)
- [ ] **SENTRY_LARAVEL_DSN**
  - Current: ❌ Not configured
  - Action:
    1. Create project: https://sentry.io/organizations/renthub/projects/new/
    2. Platform: Laravel
    3. Copy DSN
    4. Add to Forge Environment

### 🔴 Web Push (VAPID)
- [ ] **VAPID_PUBLIC_KEY**
- [ ] **VAPID_PRIVATE_KEY**
  - Generate:
    ```php
    php artisan tinker
    \Minishlink\WebPush\VAPID::createVapidKeys()
    ```

---

## 🌐 Frontend Secrets (Vercel Environment Variables)

### ✅ Already Configured
- [x] **NEXT_PUBLIC_SITE_URL** - https://renthub.international
- [x] **NEXT_PUBLIC_APP_URL** - https://renthub.international
- [x] **NEXT_PUBLIC_API_BASE_URL** - Set for all environments

### 🔴 Missing - Add to Vercel
- [ ] **NEXT_PUBLIC_REVERB_KEY** - Match backend `REVERB_APP_KEY`
- [ ] **NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID** - Match backend
- [ ] **NEXT_PUBLIC_MAPBOX_TOKEN** - Get from https://account.mapbox.com/access-tokens/
- [ ] **NEXT_PUBLIC_GOOGLE_MAPS_API_KEY** - Get from Google Cloud Console
- [ ] **NEXT_PUBLIC_SENTRY_DSN** - Same as backend Sentry DSN
- [ ] **NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY** (pk_live_...) - From Stripe Dashboard
- [ ] **NEXT_PUBLIC_GA_TRACKING_ID** - Google Analytics 4 property ID

---

## 🔄 Secret Rotation Schedule

| Secret Type | Rotation Frequency | Last Rotated | Next Due |
|-------------|-------------------|--------------|----------|
| APP_KEY | On compromise | Never | ⚠️ Generate now |
| Database passwords | 90 days | Unknown | ⚠️ Audit |
| Redis password | 90 days | Never | ⚠️ Set now |
| SendGrid API key | **IMMEDIATE** | Never | 🔴 LEAKED - rotate now! |
| AWS keys | 90 days | Never | ⚠️ Generate |
| Stripe keys | On compromise | N/A | After verification |
| OAuth secrets | Annually | N/A | After setup |
| VAPID keys | On compromise | Never | Generate once |

---

## 📋 Pre-Deployment Actions

### Immediate (Before Monday)
1. **Rotate SendGrid API key** (leaked in git history)
2. Generate **APP_KEY** on Forge server
3. Set **REDIS_PASSWORD** (not default "secret")
4. Generate **REVERB_APP_KEY** and **REVERB_APP_SECRET**
5. Generate **VAPID keys** for web push
6. Create **Sentry project** and get DSN

### Short-term (This Week)
1. Complete **Stripe** account verification → get live keys
2. Set up **AWS S3** bucket + IAM user
3. Configure **Meilisearch** master key
4. Set up **Pusher Beams** instance
5. Create **Google/Facebook OAuth** apps
6. Purchase **Twilio** phone number (if SMS needed)

### Medium-term (Next 2 Weeks)
1. Implement **AWS Secrets Manager** integration
2. Set up **automated secret rotation** (DB, Redis)
3. Enable **audit logging** for secret access
4. Document **incident response** for compromised secrets

---

## 🛡️ Security Best Practices

### ✅ Do
- Store secrets in Forge/Vercel Environment Variables (encrypted at rest)
- Use different keys for dev/staging/production
- Enable 2FA on all service accounts (Stripe, SendGrid, AWS, etc.)
- Limit AWS IAM permissions to minimum required
- Set up alert notifications for secret access
- Rotate secrets on schedule or compromise

### ❌ Don't
- Commit secrets to git (even in private repos)
- Share secrets via Slack/email/docs
- Use same password across services
- Keep default passwords ("secret", "password", etc.)
- Log secrets in application logs
- Store secrets in plain text files on servers

---

## 🚨 Leaked Secrets - Immediate Actions

### SendGrid API Key (SG.4p9fVE7...)
**Status:** 🔴 LEAKED in `setup-forge-production.sh` (removed in commit 8e34e94)

**Actions:**
1. ✅ File removed from repo
2. ⚠️ Git history still contains key - consider `git filter-repo`
3. 🔴 **Rotate key ASAP** (even though repo is private)
4. ✅ Add to `.gitignore`: `**/*production*.sh`

**Steps to rotate:**
```bash
# 1. Login to SendGrid
# 2. API Keys → Create new key
# 3. Name: "RentHub Production - Nov 2025"
# 4. Permissions: Mail Send (Full Access)
# 5. Copy key
# 6. Add to Forge:
#    Environment → MAIL_PASSWORD=SG.new_key_here
# 7. Test email send
# 8. Revoke old key
```

---

## 📞 Contacts for Secret Issues

- **Forge Support:** forge@laravel.com
- **Vercel Support:** https://vercel.com/support
- **Stripe Support:** https://support.stripe.com/
- **SendGrid Support:** https://sendgrid.com/contact/
- **AWS Support:** AWS Console → Support

---

**Next Steps:**
1. Review this checklist with team
2. Assign owners for each secret category
3. Set calendar reminders for rotation schedule
4. Test all integrations after setting production secrets
5. Document secret retrieval process for on-call engineers

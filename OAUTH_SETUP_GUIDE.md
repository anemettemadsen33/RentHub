# OAuth Social Login Setup Guide

## ✅ Current Status

**Backend Infrastructure:**
- ✅ Laravel Socialite installed (v5.23.1)
- ✅ SocialAuthController implemented
- ✅ SocialAccount model exists
- ✅ API routes configured:
  - `GET /api/v1/auth/{provider}/redirect` - Initiate OAuth
  - `GET /api/v1/auth/{provider}/callback` - Handle callback
  - `POST /api/v1/auth/{provider}/link` - Link to existing account
  - `DELETE /api/v1/auth/{provider}/unlink` - Unlink provider

**Frontend:**
- ✅ OAuth callback page exists (`/auth/callback`)
- ✅ Handles token storage and user redirect

**Supported Providers:**
- Google
- Facebook
- GitHub (bonus)

## 🔧 Setup Instructions

### 1. Google OAuth Setup

#### A. Create Google Cloud Project

1. **Go to Google Cloud Console:**
   - Visit: https://console.cloud.google.com/
   - Sign in with your Google account

2. **Create New Project:**
   - Click "Select a project" → "New Project"
   - Name: `RentHub` or `RentHub Development`
   - Click "Create"

3. **Enable Google+ API:**
   - Go to "APIs & Services" → "Enable APIs and Services"
   - Search for "Google+ API"
   - Click "Enable"

4. **Configure OAuth Consent Screen:**
   - Go to "APIs & Services" → "OAuth consent screen"
   - User Type: **External** (for testing) or **Internal** (for organization)
   - App name: `RentHub`
   - User support email: Your email
   - Developer contact: Your email
   - Scopes: Add `email`, `profile`, `openid`
   - Test users: Add your test email addresses
   - Save and continue

5. **Create OAuth 2.0 Credentials:**
   - Go to "APIs & Services" → "Credentials"
   - Click "Create Credentials" → "OAuth 2.0 Client ID"
   - Application type: **Web application**
   - Name: `RentHub Web Client`
   - Authorized JavaScript origins:
     ```
     http://localhost:8000
     http://localhost:3001
     ```
   - Authorized redirect URIs:
     ```
     http://localhost:8000/api/v1/auth/google/callback
     http://localhost:3001/auth/callback
     ```
   - Click "Create"
   - **Copy the Client ID and Client Secret!**

6. **Update Backend .env:**
   ```env
   GOOGLE_CLIENT_ID=your_google_client_id_here
   GOOGLE_CLIENT_SECRET=your_google_client_secret_here
   GOOGLE_REDIRECT_URI="${APP_URL}/api/v1/auth/google/callback"
   ```

7. **Update Frontend .env.local:**
   ```env
   NEXT_PUBLIC_GOOGLE_CLIENT_ID=your_google_client_id_here
   ```

### 2. Facebook OAuth Setup

#### A. Create Facebook App

1. **Go to Facebook Developers:**
   - Visit: https://developers.facebook.com/
   - Sign in with your Facebook account

2. **Create New App:**
   - Click "My Apps" → "Create App"
   - Use case: **Consumer** or **Business**
   - Display name: `RentHub`
   - Contact email: Your email
   - Click "Create App"

3. **Add Facebook Login Product:**
   - In dashboard, find "Facebook Login"
   - Click "Set Up"
   - Select **Web** platform

4. **Configure Facebook Login:**
   - Go to "Facebook Login" → "Settings"
   - Valid OAuth Redirect URIs:
     ```
     http://localhost:8000/api/v1/auth/facebook/callback
     http://localhost:3001/auth/callback
     ```
   - Save changes

5. **Get App Credentials:**
   - Go to "Settings" → "Basic"
   - Copy **App ID** (this is your Client ID)
   - Copy **App Secret** (click "Show" to reveal)

6. **Configure App Domains:**
   - In "Settings" → "Basic"
   - App Domains: `localhost`
   - Save changes

7. **Update Backend .env:**
   ```env
   FACEBOOK_CLIENT_ID=your_facebook_app_id_here
   FACEBOOK_CLIENT_SECRET=your_facebook_app_secret_here
   FACEBOOK_REDIRECT_URI="${APP_URL}/api/v1/auth/facebook/callback"
   ```

8. **Update Frontend .env.local:**
   ```env
   NEXT_PUBLIC_FACEBOOK_CLIENT_ID=your_facebook_app_id_here
   ```

### 3. GitHub OAuth Setup (Optional Bonus)

1. **Go to GitHub Settings:**
   - Visit: https://github.com/settings/developers
   - Click "OAuth Apps" → "New OAuth App"

2. **Register Application:**
   - Application name: `RentHub Development`
   - Homepage URL: `http://localhost:3001`
   - Authorization callback URL: `http://localhost:8000/api/v1/auth/github/callback`
   - Click "Register application"

3. **Get Credentials:**
   - Copy **Client ID**
   - Click "Generate a new client secret"
   - Copy **Client Secret**

4. **Update Backend .env:**
   ```env
   GITHUB_CLIENT_ID=your_github_client_id_here
   GITHUB_CLIENT_SECRET=your_github_client_secret_here
   GITHUB_REDIRECT_URI="${APP_URL}/api/v1/auth/github/callback"
   ```

5. **Update Frontend .env.local:**
   ```env
   NEXT_PUBLIC_GITHUB_CLIENT_ID=your_github_client_id_here
   ```

## 🧪 Testing OAuth Login

### Backend Test (API Only)

```bash
# Test Google OAuth redirect
curl http://localhost:8000/api/v1/auth/google/redirect

# Test Facebook OAuth redirect
curl http://localhost:8000/api/v1/auth/facebook/redirect
```

### Frontend Integration Test

1. **Add Social Login Buttons to Login Page:**
   
   The login page already has the callback handler. Just add buttons:
   
   ```tsx
   const handleGoogleLogin = () => {
     window.location.href = 'http://localhost:8000/api/v1/auth/google/redirect';
   };
   
   const handleFacebookLogin = () => {
     window.location.href = 'http://localhost:8000/api/v1/auth/facebook/redirect';
   };
   ```

2. **Test Flow:**
   - Click "Login with Google"
   - Redirected to Google consent screen
   - Approve permissions
   - Redirected back to `/auth/callback?token=...`
   - Automatically logged in and redirected to dashboard

### Database Verification

```bash
cd backend
php artisan tinker

# Check social accounts created
App\Models\SocialAccount::with('user')->get();

# Check user created via OAuth
App\Models\User::whereNotNull('email_verified_at')
    ->whereHas('socialAccounts')
    ->get();
```

## 📊 Database Schema

The `social_accounts` table stores:
- `user_id` - Link to users table
- `provider` - 'google', 'facebook', 'github'
- `provider_id` - User ID from OAuth provider
- `access_token` - OAuth access token
- `refresh_token` - OAuth refresh token (if available)
- `expires_at` - Token expiration
- `provider_data` - Raw OAuth user data (JSON)

## 🔐 Security Notes

### Production Requirements

1. **Use HTTPS:**
   - Update redirect URIs to `https://yourdomain.com/...`
   - OAuth providers require HTTPS in production

2. **Update App URLs:**
   ```env
   APP_URL=https://yourdomain.com
   FRONTEND_URL=https://yourdomain.com
   ```

3. **Whitelist Production Domains:**
   - Google: Add production URL to authorized origins
   - Facebook: Add production domain to app domains

4. **Enable App Review:**
   - Google: Submit for verification if using sensitive scopes
   - Facebook: Submit for app review to make public

5. **Rotate Secrets:**
   - Never commit OAuth secrets to Git
   - Use different credentials for dev/staging/production

### Rate Limits

- **Google:** 10,000 requests per day (free tier)
- **Facebook:** App-based limits, typically 200 calls/hour per user
- **GitHub:** 5,000 requests per hour (authenticated)

## 🚀 Quick Start (Development Mode)

**Minimum viable setup for local testing:**

1. **Google OAuth only** (fastest):
   ```bash
   # Get credentials from: https://console.cloud.google.com/
   # Update backend/.env with GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET
   # Restart Laravel: php artisan serve
   ```

2. **Test in browser:**
   ```
   Visit: http://localhost:8000/api/v1/auth/google/redirect
   Should redirect to Google login
   ```

3. **Add login button** (frontend):
   ```tsx
   <button onClick={() => window.location.href = 'http://localhost:8000/api/v1/auth/google/redirect'}>
     Login with Google
   </button>
   ```

## ⚠️ Common Issues

### "redirect_uri_mismatch"
- **Problem:** OAuth provider rejects redirect
- **Solution:** Ensure redirect URI in provider console exactly matches backend route
- **Check:** 
  - Google: `http://localhost:8000/api/v1/auth/google/callback`
  - Facebook: `http://localhost:8000/api/v1/auth/facebook/callback`

### "App Not Configured"
- **Problem:** Provider credentials missing
- **Solution:** Verify `.env` has `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, etc.
- **Restart:** `php artisan config:clear` then `php artisan serve`

### "Invalid Client"
- **Problem:** Wrong client ID or secret
- **Solution:** Double-check credentials copied correctly from provider console

### CORS Errors
- **Problem:** Frontend can't call backend OAuth routes
- **Solution:** Ensure `config/cors.php` allows `localhost:3001`

## 📝 Frontend Implementation Examples

### React/Next.js Social Login Button Component

```tsx
// components/SocialLoginButtons.tsx
export function SocialLoginButtons() {
  const apiUrl = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000';
  
  return (
    <div className="space-y-2">
      <button
        onClick={() => window.location.href = `${apiUrl}/api/v1/auth/google/redirect`}
        className="w-full btn btn-outline"
      >
        <svg className="w-5 h-5" viewBox="0 0 24 24">
          {/* Google icon SVG */}
        </svg>
        Continue with Google
      </button>
      
      <button
        onClick={() => window.location.href = `${apiUrl}/api/v1/auth/facebook/redirect`}
        className="w-full btn btn-outline"
      >
        <svg className="w-5 h-5" viewBox="0 0 24 24">
          {/* Facebook icon SVG */}
        </svg>
        Continue with Facebook
      </button>
    </div>
  );
}
```

## ✅ Completion Checklist

- [ ] Google Cloud project created
- [ ] Google OAuth credentials obtained
- [ ] Facebook Developer app created
- [ ] Facebook OAuth credentials obtained
- [ ] Backend `.env` updated with all credentials
- [ ] Frontend `.env.local` updated
- [ ] Services restarted (`php artisan serve`, `npm run dev`)
- [ ] Social login buttons added to frontend
- [ ] Google login tested successfully
- [ ] Facebook login tested successfully
- [ ] User account created in database
- [ ] Social account linked in `social_accounts` table

## 🎯 Summary

**OAuth is READY to use** - just needs credentials! The entire backend and database infrastructure is already implemented. You only need to:

1. Create Google/Facebook developer accounts (15 min each)
2. Get OAuth credentials (copy/paste)
3. Update `.env` files
4. Test login flow

**No code changes required!** The system is production-ready once credentials are added.

---

**Time to setup:** ~30 minutes for both providers  
**Difficulty:** Easy (just configuration, no coding)  
**Priority:** Optional for Monday, but recommended for better UX

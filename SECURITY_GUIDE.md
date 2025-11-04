# 🔐 Security Guide - RentHub

## Overview

Complete security implementation including OAuth 2.0, JWT authentication, RBAC (Role-Based Access Control), API key management, and session management for RentHub platform.

## 📋 Table of Contents

- [Authentication Methods](#authentication-methods)
- [OAuth 2.0 Implementation](#oauth-20-implementation)
- [JWT Token Strategy](#jwt-token-strategy)
- [Role-Based Access Control](#role-based-access-control)
- [API Key Management](#api-key-management)
- [Session Management](#session-management)
- [Security Best Practices](#security-best-practices)

## 🔑 Authentication Methods

### 1. Email/Password Authentication
Traditional authentication with secure password hashing.

**Features:**
- ✅ Bcrypt password hashing
- ✅ Password strength validation
- ✅ Password reset functionality
- ✅ Rate limiting on login attempts

**Endpoints:**
```
POST /api/auth/register
POST /api/auth/login
POST /api/auth/change-password
POST /api/auth/forgot-password
POST /api/auth/reset-password
```

### 2. OAuth 2.0 Social Login
Support for Google, Facebook, and GitHub authentication.

**Supported Providers:**
- Google OAuth 2.0
- Facebook Login
- GitHub OAuth

**Endpoints:**
```
GET  /api/auth/oauth/{provider}/redirect
POST /api/auth/oauth/{provider}/callback
```

### 3. API Key Authentication
For machine-to-machine and third-party integrations.

**Features:**
- ✅ Secure key generation
- ✅ Permission scoping
- ✅ IP whitelisting
- ✅ Expiration dates
- ✅ Usage tracking

**Endpoints:**
```
GET    /api/api-keys
POST   /api/api-keys
PATCH  /api/api-keys/{id}
DELETE /api/api-keys/{id}
POST   /api/api-keys/{id}/rotate
```

## 🌐 OAuth 2.0 Implementation

### Configuration

Add to `.env`:
```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/oauth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your_facebook_app_id
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret
FACEBOOK_REDIRECT_URI=http://localhost:8000/api/auth/oauth/facebook/callback

# GitHub OAuth
GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://localhost:8000/api/auth/oauth/github/callback
```

### Usage Example

**Frontend Flow:**

```javascript
// 1. Get authorization URL
const response = await fetch('/api/auth/oauth/google/redirect');
const { url } = await response.json();

// 2. Redirect user to OAuth provider
window.location.href = url;

// 3. Handle callback
const params = new URLSearchParams(window.location.search);
const code = params.get('code');
const state = params.get('state');

const authResponse = await fetch('/api/auth/oauth/google/callback', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ code, state })
});

const { user, tokens } = await authResponse.json();
```

**Backend Service Usage:**

```php
use App\Services\Auth\OAuthService;

$oauthService = app(OAuthService::class);

// Get authorization URL
$url = $oauthService->getAuthorizationUrl('google', $redirectUri);

// Handle callback
$user = $oauthService->handleCallback('google', $code, $state);

// Revoke provider
$oauthService->revokeProvider($user, 'google');
```

### Supported Scopes

**Google:**
- `email` - User's email address
- `profile` - Basic profile information

**Facebook:**
- `email` - User's email address
- `public_profile` - Public profile information

**GitHub:**
- `user:email` - User's email address
- `read:user` - User profile data

## 🎫 JWT Token Strategy

### Token Types

1. **Access Token**
   - Short-lived (15 minutes)
   - Used for API requests
   - Contains user info & permissions

2. **Refresh Token**
   - Long-lived (30 days)
   - Used to obtain new access tokens
   - Stored in database
   - Can be revoked

### Token Structure

**Access Token Payload:**
```json
{
  "iss": "https://renthub.com",
  "sub": 123,
  "iat": 1699012345,
  "exp": 1699013245,
  "jti": "550e8400-e29b-41d4-a716-446655440000",
  "type": "access",
  "user": {
    "id": 123,
    "email": "user@example.com",
    "role": "tenant"
  }
}
```

### Usage Example

**Login & Get Tokens:**

```javascript
const response = await fetch('/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});

const { user, tokens } = await response.json();
/*
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "refresh_token": "9f1a2b3c4d5e6f7g8h9i...",
  "token_type": "Bearer",
  "expires_in": 900
}
*/

// Store tokens securely
localStorage.setItem('access_token', tokens.access_token);
localStorage.setItem('refresh_token', tokens.refresh_token);
```

**Use Access Token:**

```javascript
const response = await fetch('/api/properties', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('access_token')}`
  }
});
```

**Refresh Token:**

```javascript
const response = await fetch('/api/auth/refresh', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    refresh_token: localStorage.getItem('refresh_token')
  })
});

const { access_token } = await response.json();
localStorage.setItem('access_token', access_token);
```

**Logout:**

```javascript
await fetch('/api/auth/logout', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    refresh_token: localStorage.getItem('refresh_token')
  })
});

localStorage.removeItem('access_token');
localStorage.removeItem('refresh_token');
```

### Configuration

Add to `config/auth.php`:
```php
'jwt' => [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'algorithm' => 'HS256',
    'access_token_ttl' => 900, // 15 minutes
    'refresh_token_ttl' => 2592000, // 30 days
],
```

## 👥 Role-Based Access Control (RBAC)

### Roles & Permissions

**Default Roles:**

1. **Admin** - Full system access
2. **Landlord** - Property owner
3. **Tenant** - Property renter
4. **Guest** - Basic user

**Permission Groups:**

| Group | Permissions |
|-------|-------------|
| **Properties** | `properties.view`, `properties.create`, `properties.update`, `properties.delete` |
| **Bookings** | `bookings.view`, `bookings.create`, `bookings.update`, `bookings.cancel` |
| **Users** | `users.view`, `users.create`, `users.update`, `users.delete` |
| **Payments** | `payments.view`, `payments.process`, `payments.refund` |

### Role-Permission Matrix

| Role | Properties | Bookings | Users | Payments |
|------|-----------|----------|-------|----------|
| **Admin** | All | All | All | All |
| **Landlord** | CRUD own | View, Manage | - | View |
| **Tenant** | View | Create, View own | - | View |
| **Guest** | View | - | - | - |

### Usage in Routes

**Protect by Permission:**

```php
Route::middleware(['auth:api', 'permission:properties.create'])
    ->post('/properties', [PropertyController::class, 'store']);

Route::middleware(['auth:api', 'permission:properties.update,properties.delete'])
    ->put('/properties/{id}', [PropertyController::class, 'update']);
```

**Protect by Role:**

```php
Route::middleware(['auth:api', 'role:admin,landlord'])
    ->get('/admin/dashboard', [AdminController::class, 'dashboard']);
```

### Usage in Controllers

```php
use App\Services\Auth\RBACService;

class PropertyController extends Controller
{
    public function __construct(
        protected RBACService $rbacService
    ) {}

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$this->rbacService->hasPermission($user, 'properties.create')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        // Create property...
    }
}
```

### Programmatic Usage

```php
use App\Services\Auth\RBACService;

$rbacService = app(RBACService::class);
$user = auth()->user();

// Check single permission
if ($rbacService->hasPermission($user, 'properties.create')) {
    // User can create properties
}

// Check any permission
if ($rbacService->hasAnyPermission($user, ['properties.update', 'properties.delete'])) {
    // User can update OR delete
}

// Check all permissions
if ($rbacService->hasAllPermissions($user, ['bookings.view', 'bookings.create'])) {
    // User can view AND create bookings
}

// Check role
if ($rbacService->hasRole($user, 'admin')) {
    // User is admin
}

// Assign role
$rbacService->assignRole($user, 'landlord');

// Grant permission
$rbacService->grantPermission($user, 'properties.create');
```

### Seeding Roles & Permissions

```php
use App\Services\Auth\RBACService;

$rbacService = app(RBACService::class);
$rbacService->seedDefaults();
```

Or via artisan:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

## 🔑 API Key Management

### Creating API Keys

**Via API:**

```javascript
const response = await fetch('/api/api-keys', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${accessToken}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'Production API Key',
    permissions: ['properties.view', 'bookings.create'],
    expires_at: '2025-12-31',
    ip_whitelist: '192.168.1.1,10.0.0.1'
  })
});

const { api_key, plain_key } = await response.json();
// Save plain_key securely - it won't be shown again!
```

**Via Service:**

```php
use App\Services\Auth\APIKeyService;

$apiKeyService = app(APIKeyService::class);

$apiKey = $apiKeyService->createKey(
    user: $user,
    name: 'Production API Key',
    permissions: ['properties.view', 'bookings.create'],
    expiresAt: Carbon::parse('2025-12-31'),
    ipWhitelist: '192.168.1.1,10.0.0.1'
);

// $apiKey->plain_key is only available at creation
```

### Using API Keys

**In Requests:**

```bash
# Via Header (recommended)
curl -H "X-API-Key: rh_abc123..." https://api.renthub.com/properties

# Via Query Parameter
curl https://api.renthub.com/properties?api_key=rh_abc123...
```

**In Routes:**

```php
Route::middleware(['api_key:properties.view'])
    ->get('/properties', [PropertyController::class, 'index']);
```

### Managing API Keys

**List Keys:**
```bash
GET /api/api-keys
```

**Update Key:**
```bash
PATCH /api/api-keys/{id}
{
  "name": "Updated Name",
  "permissions": ["properties.view"],
  "active": true
}
```

**Revoke Key:**
```bash
DELETE /api/api-keys/{id}
```

**Rotate Key:**
```bash
POST /api/api-keys/{id}/rotate
```

**Get Statistics:**
```bash
GET /api/api-keys/{id}/stats
```

### API Key Features

**Permission Scoping:**
```json
{
  "permissions": [
    "properties.view",
    "properties.create",
    "bookings.view"
  ]
}
```

**IP Whitelisting:**
```json
{
  "ip_whitelist": "192.168.1.1,10.0.0.1,172.16.0.0/24"
}
```

**Expiration:**
```json
{
  "expires_at": "2025-12-31T23:59:59Z"
}
```

**Usage Tracking:**
```json
{
  "usage_count": 1543,
  "last_used_at": "2024-11-03T14:30:00Z"
}
```

## 🔒 Session Management

### Refresh Token Management

**Active Sessions:**

```php
use App\Models\RefreshToken;

// Get user's active sessions
$sessions = RefreshToken::where('user_id', $user->id)
    ->where('revoked', false)
    ->where('expires_at', '>', now())
    ->get();

foreach ($sessions as $session) {
    echo "Device: {$session->device_name}\n";
    echo "Last used: {$session->last_used_at}\n";
    echo "IP: {$session->ip_address}\n";
}
```

**Logout from Specific Device:**

```php
use App\Services\Auth\JWTService;

$jwtService = app(JWTService::class);
$jwtService->revokeRefreshToken($refreshToken);
```

**Logout from All Devices:**

```php
$jwtService->revokeAllUserTokens($userId);
```

**Logout from Other Devices (except current):**

```php
$jwtService->revokeOtherTokens($userId, $currentRefreshToken);
```

### Cleanup Tasks

**Remove Expired Tokens:**

```php
use App\Services\Auth\JWTService;

$jwtService = app(JWTService::class);
$deleted = $jwtService->cleanupExpiredTokens();
```

**Schedule Cleanup:**

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        app(JWTService::class)->cleanupExpiredTokens();
    })->daily();
}
```

## 🛡️ Security Best Practices

### 1. Token Storage

**Frontend:**
```javascript
// ❌ DON'T: Store in localStorage (vulnerable to XSS)
localStorage.setItem('access_token', token);

// ✅ DO: Store in memory or secure httpOnly cookies
const tokenStore = {
  token: null,
  set(token) { this.token = token; },
  get() { return this.token; }
};
```

**Mobile Apps:**
```javascript
// ✅ DO: Use secure storage
import * as SecureStore from 'expo-secure-store';

await SecureStore.setItemAsync('access_token', token);
const token = await SecureStore.getItemAsync('access_token');
```

### 2. HTTPS Only

Always use HTTPS in production:
```env
APP_URL=https://renthub.com
FORCE_HTTPS=true
```

### 3. Rate Limiting

Add to routes:
```php
Route::middleware(['throttle:60,1'])
    ->post('/api/auth/login', [AuthController::class, 'login']);
```

### 4. CORS Configuration

Update `config/cors.php`:
```php
'allowed_origins' => [
    'https://renthub.com',
    'https://app.renthub.com',
],
'allowed_headers' => ['*'],
'exposed_headers' => ['Authorization'],
'supports_credentials' => true,
```

### 5. Security Headers

Add middleware:
```php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        
        return $response;
    }
}
```

### 6. Password Policy

```php
use Illuminate\Validation\Rules\Password;

'password' => ['required', Password::min(8)
    ->mixedCase()
    ->numbers()
    ->symbols()
    ->uncompromised()
],
```

### 7. Two-Factor Authentication (2FA)

Coming soon in next iteration.

### 8. Audit Logging

Log all authentication events:
```php
Log::info('User logged in', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

## 📊 Security Monitoring

### Key Metrics to Monitor

1. **Failed login attempts**
2. **Token refresh frequency**
3. **API key usage**
4. **Unusual access patterns**
5. **Geographic anomalies**

### Alerts

Set up alerts for:
- Multiple failed login attempts
- Unusual API usage
- Expired API keys still in use
- Token generation spikes

## 🎓 Quick Reference

### Middleware Available

| Middleware | Usage |
|------------|-------|
| `JWTAuthenticate` | JWT token authentication |
| `CheckAPIKey` | API key authentication |
| `CheckPermission` | Permission-based authorization |
| `CheckRole` | Role-based authorization |

### Services Available

| Service | Purpose |
|---------|---------|
| `OAuthService` | OAuth 2.0 authentication |
| `JWTService` | JWT token management |
| `RBACService` | Roles & permissions |
| `APIKeyService` | API key management |

### Artisan Commands

```bash
# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Clean up expired tokens
php artisan tokens:cleanup

# Clean up expired API keys
php artisan api-keys:cleanup
```

## ✅ Security Checklist

- [x] OAuth 2.0 implementation (Google, Facebook, GitHub)
- [x] JWT access & refresh tokens
- [x] Token rotation strategy
- [x] Role-based access control
- [x] Permission system
- [x] API key management
- [x] IP whitelisting
- [x] Token expiration
- [x] Session management
- [x] Secure password hashing
- [x] Password reset flow
- [ ] Two-factor authentication (planned)
- [ ] Rate limiting per user (planned)
- [ ] Audit logging (planned)

---

**Security Status**: ✅ Production Ready  
**Last Updated**: November 3, 2024

For questions or security concerns, contact security@renthub.com

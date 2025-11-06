# CORS Configuration Guide for RentHub

This guide explains how to properly configure CORS (Cross-Origin Resource Sharing) between your Vercel frontend and Laravel Forge backend.

## Understanding the Issue

When you deploy:
- **Frontend** on Vercel: `https://your-app.vercel.app`
- **Backend** on Laravel Forge: `https://your-api.on-forge.com`

These are different origins, and browsers block cross-origin requests by default for security. CORS headers allow the backend to tell browsers which origins are allowed to access it.

## Backend Configuration (Laravel on Forge)

### 1. CORS Configuration File

The file `backend/config/cors.php` is already configured with patterns that match Vercel and Forge domains:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://127.0.0.1:3000',
    ],
    
    'allowed_origins_patterns' => [
        '#^https?://([\w-]+\.)?renthub\.com$#i',
        '#^https?://[\w-]+\.vercel\.app$#i',
        '#^https?://[\w-]+\.on-forge\.com$#i',
    ],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],
    
    'max_age' => 3600,
    
    'supports_credentials' => true,
];
```

### 2. Environment Variables on Forge

In your Laravel Forge dashboard, add these environment variables to your backend:

```bash
# The exact URL of your Vercel deployment
FRONTEND_URL=https://your-app.vercel.app

# Sanctum stateful domains (domain only, no protocol)
SANCTUM_STATEFUL_DOMAINS=your-app.vercel.app

# Your backend URL
APP_URL=https://your-api.on-forge.com
```

**Important Notes:**
- `FRONTEND_URL` should include `https://`
- `SANCTUM_STATEFUL_DOMAINS` should NOT include `https://`, just the domain
- Replace `your-app` and `your-api` with your actual deployment URLs

### 3. Verify Middleware

Laravel automatically applies the `HandleCors` middleware. The configuration is in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
    // ... other middleware
})
```

## Frontend Configuration (Next.js on Vercel)

### 1. Environment Variables on Vercel

In your Vercel project settings, add:

```bash
# Your Laravel backend URL (with /api/v1 if needed)
NEXT_PUBLIC_API_URL=https://your-api.on-forge.com/api/v1

# Your Vercel deployment URL
NEXT_PUBLIC_SITE_URL=https://your-app.vercel.app

# NextAuth configuration
NEXTAUTH_URL=https://your-app.vercel.app
NEXTAUTH_SECRET=your-random-secret-key
```

### 2. API Client Configuration

Ensure your API client in the frontend includes credentials:

```javascript
// Example axios configuration
const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  withCredentials: true, // Important for Sanctum
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});
```

## Testing CORS Configuration

### 1. Test with curl

Test preflight request:

```bash
curl -X OPTIONS https://your-api.on-forge.com/api/v1/property-comparison \
  -H "Origin: https://your-app.vercel.app" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v
```

You should see headers like:
```
Access-Control-Allow-Origin: https://your-app.vercel.app
Access-Control-Allow-Credentials: true
```

### 2. Test actual request

```bash
curl https://your-api.on-forge.com/api/v1/property-comparison \
  -H "Origin: https://your-app.vercel.app" \
  -v
```

## Common Issues and Solutions

### Issue 1: "No 'Access-Control-Allow-Origin' header"

**Cause**: Backend is not returning CORS headers.

**Solutions**:
1. Verify `FRONTEND_URL` is set in backend environment
2. Check that patterns in `cors.php` match your domains
3. Ensure Laravel cache is cleared on Forge:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

### Issue 2: "Credentials flag is true but Access-Control-Allow-Credentials is not"

**Cause**: Backend CORS config doesn't support credentials.

**Solution**: Ensure `supports_credentials` is `true` in `backend/config/cors.php`

### Issue 3: "Wildcard '*' cannot be used with credentials"

**Cause**: Using `allowed_origins => ['*']` with `supports_credentials => true`.

**Solution**: Specify exact origins or use patterns instead of wildcard.

### Issue 4: Preflight request succeeds but actual request fails

**Cause**: Different middleware handling or route protection.

**Solutions**:
1. Check route middleware configuration
2. Verify Sanctum configuration
3. Ensure tokens/cookies are being sent correctly

## Deployment Checklist

- [ ] Backend environment variables set on Forge:
  - [ ] `FRONTEND_URL` with full URL including https://
  - [ ] `SANCTUM_STATEFUL_DOMAINS` with domain(s) only
  - [ ] `APP_URL` with backend URL
- [ ] Frontend environment variables set on Vercel:
  - [ ] `NEXT_PUBLIC_API_URL` with backend API URL
  - [ ] `NEXT_PUBLIC_SITE_URL` with frontend URL
  - [ ] `NEXTAUTH_URL` with frontend URL
  - [ ] `NEXTAUTH_SECRET` with secure random value
- [ ] Laravel cache cleared on Forge
- [ ] Vercel deployment triggered after environment variable changes
- [ ] CORS tested with curl or browser
- [ ] API endpoints tested from frontend

## Additional Resources

- [Laravel CORS Documentation](https://laravel.com/docs/11.x/routing#cors)
- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/11.x/sanctum#spa-authentication)
- [MDN CORS Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [Vercel Environment Variables](https://vercel.com/docs/concepts/projects/environment-variables)

## Getting Help

If you're still experiencing issues:

1. Check Laravel logs on Forge: `storage/logs/laravel.log`
2. Check browser console for detailed error messages
3. Verify network tab in browser DevTools shows request/response headers
4. Open a GitHub issue with:
   - Exact error message
   - Request/response headers
   - Environment configuration (redact secrets)

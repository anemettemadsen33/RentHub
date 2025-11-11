# Backend-Frontend Connection Guide

## Configurare

### Backend (Laravel)
📍 **URL**: `http://localhost:8000`
📍 **API Base**: `http://localhost:8000/api/v1`

#### Environment (.env)
```bash
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000
```

#### CORS Configuration
✅ Configured in `backend/config/cors.php`
- Allows `http://localhost:3000`
- Allows `http://127.0.0.1:3000`
- Supports credentials: `true`
- Allowed methods: `*` (GET, POST, PUT, DELETE, etc.)

#### Sanctum Configuration
✅ Configured in `backend/config/sanctum.php`
- Stateful domains include localhost:3000
- Token-based authentication for SPA

---

### Frontend (Next.js)
📍 **URL**: `http://localhost:3000`

#### Environment (.env.local)
```bash
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

#### API Client Configuration
✅ Configured in `frontend/src/lib/api-client.ts`
- Base URL: Uses `NEXT_PUBLIC_API_BASE_URL`
- Auto-attaches Bearer token from localStorage
- Handles 401 redirects to login
- Timeout: 30 seconds

---

## API Services

### 🔐 Authentication (`/lib/api-service.ts`)

#### Available Methods:
```typescript
authService.register(data)      // POST /register
authService.login(credentials)  // POST /login
authService.logout()            // POST /logout
authService.me()                // GET /me
authService.changePassword()    // PUT /profile/password
```

#### Example Usage:
```typescript
import { authService } from '@/lib/api-service';

// Login
const response = await authService.login({
  email: 'user@example.com',
  password: 'password123'
});

// Register
const newUser = await authService.register({
  name: 'John Doe',
  email: 'john@example.com',
  password: 'secret123',
  password_confirmation: 'secret123',
  role: 'tenant'
});
```

---

### 👤 Profile Service

```typescript
profileService.getProfile()           // GET /profile
profileService.updateProfile(data)    // PUT /profile
profileService.uploadAvatar(file)     // POST /profile/avatar
profileService.deleteAvatar()         // DELETE /profile/avatar
```

---

### 🏠 Properties Service

```typescript
propertiesService.list(params)        // GET /properties
propertiesService.featured()          // GET /properties/featured
propertiesService.search(params)      // GET /properties/search
propertiesService.show(id)            // GET /properties/{id}
propertiesService.myProperties()      // GET /my-properties
propertiesService.create(data)        // POST /properties
propertiesService.update(id, data)    // PUT /properties/{id}
propertiesService.delete(id)          // DELETE /properties/{id}
```

---

### 📅 Bookings Service

```typescript
bookingsService.list(params)              // GET /bookings
bookingsService.myBookings()              // GET /my-bookings
bookingsService.show(id)                  // GET /bookings/{id}
bookingsService.create(data)              // POST /bookings
bookingsService.checkAvailability(data)   // POST /check-availability
bookingsService.cancel(id)                // POST /bookings/{id}/cancel
```

---

### 💰 Payments Service

```typescript
paymentsService.list(params)      // GET /payments
paymentsService.create(data)      // POST /payments
paymentsService.show(id)          // GET /payments/{id}
```

---

### 🔔 Notifications Service

```typescript
notificationsService.list(params)           // GET /notifications
notificationsService.unreadCount()          // GET /notifications/unread-count
notificationsService.markAllAsRead()        // POST /notifications/mark-all-read
notificationsService.markAsRead(id)         // POST /notifications/{id}/read
notificationsService.getPreferences()       // GET /notifications/preferences
notificationsService.updatePreferences()    // PUT /notifications/preferences
```

---

## React Contexts

### 🔐 AuthContext (`useAuth()`)

```typescript
const { user, login, register, logout, isAuthenticated, isLoading, refreshUser } = useAuth();

// Login user
await login('email@example.com', 'password');

// Register new user
await register('Name', 'email@example.com', 'password', 'password', 'tenant');

// Logout
await logout();

// Refresh user data
await refreshUser();
```

### 🔔 NotificationContext (`useNotifications()`)

```typescript
const { unreadCount, loading, refresh } = useNotifications();

// Get unread count
console.log(unreadCount);

// Manually refresh
await refresh();
```

---

## Testing the Connection

### 1️⃣ Start Backend
```bash
cd backend
php artisan serve
# Runs on http://localhost:8000
```

### 2️⃣ Start Frontend
```bash
cd frontend
npm run dev
# Runs on http://localhost:3000
```

### 3️⃣ Test Authentication Flow

1. **Register**: `http://localhost:3000/auth/register`
   - Fill in the form
   - Should redirect to `/dashboard` on success

2. **Login**: `http://localhost:3000/auth/login`
   - Use registered credentials
   - Should redirect to `/dashboard` on success

3. **Check Token**: Open DevTools → Application → Local Storage
   - `auth_token` should contain Bearer token
   - `user` should contain user object

4. **API Call**: Open DevTools → Network
   - All requests to `/api/v1/*` should have `Authorization: Bearer {token}`

### 4️⃣ Test Protected Routes

Visit authenticated pages:
- `/dashboard` - Main dashboard
- `/profile` - User profile
- `/bookings` - My bookings
- `/properties` - Browse properties

### 5️⃣ Test API Calls

Open browser console on dashboard and run:
```javascript
// Check if API client is working
fetch('http://localhost:8000/api/v1/me', {
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
    'Accept': 'application/json'
  }
}).then(r => r.json()).then(console.log)
```

---

## Common Issues & Solutions

### ❌ CORS Error
**Problem**: `Access-Control-Allow-Origin` error in browser console

**Solution**:
1. Check `backend/.env` has `FRONTEND_URL=http://localhost:3000`
2. Clear Laravel config: `php artisan config:clear`
3. Restart Laravel server

### ❌ 401 Unauthorized
**Problem**: All API calls return 401

**Solution**:
1. Check if token exists: `localStorage.getItem('auth_token')`
2. Login again to get fresh token
3. Check backend `api.php` routes have `auth:sanctum` middleware

### ❌ Network Error / Timeout
**Problem**: API calls fail with network error

**Solution**:
1. Verify backend is running: `http://localhost:8000/api/v1/health`
2. Check frontend `.env.local` has correct API URL
3. Check firewall isn't blocking ports 3000 or 8000

### ❌ Token Not Sent
**Problem**: Authorization header missing

**Solution**:
Check `frontend/src/lib/api-client.ts` interceptor is working:
```typescript
// Should see this in request headers
Authorization: Bearer {your-token}
```

---

## Production Deployment

### Backend
1. Update `.env`:
   ```bash
   APP_URL=https://api.yourdomain.com
   FRONTEND_URL=https://yourdomain.com
   SANCTUM_STATEFUL_DOMAINS=yourdomain.com
   ```

2. Update `config/cors.php` allowed_origins

### Frontend
1. Update `.env.production`:
   ```bash
   NEXT_PUBLIC_API_URL=https://api.yourdomain.com
   NEXT_PUBLIC_API_BASE_URL=https://api.yourdomain.com/api/v1
   ```

2. Build and deploy:
   ```bash
   npm run build
   ```

---

## Quick Reference

### Backend Routes
- **Auth**: `/api/v1/login`, `/api/v1/register`, `/api/v1/logout`, `/api/v1/me`
- **Profile**: `/api/v1/profile`, `/api/v1/profile/password`
- **Properties**: `/api/v1/properties`, `/api/v1/my-properties`
- **Bookings**: `/api/v1/bookings`, `/api/v1/my-bookings`
- **Notifications**: `/api/v1/notifications`, `/api/v1/notifications/unread-count`
- **Settings**: `/api/v1/settings` (admin only)

### Frontend Pages
- **Auth**: `/auth/login`, `/auth/register`
- **Main**: `/`, `/properties`, `/properties/[id]`
- **User**: `/dashboard`, `/profile`, `/bookings`, `/payments/history`
- **Admin**: `/admin/settings`

### Key Files
- **Backend API Routes**: `backend/routes/api.php`
- **Frontend API Client**: `frontend/src/lib/api-client.ts`
- **API Service Layer**: `frontend/src/lib/api-service.ts`
- **API Endpoints**: `frontend/src/lib/api-endpoints.ts`
- **Auth Context**: `frontend/src/contexts/auth-context.tsx`

---

## Status: ✅ FULLY CONNECTED

✅ CORS configured correctly  
✅ Sanctum authentication working  
✅ API client with auto token attachment  
✅ Type-safe service layer  
✅ React contexts for auth & notifications  
✅ All major endpoints mapped  

**Last Updated**: 2025-11-07

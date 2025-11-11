# Backend-Frontend Integration Guide

## Overview

This guide explains how to connect the Laravel Filament backend with the Next.js frontend, including settings management and SMTP configuration.

## Architecture

- **Backend**: Laravel 11 + Filament v4 (API + Admin Panel)
  - Hosted on: Laravel Forge (Production)
  - API: `/api/v1/*`
  - Admin Panel: `/admin` (Filament)

- **Frontend**: Next.js 15 (App Router)
  - Hosted on: Vercel (Production)
  - Consumes: Backend REST API

## Settings Management

### Backend Settings (Laravel)

The backend has a **Settings** page in Filament (`/admin/settings`) that allows admins to configure:

1. **Frontend URL**: The URL where the frontend is hosted
2. **Company Information**: Name, email, phone, address, Google Maps
3. **Mail (SMTP)**: Configuration for sending emails

**Model**: `App\Models\Setting`
- Stores key-value pairs with caching
- `Setting::get('key', 'default')` - Get value
- `Setting::set('key', 'value', 'group', 'type')` - Set value

**API Endpoints**:

```bash
# Public (no auth required)
GET /api/v1/settings/public
Response: { company_name, company_email, company_phone, company_address, frontend_url }

# Admin only (requires auth + admin role)
GET /api/v1/settings
PUT /api/v1/settings
POST /api/v1/settings/test-email
```

### Frontend Settings (Next.js)

The frontend has an admin settings page at `/admin/settings` with three tabs:

1. **Frontend Tab**: Configure frontend URL
2. **Company Info Tab**: Company name, email, phone, address, Google Maps
3. **Email (SMTP) Tab**: SMTP configuration with test email feature

**File**: `src/app/admin/settings/page.tsx`

## Environment Configuration

### Backend (.env)

```bash
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@renthub.com
MAIL_FROM_NAME=RentHub

# Database
DB_CONNECTION=sqlite
# or PostgreSQL/MySQL for production

# Redis (for cache/queue)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Frontend (.env.local)

```bash
# API Configuration
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1

# App Configuration
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

## Starting the Application

### 1. Start Backend

```bash
cd backend

# Install dependencies
composer install

# Copy .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start server
php artisan serve
# Runs on http://localhost:8000
```

**Admin Panel**: http://localhost:8000/admin
- Default credentials will be created by seeder

**API**: http://localhost:8000/api/v1/

### 2. Start Frontend

```bash
cd frontend

# Install dependencies
npm install

# Start dev server
npm run dev
# Runs on http://localhost:3000
```

## Authentication Flow (Laravel Sanctum)

### CORS Configuration

Backend (`config/cors.php`):
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    'http://127.0.0.1:3000',
],
```

### Sanctum Stateful Domains

Backend (`config/sanctum.php`):
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost:3000,localhost'))
```

### Login Flow

1. Frontend calls `POST /api/v1/login` with `{ email, password }`
2. Backend validates credentials
3. Backend returns token: `{ token, user }`
4. Frontend stores token in localStorage/cookies
5. Frontend includes token in headers: `Authorization: Bearer {token}`

### API Client Setup

Frontend (`src/lib/api-client.ts`):
```typescript
const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

## Email Configuration

### Testing with Mailtrap

1. Sign up at https://mailtrap.io (free)
2. Get SMTP credentials
3. Update backend `.env`:
   ```bash
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   ```

### Production Email (Gmail)

1. Enable 2FA on Gmail account
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Update settings:
   ```bash
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   ```

### Test Email from Frontend

1. Login as admin
2. Go to `/admin/settings`
3. Click "Email (SMTP)" tab
4. Enter test email address
5. Click "Send Test"

## Deployment

### Backend (Laravel Forge)

1. Create new site on Forge
2. Connect Git repository
3. Set environment variables in Forge dashboard
4. Enable deployment on push
5. Run migrations: `php artisan migrate --force`

**Important Environment Variables**:
- `APP_URL` - Your backend domain
- `FRONTEND_URL` - Your Vercel frontend URL
- `SANCTUM_STATEFUL_DOMAINS` - Your Vercel domain (without https://)
- Database credentials
- Mail credentials

### Frontend (Vercel)

1. Import project from Git
2. Framework: Next.js
3. Root Directory: `frontend`
4. Environment Variables:
   - `NEXT_PUBLIC_API_URL` - Your Forge backend URL
   - `NEXT_PUBLIC_API_BASE_URL` - Your Forge backend URL + `/api/v1`
   - `NEXT_PUBLIC_APP_URL` - Your Vercel URL

5. Deploy

**Update Backend Settings**:
After deployment, login to backend `/admin/settings` and update:
- Frontend URL to your Vercel URL
- Update SANCTUM_STATEFUL_DOMAINS in backend .env

## Settings Synchronization

### From Backend to Frontend

Settings stored in backend are accessible via API:

```typescript
// Get public settings (no auth)
const response = await fetch('http://localhost:8000/api/v1/settings/public');
const { company_name, company_email, frontend_url } = response.data;
```

### From Frontend Admin Panel

Admins can update settings from frontend:

1. Login as admin
2. Navigate to `/admin/settings`
3. Update Frontend URL, Company Info, or SMTP settings
4. Click "Save Settings"
5. Changes are saved to backend database
6. Settings automatically cached for performance

## Common Issues

### CORS Errors

**Problem**: `Access to XMLHttpRequest has been blocked by CORS policy`

**Solution**:
1. Check backend `.env` has correct `FRONTEND_URL`
2. Verify `config/cors.php` includes your frontend URL
3. Restart backend: `php artisan config:clear && php artisan serve`

### Authentication Failed

**Problem**: 401 Unauthorized

**Solution**:
1. Check token is stored: `localStorage.getItem('token')`
2. Verify token is sent in headers: Check Network tab
3. Check backend `.env` has correct `SANCTUM_STATEFUL_DOMAINS`

### Email Not Sending

**Problem**: Test email fails

**Solution**:
1. Check SMTP credentials in Settings page
2. Verify port is correct (587 for TLS, 465 for SSL)
3. Check firewall allows SMTP connections
4. View Laravel logs: `backend/storage/logs/laravel.log`

## API Endpoints

### Public Endpoints (No Auth)

```
GET  /api/v1/properties           - List properties
GET  /api/v1/properties/{id}      - Property details
GET  /api/v1/settings/public      - Public settings
POST /api/v1/login                - Login
POST /api/v1/register             - Register
POST /api/v1/forgot-password      - Request password reset
POST /api/v1/reset-password       - Reset password
```

### Protected Endpoints (Requires Auth)

```
GET  /api/v1/me                   - Current user
POST /api/v1/logout               - Logout
GET  /api/v1/bookings             - User bookings
POST /api/v1/bookings             - Create booking
GET  /api/v1/favorites            - User favorites
POST /api/v1/favorites            - Add to favorites
```

### Admin Endpoints (Requires Admin Role)

```
GET  /api/v1/settings             - All settings
PUT  /api/v1/settings             - Update settings
POST /api/v1/settings/test-email  - Test email configuration
```

## Development Workflow

1. **Start Backend**: `cd backend && php artisan serve`
2. **Start Frontend**: `cd frontend && npm run dev`
3. **Access Admin Panel**: http://localhost:8000/admin
4. **Access Frontend**: http://localhost:3000
5. **Configure Settings**: Admin Panel → Settings
6. **Test Frontend Admin**: http://localhost:3000/admin/settings

## Database Models

### Setting Model

```php
Setting::get('frontend_url', 'http://localhost:3000')
Setting::set('frontend_url', 'https://renthub.vercel.app', 'frontend', 'url')
```

Settings are cached automatically. Clear cache: `php artisan cache:clear`

## Next Steps

1. ✅ Backend API endpoints created
2. ✅ Frontend settings page created
3. ✅ SMTP configuration UI ready
4. ⏳ Start backend and test API
5. ⏳ Configure SMTP in settings
6. ⏳ Test authentication flow
7. ⏳ Test email sending
8. ⏳ Deploy to production

## Support

- **Backend Logs**: `backend/storage/logs/laravel.log`
- **Frontend Console**: Browser DevTools
- **API Testing**: Use Postman or Thunder Client
- **Database**: `backend/database/database.sqlite` (default)

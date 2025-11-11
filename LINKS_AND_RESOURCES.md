# 🔗 RentHub - Link-uri & Resurse Importante

## 🌐 URLs Aplicație

### Development
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **API Base**: http://localhost:8000/api/v1

### Pagini Frontend Principale
- **Homepage**: http://localhost:3000
- **Login**: http://localhost:3000/auth/login
- **Register**: http://localhost:3000/auth/register
- **Dashboard**: http://localhost:3000/dashboard
- **Profile**: http://localhost:3000/profile
- **Properties**: http://localhost:3000/properties
- **Bookings**: http://localhost:3000/bookings
- **Messages**: http://localhost:3000/messages
- **Payment History**: http://localhost:3000/payments/history
- **Admin Settings**: http://localhost:3000/admin/settings

---

## 📁 Fișiere Importante

### Backend (Laravel)
```
backend/
├── routes/api.php                     - Toate rutele API
├── app/Http/Controllers/Api/
│   ├── AuthController.php             - Login, Register, Logout
│   ├── ProfileController.php          - User profile management
│   ├── PropertyController.php         - Properties CRUD
│   ├── BookingController.php          - Bookings management
│   ├── NotificationController.php     - Notifications & preferences
│   ├── PaymentController.php          - Payments
│   └── SettingsController.php         - Admin settings
├── config/
│   ├── cors.php                       - CORS configuration
│   └── sanctum.php                    - Sanctum authentication
└── .env                               - Environment variables
```

### Frontend (Next.js)
```
frontend/
├── src/
│   ├── lib/
│   │   ├── api-client.ts              - ⭐ Axios client cu interceptors
│   │   ├── api-endpoints.ts           - ⭐ Toate endpoint-urile mapate
│   │   ├── api-service.ts             - ⭐ Type-safe service layer
│   │   └── api-test-utils.ts          - Browser test utilities
│   ├── contexts/
│   │   ├── auth-context.tsx           - ⭐ Authentication context
│   │   └── notification-context.tsx   - ⭐ Notifications context
│   ├── components/
│   │   ├── navbar.tsx                 - Navigation cu unread badge
│   │   ├── providers.tsx              - Root providers wrapper
│   │   └── layouts/main-layout.tsx    - Layout principal
│   └── app/
│       ├── auth/
│       │   ├── login/page.tsx         - Login page
│       │   └── register/page.tsx      - Register page
│       ├── dashboard/page.tsx         - Dashboard principal
│       ├── profile/page.tsx           - User profile & settings
│       └── admin/settings/page.tsx    - Admin settings
└── .env.local                         - Environment variables
```

---

## 📚 Documentație

### Ghiduri Principale
1. **CONNECTION_STATUS.md** - ✅ Status complet integrare
2. **BACKEND_FRONTEND_CONNECTION.md** - 📖 Ghid detaliat de integrare
3. **QUICK_START.md** - 🚀 Pornire rapidă
4. **test-connection.ps1** - 🧪 Script de testare

### Secțiuni Importante

#### 📖 BACKEND_FRONTEND_CONNECTION.md
- Configurare Environment
- CORS & Sanctum setup
- API Endpoints reference
- React Contexts usage
- Testing the connection
- Common issues & solutions
- Production deployment

#### 🚀 QUICK_START.md
- Cum pornești aplicația
- Primul test (register/login)
- API endpoints principale
- Debugging tips

#### ✅ CONNECTION_STATUS.md
- Ce s-a realizat
- Infrastructure overview
- Endpoints testate
- Checklist complet
- Status final

---

## 🧪 Testare

### PowerShell Script
```powershell
# Run din root folder
.\test-connection.ps1

# Verifică:
# ✅ Backend running
# ✅ CORS configured
# ✅ Public endpoints
# ✅ Auth endpoints
# ✅ Database connection
# ✅ Frontend .env.local
```

### Browser Console (F12)
```javascript
// Încarcă automat în orice pagină
apiTest.testAllEndpoints()  // Toate testele
apiTest.checkAuth()         // Auth status
apiTest.testBackend()       // Backend connection
apiTest.testAuthRequest()   // Authenticated request
apiTest.testNotifications() // Notifications
```

### Manual API Testing

#### Postman / Thunder Client Collections
```
GET  http://localhost:8000/api/v1/properties
GET  http://localhost:8000/api/v1/properties/featured
POST http://localhost:8000/api/v1/login
POST http://localhost:8000/api/v1/register
GET  http://localhost:8000/api/v1/me
     Headers: Authorization: Bearer {token}
```

---

## 🔑 Environment Variables

### Backend (.env)
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=
```

### Frontend (.env.local)
```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=http://localhost:3000
```

---

## 🛠️ Comenzi Utile

### Backend (Laravel)
```bash
cd backend

# Pornire server
php artisan serve

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Database
php artisan migrate
php artisan db:seed

# Verificare rute
php artisan route:list --path=api/v1
```

### Frontend (Next.js)
```bash
cd frontend

# Pornire dev
npm run dev

# Build production
npm run build
npm start

# Type checking
npm run type-check

# Linting
npm run lint

# Clear cache
rm -rf .next
rm -rf node_modules
npm install
```

---

## 🐛 Debugging

### DevTools Network Tab
1. Deschide F12
2. Tab "Network"
3. Filtrează: "Fetch/XHR"
4. Verifică:
   - Request URL (http://localhost:8000/api/v1/...)
   - Request Headers (Authorization: Bearer ...)
   - Response status (200, 401, 422, etc.)
   - Response data

### LocalStorage
1. F12 → Application → Local Storage → http://localhost:3000
2. Verifică:
   - `auth_token` - Bearer token
   - `user` - JSON user object

### Backend Logs
```bash
cd backend
tail -f storage/logs/laravel.log
```

### Frontend Console
```javascript
// Check API base URL
console.log(process.env.NEXT_PUBLIC_API_BASE_URL)

// Check auth
console.log(localStorage.getItem('auth_token'))
console.log(localStorage.getItem('user'))

// Manual API call
fetch('http://localhost:8000/api/v1/me', {
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
  }
}).then(r => r.json()).then(console.log)
```

---

## 📊 API Endpoints Quick Reference

### Auth
```
POST /api/v1/register
POST /api/v1/login
POST /api/v1/logout          [auth]
GET  /api/v1/me              [auth]
PUT  /api/v1/profile/password [auth]
```

### Profile
```
GET  /api/v1/profile         [auth]
PUT  /api/v1/profile         [auth]
POST /api/v1/profile/avatar  [auth]
```

### Properties
```
GET  /api/v1/properties
GET  /api/v1/properties/featured
GET  /api/v1/properties/{id}
GET  /api/v1/my-properties   [auth]
POST /api/v1/properties      [auth, owner]
PUT  /api/v1/properties/{id} [auth, owner]
```

### Bookings
```
GET  /api/v1/bookings        [auth]
GET  /api/v1/my-bookings     [auth]
POST /api/v1/bookings        [auth]
POST /api/v1/check-availability
```

### Notifications
```
GET  /api/v1/notifications             [auth]
GET  /api/v1/notifications/unread-count [auth]
POST /api/v1/notifications/mark-all-read [auth]
GET  /api/v1/notifications/preferences  [auth]
PUT  /api/v1/notifications/preferences  [auth]
```

---

## 🔄 Flow de Autentificare

```
1. User → http://localhost:3000/auth/register
   ↓
2. Frontend → POST /api/v1/register
   ↓
3. Backend → Creează user + generează token
   ↓
4. Backend → Response: { user, token }
   ↓
5. Frontend → Salvează în localStorage
   - auth_token: "token_value"
   - user: { id, name, email, ... }
   ↓
6. Frontend → Redirect to /dashboard
   ↓
7. Toate request-urile următoare
   Headers: { Authorization: "Bearer token_value" }
```

---

## 📝 Code Snippets

### Login Component
```typescript
import { useAuth } from '@/contexts/auth-context';

const { login } = useAuth();

const handleSubmit = async (e) => {
  e.preventDefault();
  try {
    await login(email, password);
    // Auto redirect to /dashboard
  } catch (error) {
    // Error toast shown automatically
  }
};
```

### Fetch Properties
```typescript
import { propertiesService } from '@/lib/api-service';

const properties = await propertiesService.list({
  city: 'București',
  min_price: 100,
  max_price: 500
});
```

### Check Notifications
```typescript
import { useNotifications } from '@/contexts/notification-context';

const { unreadCount, refresh } = useNotifications();

// Show badge
{unreadCount > 0 && <Badge>{unreadCount}</Badge>}

// Refresh manually
await refresh();
```

---

## 📞 Support & Resources

### Verificare Rapidă
1. Backend running? → `curl http://localhost:8000/api/v1/properties`
2. Frontend running? → Visit `http://localhost:3000`
3. Test connection → `.\test-connection.ps1`
4. Browser tests → `F12` → Console → `apiTest.testAllEndpoints()`

### Dacă Ceva Nu Funcționează
1. Check **CONNECTION_STATUS.md** - Checklist complet
2. Run **test-connection.ps1** - Diagnostic automat
3. Check **BACKEND_FRONTEND_CONNECTION.md** - Troubleshooting section
4. Verifică console logs (browser & terminal)

---

**Ultima actualizare**: 2025-11-07  
**Status**: 🟢 FULLY OPERATIONAL

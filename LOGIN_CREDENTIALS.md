# 🔐 RENTHUB - LOGIN CREDENTIALS

## Frontend User Accounts

### Test User 1 (Created via API)
```
Email: test_20251111000337@renthub.test
Password: TestPassword123!
Role: tenant
```

### Manual Test User (Create via UI)
```
Email: testmanual@renthub.test
Password: TestManual123!
Role: tenant
```

---

## Admin Panel Access

### Admin Account
```
URL: http://127.0.0.1:8000/admin/login
Email: admin@renthub.com
Password: admin123
Role: admin
```

---

## Quick Test URLs

### Frontend
- Homepage: http://localhost:3000
- Register: http://localhost:3000/auth/register
- Login: http://localhost:3000/auth/login
- Properties: http://localhost:3000/properties
- Dashboard: http://localhost:3000/dashboard

### Backend Admin
- Login: http://127.0.0.1:8000/admin/login
- Dashboard: http://127.0.0.1:8000/admin
- Users: http://127.0.0.1:8000/admin/users
- Properties: http://127.0.0.1:8000/admin/properties
- Settings: http://127.0.0.1:8000/admin/settings
- Reports: http://127.0.0.1:8000/admin/reports

### API Endpoints
- Base URL: http://127.0.0.1:8000/api/v1
- Register: POST /api/v1/register
- Login: POST /api/v1/login
- Properties: GET /api/v1/properties
- Currencies: GET /api/v1/currencies

---

## Testing Sequence

1. **Test Frontend Registration**
   - Open: http://localhost:3000/auth/register
   - Use: testmanual@renthub.test / TestManual123!

2. **Test Frontend Login**
   - Open: http://localhost:3000/auth/login
   - Use: testmanual@renthub.test / TestManual123!

3. **Test Admin Login**
   - Open: http://127.0.0.1:8000/admin/login
   - Use: admin@renthub.com / admin123

4. **Create Property in Admin**
   - Login to admin panel
   - Go to Properties → Create
   - Fill form and save

5. **View Property on Frontend**
   - Login to frontend
   - Go to Properties
   - Verify admin-created property appears

---

**Last Updated:** November 11, 2025

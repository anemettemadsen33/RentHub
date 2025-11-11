# 🧪 RENTHUB - MANUAL BROWSER TESTING GUIDE
**Date:** November 11, 2025  
**Environment:** Development

---

## ✅ PRE-TEST CHECKLIST

### Servers Running:
- [x] Backend: http://127.0.0.1:8000 ✓
- [x] Frontend: http://localhost:3000 ✓

### Test Credentials:
```
User Account (Created via API):
Email: test_20251111000337@renthub.test
Password: TestPassword123!

Admin Account (Default):
Email: admin@renthub.com
Password: password (or check database)
```

---

## 🔍 TESTING SEQUENCE

### PART 1: FRONTEND TESTING (http://localhost:3000)

#### Test 1.1: Homepage & Navigation
- [ ] Open http://localhost:3000
- [ ] Verify homepage loads without errors
- [ ] Check navigation menu items visible
- [ ] Open browser console (F12) - should have NO red errors
- [ ] Click through main menu items
- [ ] Verify all pages load

**Expected Result:** Clean homepage, no console errors, navigation works

---

#### Test 1.2: User Registration
- [ ] Click "Register" or "Sign Up"
- [ ] Fill form:
  - Name: `Test User Manual`
  - Email: `testmanual@renthub.test`
  - Password: `TestManual123!`
  - Confirm Password: `TestManual123!`
- [ ] Submit form
- [ ] Check console for success message
- [ ] Verify redirect to dashboard or login

**Expected Result:** Registration successful, user created, no errors

**Console Check:**
```javascript
// Should see:
[authService] Register success: 201
// Should NOT see:
419 CSRF error
Network error
```

---

#### Test 1.3: User Login
- [ ] Go to login page
- [ ] Enter credentials:
  - Email: `testmanual@renthub.test`
  - Password: `TestManual123!`
- [ ] Submit
- [ ] Verify redirect to dashboard
- [ ] Check if user name appears in header/navbar

**Expected Result:** Login successful, dashboard accessible

---

#### Test 1.4: Properties Page
- [ ] Navigate to Properties/Browse
- [ ] Check if properties list loads
- [ ] Try filters (if available)
- [ ] Try search (if available)
- [ ] Click on a property (if any exist)

**Expected Result:** Properties page loads, filters work

---

#### Test 1.5: User Dashboard
- [ ] Go to user dashboard
- [ ] Check all dashboard sections load:
  - [ ] Profile info
  - [ ] My Bookings
  - [ ] My Properties
  - [ ] Messages/Notifications
- [ ] Try to edit profile
- [ ] Update any field
- [ ] Save changes

**Expected Result:** Dashboard functional, profile updates work

---

### PART 2: ADMIN PANEL TESTING (http://127.0.0.1:8000/admin)

#### Test 2.1: Admin Login
- [ ] Open http://127.0.0.1:8000/admin/login
- [ ] Enter admin credentials:
  - Email: `admin@renthub.com`
  - Password: `password` (or your admin password)
- [ ] Click Login
- [ ] Verify redirect to admin dashboard

**Expected Result:** Admin panel accessible, dashboard loads

**If login fails:**
```bash
# Create admin user via terminal:
php artisan tinker
User::create([
    'name' => 'Admin',
    'email' => 'admin@renthub.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
exit
```

---

#### Test 2.2: Admin Dashboard Navigation
- [ ] Check sidebar menu items:
  - [ ] Dashboard
  - [ ] Users
  - [ ] Properties
  - [ ] Bookings
  - [ ] Reviews
  - [ ] Settings
  - [ ] Reports
- [ ] Click each menu item
- [ ] Verify each page loads without errors

**Expected Result:** All admin pages accessible, no Filament errors

---

#### Test 2.3: Users Management
- [ ] Go to Users section
- [ ] Verify users list shows
- [ ] Check if test user appears
- [ ] Click "Create" to add new user
- [ ] Fill form:
  - Name: `Admin Test User`
  - Email: `admintest@renthub.test`
  - Password: `Admin123!`
  - Role: `tenant`
- [ ] Save
- [ ] Verify user appears in list

**Expected Result:** User CRUD operations work

---

#### Test 2.4: Properties Management
- [ ] Go to Properties section
- [ ] Click "Create Property"
- [ ] Fill form:
  - Title: `Test Property 1`
  - Type: `apartment`
  - Status: `available`
  - Price: `100`
  - Address: `123 Test Street`
  - City: `Test City`
  - Country: `US`
  - Bedrooms: `2`
  - Bathrooms: `1`
  - Description: `This is a test property`
- [ ] Save
- [ ] Verify property appears in list

**Expected Result:** Property created successfully

---

#### Test 2.5: Settings Page
- [ ] Go to Settings (from sidebar)
- [ ] Check all tabs load:
  - [ ] General
  - [ ] Email
  - [ ] Payment
  - [ ] Localization
- [ ] Try updating a setting
- [ ] Save changes
- [ ] Check for success message

**Expected Result:** Settings page loads, updates work

**Known Issue Check:** If you see type errors, verify Settings.php has:
```php
protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
```

---

#### Test 2.6: Reports Page
- [ ] Go to Reports
- [ ] Check if page loads
- [ ] Try generating a report
- [ ] Verify report displays

**Expected Result:** Reports accessible

---

### PART 3: FRONTEND-BACKEND INTEGRATION

#### Test 3.1: Property Viewing (Frontend)
- [ ] Logout from admin
- [ ] Go to frontend: http://localhost:3000
- [ ] Login as regular user
- [ ] Go to Properties page
- [ ] Verify "Test Property 1" appears
- [ ] Click on property
- [ ] View property details

**Expected Result:** Property created in admin shows on frontend

---

#### Test 3.2: Create Booking (Frontend)
- [ ] On property details page
- [ ] Select check-in and check-out dates
- [ ] Click "Book Now" or similar
- [ ] Complete booking form
- [ ] Submit booking
- [ ] Check confirmation

**Expected Result:** Booking created successfully

---

#### Test 3.3: Verify Booking in Admin
- [ ] Go to admin panel
- [ ] Navigate to Bookings
- [ ] Verify new booking appears
- [ ] Check booking details match

**Expected Result:** Booking visible in admin panel

---

### PART 4: ERROR CHECKING

#### Test 4.1: Browser Console Errors
**Frontend (http://localhost:3000):**
- [ ] Open F12 Developer Tools
- [ ] Go to Console tab
- [ ] Navigate through all pages
- [ ] Document any RED errors

**Expected:** Only warnings acceptable, NO red errors

**Admin (http://127.0.0.1:8000/admin):**
- [ ] Open F12 Developer Tools
- [ ] Go to Console tab
- [ ] Navigate through admin pages
- [ ] Document any errors

**Expected:** Clean console, Filament loads properly

---

#### Test 4.2: Network Tab Check
- [ ] Open Network tab in DevTools
- [ ] Perform actions (register, login, create property)
- [ ] Check all API calls:
  - [ ] All should return 200/201/204
  - [ ] NO 404 (Not Found)
  - [ ] NO 500 (Server Error)
  - [ ] NO 419 (CSRF Error)

**Expected:** All API calls successful

---

#### Test 4.3: Responsive Design Check
- [ ] Toggle device toolbar (Ctrl+Shift+M)
- [ ] Test mobile view (375x667)
- [ ] Test tablet view (768x1024)
- [ ] Verify layout adapts properly
- [ ] Check navigation on mobile

**Expected:** Responsive design works on all devices

---

## 📊 RESULTS TEMPLATE

### Frontend Tests:
```
✓ Homepage loads
✓ Registration works
✓ Login works
✓ Properties page accessible
✗ Dashboard has error: [describe]
✓ No console errors
```

### Admin Panel Tests:
```
✓ Admin login works
✓ Dashboard loads
✓ Users CRUD works
✓ Properties CRUD works
✓ Settings page loads
✗ Reports has error: [describe]
```

### Integration Tests:
```
✓ Property syncs between admin and frontend
✓ Booking creation works
✓ Booking appears in admin
```

### Error Summary:
```
Console Errors: [list any]
Network Errors: [list any]
UI/UX Issues: [list any]
```

---

## 🔧 QUICK FIXES FOR COMMON ISSUES

### Issue: CSRF 419 Error
```bash
# Backend
cd backend
php artisan config:clear
php artisan cache:clear

# Check .env
SESSION_DOMAIN=
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost,127.0.0.1:3000
```

### Issue: Admin Login Fails
```bash
# Create admin user
cd backend
php artisan tinker
User::create(['name'=>'Admin','email'=>'admin@renthub.com','password'=>bcrypt('password'),'role'=>'admin']);
exit
```

### Issue: Frontend Won't Load
```bash
# Restart frontend
cd frontend
npm run dev
```

### Issue: Properties Don't Show
```sql
-- Check database
mysql -u root renthub
SELECT * FROM properties;
SELECT * FROM users;
```

### Issue: Filament Navigation Error
```php
// Check backend/app/Filament/Pages/Settings.php
protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';
protected static UnitEnum|string|null $navigationGroup = 'Administrare';
```

---

## ✅ FINAL CHECKLIST

Before considering testing complete:

- [ ] All frontend pages load without errors
- [ ] User registration and login work
- [ ] Admin panel fully accessible
- [ ] Can create users, properties, bookings in admin
- [ ] Frontend displays admin-created content
- [ ] No console errors in browser
- [ ] All API calls return proper status codes
- [ ] Responsive design works
- [ ] All core features functional

---

## 📝 REPORT ISSUES HERE

**Found an error?** Document it like this:

```
Page: http://localhost:3000/properties
Error: Property list not loading
Console: TypeError: Cannot read property 'map' of undefined
Status Code: 500
Steps to Reproduce: 
1. Go to properties page
2. Error appears immediately
```

---

**Testing Status:** 🟡 In Progress  
**Last Updated:** November 11, 2025  
**Tester:** [Your Name]

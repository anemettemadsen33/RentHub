# 🎯 Browser Console Verification Guide

## How to Check Browser Console

### Open Developer Tools
1. Press **F12** (or Right-click → Inspect)
2. Click on **Console** tab
3. Clear console: Click 🚫 icon

---

## ✅ Pages to Verify

### 1. Homepage (http://localhost:3000)
**Expected:** Clean console, no errors
```
✓ No red errors
✓ No yellow warnings (minor warnings OK)
✓ Page loads completely
✓ Images load
✓ Navigation works
```

### 2. Properties Page (http://localhost:3000/properties)
**Expected:** Properties load successfully
```
✓ API call to /api/v1/properties succeeds
✓ 4 properties displayed
✓ No CORS errors
✓ No 404 errors
✓ Images render
```

### 3. Property Details (http://localhost:3000/properties/1)
**Expected:** Property details display
```
✓ Property data loads
✓ Images carousel works
✓ Booking form functional
✓ No API errors
```

### 4. Login Page (http://localhost:3000/auth/login)
**Expected:** Form works without errors
```
✓ No JavaScript errors
✓ Form validation works
✓ API endpoint accessible
```

### 5. Dashboard (http://localhost:3000/dashboard)
**Expected:** Stats load (requires login)
```
✓ Dashboard stats API call succeeds
✓ No authentication errors
✓ Charts/stats render
✓ Navigation functional
```

### 6. Profile Page (http://localhost:3000/profile)
**Expected:** Profile loads (requires login)
```
✓ Profile data loads
✓ Form fields populated
✓ No validation errors
```

### 7. Bookings Page (http://localhost:3000/bookings)
**Expected:** Bookings list (requires login)
```
✓ Bookings API call succeeds
✓ Booking #1 displays
✓ Property info shown
✓ No errors
```

---

## ⚠️ Common Warnings (OK to Ignore)

These warnings are normal in development:

```
⚠️ "Image with src ... was detected as the Largest Contentful Paint"
   → Next.js optimization warning (OK)

⚠️ "Prop `className` did not match"
   → Hydration warning (OK in dev)

⚠️ "Using kebab-case for css properties"
   → Styling warning (OK)

ℹ️ "Download the React DevTools"
   → Info message (OK)
```

---

## ❌ Errors to Report

### Critical Errors (Red):
```
❌ "Failed to fetch"
❌ "CORS error"
❌ "404 Not Found"
❌ "500 Internal Server Error"
❌ "Uncaught TypeError"
❌ "Uncaught ReferenceError"
```

### Network Errors:
```
❌ API calls failing
❌ Images not loading (404)
❌ Authentication errors
```

---

## 🔍 Network Tab Verification

Switch to **Network** tab in DevTools:

### Check API Calls
1. Filter by **XHR** or **Fetch**
2. Look for status codes:
   - ✅ 200 OK
   - ✅ 201 Created
   - ✅ 304 Not Modified
   - ❌ 400 Bad Request
   - ❌ 401 Unauthorized
   - ❌ 404 Not Found
   - ❌ 500 Server Error

### Example Good Network Log:
```
✓ GET /api/v1/properties → 200 OK
✓ GET /api/v1/dashboard/stats → 200 OK
✓ POST /api/v1/login → 200 OK
✓ GET /api/v1/bookings → 200 OK
```

---

## 🧪 Quick Test Checklist

Open each page and verify:

- [ ] Homepage loads without errors
- [ ] Properties page shows 4 properties
- [ ] Property details page works
- [ ] Login page functional
- [ ] Registration page works
- [ ] Dashboard loads (after login)
- [ ] Profile page accessible (after login)
- [ ] Bookings page shows booking #1 (after login)
- [ ] Admin panel loads (http://127.0.0.1:8000/admin)
- [ ] No red errors in any console

---

## 📸 Screenshot Locations

If you find errors, take screenshots:
1. Press **Print Screen** or **Win + Shift + S**
2. Save to: `C:\laragon\www\RentHub\screenshots\`
3. Name: `error-[page-name]-[timestamp].png`

---

## ✅ Verification Complete

If all pages load without critical errors:

**Status: ✅ CONSOLE VERIFICATION PASSED**

Console is clean ✓  
No API errors ✓  
All pages functional ✓  
Ready for production testing ✓

---

## 🆘 If You Find Errors

1. **Note the error message** (copy full text)
2. **Note which page** it occurred on
3. **Check if it's a critical error** (red) or warning (yellow)
4. **Report back** with details

Example report:
```
Page: http://localhost:3000/properties
Error: "Failed to fetch /api/v1/properties"
Type: Network Error (red)
Status: 500
```

---

**Last Updated:** November 11, 2025  
**Browser:** Chrome/Edge/Firefox (any modern browser)

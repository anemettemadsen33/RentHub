# 🧪 Live Testing Guide - RentHub Pages & Functions

**Instructions**: Folosește acest ghid pentru a testa manual fiecare funcție și pagină live în browser.

---

## 🚀 Setup - Start Servers

```bash
# Terminal 1 - Backend
cd backend
php artisan serve
# Backend: http://localhost:8000

# Terminal 2 - Frontend  
cd frontend
npm run dev
# Frontend: http://localhost:3000
```

---

## 📋 Test Checklist - Public Pages

### ✅ 1. Home Page
**URL**: http://localhost:3000

**Verificări:**
- [ ] Pagina se încarcă fără erori
- [ ] Hero section apare
- [ ] Search bar funcționează
- [ ] Featured properties apar (dacă există)
- [ ] Footer și header afișate corect
- [ ] Dark mode toggle funcționează
- [ ] Responsive pe mobile

**Console Check:**
```javascript
// Open browser console (F12)
console.log('Testing Home Page')
// Should have no errors in red
```

---

### ✅ 2. Properties List Page
**URL**: http://localhost:3000/properties

**Verificări:**
- [ ] Lista de properties se încarcă
- [ ] Filters funcționează (price, location, etc.)
- [ ] Search funcționează
- [ ] Property cards afișate corect
- [ ] Click pe property -> navighează la detail
- [ ] Pagination funcționează (dacă există)
- [ ] "No properties" message dacă lista e goală

**Test în Console:**
```javascript
// Check if properties are loaded
fetch('http://localhost:8000/api/v1/properties')
  .then(r => r.json())
  .then(d => console.log('Properties:', d))
```

---

### ✅ 3. Property Detail Page
**URL**: http://localhost:3000/properties/1

**Verificări:**
- [ ] Property details se încarcă
- [ ] Images gallery funcționează
- [ ] Description afișată
- [ ] Pricing information corectă
- [ ] Amenities listate
- [ ] Reviews section (dacă există)
- [ ] Book button prezent
- [ ] Map showing location (dacă implementat)

**Test:**
```javascript
// Check property detail
fetch('http://localhost:8000/api/v1/properties/1')
  .then(r => r.json())
  .then(d => console.log('Property Detail:', d))
```

---

### ✅ 4. Login Page
**URL**: http://localhost:3000/auth/login

**Verificări:**
- [ ] Login form afișat
- [ ] Email field validation
- [ ] Password field (hidden text)
- [ ] "Remember me" checkbox
- [ ] "Forgot password" link
- [ ] Submit button funcționează
- [ ] Error messages pentru credentials greșite
- [ ] Success -> redirect to dashboard

**Test Login:**
```javascript
// Test in console (după ce dai submit în form)
localStorage.getItem('token') // Should have token after login
```

**Test Credentials:**
```
Email: admin@renthub.com
Password: password
```

---

### ✅ 5. Register Page
**URL**: http://localhost:3000/auth/register

**Verificări:**
- [ ] Registration form afișat
- [ ] Name field
- [ ] Email field cu validation
- [ ] Password field cu strength indicator
- [ ] Password confirmation field
- [ ] Terms & conditions checkbox
- [ ] Role selection (Host/Guest)
- [ ] Submit button funcționează
- [ ] Success -> redirect sau confirm message
- [ ] Error handling pentru email duplicate

**Test Registration:**
```javascript
// New user test data
const testUser = {
  name: "Test User",
  email: "test" + Date.now() + "@test.com",
  password: "Password123!",
  password_confirmation: "Password123!"
}
```

---

## 🔐 Protected Pages (Require Login)

**⚠️ Important**: Login first la http://localhost:3000/auth/login

---

### ✅ 6. Dashboard
**URL**: http://localhost:3000/dashboard

**Verificări:**
- [ ] Redirect dacă nu ești logged in
- [ ] Welcome message cu user name
- [ ] Stats cards (bookings, properties, revenue)
- [ ] Recent activity/bookings
- [ ] Quick actions buttons
- [ ] Navigation sidebar
- [ ] User profile icon

**Console Test:**
```javascript
// Check if authenticated
fetch('http://localhost:8000/api/v1/user', {
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Accept': 'application/json'
  }
})
.then(r => r.json())
.then(d => console.log('Current User:', d))
```

---

### ✅ 7. My Properties (Host)
**URL**: http://localhost:3000/dashboard/properties

**Verificări:**
- [ ] Lista cu property-urile user-ului
- [ ] "Add New Property" button
- [ ] Edit button pentru fiecare property
- [ ] Delete button cu confirmation
- [ ] View bookings pentru fiecare property
- [ ] Status toggle (active/inactive)

---

### ✅ 8. Create New Property
**URL**: http://localhost:3000/dashboard/properties/new

**Verificări:**
- [ ] Multi-step form sau single page
- [ ] Title field
- [ ] Description textarea
- [ ] Price per night
- [ ] Location fields (address, city, country)
- [ ] Property type dropdown
- [ ] Amenities checkboxes
- [ ] Image upload (multiple)
- [ ] Max guests number
- [ ] Bedrooms/Bathrooms
- [ ] Submit button
- [ ] Validation errors shown
- [ ] Success -> redirect to property list

**Test Create:**
```javascript
// Test property data
const newProperty = {
  title: "Test Property " + Date.now(),
  description: "Beautiful test property",
  price_per_night: 100,
  location: "Test City",
  max_guests: 4,
  bedrooms: 2,
  bathrooms: 1
}
```

---

### ✅ 9. My Bookings
**URL**: http://localhost:3000/bookings

**Verificări:**
- [ ] Lista cu bookings
- [ ] Upcoming vs Past tabs
- [ ] Booking details (dates, property, price)
- [ ] Status (pending, confirmed, cancelled)
- [ ] Cancel booking button
- [ ] View details button
- [ ] Empty state message

---

### ✅ 10. Booking Detail
**URL**: http://localhost:3000/bookings/1

**Verificări:**
- [ ] Booking information completă
- [ ] Property details
- [ ] Guest information (pentru host)
- [ ] Check-in/check-out dates
- [ ] Total price breakdown
- [ ] Payment status
- [ ] Cancel button (dacă permis)
- [ ] Contact host/guest button
- [ ] Download invoice button

---

### ✅ 11. Messages
**URL**: http://localhost:3000/messages

**Verificări:**
- [ ] Conversations list
- [ ] Unread count badge
- [ ] Message thread display
- [ ] Send new message
- [ ] Real-time updates (dacă pusher configurat)
- [ ] Message timestamps
- [ ] User avatars

**Test Messaging:**
```javascript
// Check conversations
fetch('http://localhost:8000/api/v1/conversations', {
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Accept': 'application/json'
  }
})
.then(r => r.json())
.then(d => console.log('Conversations:', d))
```

---

### ✅ 12. Notifications
**URL**: http://localhost:3000/notifications

**Verificări:**
- [ ] Notifications list
- [ ] Mark as read functionality
- [ ] Mark all as read
- [ ] Delete notification
- [ ] Notification types (booking, message, payment)
- [ ] Click -> navigate to related item

---

### ✅ 13. Profile
**URL**: http://localhost:3000/profile

**Verificări:**
- [ ] User info displayed
- [ ] Edit profile form
- [ ] Avatar upload
- [ ] Email (може fi readonly)
- [ ] Phone number
- [ ] Bio/Description
- [ ] Language preference
- [ ] Currency preference
- [ ] Update button
- [ ] Success message

---

### ✅ 14. Settings
**URL**: http://localhost:3000/dashboard/settings

**Verificări:**
- [ ] Account settings section
- [ ] Notification preferences
- [ ] Privacy settings
- [ ] Payment methods
- [ ] Language selection
- [ ] Currency selection
- [ ] Change password form
- [ ] 2FA toggle (dacă implementat)
- [ ] Delete account option

---

### ✅ 15. Wishlist
**URL**: http://localhost:3000/wishlists

**Verificări:**
- [ ] Saved properties list
- [ ] Remove from wishlist button
- [ ] Empty state message
- [ ] Click property -> go to detail
- [ ] Add to wishlist button on property pages

**Test:**
```javascript
// Add to wishlist
fetch('http://localhost:8000/api/v1/wishlists', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token'),
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({ property_id: 1 })
})
.then(r => r.json())
.then(d => console.log('Added to Wishlist:', d))
```

---

### ✅ 16. Saved Searches
**URL**: http://localhost:3000/saved-searches

**Verificări:**
- [ ] Saved searches list
- [ ] Search criteria displayed
- [ ] Run search again button
- [ ] Delete saved search
- [ ] Create new saved search
- [ ] Email notifications toggle

---

## 🧪 Advanced Features Testing

### ✅ 17. Payment Flow
**Test booking with payment:**

1. Go to property detail
2. Select dates
3. Click "Book Now"
4. Review booking details
5. Enter payment information
6. Submit payment
7. Check confirmation page
8. Verify email sent (check logs)

**Test Payment:**
```javascript
// Stripe test card
Card: 4242 4242 4242 4242
Expiry: 12/25
CVC: 123
```

---

### ✅ 18. Review System
**Test leaving review:**

1. Go to completed booking
2. Click "Leave Review"
3. Rate property (stars)
4. Write review text
5. Upload photos (optional)
6. Submit review
7. Check property page for review

---

### ✅ 19. Host Features

**Calendar Management:**
- [ ] Block dates
- [ ] Set special pricing
- [ ] View bookings on calendar

**Analytics:**
- [ ] Revenue dashboard
- [ ] Booking statistics
- [ ] Property performance

---

### ✅ 20. Search & Filters
**URL**: http://localhost:3000/properties

**Test wszystkie filters:**
- [ ] Location search (autocomplete)
- [ ] Date range picker
- [ ] Guests number
- [ ] Price range slider
- [ ] Property type checkboxes
- [ ] Amenities filters
- [ ] Sort by (price, rating, newest)

**Console Test:**
```javascript
// Test search with filters
const filters = {
  location: 'Paris',
  min_price: 50,
  max_price: 200,
  guests: 2,
  check_in: '2025-12-01',
  check_out: '2025-12-07'
}

const query = new URLSearchParams(filters).toString()
fetch(`http://localhost:8000/api/v1/properties?${query}`)
  .then(r => r.json())
  .then(d => console.log('Filtered Properties:', d))
```

---

## 🔍 Error Testing

### Test Error Scenarios

**1. 404 Page**
- Visit: http://localhost:3000/nonexistent
- Should show custom 404 page

**2. 500 Error**
- Trigger server error
- Should show error boundary

**3. Network Offline**
- Disconnect internet
- Try to load page
- Should show offline message

**4. Invalid Token**
```javascript
// Set invalid token
localStorage.setItem('token', 'invalid_token')
// Try to access protected page
// Should redirect to login
```

---

## 📊 Performance Testing

### Check Performance
```javascript
// In browser console
performance.getEntriesByType('navigation')[0].duration
// Should be < 3000ms for good performance

// Check bundle size
// In Network tab, filter by JS
// Main bundle should be < 500KB
```

---

## ✅ Final Checklist

**Browser Compatibility:**
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (if Mac available)

**Responsive Design:**
- [ ] Desktop (1920x1080)
- [ ] Laptop (1366x768)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

**Dark Mode:**
- [ ] Toggle works
- [ ] All pages support dark mode
- [ ] No contrast issues

**Accessibility:**
- [ ] Tab navigation works
- [ ] Screen reader friendly
- [ ] ARIA labels present
- [ ] Focus indicators visible

---

## 📝 Report Issues

**Pentru fiecare issue găsit, notează:**
1. Pagina/URL
2. Pașii de reproducere
3. Comportament așteptat
4. Comportament actual
5. Screenshot/error message
6. Browser & device

---

## 🎯 Success Criteria

**Aplicația este ready dacă:**
- ✅ Toate paginile publice se încarcă
- ✅ Login & Register funcționează
- ✅ Dashboard afișează date
- ✅ CRUD operations funcționează
- ✅ No critical errors în console
- ✅ Mobile responsive
- ✅ Dark mode funcționează

---

**Happy Testing! 🧪**

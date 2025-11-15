# Complete Booking Flow - Testing Checklist

## 🚀 All Services Running ✅

- ✅ **Backend:** http://localhost:8000 (PID 16552)
- ✅ **WebSocket (Reverb):** ws://0.0.0.0:8080 (PID 20568)
- ✅ **Frontend:** http://localhost:3001 (Port 3000 was in use)

## 📊 Test Data Available

- **Users:** 3 accounts
- **Properties:** 5 listings
- **Bookings:** 1 existing booking

## 🧪 End-to-End Testing Workflow

### 1. Login to Application

**URL:** http://localhost:3001/login

**Test Accounts:**
```
Admin Account:
Email: admin@renthub.com
Password: password

Property Owner Account:
Email: owner@renthub.test
Password: password

Guest Account:
Email: guest@renthub.test
Password: password
```

### 2. Browse Properties

- [ ] Navigate to home page or properties list
- [ ] View property cards with images, price, location
- [ ] Click on a property to view details
- [ ] Check property amenities, description, pricing
- [ ] Verify map location displays correctly
- [ ] Review property reviews/ratings

### 3. Create a Booking

**Test Booking Details:**
- Check-in: Tomorrow's date
- Check-out: 3 days later
- Guests: 2 adults

**Steps:**
- [ ] Click "Book Now" or "Reserve" button
- [ ] Select check-in/check-out dates in calendar
- [ ] Enter number of guests
- [ ] Review booking summary:
  - Nightly rate × nights
  - Cleaning fee (if applicable)
  - Service fee (5%)
  - Tax (10%)
  - Total amount
- [ ] Click "Confirm Booking" or "Continue to Payment"
- [ ] Verify redirect to payment page

### 4. Process Payment with Stripe

**Payment Page:** `/bookings/[id]/payment`

#### Option A: Test Stripe Card Payment

- [ ] Select "Card Payment" option
- [ ] Click "Pay with Card" button
- [ ] Wait for Stripe form to load
- [ ] Enter test card details:
  ```
  Card Number: 4242 4242 4242 4242
  Expiry Date: 12/34
  CVC: 123
  Name: Test User
  ```
- [ ] Verify amount displayed is correct
- [ ] Click "Pay [amount]" button
- [ ] Watch for loading state
- [ ] Verify success notification appears
- [ ] Check redirect to confirmation page

**Expected Behaviors:**
- ✅ Stripe form loads within 2 seconds
- ✅ Card validation works (red border on invalid)
- ✅ Payment processes within 3 seconds
- ✅ Success toast notification shows
- ✅ Redirect to `/bookings/[id]/confirmation`

#### Option B: Test Bank Transfer

- [ ] Select "Bank Transfer" option
- [ ] Review bank account details displayed:
  - Bank name: Banca Transilvania
  - Beneficiary: RentHub SRL
  - IBAN: RO49 AAAA 1B31 0075 9384 0000
  - SWIFT: BTRLRO22
  - Reference: INV-000001
- [ ] Click "Download Invoice" button
- [ ] Verify PDF invoice generates
- [ ] Check payment status shows "Pending"

### 5. Verify Confirmation Page

**URL:** `/bookings/[id]/confirmation`

- [ ] Booking confirmation message displays
- [ ] Booking details are correct:
  - Property name and address
  - Check-in/check-out dates
  - Number of guests
  - Total amount paid
- [ ] Payment status shows "Completed" (for Stripe) or "Pending" (for bank transfer)
- [ ] Invoice download button available
- [ ] Booking reference number displayed

### 6. Check Bookings List

**URL:** `/bookings` or `/account/bookings`

- [ ] Navigate to user's bookings page
- [ ] New booking appears in list
- [ ] Booking status is correct
- [ ] Click on booking to view details
- [ ] Verify all information matches

### 7. Backend Verification

**Database Checks:**

```bash
# Check payment was created
cd backend
php artisan tinker --execute="App\Models\Payment::latest()->first();"

# Check booking status updated
php artisan tinker --execute="App\Models\Booking::latest()->first()->payment_status;"

# Check invoice was generated
php artisan tinker --execute="App\Models\Invoice::latest()->first();"
```

**Expected Results:**
- ✅ Payment record exists with status "completed"
- ✅ Booking payment_status is "paid"
- ✅ Invoice generated with correct amounts
- ✅ Transaction ID stored (Stripe PaymentIntent ID)

### 8. Real-time Features Testing (Optional)

**WebSocket Notifications:**
- [ ] Open browser console (F12)
- [ ] Check for WebSocket connection to ws://localhost:8080
- [ ] Create booking and watch for real-time notifications
- [ ] Verify property owner receives payment notification

**Messages Page:**
- [ ] Navigate to `/messages`
- [ ] Send message to property owner
- [ ] Verify real-time message delivery
- [ ] Check WebSocket events in console

## 🐛 Common Issues & Solutions

### Payment Form Not Loading
**Problem:** Stripe form doesn't appear  
**Solution:** 
- Check browser console for errors
- Verify `NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY` in `.env.local`
- Ensure Stripe packages installed: `npm list @stripe/stripe-js`

### "Failed to Create Payment Intent"
**Problem:** Error when clicking "Pay with Card"  
**Solution:**
- Verify backend is running on port 8000
- Check user is logged in (token in localStorage)
- Verify booking ID exists in database
- Check backend logs: `backend/storage/logs/laravel.log`

### Payment Succeeds but Booking Not Updated
**Problem:** Payment processes but booking still shows "unpaid"  
**Solution:**
- Check `payments` table for new record
- Verify `payment_method` is 'stripe' or 'card'
- Check backend logs for errors during status update
- Manually update: `php artisan tinker --execute="App\Models\Booking::find(1)->update(['payment_status' => 'paid']);"`

### Port 3000 Already in Use
**Problem:** Frontend can't start on port 3000  
**Solution:**
- Next.js auto-switches to port 3001 (already done!)
- Update browser bookmark to http://localhost:3001
- Or kill process on port 3000: `Get-Process -Id 16044 | Stop-Process`

### WebSocket Connection Failed
**Problem:** Real-time features not working  
**Solution:**
- Verify Reverb is running: `netstat -ano | findstr :8080`
- Restart Reverb: `php artisan reverb:start`
- Check `.env.local` has correct WebSocket config

## 📸 Screenshot Checklist

For documentation/demo purposes, capture:
- [ ] Property listing page
- [ ] Property details page
- [ ] Booking form with date selection
- [ ] Payment page with Stripe form
- [ ] Successful payment confirmation
- [ ] Bookings list showing new booking

## ✅ Success Criteria

**Complete booking flow is successful if:**

1. ✅ User can browse and select a property
2. ✅ Booking form validates dates and guest count
3. ✅ Booking summary shows correct pricing
4. ✅ Payment page loads with Stripe form
5. ✅ Test card payment processes successfully
6. ✅ Payment creates database records:
   - Payment record (status: completed)
   - Booking updated (payment_status: paid)
   - Invoice generated
7. ✅ User redirected to confirmation page
8. ✅ Booking appears in user's bookings list
9. ✅ No errors in browser console
10. ✅ No errors in backend logs

## 🎯 Next Actions After Testing

Based on test results:

**If Everything Works:**
- ✅ Mark Task 8 as complete
- Document any UX improvements needed
- Move to Task 9 (next-intl migration) or Task 6/7 (OAuth/File Upload)

**If Issues Found:**
- Document specific bugs
- Prioritize critical issues
- Fix blockers before proceeding

## 📝 Test Log Template

```
Date: 2025-11-15
Tester: [Your Name]
Environment: Local Development

STEP 1 - Login: [ PASS / FAIL ]
Notes: _________________________________

STEP 2 - Browse Properties: [ PASS / FAIL ]
Notes: _________________________________

STEP 3 - Create Booking: [ PASS / FAIL ]
Notes: _________________________________

STEP 4 - Stripe Payment: [ PASS / FAIL ]
Card Used: 4242 4242 4242 4242
Amount: _______
Notes: _________________________________

STEP 5 - Confirmation: [ PASS / FAIL ]
Notes: _________________________________

STEP 6 - Bookings List: [ PASS / FAIL ]
Notes: _________________________________

OVERALL RESULT: [ PASS / FAIL ]

Critical Issues Found:
1. _________________________________
2. _________________________________

Minor Issues Found:
1. _________________________________
2. _________________________________
```

---

**Ready to start testing!** Open http://localhost:3001 and begin with Step 1. 🚀

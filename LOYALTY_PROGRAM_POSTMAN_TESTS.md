# 🧪 Loyalty Program - Postman Testing Guide

## 📋 Quick Start

### Prerequisites
1. Backend server running: `cd backend && php artisan serve`
2. Database migrated with loyalty tables
3. Loyalty tiers seeded
4. User account created (for testing)
5. Admin account created (for admin endpoints)

---

## 🔑 Step 1: Get Authentication Token

### Register/Login a User
```http
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password123"
}
```

**Save the token from response:**
```json
{
  "token": "1|xxxxxxxxxxxxx"
}
```

---

## 🎮 Step 2: Test User Endpoints

### 1. Get Current User Loyalty Points

```http
GET http://localhost:8000/api/v1/loyalty/points
Authorization: Bearer 1|xxxxxxxxxxxxx
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "tier": {
      "name": "Silver",
      "slug": "silver",
      "badge_color": "#C0C0C0",
      "icon": "🥈"
    },
    "total_points": 100,
    "available_points": 100,
    "redeemed_points": 0,
    "next_tier": "Gold",
    "points_to_next_tier": 900
  }
}
```

**Test Cases:**
- ✅ New user should have 100 welcome bonus points
- ✅ New user should be in Silver tier
- ✅ Should show correct points to next tier

---

### 2. Get Points History

```http
GET http://localhost:8000/api/v1/loyalty/points/history?per_page=10
Authorization: Bearer 1|xxxxxxxxxxxxx
```

**Test with filters:**
```http
GET http://localhost:8000/api/v1/loyalty/points/history?type=earned&per_page=10
GET http://localhost:8000/api/v1/loyalty/points/history?type=bonus&per_page=10
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "type": "bonus",
        "points": 100,
        "description": "Welcome bonus",
        "expires_at": "2026-11-03T10:00:00Z",
        "created_at": "2025-11-03T10:00:00Z"
      }
    ],
    "total": 1
  }
}
```

---

### 3. Calculate Points Value

```http
POST http://localhost:8000/api/v1/loyalty/points/calculate
Authorization: Bearer 1|xxxxxxxxxxxxx
Content-Type: application/json

{
  "points": 1000
}
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "points": 1000,
    "discount_value": 10.00,
    "currency": "USD",
    "conversion_rate": "100 points = $1"
  }
}
```

**Test Cases:**
- ✅ 100 points = $1
- ✅ 1000 points = $10
- ✅ 5000 points = $50

---

### 4. Redeem Points (Requires Booking)

**First, create a test booking, then:**

```http
POST http://localhost:8000/api/v1/loyalty/points/redeem
Authorization: Bearer 1|xxxxxxxxxxxxx
Content-Type: application/json

{
  "points": 1000,
  "booking_id": 1
}
```

**Test Cases:**

**❌ Test 1: Insufficient Points**
```json
{
  "points": 1000,
  "booking_id": 1
}
```
Expected: Error - "Insufficient points"

**❌ Test 2: Below Minimum (500 points)**
```json
{
  "points": 400,
  "booking_id": 1
}
```
Expected: Error - "Minimum 500 points required"

**✅ Test 3: Valid Redemption**
```json
{
  "points": 1000,
  "booking_id": 1
}
```
Expected: Success

**❌ Test 4: Exceeds 50% of Booking**
```json
{
  "points": 50000,
  "booking_id": 1
}
```
Expected: Error - "Exceeds maximum discount"

---

### 5. Get Expiring Points

```http
GET http://localhost:8000/api/v1/loyalty/points/expiring?days=30
Authorization: Bearer 1|xxxxxxxxxxxxx
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "expiring_soon": 0,
    "expires_at": null,
    "days_remaining": null
  }
}
```

---

### 6. Get All Tiers (Public)

```http
GET http://localhost:8000/api/v1/loyalty/tiers
```

**Expected Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Silver",
      "slug": "silver",
      "min_points": 0,
      "max_points": 999,
      "discount_percentage": 5,
      "points_multiplier": 1,
      "priority_booking": false,
      "badge_color": "#C0C0C0",
      "icon": "🥈",
      "benefits": [
        "Welcome bonus: 100 points",
        "Earn 1 point per $1 spent",
        "5% discount on bookings"
      ]
    },
    {
      "id": 2,
      "name": "Gold",
      "slug": "gold",
      "min_points": 1000,
      "max_points": 4999,
      "discount_percentage": 10,
      "points_multiplier": 1.5,
      "priority_booking": true,
      "badge_color": "#FFD700",
      "icon": "🥇"
    },
    {
      "id": 3,
      "name": "Platinum",
      "slug": "platinum",
      "min_points": 5000,
      "max_points": null,
      "discount_percentage": 15,
      "points_multiplier": 2,
      "priority_booking": true,
      "badge_color": "#E5E4E2",
      "icon": "💎"
    }
  ]
}
```

---

### 7. Get Specific Tier Details

```http
GET http://localhost:8000/api/v1/loyalty/tiers/gold
GET http://localhost:8000/api/v1/loyalty/tiers/platinum
```

---

## 👑 Step 3: Test Admin Endpoints

### Get Admin Token First

```http
POST http://localhost:8000/api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "admin123"
}
```

---

### 1. Award Points Manually

```http
POST http://localhost:8000/api/v1/admin/loyalty/award-points
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "user_id": 1,
  "points": 500,
  "description": "Customer service bonus for issue #123",
  "expires_in_days": 365
}
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "500 points awarded successfully",
  "data": {
    "user_id": 1,
    "points_awarded": 500,
    "new_total": 600,
    "tier": "Silver"
  }
}
```

**Test Cases:**
- ✅ Award 500 points
- ✅ Award 5000 points (should upgrade to Gold)
- ✅ Award 10000 points (should upgrade to Platinum)

---

### 2. Adjust User Points

```http
POST http://localhost:8000/api/v1/admin/loyalty/adjust-points
Authorization: Bearer {admin-token}
Content-Type: application/json

{
  "user_id": 1,
  "points": -200,
  "description": "Correction for duplicate points"
}
```

**Test Cases:**
- ✅ Positive adjustment: `{"points": 300}`
- ✅ Negative adjustment: `{"points": -200}`

---

### 3. Get Loyalty Leaderboard

```http
GET http://localhost:8000/api/v1/admin/loyalty/leaderboard?limit=10
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "data": [
    {
      "user_id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "total_points": 8500,
      "available_points": 7000,
      "tier": {
        "name": "Platinum",
        "slug": "platinum"
      },
      "bookings_count": 15
    }
  ]
}
```

---

### 4. Get User Loyalty Details

```http
GET http://localhost:8000/api/v1/admin/loyalty/users/1
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com"
    },
    "loyalty": {
      "tier": "Silver",
      "total_points": 600,
      "available_points": 600,
      "redeemed_points": 0
    },
    "transactions": [
      {
        "type": "bonus",
        "points": 100,
        "description": "Welcome bonus",
        "created_at": "2025-11-03T10:00:00Z"
      },
      {
        "type": "bonus",
        "points": 500,
        "description": "Customer service bonus",
        "created_at": "2025-11-03T11:00:00Z"
      }
    ]
  }
}
```

---

### 5. Get Loyalty Statistics

```http
GET http://localhost:8000/api/v1/admin/loyalty/statistics
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "total_users_with_loyalty": 150,
    "total_points_awarded": 25000,
    "total_points_redeemed": 5000,
    "total_points_available": 20000,
    "tier_distribution": {
      "Silver": 100,
      "Gold": 40,
      "Platinum": 10
    },
    "points_expiring_30_days": 2000,
    "average_points_per_user": 166.67
  }
}
```

---

### 6. Award Birthday Bonuses (Cron Simulation)

```http
POST http://localhost:8000/api/v1/admin/loyalty/birthday-bonuses
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "Birthday bonuses awarded",
  "data": {
    "users_awarded": 5,
    "total_points_awarded": 750
  }
}
```

**Note:** Only awards to users whose birthday is today.

---

### 7. Expire Old Points (Cron Simulation)

```http
POST http://localhost:8000/api/v1/admin/loyalty/expire-points
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "message": "Old points expired",
  "data": {
    "points_expired": 1000,
    "users_affected": 10
  }
}
```

---

### 8. Get Users with Expiring Points

```http
GET http://localhost:8000/api/v1/admin/loyalty/expiring-soon?days=30
Authorization: Bearer {admin-token}
```

**Expected Response:**
```json
{
  "status": "success",
  "data": [
    {
      "user_id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "expiring_points": 500,
      "expires_at": "2025-12-03T10:00:00Z",
      "days_remaining": 30
    }
  ]
}
```

---

## 🎯 Complete Testing Workflow

### Scenario 1: New User Journey

1. **Register User**
   ```http
   POST /api/v1/auth/register
   ```

2. **Check Welcome Bonus** (Should have 100 points)
   ```http
   GET /api/v1/loyalty/points
   ```

3. **View Tier** (Should be Silver)
   ```http
   GET /api/v1/loyalty/tiers/silver
   ```

---

### Scenario 2: Earning & Redeeming Points

1. **Create a Booking** (Total: $200)
   ```http
   POST /api/v1/bookings
   ```

2. **Complete Booking** (Should award ~200 points)
   ```http
   POST /api/v1/bookings/{id}/complete
   ```

3. **Check Points Balance**
   ```http
   GET /api/v1/loyalty/points
   ```
   Expected: 100 (welcome) + 200 (booking) = 300 points

4. **View History**
   ```http
   GET /api/v1/loyalty/points/history
   ```

5. **Calculate Redemption Value** (For 1000 points if available)
   ```http
   POST /api/v1/loyalty/points/calculate
   ```

---

### Scenario 3: Tier Upgrade

1. **Admin Awards 1000 Points**
   ```http
   POST /api/v1/admin/loyalty/award-points
   {
     "user_id": 1,
     "points": 1000,
     "description": "Special promotion"
   }
   ```

2. **Check User Tier** (Should upgrade to Gold)
   ```http
   GET /api/v1/loyalty/points
   ```
   Expected tier: "Gold"

3. **Verify on Leaderboard**
   ```http
   GET /api/v1/admin/loyalty/leaderboard
   ```

---

### Scenario 4: Points Expiration

1. **Admin Checks Expiring Points**
   ```http
   GET /api/v1/admin/loyalty/expiring-soon?days=30
   ```

2. **Admin Runs Expiration** (Manual trigger)
   ```http
   POST /api/v1/admin/loyalty/expire-points
   ```

3. **User Checks Points**
   ```http
   GET /api/v1/loyalty/points
   ```

---

## 🐛 Common Issues & Solutions

### Issue 1: "Unauthenticated" Error
**Solution:** Check Authorization header format:
```
Authorization: Bearer {token}
```

### Issue 2: "Insufficient points"
**Solution:** Award points first:
```http
POST /api/v1/admin/loyalty/award-points
```

### Issue 3: "User loyalty not found"
**Solution:** Ensure user registered and welcome bonus triggered.

### Issue 4: Points not showing
**Solution:** Check:
1. Migrations run: `php artisan migrate`
2. Seeders run: `php artisan db:seed --class=LoyaltyTierSeeder`
3. User has UserLoyalty record

---

## ✅ Testing Checklist

### User Endpoints
- [ ] Get current points and tier
- [ ] View points history with pagination
- [ ] Filter history by type (earned, redeemed, bonus, expired)
- [ ] Calculate points value
- [ ] Redeem points successfully
- [ ] Redeem with validation errors
- [ ] View expiring points
- [ ] Get all tiers
- [ ] Get specific tier details

### Admin Endpoints
- [ ] Award points manually
- [ ] Adjust points (positive/negative)
- [ ] View leaderboard
- [ ] Get user loyalty details
- [ ] View system statistics
- [ ] Award birthday bonuses
- [ ] Expire old points
- [ ] Get users with expiring points

### Business Logic
- [ ] Welcome bonus (100 points) on registration
- [ ] Points awarded after booking completion
- [ ] Tier multipliers work (Silver 1x, Gold 1.5x, Platinum 2x)
- [ ] Automatic tier upgrade at thresholds
- [ ] Points redemption validation (min 500, max 50% of booking)
- [ ] Points expiration after 12 months
- [ ] Birthday bonuses based on tier

### Integration
- [ ] Loyalty hooks into BookingController
- [ ] Loyalty hooks into PaymentController
- [ ] Discount applied when redeeming points
- [ ] Tier benefits applied to bookings

---

## 📊 Expected Test Results

| Test | Expected Points | Expected Tier |
|------|----------------|---------------|
| New registration | 100 | Silver |
| After $200 booking | 300 | Silver |
| After admin awards 1000 | 1300 | Gold |
| After $5000 total bookings | ~5100 | Platinum |

---

## 🎉 Success Criteria

✅ All user endpoints return correct data  
✅ All admin endpoints work properly  
✅ Points are calculated correctly  
✅ Tier upgrades happen automatically  
✅ Redemption validation works  
✅ History tracking is accurate  
✅ Statistics are correct  

---

**Ready for Production Testing!**

Use this guide to thoroughly test the Loyalty Program before deploying to production.

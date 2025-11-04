# Loyalty Program API Guide

Complete guide for integrating the RentHub Loyalty Program into your application.

## Table of Contents
- [Overview](#overview)
- [Tier System](#tier-system)
- [API Endpoints](#api-endpoints)
- [Usage Examples](#usage-examples)
- [Integration Guide](#integration-guide)

## Overview

The RentHub Loyalty Program rewards users with points for bookings, which can be redeemed for discounts. Users progress through three tiers (Silver, Gold, Platinum) with increasing benefits.

**Key Features:**
- Earn 1-2 points per dollar spent (tier-based)
- 100 points = $1 discount
- Three tier levels with exclusive benefits
- Automatic tier upgrades
- Points expire after 12 months
- Birthday bonuses
- Referral rewards

## Tier System

### Silver Tier (0-999 points)
- **Discount:** 5%
- **Point Multiplier:** 1.0x
- **Benefits:**
  - Early access to new properties
  - Birthday bonus: 100 points
  - Basic support

### Gold Tier (1,000-4,999 points)
- **Discount:** 10%
- **Point Multiplier:** 1.5x
- **Benefits:**
  - All Silver benefits
  - Priority booking
  - Priority support (24/7)
  - Free cancellation (24h before)
  - Birthday bonus: 250 points

### Platinum Tier (5,000+ points)
- **Discount:** 15%
- **Point Multiplier:** 2.0x
- **Benefits:**
  - All Gold benefits
  - Personal concierge service
  - Exclusive VIP properties
  - Airport pickup service
  - Late checkout
  - Welcome gift
  - Free cancellation (48h before)
  - Birthday bonus: 500 points

## API Endpoints

### Base URL
```
https://yourdomain.com/api/v1
```

### Authentication
All user-specific endpoints require Bearer token authentication:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

---

## Endpoint Reference

### 1. Get User Loyalty Information
Get the authenticated user's complete loyalty profile.

**Endpoint:** `GET /loyalty`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "loyalty": {
      "id": 1,
      "user_id": 1,
      "current_tier_id": 2,
      "total_points_earned": 1250,
      "available_points": 850,
      "redeemed_points": 400,
      "expired_points": 0,
      "tier_achieved_at": "2025-01-15T10:30:00.000000Z"
    },
    "tier": {
      "id": 2,
      "name": "Gold",
      "slug": "gold",
      "discount_percentage": 10,
      "points_multiplier": 1.5,
      "priority_booking": true,
      "badge_color": "#FFD700",
      "icon": "🥇"
    },
    "stats": {
      "total_earned": 1250,
      "available": 850,
      "redeemed": 400,
      "expired": 0,
      "progress_to_next_tier": 25.0,
      "points_to_next_tier": 3750
    },
    "benefits": [...]
  }
}
```

---

### 2. Get All Loyalty Tiers
Retrieve information about all available loyalty tiers.

**Endpoint:** `GET /loyalty/tiers`  
**Auth Required:** No

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Silver",
      "slug": "silver",
      "min_points": 0,
      "max_points": 999,
      "discount_percentage": 5,
      "points_multiplier": 1.0,
      "priority_booking": false,
      "badge_color": "#C0C0C0",
      "icon": "🥈",
      "benefits": [
        {
          "name": "5% Discount",
          "description": "Get 5% off on all bookings",
          "type": "discount",
          "icon": "💰"
        }
      ]
    }
  ]
}
```

---

### 3. Get Transaction History
Get the user's loyalty transaction history with pagination.

**Endpoint:** `GET /loyalty/transactions`  
**Auth Required:** Yes

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "type": "earned",
        "points": 150,
        "description": "Points earned from booking #123",
        "booking_id": 123,
        "expires_at": "2026-03-15T00:00:00.000000Z",
        "is_expired": false,
        "created_at": "2025-03-15T14:30:00.000000Z"
      },
      {
        "id": 2,
        "type": "redeemed",
        "points": -500,
        "description": "Redeemed 500 points",
        "booking_id": 124,
        "created_at": "2025-03-20T10:15:00.000000Z"
      }
    ],
    "per_page": 20,
    "total": 45
  }
}
```

---

### 4. Redeem Points
Redeem loyalty points for a discount.

**Endpoint:** `POST /loyalty/redeem`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "points": 500,
  "booking_id": 123,
  "description": "Applied to booking discount"
}
```

**Validation:**
- `points`: Required, integer, minimum 500
- `booking_id`: Optional, must exist in bookings table
- `description`: Optional, string

**Response:**
```json
{
  "success": true,
  "message": "Points redeemed successfully",
  "data": {
    "transaction": {
      "id": 15,
      "type": "redeemed",
      "points": -500,
      "description": "Applied to booking discount",
      "created_at": "2025-03-25T15:20:00.000000Z"
    },
    "discount_amount": 5.00,
    "remaining_points": 350
  }
}
```

**Error Response (Insufficient Points):**
```json
{
  "success": false,
  "message": "Insufficient points to redeem"
}
```

---

### 5. Calculate Discount from Points
Calculate the discount amount for a given number of points.

**Endpoint:** `POST /loyalty/calculate-discount`  
**Auth Required:** Yes

**Request Body:**
```json
{
  "points": 1000
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "points": 1000,
    "discount_amount": 10.00
  }
}
```

---

### 6. Get Leaderboard
Get the top loyalty program members.

**Endpoint:** `GET /loyalty/leaderboard`  
**Auth Required:** No

**Query Parameters:**
- `limit` (optional): Number of users to return (default: 10, max: 100)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "tier": {
        "name": "Platinum",
        "badge_color": "#E5E4E2"
      },
      "total_points": 15420,
      "available_points": 12300
    }
  ]
}
```

---

### 7. Claim Birthday Bonus
Claim the annual birthday bonus points.

**Endpoint:** `POST /loyalty/claim-birthday`  
**Auth Required:** Yes

**Response:**
```json
{
  "success": true,
  "message": "Birthday bonus awarded!",
  "data": {
    "transaction": {
      "id": 20,
      "type": "bonus",
      "points": 250,
      "description": "Birthday bonus"
    },
    "points_awarded": 250
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Birthday bonus already claimed this year or not eligible"
}
```

---

### 8. Get Expiring Points
Get points that will expire soon.

**Endpoint:** `GET /loyalty/expiring-points`  
**Auth Required:** Yes

**Query Parameters:**
- `days` (optional): Number of days to look ahead (default: 30)

**Response:**
```json
{
  "success": true,
  "data": {
    "total_expiring": 250,
    "transactions": [
      {
        "id": 8,
        "points": 150,
        "expires_at": "2025-04-15T00:00:00.000000Z",
        "description": "Points earned from booking #101"
      },
      {
        "id": 12,
        "points": 100,
        "expires_at": "2025-04-20T00:00:00.000000Z",
        "description": "Welcome bonus"
      }
    ],
    "days": 30
  }
}
```

---

### 9. Get Tier Benefits
Get detailed benefits for a specific tier.

**Endpoint:** `GET /loyalty/tiers/{tierId}/benefits`  
**Auth Required:** No

**Response:**
```json
{
  "success": true,
  "data": {
    "tier": {
      "id": 3,
      "name": "Platinum",
      "slug": "platinum",
      "min_points": 5000,
      "discount_percentage": 15,
      "points_multiplier": 2.0,
      "priority_booking": true,
      "badge_color": "#E5E4E2"
    },
    "benefits": [
      {
        "name": "15% Discount",
        "description": "Get 15% off on all bookings",
        "type": "discount",
        "value": "15%",
        "icon": "💰"
      },
      {
        "name": "Personal Concierge",
        "description": "Dedicated concierge for all your needs",
        "type": "personal_concierge",
        "value": "yes",
        "icon": "👔"
      }
    ]
  }
}
```

---

## Usage Examples

### JavaScript/TypeScript

#### Initialize Loyalty on User Registration
```typescript
// After user registration
async function initializeLoyalty(userId: number) {
  const response = await fetch('/api/v1/loyalty', {
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    }
  });
  
  const { data } = await response.json();
  return data;
}
```

#### Display User's Loyalty Dashboard
```typescript
async function getLoyaltyDashboard() {
  const response = await fetch('/api/v1/loyalty', {
    headers: { 'Authorization': `Bearer ${accessToken}` }
  });
  
  const { data } = await response.json();
  
  return {
    points: data.loyalty.available_points,
    tier: data.tier.name,
    discount: data.tier.discount_percentage,
    progressToNext: data.stats.progress_to_next_tier,
    pointsNeeded: data.stats.points_to_next_tier
  };
}
```

#### Redeem Points at Checkout
```typescript
async function applyLoyaltyDiscount(bookingId: number, points: number) {
  const response = await fetch('/api/v1/loyalty/redeem', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      points: points,
      booking_id: bookingId,
      description: 'Applied discount to booking'
    })
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }
  
  const { data } = await response.json();
  return data.discount_amount;
}
```

#### Check Points Expiring Soon
```typescript
async function checkExpiringPoints(days = 30) {
  const response = await fetch(`/api/v1/loyalty/expiring-points?days=${days}`, {
    headers: { 'Authorization': `Bearer ${accessToken}` }
  });
  
  const { data } = await response.json();
  
  if (data.total_expiring > 0) {
    alert(`You have ${data.total_expiring} points expiring soon!`);
  }
}
```

### React Component Example

```tsx
import { useState, useEffect } from 'react';

const LoyaltyDashboard = () => {
  const [loyalty, setLoyalty] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchLoyaltyData();
  }, []);

  const fetchLoyaltyData = async () => {
    try {
      const response = await fetch('/api/v1/loyalty', {
        headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
      });
      const { data } = await response.json();
      setLoyalty(data);
    } catch (error) {
      console.error('Error fetching loyalty data:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="loyalty-dashboard">
      <div className="tier-badge" style={{ backgroundColor: loyalty.tier.badge_color }}>
        {loyalty.tier.icon} {loyalty.tier.name}
      </div>
      
      <div className="points-display">
        <h2>{loyalty.loyalty.available_points.toLocaleString()} Points</h2>
        <p>Worth ${(loyalty.loyalty.available_points / 100).toFixed(2)}</p>
      </div>

      <div className="progress-bar">
        <div 
          className="progress-fill" 
          style={{ width: `${loyalty.stats.progress_to_next_tier}%` }}
        />
        <p>{loyalty.stats.points_to_next_tier} points to next tier</p>
      </div>

      <div className="benefits">
        <h3>Your Benefits</h3>
        <ul>
          <li>🎫 {loyalty.tier.discount_percentage}% discount on all bookings</li>
          <li>⭐ Earn {loyalty.tier.points_multiplier}x points</li>
          {loyalty.tier.priority_booking && <li>⚡ Priority booking access</li>}
        </ul>
      </div>
    </div>
  );
};
```

### Python Example

```python
import requests

class LoyaltyClient:
    def __init__(self, base_url, access_token):
        self.base_url = base_url
        self.headers = {
            'Authorization': f'Bearer {access_token}',
            'Content-Type': 'application/json'
        }
    
    def get_loyalty_info(self):
        response = requests.get(
            f'{self.base_url}/loyalty',
            headers=self.headers
        )
        return response.json()
    
    def redeem_points(self, points, booking_id=None):
        data = {'points': points}
        if booking_id:
            data['booking_id'] = booking_id
        
        response = requests.post(
            f'{self.base_url}/loyalty/redeem',
            headers=self.headers,
            json=data
        )
        return response.json()
    
    def get_transactions(self, page=1):
        response = requests.get(
            f'{self.base_url}/loyalty/transactions',
            headers=self.headers,
            params={'page': page}
        )
        return response.json()

# Usage
client = LoyaltyClient('https://api.renthub.com/api/v1', 'your_token')
loyalty = client.get_loyalty_info()
print(f"You have {loyalty['data']['loyalty']['available_points']} points")
```

## Integration Guide

### Step 1: Initialize on Registration
When a new user registers, their loyalty account is automatically created. Optionally award a welcome bonus:

```php
// In your registration controller
$loyaltyService->initializeLoyaltyAccount($user);
$loyaltyService->awardWelcomeBonus($user, 100);
```

### Step 2: Award Points on Booking Completion
Hook into your booking completion flow:

```php
// In BookingObserver or booking completion handler
if ($booking->status === 'completed') {
    app(LoyaltyService::class)->awardPointsForBooking($booking);
}
```

### Step 3: Display Loyalty Info
Show users their loyalty status on their dashboard or profile page.

### Step 4: Allow Point Redemption
At checkout, allow users to apply points for discounts.

### Step 5: Show Tier Benefits
Display tier benefits on marketing pages to encourage engagement.

## Best Practices

1. **Always check available points** before allowing redemption
2. **Show expiring points** to encourage usage
3. **Display tier progress** to motivate users
4. **Highlight benefits** at each tier level
5. **Send notifications** for tier upgrades and expiring points
6. **Validate minimum redemption** (500 points)

## Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error description here",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

Common HTTP status codes:
- `200`: Success
- `400`: Bad request / Validation error
- `401`: Unauthorized
- `404`: Resource not found
- `500`: Server error

## Support

For questions or issues with the Loyalty Program API:
- Email: support@renthub.com
- Documentation: https://docs.renthub.com
- API Status: https://status.renthub.com

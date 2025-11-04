# Wishlist/Favorites API Guide

## Quick Reference

### Authentication
All wishlist endpoints require authentication via Sanctum token:
```
Authorization: Bearer {your-token}
```

## API Endpoints

### 1. Get All Wishlists
```http
GET /api/v1/wishlists
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "name": "Summer Vacation",
      "description": "Beach properties for summer 2025",
      "is_public": true,
      "share_token": "abc123...",
      "items_count": 5,
      "created_at": "2025-11-02T10:00:00Z",
      "updated_at": "2025-11-02T10:00:00Z"
    }
  ]
}
```

### 2. Create Wishlist
```http
POST /api/v1/wishlists
```

**Request:**
```json
{
  "name": "Business Trips",
  "description": "Properties near business districts",
  "is_public": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Wishlist created successfully",
  "data": {
    "id": 2,
    "user_id": 5,
    "name": "Business Trips",
    "description": "Properties near business districts",
    "is_public": false,
    "share_token": "xyz789...",
    "created_at": "2025-11-02T11:00:00Z",
    "updated_at": "2025-11-02T11:00:00Z"
  }
}
```

### 3. Get Wishlist Details
```http
GET /api/v1/wishlists/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Summer Vacation",
    "description": "Beach properties",
    "is_public": true,
    "items_count": 3,
    "items": [
      {
        "id": 1,
        "property_id": 10,
        "notes": "Perfect location!",
        "price_alert": 150.00,
        "notify_availability": true,
        "created_at": "2025-11-02T10:00:00Z",
        "property": {
          "id": 10,
          "title": "Beach House in Malibu",
          "price_per_night": 200.00,
          "main_image": "...",
          "city": "Malibu",
          "country": "USA"
        }
      }
    ]
  }
}
```

### 4. Update Wishlist
```http
PUT /api/v1/wishlists/{id}
```

**Request:**
```json
{
  "name": "Updated Name",
  "description": "Updated description",
  "is_public": true
}
```

### 5. Delete Wishlist
```http
DELETE /api/v1/wishlists/{id}
```

**Response:**
```json
{
  "success": true,
  "message": "Wishlist deleted successfully"
}
```

### 6. Add Property to Wishlist
```http
POST /api/v1/wishlists/{id}/properties
```

**Request:**
```json
{
  "property_id": 15,
  "notes": "Great for family vacation",
  "price_alert": 120.00,
  "notify_availability": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Property added to wishlist",
  "data": {
    "id": 5,
    "wishlist_id": 1,
    "property_id": 15,
    "notes": "Great for family vacation",
    "price_alert": 120.00,
    "notify_availability": true,
    "property": {...}
  }
}
```

### 7. Remove Property from Wishlist
```http
DELETE /api/v1/wishlists/{wishlistId}/items/{itemId}
```

**Response:**
```json
{
  "success": true,
  "message": "Property removed from wishlist"
}
```

### 8. Update Wishlist Item
```http
PUT /api/v1/wishlists/{wishlistId}/items/{itemId}
```

**Request:**
```json
{
  "notes": "Updated notes",
  "price_alert": 100.00,
  "notify_availability": false
}
```

### 9. Quick Toggle Property (Add/Remove)
```http
POST /api/v1/wishlists/toggle-property
```

**Request:**
```json
{
  "property_id": 20,
  "wishlist_id": 1  // Optional, uses default "My Favorites" if not provided
}
```

**Response (Added):**
```json
{
  "success": true,
  "message": "Property added to wishlist",
  "action": "added",
  "data": {...}
}
```

**Response (Removed):**
```json
{
  "success": true,
  "message": "Property removed from wishlist",
  "action": "removed"
}
```

### 10. Check if Property is in Wishlist
```http
GET /api/v1/wishlists/check/{propertyId}
```

**Response:**
```json
{
  "success": true,
  "in_wishlist": true,
  "wishlists": [
    {
      "id": 1,
      "name": "Summer Vacation",
      "items": [
        {
          "id": 3,
          "property_id": 20
        }
      ]
    }
  ]
}
```

### 11. Get Shared Wishlist (Public)
```http
GET /api/v1/wishlists/shared/{token}
```

**Note:** No authentication required for public wishlists.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Summer Vacation",
    "description": "Beach properties",
    "is_public": true,
    "items_count": 5,
    "user": {
      "id": 5,
      "name": "John Doe"
    },
    "items": [...]
  }
}
```

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Wishlist not found"
}
```

### 409 Conflict (Property Already in Wishlist)
```json
{
  "success": false,
  "message": "Property already in wishlist"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "errors": {
    "name": ["The name field is required."],
    "property_id": ["The property id must be a valid property."]
  }
}
```

## Frontend Integration Examples

### React/Next.js with Axios

```typescript
import api from './client';

// Get all wishlists
const getWishlists = async () => {
  const response = await api.get('/wishlists');
  return response.data.data;
};

// Quick toggle property
const toggleFavorite = async (propertyId: number) => {
  const response = await api.post('/wishlists/toggle-property', {
    property_id: propertyId
  });
  return response.data.action; // 'added' or 'removed'
};

// Add property with price alert
const addWithAlert = async (wishlistId: number, propertyId: number, priceThreshold: number) => {
  const response = await api.post(`/wishlists/${wishlistId}/properties`, {
    property_id: propertyId,
    price_alert: priceThreshold,
    notify_availability: true
  });
  return response.data.data;
};
```

### Usage in Component

```tsx
'use client';

import { useState, useEffect } from 'react';
import { Heart } from 'lucide-react';
import { toggleFavorite, checkInWishlist } from '@/lib/api/wishlists';

export default function FavoriteButton({ propertyId }) {
  const [isFavorite, setIsFavorite] = useState(false);

  useEffect(() => {
    checkInWishlist(propertyId).then(result => {
      setIsFavorite(result.in_wishlist);
    });
  }, [propertyId]);

  const handleToggle = async () => {
    const action = await toggleFavorite(propertyId);
    setIsFavorite(action === 'added');
  };

  return (
    <button onClick={handleToggle}>
      <Heart fill={isFavorite ? 'red' : 'none'} />
    </button>
  );
}
```

## Webhooks & Notifications

### Price Drop Event
When a property price drops below a user's alert threshold:

1. **Email Notification** sent to user
2. **Database Notification** created
3. **Notification contains:**
   - Property details
   - Old vs new price
   - Savings amount
   - Direct link to property

### Notification Payload
```json
{
  "type": "price_drop",
  "property_id": 10,
  "property_title": "Beach House in Malibu",
  "old_price": 200.00,
  "new_price": 150.00,
  "savings": 50.00,
  "wishlist_id": 1
}
```

## Best Practices

1. **Use toggle endpoint** for simple add/remove actions
2. **Cache wishlist status** on frontend to reduce API calls
3. **Batch check multiple properties** when displaying lists
4. **Set reasonable price alerts** to avoid spam
5. **Use pagination** for wishlists with many items (future enhancement)

## Rate Limiting

- 60 requests per minute per user
- Applies to all authenticated endpoints
- Public shared wishlists: 100 requests per minute per IP

## Postman Collection

Import the collection for testing:
```
File: POSTMAN_WISHLIST_TESTS.md
```

## Support

For issues or questions:
- Backend: Check Laravel logs in `storage/logs/laravel.log`
- Frontend: Check browser console
- Database: Verify migrations ran successfully

---

**Last Updated:** November 2, 2025
**Version:** 1.0.0

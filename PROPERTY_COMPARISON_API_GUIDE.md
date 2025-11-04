# 🔄 Property Comparison - API Guide & Implementation

**Task 3.4: Property Comparison Feature**  
**Date:** November 2, 2025  
**Status:** ✅ COMPLETE

---

## 📋 Features Implemented

### ✅ Backend (Laravel + Filament)
- [x] PropertyComparison Model & Migration
- [x] API endpoints for comparison management
- [x] Support for both authenticated and guest users
- [x] Session-based comparison for guests
- [x] Maximum 4 properties per comparison
- [x] Detailed comparison matrix generation
- [x] Filament Admin Resource

### ✅ Frontend (Next.js)
- [x] ComparisonContext for state management
- [x] CompareButton component
- [x] ComparisonBar (floating bottom bar)
- [x] Full comparison page with side-by-side view
- [x] Real-time updates
- [x] Toast notifications

---

## 🗄️ Database Schema

### `property_comparisons` Table
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key to users) - nullable for guests
- property_ids (json) - array of property IDs (max 4)
- session_id (string) - for guest users
- expires_at (timestamp) - auto-cleanup
- created_at (timestamp)
- updated_at (timestamp)

Indexes:
- user_id, created_at
- session_id, created_at
```

---

## 🔌 API Endpoints

### 1. Get Comparison List
**GET** `/api/v1/property-comparison`

**Headers:**
```json
{
  "X-Session-Id": "session-123456" // For guest users
}
```

**Response:**
```json
{
  "property_ids": [1, 2, 3],
  "properties": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "type": "villa",
      "price_per_night": 150,
      "price_per_month": 3500,
      "bedrooms": 3,
      "bathrooms": 2,
      "guests": 6,
      "area_sqm": 120,
      "city": "Bucharest",
      "country": "Romania",
      "images": ["image1.jpg"],
      "amenities": ["WiFi", "Pool"],
      "average_rating": 4.8,
      "review_count": 45,
      "owner": {
        "id": 5,
        "name": "John Doe",
        "avatar": "avatar.jpg"
      }
    }
  ],
  "count": 3
}
```

---

### 2. Add Property to Comparison
**POST** `/api/v1/property-comparison/add`

**Headers:**
```json
{
  "Authorization": "Bearer {token}", // Optional
  "X-Session-Id": "session-123456"  // Required for guests
}
```

**Request:**
```json
{
  "property_id": 5
}
```

**Response:**
```json
{
  "message": "Property added to comparison",
  "property_ids": [1, 2, 3, 5],
  "session_id": "session-123456"
}
```

**Error (Max reached):**
```json
{
  "message": "Maximum 4 properties can be compared at once"
}
```

---

### 3. Remove Property from Comparison
**DELETE** `/api/v1/property-comparison/remove/{propertyId}`

**Headers:**
```json
{
  "X-Session-Id": "session-123456"
}
```

**Response:**
```json
{
  "message": "Property removed from comparison",
  "property_ids": [1, 2, 3]
}
```

---

### 4. Clear All Comparisons
**DELETE** `/api/v1/property-comparison/clear`

**Headers:**
```json
{
  "X-Session-Id": "session-123456"
}
```

**Response:**
```json
{
  "message": "Comparison list cleared"
}
```

---

### 5. Get Detailed Comparison
**POST** `/api/v1/property-comparison/compare`

**Request:**
```json
{
  "property_ids": [1, 2, 3]
}
```

**Response:**
```json
{
  "properties": [
    {
      "id": 1,
      "title": "Luxury Villa",
      "description": "...",
      "type": "villa",
      "furnishing_status": "fully_furnished",
      "price_per_night": 150,
      "price_per_week": 900,
      "price_per_month": 3500,
      "cleaning_fee": 50,
      "security_deposit": 300,
      "bedrooms": 3,
      "bathrooms": 2,
      "guests": 6,
      "area_sqm": 120,
      "square_footage": 1292,
      "built_year": 2020,
      "floor_number": 2,
      "street_address": "123 Main St",
      "city": "Bucharest",
      "state": "Bucharest",
      "country": "Romania",
      "latitude": 44.4268,
      "longitude": 26.1025,
      "amenities": [
        {"id": 1, "name": "WiFi", "icon": "wifi"},
        {"id": 2, "name": "Pool", "icon": "pool"}
      ],
      "parking_available": true,
      "parking_spaces": 2,
      "min_nights": 2,
      "max_nights": 30,
      "cancellation_policy": "flexible",
      "rules": "No smoking, No pets",
      "images": ["image1.jpg", "image2.jpg"],
      "average_rating": 4.8,
      "review_count": 45,
      "rating_breakdown": {
        "cleanliness": 4.9,
        "accuracy": 4.7,
        "communication": 4.8,
        "location": 4.9,
        "checkin": 4.8,
        "value": 4.7
      },
      "owner": {
        "id": 5,
        "name": "John Doe",
        "avatar": "avatar.jpg",
        "joined_at": "2023-01-15"
      }
    }
  ],
  "comparison_matrix": [
    {
      "feature": "Price per Night",
      "type": "currency",
      "values": [150, 120, 180]
    },
    {
      "feature": "Bedrooms",
      "type": "number",
      "values": [3, 2, 4]
    },
    {
      "feature": "Rating",
      "type": "rating",
      "values": [4.8, 4.5, 4.9]
    }
  ]
}
```

---

## 🧪 Testing Guide

### Test 1: Add Properties to Comparison (Guest User)
```bash
# Generate session ID
SESSION_ID="session-test-$(date +%s)"

# Add first property
curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "X-Session-Id: $SESSION_ID" \
  -d '{"property_id": 1}'

# Add second property
curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "X-Session-Id: $SESSION_ID" \
  -d '{"property_id": 2}'

# Add third property
curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "X-Session-Id: $SESSION_ID" \
  -d '{"property_id": 3}'

# Get comparison list
curl -X GET http://localhost/api/v1/property-comparison \
  -H "X-Session-Id: $SESSION_ID"
```

### Test 2: Maximum Limit (4 properties)
```bash
# Try to add 5th property (should fail)
curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "X-Session-Id: $SESSION_ID" \
  -d '{"property_id": 4}'

curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "X-Session-Id: $SESSION_ID" \
  -d '{"property_id": 5}'  # Should return error
```

### Test 3: Get Detailed Comparison
```bash
curl -X POST http://localhost/api/v1/property-comparison/compare \
  -H "Content-Type: application/json" \
  -d '{"property_ids": [1, 2, 3]}'
```

### Test 4: Remove Property
```bash
curl -X DELETE http://localhost/api/v1/property-comparison/remove/2 \
  -H "X-Session-Id: $SESSION_ID"
```

### Test 5: Clear All
```bash
curl -X DELETE http://localhost/api/v1/property-comparison/clear \
  -H "X-Session-Id: $SESSION_ID"
```

### Test 6: Authenticated User
```bash
# Login first
TOKEN=$(curl -X POST http://localhost/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email": "tenant@example.com", "password": "password123"}' \
  | jq -r '.token')

# Add property as authenticated user
curl -X POST http://localhost/api/v1/property-comparison/add \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"property_id": 1}'

# Get comparison
curl -X GET http://localhost/api/v1/property-comparison \
  -H "Authorization: Bearer $TOKEN"
```

---

## 💻 Frontend Usage

### 1. Add Compare Button to Property Card
```tsx
import CompareButton from '@/components/properties/CompareButton';

function PropertyCard({ property }) {
  return (
    <div className="property-card">
      {/* ... other content ... */}
      <CompareButton propertyId={property.id} />
    </div>
  );
}
```

### 2. Use Comparison Context
```tsx
'use client';
import { useComparison } from '@/contexts/ComparisonContext';

function MyComponent() {
  const {
    comparisonIds,
    properties,
    addToComparison,
    removeFromComparison,
    isInComparison,
    count,
    maxReached
  } = useComparison();

  return (
    <div>
      <p>Comparing {count} properties</p>
      {properties.map(property => (
        <div key={property.id}>{property.title}</div>
      ))}
    </div>
  );
}
```

### 3. Navigate to Comparison Page
```tsx
import { useRouter } from 'next/navigation';
import { useComparison } from '@/contexts/ComparisonContext';

function CompareNowButton() {
  const router = useRouter();
  const { properties, count } = useComparison();

  const handleCompare = () => {
    if (count >= 2) {
      const ids = properties.map(p => p.id).join(',');
      router.push(`/compare?ids=${ids}`);
    }
  };

  return (
    <button onClick={handleCompare} disabled={count < 2}>
      Compare {count} Properties
    </button>
  );
}
```

---

## 🎨 UI Components

### CompareButton
- Location: `/frontend/src/components/properties/CompareButton.tsx`
- Toggles property in/out of comparison
- Shows loading state
- Displays max limit warning

### ComparisonBar
- Location: `/frontend/src/components/properties/ComparisonBar.tsx`
- Fixed bottom bar
- Shows all properties in comparison
- Quick remove buttons
- "Compare Now" CTA
- Auto-hides when empty

### Comparison Page
- Location: `/frontend/src/app/compare/page.tsx`
- Side-by-side property cards
- Detailed comparison matrix
- Highlights best values
- Amenities comparison
- Rating breakdown
- Remove individual properties

---

## 🔧 Filament Admin

Access the PropertyComparison resource in Filament admin panel:
- **URL:** `/admin/property-comparisons`
- **Features:**
  - View all comparisons
  - Filter by user/session
  - See property IDs
  - Track creation dates
  - Monitor expires_at dates

---

## ⚡ Performance Notes

1. **Session Storage:**
   - Guest comparisons use localStorage + server-side session ID
   - Authenticated users link to their account

2. **Expiration:**
   - Guest comparisons: 1 day
   - Authenticated comparisons: 7 days
   - Clean up via scheduled job

3. **Caching:**
   - Property data is fresh-fetched on comparison page
   - No caching to ensure accuracy

---

## 🚀 Next Steps

1. **Add scheduled cleanup job:**
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        PropertyComparison::where('expires_at', '<', now())->delete();
    })->daily();
}
```

2. **Add comparison analytics:**
   - Track most compared properties
   - Popular comparison combinations

3. **Export comparison:**
   - PDF export
   - Email comparison

4. **Social sharing:**
   - Share comparison URL
   - Public comparison links

---

## ✅ Checklist

- [x] Database migration created
- [x] PropertyComparison model
- [x] API endpoints (5 routes)
- [x] Filament resource
- [x] ComparisonContext
- [x] CompareButton component
- [x] ComparisonBar component
- [x] Comparison page
- [x] Toast notifications
- [x] React-hot-toast installed
- [x] Layout updated with providers
- [x] Documentation

---

## 📦 Files Created/Modified

### Backend
- `database/migrations/2025_11_02_214133_create_property_comparisons_table.php`
- `app/Models/PropertyComparison.php`
- `app/Http/Controllers/Api/V1/PropertyComparisonController.php`
- `app/Filament/Resources/PropertyComparison/PropertyComparisonResource.php`
- `routes/api.php` (modified)

### Frontend
- `src/contexts/ComparisonContext.tsx`
- `src/components/properties/CompareButton.tsx`
- `src/components/properties/ComparisonBar.tsx`
- `src/app/compare/page.tsx`
- `src/app/layout.tsx` (modified)
- `package.json` (modified - added react-hot-toast)

---

**Task 3.4 Complete! ✅**

The Property Comparison feature is now fully functional with:
- ✅ Side-by-side comparison up to 4 properties
- ✅ Feature comparison matrix with best value highlighting
- ✅ Works for both guests and authenticated users
- ✅ Beautiful floating comparison bar
- ✅ Full amenities and ratings breakdown
- ✅ Responsive design

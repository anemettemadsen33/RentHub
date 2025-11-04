# 🔄 Property Comparison - Quick Start Guide

**Task 3.4: Property Comparison**  
**Status:** ✅ COMPLETE

---

## 🎯 What Was Built

A complete **side-by-side property comparison** system that allows users to:
- ✅ Compare up to 4 properties at once
- ✅ View detailed feature comparison matrix
- ✅ See best values highlighted in green
- ✅ Compare amenities, ratings, and prices
- ✅ Works for both guests and authenticated users
- ✅ Persistent comparisons across sessions

---

## 🚀 Quick Test (5 minutes)

### 1. Run Migration
```bash
cd C:\laragon\www\RentHub\backend
php artisan migrate
```

### 2. Test API (Guest User)
```bash
# Create session ID
$SESSION_ID = "session-test-$(Get-Date -Format 'yyyyMMddHHmmss')"

# Add property to comparison
curl -X POST http://localhost/api/v1/property-comparison/add `
  -H "Content-Type: application/json" `
  -H "X-Session-Id: $SESSION_ID" `
  -d '{"property_id": 1}'

# Add another property
curl -X POST http://localhost/api/v1/property-comparison/add `
  -H "Content-Type: application/json" `
  -H "X-Session-Id: $SESSION_ID" `
  -d '{"property_id": 2}'

# Get comparison list
curl -X GET http://localhost/api/v1/property-comparison `
  -H "X-Session-Id: $SESSION_ID"

# Get detailed comparison
curl -X POST http://localhost/api/v1/property-comparison/compare `
  -H "Content-Type: application/json" `
  -d '{"property_ids": [1, 2]}'
```

### 3. Test Frontend
```bash
cd C:\laragon\www\RentHub\frontend
npm run dev
```

Visit:
1. **Any property listing** - Click "Compare" button
2. **Add 2-4 properties** - See floating comparison bar at bottom
3. **Click "Compare Now"** - View side-by-side comparison
4. **Comparison page** - `/compare?ids=1,2,3`

---

## 📋 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/property-comparison` | Get user's comparison list |
| POST | `/api/v1/property-comparison/add` | Add property to comparison |
| DELETE | `/api/v1/property-comparison/remove/{id}` | Remove property |
| DELETE | `/api/v1/property-comparison/clear` | Clear all |
| POST | `/api/v1/property-comparison/compare` | Get detailed comparison |

---

## 🎨 UI Components

### 1. CompareButton
```tsx
import CompareButton from '@/components/properties/CompareButton';

<CompareButton propertyId={property.id} />
```

### 2. ComparisonBar (Auto-added to layout)
- Fixed at bottom of page
- Shows when properties are added
- Quick access to comparison

### 3. Comparison Page
- URL: `/compare?ids=1,2,3`
- Side-by-side view
- Feature matrix
- Amenities comparison
- Rating breakdown

---

## 💡 How It Works

### For Guests:
1. Click "Compare" on any property
2. Session ID stored in localStorage
3. Comparison saved on server
4. Persists for 24 hours

### For Authenticated Users:
1. Comparison linked to user account
2. Persists for 7 days
3. Syncs across devices

---

## 🎯 Key Features

### Comparison Matrix
- **Best values highlighted in green**
- Price comparison (lowest is best)
- Size comparison (largest is best)
- Rating comparison (highest is best)
- Boolean features (checkmarks)

### Amenities Comparison
- All amenities listed
- Checkmarks show availability
- "Show All" button for 10+ amenities

### Rating Breakdown
- 6 rating categories per property
- Cleanliness, Accuracy, Communication
- Location, Check-in, Value

---

## 🔧 Integration Points

### Add to Property Card
```tsx
// In your PropertyCard component
import CompareButton from '@/components/properties/CompareButton';
import { useComparison } from '@/contexts/ComparisonContext';

function PropertyCard({ property }) {
  const { isInComparison } = useComparison();
  
  return (
    <div>
      <h3>{property.title}</h3>
      <CompareButton propertyId={property.id} />
      {isInComparison(property.id) && (
        <span>✓ In comparison</span>
      )}
    </div>
  );
}
```

### Use Context Anywhere
```tsx
import { useComparison } from '@/contexts/ComparisonContext';

const { count, properties, maxReached } = useComparison();

// Show count: {count}/4 properties
// Check if max: {maxReached}
// List properties: {properties}
```

---

## 📊 Filament Admin

Access comparisons in admin panel:
```
http://localhost/admin/property-comparisons
```

View:
- All user comparisons
- Guest session comparisons
- Property IDs
- Creation & expiration dates

---

## 🧪 Complete Test Checklist

- [ ] Add property to comparison (guest)
- [ ] Add 2nd property (see comparison bar appear)
- [ ] Add 3rd and 4th properties
- [ ] Try to add 5th (should show error)
- [ ] Remove one property from bar
- [ ] Click "Compare Now"
- [ ] View comparison page
- [ ] Check feature matrix highlights
- [ ] View amenities comparison
- [ ] View rating breakdown
- [ ] Remove property from comparison page
- [ ] Clear all comparisons
- [ ] Test as authenticated user
- [ ] Check admin panel

---

## 📁 Files Reference

### Backend
```
app/Models/PropertyComparison.php
app/Http/Controllers/Api/V1/PropertyComparisonController.php
database/migrations/2025_11_02_214133_create_property_comparisons_table.php
routes/api.php
```

### Frontend
```
src/contexts/ComparisonContext.tsx
src/components/properties/CompareButton.tsx
src/components/properties/ComparisonBar.tsx
src/app/compare/page.tsx
src/app/layout.tsx
```

---

## 🎓 Usage Examples

### Example 1: Property Search Page
```tsx
function PropertySearchPage() {
  return (
    <div>
      {properties.map(property => (
        <div key={property.id}>
          <PropertyCard property={property} />
          <CompareButton propertyId={property.id} />
        </div>
      ))}
    </div>
  );
}
```

### Example 2: Show Comparison Count
```tsx
function Header() {
  const { count } = useComparison();
  
  return (
    <nav>
      <Link href="/compare">
        Compare ({count})
      </Link>
    </nav>
  );
}
```

### Example 3: Navigate to Comparison
```tsx
function MyButton() {
  const router = useRouter();
  const { properties } = useComparison();
  
  const handleCompare = () => {
    const ids = properties.map(p => p.id).join(',');
    router.push(`/compare?ids=${ids}`);
  };
  
  return <button onClick={handleCompare}>Compare</button>;
}
```

---

## ⚡ Performance Tips

1. **Session Management:**
   - Guests: localStorage session ID
   - Auto-generated on first use
   - Persists until cleared

2. **Data Loading:**
   - Comparison list: lightweight data
   - Full comparison: detailed data
   - Only loaded when needed

3. **Cleanup:**
   - Guest comparisons expire after 1 day
   - User comparisons expire after 7 days
   - Automatic cleanup (add to scheduler)

---

## 🔜 Optional Enhancements

1. **Add Cleanup Job:**
```php
// app/Console/Kernel.php
$schedule->call(function () {
    PropertyComparison::where('expires_at', '<', now())->delete();
})->daily();
```

2. **Add to Navigation:**
```tsx
<Link href="/compare">
  <CompareIcon /> Compare ({count})
</Link>
```

3. **Save Comparison:**
   - Allow naming comparisons
   - Share comparison URL
   - Export to PDF

---

## ✅ Task 3.4 Complete!

You now have a fully functional property comparison system! 🎉

**What's Next?**
- Continue with remaining Phase 3 tasks
- Or enhance this feature with exports/sharing
- Or move to Phase 4 features

See full documentation: `PROPERTY_COMPARISON_API_GUIDE.md`

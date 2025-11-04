# Map Search Setup Guide 🗺️

Quick setup guide for the Map-based Property Search feature.

## Prerequisites

- PHP 8.1+
- Laravel 10+
- Node.js 18+
- Next.js 14+
- Existing RentHub installation

---

## Backend Setup

### 1. Install Dependencies (if needed)

The map search feature uses built-in Laravel functionality, no additional packages needed.

### 2. Run Migration

```bash
cd backend
php artisan migrate
```

This will run the migration: `2025_11_02_183348_add_spatial_index_to_properties_table.php`

**Note**: If you encounter errors about Google Client, first install Google Calendar dependencies:

```bash
composer require google/apiclient
```

Then run migration:

```bash
php artisan migrate
```

### 3. Verify Routes

Check that map search routes are registered:

```bash
php artisan route:list | grep map
```

You should see:
```
POST   api/v1/map/search-radius  .................. map.search-radius
POST   api/v1/map/search-bounds  .................. map.search-bounds
GET    api/v1/map/property/{id}  .................. map.property
POST   api/v1/map/geocode  ........................ map.geocode
```

### 4. Seed Test Data (Optional)

Create some test properties with coordinates:

```bash
php artisan tinker
```

```php
// Create test property
$property = new App\Models\Property();
$property->title = "Test Apartment in Bucharest";
$property->description = "Beautiful test apartment";
$property->type = "apartment";
$property->bedrooms = 2;
$property->bathrooms = 1;
$property->guests = 4;
$property->price_per_night = 75;
$property->street_address = "Strada Aviatorilor 10";
$property->city = "București";
$property->state = "București";
$property->country = "România";
$property->postal_code = "010001";
$property->latitude = 44.4268;
$property->longitude = 26.1025;
$property->is_active = true;
$property->status = 'published';
$property->user_id = 1; // Admin user
$property->save();
```

---

## Frontend Setup

### 1. Install Leaflet Dependencies

```bash
cd frontend
npm install leaflet
npm install @types/leaflet --save-dev
```

### 2. Verify Component Files

Ensure these files exist:
- `src/components/map/SimpleMapSearch.tsx`
- `src/app/search/map/page.tsx`

### 3. Add Leaflet CSS

The component already includes Leaflet CSS via CDN, but for better performance, add it to your layout:

**In `src/app/layout.tsx`**:

```tsx
export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <head>
        <link
          rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossOrigin=""
        />
      </head>
      <body>{children}</body>
    </html>
  )
}
```

### 4. Test Frontend

Start development server:

```bash
npm run dev
```

Visit: `http://localhost:3000/search/map`

---

## Filament Admin Setup

### 1. Register Widget (Optional)

The widget auto-registers. To customize placement, edit your Admin Panel Provider:

**In `app/Providers/Filament/AdminPanelProvider.php`**:

```php
use App\Filament\Widgets\PropertiesMapWidget;

public function panel(Panel $panel): Panel
{
    return $panel
        ->widgets([
            PropertiesMapWidget::class,
            // ... other widgets
        ]);
}
```

### 2. View Widget

1. Login to Filament admin: `http://localhost/admin`
2. Navigate to Dashboard
3. You should see "Properties Map View" widget

---

## Troubleshooting

### Issue: Migration fails with Google Client error

**Solution**:
```bash
cd backend
composer require google/apiclient
php artisan migrate
```

### Issue: Map not displaying

**Causes**:
1. Leaflet CSS not loaded
2. Component height not set
3. Coordinates invalid

**Solutions**:
```tsx
// Ensure component has height
<SimpleMapSearch className="h-[600px] w-full" />

// Check console for errors
// Verify Leaflet CSS is loaded
```

### Issue: No properties showing

**Causes**:
1. No properties with coordinates in database
2. Properties not active or published
3. Coordinates outside viewport

**Solutions**:
```bash
# Check properties
php artisan tinker
App\Models\Property::whereNotNull('latitude')->whereNotNull('longitude')->count();

# Check active properties
App\Models\Property::where('is_active', true)->where('status', 'published')->count();
```

### Issue: Markers not clustering

**Cause**: Zoom level too high (>= 14)

**Solution**: Zoom out on map. Clustering only applies at zoom < 14.

---

## Testing

### 1. Test Backend API

```bash
# Test radius search
curl -X POST http://localhost/api/v1/map/search-radius \
  -H "Content-Type: application/json" \
  -d '{"latitude":44.4268,"longitude":26.1025,"radius":10}'

# Test bounds search
curl -X POST http://localhost/api/v1/map/search-bounds \
  -H "Content-Type: application/json" \
  -d '{"sw_lat":44.3968,"sw_lng":26.0725,"ne_lat":44.4568,"ne_lng":26.1325,"zoom":12}'

# Test property details
curl http://localhost/api/v1/map/property/1
```

### 2. Test Frontend

1. Visit `/search/map`
2. Open browser console (F12)
3. Check for JavaScript errors
4. Verify API calls in Network tab
5. Test map interactions:
   - Pan map
   - Zoom in/out
   - Click markers
   - Apply filters

### 3. Test Filament Widget

1. Login to admin panel
2. Navigate to dashboard
3. Verify widget displays
4. Test zoom controls
5. Click property markers

---

## Performance Optimization

### 1. Database Indexing

Verify spatial index exists:

```sql
SHOW INDEX FROM properties WHERE Key_name = 'properties_geo_index';
```

### 2. Enable Query Caching

**In `.env`**:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. Enable Response Caching

Add caching to API controller:

```php
use Illuminate\Support\Facades\Cache;

public function searchBounds(Request $request)
{
    $cacheKey = 'map_search_' . md5(json_encode($request->all()));
    
    return Cache::remember($cacheKey, 300, function () use ($request) {
        // ... existing search logic
    });
}
```

---

## Production Deployment

### 1. Environment Configuration

**In `.env`**:
```env
# Enable caching
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Optimize for production
APP_ENV=production
APP_DEBUG=false
```

### 2. Optimize Laravel

```bash
cd backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Build Frontend

```bash
cd frontend
npm run build
```

### 4. Enable CDN for Leaflet

Consider self-hosting Leaflet assets or using a CDN like:
- jsDelivr: `https://cdn.jsdelivr.net/npm/leaflet@1.9.4/`
- unpkg: `https://unpkg.com/leaflet@1.9.4/`

---

## Advanced Configuration

### 1. Custom Map Provider

To use Mapbox or Google Maps instead of OpenStreetMap:

**Mapbox**:
```javascript
L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
  attribution: '© Mapbox',
  id: 'mapbox/streets-v11',
  accessToken: 'YOUR_MAPBOX_TOKEN'
}).addTo(map);
```

**Google Maps**:
```bash
npm install @googlemaps/js-api-loader
```

### 2. Geocoding Integration

Add geocoding service to `.env`:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_key
MAPBOX_ACCESS_TOKEN=your_mapbox_token
```

Update geocode endpoint in `MapSearchController.php`:

```php
use Illuminate\Support\Facades\Http;

public function geocode(Request $request)
{
    $address = $request->input('address');
    $apiKey = config('services.google.maps_key');
    
    $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
        'address' => $address,
        'key' => $apiKey
    ]);
    
    if ($response->successful()) {
        $data = $response->json();
        
        if (!empty($data['results'])) {
            $location = $data['results'][0]['geometry']['location'];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'formatted_address' => $data['results'][0]['formatted_address']
                ]
            ]);
        }
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Geocoding failed'
    ], 422);
}
```

### 3. Custom Clustering Algorithm

For more advanced clustering, consider using:
- **Supercluster**: Fast clustering algorithm
- **MarkerClusterer**: Google Maps clustering
- **Leaflet.markercluster**: Leaflet plugin

---

## Support & Documentation

- **Full Documentation**: `TASK_2.4_MAP_SEARCH_COMPLETE.md`
- **API Guide**: `MAP_SEARCH_API_GUIDE.md`
- **Code Examples**: See component files

---

## Next Steps

After setup, you can:

1. ✅ Test map search on frontend
2. ✅ View properties map in admin
3. ✅ Integrate with property listings page
4. ✅ Add geocoding for address search
5. ✅ Customize map styling
6. ✅ Add more filters
7. ✅ Implement save search feature

---

**Setup Complete!** 🎉

Your map-based property search is now ready to use.

For questions or issues, refer to the documentation files or check the code comments.

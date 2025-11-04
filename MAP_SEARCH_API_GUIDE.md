# Map Search API Guide 🗺️

Complete guide for using the Map-based Property Search API

## Table of Contents
- [Overview](#overview)
- [API Endpoints](#api-endpoints)
- [Request/Response Examples](#requestresponse-examples)
- [Clustering Logic](#clustering-logic)
- [Frontend Integration](#frontend-integration)
- [Performance Tips](#performance-tips)

---

## Overview

The Map Search API provides powerful geospatial search capabilities for properties:

- **Radius Search**: Find properties within a specific distance
- **Bounds Search**: Find properties within map viewport
- **Clustering**: Automatic marker clustering for performance
- **Filtering**: Support for type, price, bedrooms, bathrooms, guests, amenities

### Base URL
```
http://your-domain.com/api/v1
```

---

## API Endpoints

### 1. Search Within Radius

Find properties within a circular area.

**Endpoint**: `POST /map/search-radius`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| latitude | float | Yes | Center latitude (-90 to 90) |
| longitude | float | Yes | Center longitude (-180 to 180) |
| radius | float | Yes | Radius in kilometers (0.1 to 100) |
| type | string | No | Property type (apartment, house, villa, studio) |
| min_price | float | No | Minimum price per night |
| max_price | float | No | Maximum price per night |
| bedrooms | integer | No | Minimum number of bedrooms |
| bathrooms | integer | No | Minimum number of bathrooms |
| guests | integer | No | Minimum guest capacity |
| amenities | array | No | Array of amenity IDs |

**Example Request**:
```bash
curl -X POST http://localhost/api/v1/map/search-radius \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 44.4268,
    "longitude": 26.1025,
    "radius": 10,
    "type": "apartment",
    "min_price": 50,
    "max_price": 200,
    "bedrooms": 2,
    "bathrooms": 1,
    "guests": 4
  }'
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "properties": [
      {
        "id": 1,
        "title": "Beautiful Apartment in City Center",
        "description": "Modern 2-bedroom apartment...",
        "type": "apartment",
        "bedrooms": 2,
        "bathrooms": 1,
        "guests": 4,
        "price_per_night": 75,
        "latitude": 44.4268,
        "longitude": 26.1025,
        "street_address": "Strada Aviatorilor 10",
        "city": "București",
        "state": "București",
        "country": "România",
        "main_image": "https://example.com/images/property1.jpg",
        "images": ["image1.jpg", "image2.jpg"],
        "distance": 2.5,
        "user": {
          "id": 1,
          "name": "John Doe"
        }
      }
    ],
    "count": 15,
    "center": {
      "latitude": 44.4268,
      "longitude": 26.1025
    },
    "radius": 10
  }
}
```

---

### 2. Search Within Bounds

Find properties within a rectangular map viewport.

**Endpoint**: `POST /map/search-bounds`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| sw_lat | float | Yes | Southwest corner latitude |
| sw_lng | float | Yes | Southwest corner longitude |
| ne_lat | float | Yes | Northeast corner latitude |
| ne_lng | float | Yes | Northeast corner longitude |
| zoom | integer | No | Map zoom level (1-20) for clustering |
| type | string | No | Property type filter |
| min_price | float | No | Minimum price per night |
| max_price | float | No | Maximum price per night |
| bedrooms | integer | No | Minimum bedrooms |
| bathrooms | integer | No | Minimum bathrooms |
| guests | integer | No | Minimum guests |
| amenities | array | No | Amenity IDs |

**Example Request**:
```bash
curl -X POST http://localhost/api/v1/map/search-bounds \
  -H "Content-Type: application/json" \
  -d '{
    "sw_lat": 44.3968,
    "sw_lng": 26.0725,
    "ne_lat": 44.4568,
    "ne_lng": 26.1325,
    "zoom": 12,
    "type": "apartment",
    "min_price": 50,
    "max_price": 200
  }'
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "markers": [
      {
        "type": "property",
        "id": 1,
        "latitude": 44.4268,
        "longitude": 26.1025,
        "title": "Beautiful Apartment",
        "price": 75,
        "image": "https://example.com/images/property1.jpg"
      },
      {
        "type": "cluster",
        "count": 5,
        "latitude": 44.4168,
        "longitude": 26.1125,
        "properties": [2, 3, 4, 5, 6],
        "min_price": 60
      }
    ],
    "count": 25,
    "bounds": {
      "southwest": {
        "lat": 44.3968,
        "lng": 26.0725
      },
      "northeast": {
        "lat": 44.4568,
        "lng": 26.1325
      }
    }
  }
}
```

---

### 3. Get Property Map Data

Get detailed property information for map popup.

**Endpoint**: `GET /map/property/{id}`

**Example Request**:
```bash
curl http://localhost/api/v1/map/property/1
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Beautiful Apartment",
    "type": "apartment",
    "bedrooms": 2,
    "bathrooms": 1,
    "guests": 4,
    "price_per_night": 75,
    "latitude": 44.4268,
    "longitude": 26.1025,
    "address": {
      "street": "Strada Aviatorilor 10",
      "city": "București",
      "state": "București",
      "country": "România"
    },
    "images": {
      "main": "https://example.com/images/property1.jpg",
      "all": [
        "https://example.com/images/property1.jpg",
        "https://example.com/images/property2.jpg"
      ]
    },
    "rating": 4.5,
    "reviews_count": 12
  }
}
```

---

### 4. Geocode Address

Convert address to coordinates (placeholder - integrate with geocoding service).

**Endpoint**: `POST /map/geocode`

**Parameters**:
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| address | string | Yes | Full address to geocode |

**Example Request**:
```bash
curl -X POST http://localhost/api/v1/map/geocode \
  -H "Content-Type: application/json" \
  -d '{
    "address": "Strada Aviatorilor 10, București, România"
  }'
```

---

## Clustering Logic

### How It Works

1. **Zoom Level Check**: 
   - Zoom ≥ 14: No clustering (show individual properties)
   - Zoom < 14: Apply grid-based clustering

2. **Grid-based Clustering**:
   - Divides map into grid cells based on zoom
   - Groups properties in same grid cell
   - Calculates cluster center (average position)
   - Shows minimum price in cluster

3. **Single-property Clusters**:
   - Automatically converted to property markers
   - No cluster icon shown for single properties

### Marker Types

**Property Marker**:
```json
{
  "type": "property",
  "id": 1,
  "latitude": 44.4268,
  "longitude": 26.1025,
  "title": "Beautiful Apartment",
  "price": 75,
  "image": "https://..."
}
```

**Cluster Marker**:
```json
{
  "type": "cluster",
  "count": 5,
  "latitude": 44.4168,
  "longitude": 26.1125,
  "properties": [2, 3, 4, 5, 6],
  "min_price": 60
}
```

---

## Frontend Integration

### React/Next.js Example

```tsx
import SimpleMapSearch from '@/components/map/SimpleMapSearch';
import { useRouter } from 'next/navigation';

export default function MapSearchPage() {
  const router = useRouter();

  const handlePropertyClick = (propertyId: number) => {
    router.push(`/properties/${propertyId}`);
  };

  return (
    <SimpleMapSearch
      initialCenter={{ lat: 45.9432, lng: 24.9668 }}
      initialZoom={7}
      filters={{
        type: 'apartment',
        min_price: 50,
        max_price: 200,
        bedrooms: 2,
        bathrooms: 1,
        guests: 4
      }}
      onPropertyClick={handlePropertyClick}
      className="h-[600px] w-full"
    />
  );
}
```

### Vanilla JavaScript Example

```javascript
async function searchProperties(bounds, zoom) {
  const response = await fetch('/api/v1/map/search-bounds', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      sw_lat: bounds.sw.lat,
      sw_lng: bounds.sw.lng,
      ne_lat: bounds.ne.lat,
      ne_lng: bounds.ne.lng,
      zoom: zoom
    })
  });

  const data = await response.json();
  
  if (data.success) {
    renderMarkers(data.data.markers);
  }
}

function renderMarkers(markers) {
  markers.forEach(marker => {
    if (marker.type === 'property') {
      // Render property marker
      const icon = createPriceIcon(marker.price);
      L.marker([marker.latitude, marker.longitude], { icon })
        .addTo(map)
        .bindPopup(createPropertyPopup(marker));
    } else {
      // Render cluster marker
      const icon = createClusterIcon(marker.count, marker.min_price);
      L.marker([marker.latitude, marker.longitude], { icon })
        .addTo(map)
        .bindPopup(`${marker.count} properties from €${marker.min_price}/night`);
    }
  });
}
```

---

## Performance Tips

### 1. Debounce Map Events
```javascript
let searchTimeout;

map.on('moveend', () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    searchProperties(map.getBounds(), map.getZoom());
  }, 300);
});
```

### 2. Cache Results
```javascript
const cache = new Map();

async function searchWithCache(bounds, zoom) {
  const key = `${bounds.toString()}-${zoom}`;
  
  if (cache.has(key)) {
    return cache.get(key);
  }
  
  const data = await searchProperties(bounds, zoom);
  cache.set(key, data);
  
  return data;
}
```

### 3. Limit Concurrent Requests
```javascript
let currentRequest = null;

async function searchProperties(bounds, zoom) {
  if (currentRequest) {
    currentRequest.abort();
  }
  
  currentRequest = new AbortController();
  
  const response = await fetch('/api/v1/map/search-bounds', {
    method: 'POST',
    signal: currentRequest.signal,
    body: JSON.stringify({ ...bounds, zoom })
  });
  
  return response.json();
}
```

### 4. Optimize Marker Rendering
- Remove old markers before adding new ones
- Use marker pools for reusing marker instances
- Implement virtual viewport (only render visible markers)

---

## Error Handling

### Common Errors

**Validation Error (422)**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "latitude": ["The latitude must be between -90 and 90."],
    "radius": ["The radius must be at least 0.1."]
  }
}
```

**Property Not Found (404)**:
```json
{
  "success": false,
  "message": "Property not found"
}
```

### Error Handling Example

```javascript
try {
  const response = await fetch('/api/v1/map/search-bounds', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(searchParams)
  });

  const data = await response.json();

  if (!data.success) {
    console.error('Search failed:', data.message);
    return;
  }

  renderMarkers(data.data.markers);
} catch (error) {
  console.error('Network error:', error);
  showErrorMessage('Failed to load properties. Please try again.');
}
```

---

## Testing with Postman

### Import Collection

Create a new Postman collection with these requests:

1. **Radius Search**:
   - Method: POST
   - URL: `{{baseUrl}}/api/v1/map/search-radius`
   - Body: Raw JSON
   ```json
   {
     "latitude": 44.4268,
     "longitude": 26.1025,
     "radius": 10
   }
   ```

2. **Bounds Search**:
   - Method: POST
   - URL: `{{baseUrl}}/api/v1/map/search-bounds`
   - Body: Raw JSON
   ```json
   {
     "sw_lat": 44.3968,
     "sw_lng": 26.0725,
     "ne_lat": 44.4568,
     "ne_lng": 26.1325,
     "zoom": 12
   }
   ```

3. **Get Property**:
   - Method: GET
   - URL: `{{baseUrl}}/api/v1/map/property/1`

---

## Advanced Features

### Custom Distance Calculation

The Haversine formula is used for accurate distance calculation:

```php
public static function calculateDistance(
    float $lat1, 
    float $lon1, 
    float $lat2, 
    float $lon2
): float {
    $earthRadius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}
```

### Bounding Box Optimization

Before calculating exact distances, a bounding box filter is applied:

```php
$latDelta = $radiusKm / 111; // 1 degree latitude ≈ 111 km
$lonDelta = $radiusKm / (111 * cos(deg2rad($latitude)));

$query->whereBetween('latitude', [
    $latitude - $latDelta,
    $latitude + $latDelta
])->whereBetween('longitude', [
    $longitude - $lonDelta,
    $longitude + $lonDelta
]);
```

---

## Support

For issues or questions:
- Check documentation: `TASK_2.4_MAP_SEARCH_COMPLETE.md`
- Review code: `app/Services/GeoSearchService.php`
- Test API: Use Postman or curl examples above

---

**Last Updated**: November 2, 2025

# Pusher Beams Integration - Next Steps

## ✅ Completed
1. Backend configuration (`config/services.php` with beams entry)
2. Environment variables added to `.env.example` (backend + frontend)
3. `BeamsClient` service created for publishing web push
4. `beams:test` Artisan command for verification
5. Frontend service worker (`public/pusher-beams-sw.js`)
6. Frontend Beams helper (`src/lib/beams.ts`)
7. Documentation updated (README + DEPLOYMENT.md)
8. All dependencies installed (pusher-js, mapbox-gl, @pusher/push-notifications-web)
9. Type check passing (0 errors)
10. Property detail route fixed (await params)

## 🔧 Environment Setup Required

### Backend (.env)
```bash
# Add these to backend/.env (DO NOT COMMIT SECRET KEY)
PUSHER_BEAMS_INSTANCE_ID=0223b504-a3c5-40f5-a2d2-110c12c80fb4
PUSHER_BEAMS_SECRET_KEY=836A91127B194EBDCC22FB8372A0C691BDEFBE04C12B453CF3238434713342D5

# Also ensure Pusher (websockets) is configured
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

# Mapbox (for map view)
MAPBOX_PUBLIC_TOKEN=your_mapbox_token
```

### Frontend (.env.local)
```bash
# Add these to frontend/.env.local
NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID=0223b504-a3c5-40f5-a2d2-110c12c80fb4

# Websockets
NEXT_PUBLIC_PUSHER_KEY=your_app_key
NEXT_PUBLIC_PUSHER_CLUSTER=mt1

# Map
NEXT_PUBLIC_MAPBOX_TOKEN=your_mapbox_token
```

## 🧪 Testing Beams

### Backend Test
```bash
cd backend
php artisan beams:test --interest=broadcast --title="Test Push" --body="Hello from Beams!"
```

### Frontend Subscription Example
Add to a component (e.g., in user dashboard layout):
```tsx
import { useEffect } from 'react';
import { startBeams } from '@/lib/beams';
import { useAuth } from '@/contexts/auth-context';

export function BeamsSubscriber() {
  const { user } = useAuth();
  
  useEffect(() => {
    if (!user) return;
    
    // Subscribe to user-specific and broadcast interests
    startBeams([`user-${user.id}`, 'broadcast'])
      .then(() => console.log('✅ Beams subscribed'))
      .catch(console.error);
  }, [user]);
  
  return null;
}
```

## 🚀 Next Steps (Priority Order)

### 1. Database Migrations (Required)
Run pending migrations for saved searches feature:
```bash
cd backend
php artisan migrate
```

### 2. Queue Worker Setup (Required for Notifications)
Start the queue worker to process saved search matches and notifications:
```bash
cd backend
php artisan queue:work --queue=default
```

Add to scheduler for automated checks (already configured in Kernel):
```bash
# Runs automatically via Laravel scheduler
php artisan schedule:work
```

### 3. Testing Suite Execution
Install test dependencies and run tests:
```bash
cd frontend/tests
npm install
cd ..
npm test                    # Unit tests
npm run test:e2e           # E2E tests
```

### 4. Feature Integration Examples

#### Saved Search Notifications via Beams
Add to `CheckSavedSearchesJob` after sending email:
```php
use App\Services\BeamsClient;

// In CheckSavedSearchesJob handle() method, after notification sent:
$beamsClient = app(BeamsClient::class);
$beamsClient->publishToInterests(
    ["user-{$savedSearch->user_id}"],
    'New Property Match',
    "{$matchedProperties->count()} properties match \"{$savedSearch->name}\"",
    ['saved_search_id' => $savedSearch->id],
    ['deep_link' => config('app.frontend_url') . '/saved-searches']
);
```

#### New Booking Web Push
In booking created event listener:
```php
$beamsClient = app(BeamsClient::class);
$beamsClient->publishToInterests(
    ["user-{$booking->property->user_id}"],
    'New Booking',
    "{$booking->user->name} booked {$booking->property->title}",
    ['booking_id' => $booking->id],
    ['deep_link' => config('app.frontend_url') . "/bookings/{$booking->id}"]
);
```

### 4.1 Frontend: Map Provider Toggle (New)

Allow users to switch between Leaflet (free) and Mapbox at runtime. Preference is stored in localStorage and does not require a page reload.

Added files:
- `frontend/src/lib/map-provider.ts` – get/set preference + change subscription
- `frontend/src/components/map-view-provider.tsx` – now listens for runtime preference changes
- `frontend/src/components/map-provider-toggle.tsx` – small UI control to switch providers

Usage example (place in a map page toolbar or filters bar):

```tsx
import { MapProviderToggle } from '@/components/map-provider-toggle';

export default function PropertiesPage() {
  return (
    <div className="flex items-center justify-between mb-3">
      <h1 className="text-xl font-semibold">Properties</h1>
      <MapProviderToggle />
    </div>
  );
}
```

Environment notes:
- `.env.example` includes `NEXT_PUBLIC_MAP_PROVIDER` and `NEXT_PUBLIC_MAP_CLUSTER_THRESHOLD`.
- When `NEXT_PUBLIC_MAPBOX_TOKEN` is present and no preference is stored, Mapbox is the default; otherwise Leaflet.

### 5. Performance & Accessibility Audit
```bash
cd frontend
npm run build
npm start

# Run Lighthouse audit
npx lighthouse http://localhost:3000 --view
```

### 6. Production Deployment Checklist

#### Backend (Laravel Forge / VPS)
- [ ] Set Beams env vars in production .env
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Configure queue worker daemon
- [ ] Verify scheduler cron is active
- [ ] Test beams with: `php artisan beams:test`
- [ ] Monitor logs: `tail -f storage/logs/laravel.log`

#### Frontend (Vercel)
- [ ] Add `NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID` in Vercel env vars
- [ ] Add Pusher & Mapbox tokens
- [ ] Deploy and test service worker registration
- [ ] Verify web push permissions prompt works
- [ ] Test notification click deep links

### 7. Optional Enhancements

#### User-Authenticated Beams
For user-specific secure channels, implement TokenProvider:
```php
// Backend: Create endpoint /api/beams/auth
Route::post('/beams/auth', function(Request $request) {
    $beamsToken = BeamsClient::generateToken($request->user()->id);
    return response()->json(['token' => $beamsToken]);
})->middleware('auth:sanctum');
```

```tsx
// Frontend: Use TokenProvider for authenticated subscriptions
import { TokenProvider } from '@pusher/push-notifications-web';

const beamsTokenProvider = new TokenProvider({
  url: `${API_URL}/beams/auth`,
  headers: { Authorization: `Bearer ${token}` }
});

await client.setUserId(userId, beamsTokenProvider);
```

#### Notification Preferences
Add user settings for notification channels (email, push, in-app):
```bash
php artisan make:migration add_notification_preferences_to_users_table
```

#### Unread Notifications Counter
Create a global notification center with badge count using the existing notification provider.

## 📊 Current Status

✅ All code implementation complete  
✅ Type checking passing  
✅ Dependencies installed  
⏳ Environment variables need configuration  
⏳ Migrations pending execution  
⏳ Queue worker needs to be started  
⏳ Integration testing pending  

## 🔍 Verification Commands

```bash
# Backend health check
cd backend
php artisan config:cache
php artisan route:list | grep beams

# Frontend build check
cd frontend
npm run build
npm run type-check

# Test dependencies
cd frontend/tests
npm list --depth=0
```

## 🐛 Known Issues to Address

1. **url.parse() deprecation warning**: Update to WHATWG URL API in Next.js config
2. **Controller duplication**: Review PropertyComparisonController versions
3. **Test coverage**: Expand unit and E2E test scenarios
4. **Accessibility**: Run axe-core scans on all pages

## 📚 Documentation References

- [Pusher Beams Docs](https://pusher.com/docs/beams)
- [Next.js 15 Migration Guide](https://nextjs.org/docs/app/building-your-application/upgrading)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Playwright Testing](https://playwright.dev/)

---

**Immediate Action**: Configure environment variables and run migrations to enable full functionality.

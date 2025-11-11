# Laravel Echo Quick Start

## ✅ Installation Complete

All necessary packages have been installed:
- ✅ `laravel-echo` - Frontend real-time client
- ✅ `pusher-js` - Pusher JavaScript SDK
- ✅ `pusher/pusher-php-server` - Backend Pusher SDK
- ✅ BroadcastServiceProvider enabled
- ✅ Channel authorization routes configured
- ✅ Sample broadcast events created

## 🚀 Quick Setup (5 minutes)

### Step 1: Get Free Pusher Account

1. Go to https://pusher.com/
2. Sign up for a free account
3. Create a new Channels app
4. Note your credentials from the "App Keys" tab:
   - App ID
   - Key
   - Secret
   - Cluster

### Step 2: Configure Backend

Edit `backend/.env`:

```env
# Change from 'log' to 'pusher'
BROADCAST_CONNECTION=pusher

# Add your Pusher credentials
PUSHER_APP_ID=1234567
PUSHER_APP_KEY=abcdef123456
PUSHER_APP_SECRET=xyz789secret
PUSHER_APP_CLUSTER=mt1
```

### Step 3: Configure Frontend

Edit `frontend/.env.local`:

```env
# Add your Pusher key (must match backend)
NEXT_PUBLIC_PUSHER_KEY=abcdef123456
NEXT_PUBLIC_PUSHER_CLUSTER=mt1
```

### Step 4: Test It!

**Backend Test:**
```bash
cd backend
php artisan tinker
```

```php
// Create a test notification
$user = App\Models\User::first();
event(new App\Events\UserNotification($user, 'Test', 'Laravel Echo is working!'));
```

**Frontend Test:**

Add this to any page:

```typescript
'use client';

import { useUserNotifications } from '@/hooks/use-echo';
import { useAuth } from '@/hooks/use-auth';

export default function TestEcho() {
  const { user } = useAuth();
  
  useUserNotifications((notification) => {
    console.log('✅ Received notification:', notification);
    alert(`${notification.title}: ${notification.message}`);
  });
  
  return <div>Listening for notifications...</div>;
}
```

Then trigger from backend and watch it appear in real-time!

## 📚 Usage Examples

### 1. User Notifications

**Backend (send notification):**
```php
use App\Events\UserNotification;

event(new UserNotification(
    user: $user,
    title: 'New Booking',
    message: 'You have a new booking request',
    type: 'success',
    data: ['booking_id' => $booking->id]
));
```

**Frontend (receive notification):**
```typescript
import { useUserNotifications } from '@/hooks/use-echo';

function NotificationBell() {
  const notifications = useUserNotifications((notification) => {
    toast.success(notification.message);
  });
  
  return <Bell count={notifications.length} />;
}
```

### 2. Booking Updates

**Backend:**
```php
use App\Events\BookingStatusUpdated;

// After updating booking status
event(new BookingStatusUpdated($booking));
```

**Frontend:**
```typescript
import { useChannelEvent } from '@/hooks/use-echo';

function BookingDetails({ bookingId }: { bookingId: string }) {
  useChannelEvent(
    `booking.${bookingId}`,
    'booking.status.updated',
    (data) => {
      console.log('Booking status changed:', data.status);
      // Refresh booking data or update UI
    },
    'private'
  );
}
```

### 3. Real-time Chat

**Backend:**
```php
use App\Events\NewMessage;

// After creating a message
event(new NewMessage($message));
```

**Frontend:**
```typescript
import { useConversationMessages } from '@/hooks/use-echo';

function Chat({ conversationId }: { conversationId: string }) {
  const { messages, typingUsers, whisper } = useConversationMessages(
    conversationId,
    (newMessage) => {
      // New message received
      setMessages(prev => [...prev, newMessage]);
    }
  );
  
  const handleTyping = () => {
    whisper('typing', { user: currentUser.name });
  };
  
  return (
    <div>
      {typingUsers.length > 0 && (
        <div>{typingUsers.join(', ')} is typing...</div>
      )}
      {/* Chat UI */}
    </div>
  );
}
```

### 4. Property Updates (Owner Dashboard)

**Backend:**
```php
// When property is viewed or booking created
broadcast(new \Illuminate\Broadcasting\PrivateChannel('property.' . $propertyId))
    ->with([
        'type' => 'view',
        'viewer_count' => $viewCount
    ]);
```

**Frontend:**
```typescript
import { useChannelEvent } from '@/hooks/use-echo';

function OwnerDashboard({ propertyId }: { propertyId: string }) {
  const [viewCount, setViewCount] = useState(0);
  
  useChannelEvent(
    `property.${propertyId}`,
    'view',
    (data) => {
      setViewCount(data.viewer_count);
    },
    'private'
  );
}
```

### 5. Who's Online (Presence Channel)

**Frontend:**
```typescript
import { usePresenceChannel } from '@/hooks/use-echo';

function OnlineUsers() {
  const { members } = usePresenceChannel('chat.lobby', {
    onJoining: (member) => {
      toast.info(`${member.name} joined`);
    },
    onLeaving: (member) => {
      toast.info(`${member.name} left`);
    }
  });
  
  return (
    <div>
      <h3>Online ({members.length})</h3>
      {members.map(m => (
        <div key={m.id}>
          <Avatar src={m.avatar} />
          {m.name}
        </div>
      ))}
    </div>
  );
}
```

## 🎯 Available Hooks

All hooks are in `frontend/src/hooks/use-echo.ts`:

- **`useEcho()`** - Get Echo instance and connection state
- **`useChannel(channel)`** - Subscribe to public channel
- **`usePrivateChannel(channel)`** - Subscribe to private channel
- **`usePresenceChannel(channel)`** - Subscribe to presence channel
- **`useChannelEvent(channel, event, callback)`** - Listen to specific event
- **`useUserNotifications(onNotification)`** - User notifications
- **`useConversationMessages(conversationId, onMessage)`** - Chat messages
- **`useEchoCleanup()`** - Cleanup all subscriptions on unmount

## 📝 Created Files

### Backend
- ✅ `app/Providers/BroadcastServiceProvider.php` - Broadcasting setup
- ✅ `routes/channels.php` - Channel authorization
- ✅ `app/Events/UserNotification.php` - User notifications
- ✅ `app/Events/BookingStatusUpdated.php` - Booking updates
- ✅ `app/Events/NewMessage.php` - Chat messages

### Frontend
- ✅ `src/lib/echo.ts` - Echo client wrapper
- ✅ `src/hooks/use-echo.ts` - React hooks (9 hooks)

### Documentation
- ✅ `docs/LARAVEL_ECHO_MIGRATION.md` - Full migration guide
- ✅ `docs/LARAVEL_ECHO_SETUP.md` - Comprehensive setup guide
- ✅ `docs/LARAVEL_ECHO_QUICKSTART.md` - This quick start

## 🔍 Debugging

### Check Backend Broadcasting

```bash
cd backend
php artisan tinker
```

```php
// Test Pusher connection
config('broadcasting.connections.pusher.key'); // Should show your key
config('broadcasting.default'); // Should be 'pusher'

// Test event broadcasting
$user = User::first();
event(new App\Events\UserNotification($user, 'Test', 'Hello!'));
```

Check your Pusher dashboard to see if the event appears.

### Check Frontend Connection

Open browser console:

```javascript
// Should see Echo connection logs
// Look for: "Echo connected: true"
```

### Common Issues

1. **"Connection refused"** → Check Pusher credentials match in backend and frontend
2. **"401 Unauthorized"** → Check BroadcastServiceProvider is enabled and routes/channels.php exists
3. **"Event not received"** → Check event implements ShouldBroadcast and channel authorization
4. **"CORS error"** → Check SANCTUM_STATEFUL_DOMAINS includes your frontend URL

## 🎉 Next Steps

1. ✅ Configure Pusher credentials
2. Test with UserNotification event
3. Integrate into your existing features:
   - Booking notifications
   - Chat system
   - Property updates
   - User presence
4. Remove Socket.IO once Echo is verified
5. Deploy to production

## 📖 Full Documentation

- [Complete Setup Guide](./LARAVEL_ECHO_SETUP.md) - Detailed configuration
- [Migration Guide](./LARAVEL_ECHO_MIGRATION.md) - Migrate from Socket.IO
- [Laravel Docs](https://laravel.com/docs/11.x/broadcasting) - Official Laravel broadcasting
- [Pusher Docs](https://pusher.com/docs/channels/) - Pusher Channels documentation

## 💡 Pro Tips

1. **Use presence channels** for "who's online" features
2. **Whisper events** for typing indicators (don't hit backend)
3. **Queue events** for better performance: `implements ShouldBroadcast, ShouldQueue`
4. **Use .env for configuration** - never hardcode Pusher credentials
5. **Test in development** with Pusher's free tier (100 connections)

## 🎯 Performance

- Echo automatically reconnects on disconnect
- Whisper events don't hit your server (client-to-client)
- Presence channels use minimal bandwidth
- All hooks auto-cleanup on unmount

Happy real-time coding! 🚀

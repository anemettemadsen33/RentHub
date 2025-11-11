# Laravel Echo Setup Guide

## Installation Complete ✅

Laravel Echo and Pusher JS have been installed in the frontend project.

## Configuration Steps

### 1. Get Pusher Credentials

Sign up for a free Pusher account at https://pusher.com/

Create a new Channels app and note down:
- App ID
- Key
- Secret
- Cluster (e.g., `mt1`, `us2`, `eu`, etc.)

### 2. Configure Backend (.env)

Update your `backend/.env` file with Pusher credentials:

```env
# Change broadcast connection from 'log' to 'pusher'
BROADCAST_CONNECTION=pusher

# Pusher Configuration
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
```

### 3. Configure Frontend (.env.local)

Update your `frontend/.env.local` file:

```env
# Pusher (WebSockets) - must match backend
NEXT_PUBLIC_PUSHER_KEY=your_key
NEXT_PUBLIC_PUSHER_CLUSTER=mt1
```

### 4. Enable Broadcasting in Laravel

Uncomment the BroadcastServiceProvider in `backend/config/app.php`:

```php
'providers' => ServiceProvider::defaultProviders()->merge([
    // ...
    App\Providers\BroadcastServiceProvider::class,
    // ...
])->toArray(),
```

### 5. Install Pusher PHP SDK (Backend)

```bash
cd backend
composer require pusher/pusher-php-server
```

### 6. Test the Connection

#### Backend Test:
```bash
cd backend
php artisan tinker
```

```php
// Test broadcasting
use App\Models\User;
$user = User::first();
broadcast(new App\Events\UserNotification($user, 'Test notification'));
```

#### Frontend Test:

```typescript
import { useEcho } from '@/hooks/use-echo';

function TestComponent() {
  const { echo, isConnected } = useEcho();
  
  useEffect(() => {
    console.log('Echo connected:', isConnected);
    if (isConnected) {
      console.log('✅ Laravel Echo is working!');
    }
  }, [isConnected]);
  
  return <div>Echo Status: {isConnected ? 'Connected' : 'Disconnected'}</div>;
}
```

## Usage Examples

### 1. Listen to User Notifications

```typescript
import { useUserNotifications } from '@/hooks/use-echo';

function NotificationBell() {
  const notifications = useUserNotifications((notification) => {
    toast.success(notification.message);
  });
  
  return <span>🔔 {notifications.length}</span>;
}
```

### 2. Real-time Chat

```typescript
import { useConversationMessages } from '@/hooks/use-echo';

function ChatRoom({ conversationId }: { conversationId: string }) {
  const { messages, typingUsers, whisper } = useConversationMessages(
    conversationId,
    (message) => {
      // New message received
      console.log('New message:', message);
    }
  );
  
  const handleTyping = () => {
    whisper('typing', { user: 'John' });
  };
  
  return (
    <div>
      {typingUsers.length > 0 && <span>{typingUsers.join(', ')} is typing...</span>}
    </div>
  );
}
```

### 3. Property Booking Updates

```typescript
import { useChannelEvent } from '@/hooks/use-echo';

function PropertyBookings({ propertyId }: { propertyId: string }) {
  useChannelEvent(
    `property.${propertyId}`,
    'booking.created',
    (booking) => {
      toast.info('New booking received!');
      // Refresh bookings list
    },
    'private'
  );
}
```

### 4. Presence Channel (Who's Online)

```typescript
import { usePresenceChannel } from '@/hooks/use-echo';

function OnlineUsers({ roomId }: { roomId: string }) {
  const { members, joining, leaving } = usePresenceChannel(
    `chat.${roomId}`,
    {
      onJoining: (member) => {
        console.log(`${member.name} joined`);
      },
      onLeaving: (member) => {
        console.log(`${member.name} left`);
      }
    }
  );
  
  return (
    <div>
      <h3>Online Users ({members.length})</h3>
      {members.map(m => <div key={m.id}>{m.name}</div>)}
    </div>
  );
}
```

## Backend Broadcasting Events

Create broadcast events using:

```bash
php artisan make:event UserNotification
```

Example event:

```php
<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserNotification implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public string $message,
        public array $data = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

## Channel Authorization

Define authorization rules in `backend/routes/channels.php`:

```php
use App\Models\User;
use App\Models\Property;
use App\Models\Conversation;

// User's private channel
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

// Property channel (owner only)
Broadcast::channel('property.{propertyId}', function (User $user, int $propertyId) {
    return Property::where('id', $propertyId)
        ->where('owner_id', $user->id)
        ->exists();
});

// Conversation channel (participants only)
Broadcast::channel('conversation.{conversationId}', function (User $user, int $conversationId) {
    return Conversation::where('id', $conversationId)
        ->whereHas('participants', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->exists();
});

// Chat presence channel
Broadcast::channel('chat.{roomId}', function (User $user, string $roomId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url,
    ];
});
```

## Troubleshooting

### Echo Not Connecting

1. **Check Pusher credentials** - Ensure they match in both `.env` and `.env.local`
2. **Verify API URL** - Echo needs to authenticate with your backend
3. **Check CORS** - Ensure backend allows frontend origin
4. **Browser console** - Look for connection errors or 401/403 responses

### Authentication Failing

1. **Token not set** - Ensure you call `getEcho(token)` with valid auth token
2. **Sanctum issues** - Check `SANCTUM_STATEFUL_DOMAINS` in backend `.env`
3. **Channel authorization** - Verify `routes/channels.php` rules

### Events Not Received

1. **Event not implementing ShouldBroadcast** - Check your event class
2. **Wrong channel name** - Ensure frontend subscribes to correct channel
3. **Broadcasting driver** - Verify `BROADCAST_CONNECTION=pusher` in backend `.env`
4. **Queue not running** - Events may be queued, run `php artisan queue:work`

### Development Tools

- **Pusher Debug Console**: View real-time events in your Pusher dashboard
- **Laravel Telescope**: Monitor broadcast events (if installed)
- **Browser DevTools**: Network tab shows Echo connection attempts

## Migration from Socket.IO

If you're currently using Socket.IO:

1. **Don't remove Socket.IO yet** - Keep it until Echo is fully tested
2. **Run both in parallel** - Test Echo in development while Socket.IO runs in production
3. **Feature flag** - Use environment variable to toggle between them
4. **Remove Socket.IO** - Once Echo is stable, remove socket.io-client dependency

```typescript
// Feature flag example
const useEchoInsteadOfSocketIO = process.env.NEXT_PUBLIC_USE_ECHO === 'true';

if (useEchoInsteadOfSocketIO) {
  // Use Echo hooks
} else {
  // Use Socket.IO
}
```

## Performance Tips

1. **Cleanup on unmount** - Always use `useEchoCleanup()` hook
2. **Limit subscriptions** - Only subscribe to channels you need
3. **Batch updates** - Use React state batching for multiple events
4. **Debounce typing events** - Don't whisper on every keystroke

## Security Best Practices

1. **Always use private channels** for user-specific data
2. **Validate authorization** in `routes/channels.php`
3. **Sanitize broadcast data** - Don't send sensitive information
4. **Rate limit** - Prevent spam in presence/whisper events
5. **HTTPS in production** - Use encrypted connections

## Next Steps

1. Configure Pusher credentials
2. Enable BroadcastServiceProvider
3. Install pusher-php-server
4. Create your first broadcast event
5. Test with the provided examples
6. Replace Socket.IO usage gradually

## Resources

- [Laravel Broadcasting Docs](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Echo Docs](https://laravel.com/docs/11.x/broadcasting#client-side-installation)
- [Pusher Channels Docs](https://pusher.com/docs/channels/)
- [Migration Guide](./LARAVEL_ECHO_MIGRATION.md)

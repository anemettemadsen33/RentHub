# Laravel Echo Migration Guide

## Overview
This guide covers migrating from Socket.IO to Laravel Echo with Pusher for real-time features in RentHub.

## Status
✅ **Frontend Code**: Complete
⚠️ **Installation**: Requires npm install
⚠️ **Backend**: Requires Laravel broadcasting configuration
⚠️ **Testing**: Pending after installation

## Installation Steps

### 1. Install Laravel Echo

```bash
cd frontend
npm install laravel-echo --save
```

The `pusher-js` package is already installed in the frontend.

### 2. Update Environment Variables

Create or update `frontend/.env.local`:

```env
# Pusher Configuration
NEXT_PUBLIC_PUSHER_APP_KEY=your_pusher_app_key
NEXT_PUBLIC_PUSHER_APP_CLUSTER=us2
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

### 3. Backend Laravel Configuration

Update `backend/config/broadcasting.php`:

```php
'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'encrypted' => true,
            'host' => env('PUSHER_HOST'),
            'port' => env('PUSHER_PORT', 443),
            'scheme' => env('PUSHER_SCHEME', 'https'),
        ],
    ],
],
```

Update `backend/.env`:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=us2
```

Install Laravel broadcasting dependencies:

```bash
cd backend
composer require pusher/pusher-php-server
```

Uncomment in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\BroadcastServiceProvider::class,
],
```

### 4. Remove Socket.IO (Optional)

After verifying Echo works, you can remove Socket.IO:

```bash
npm uninstall socket.io-client
```

Remove Socket.IO imports from components (search for `socket.io-client`).

## Usage Examples

### Basic Channel Subscription

```typescript
import { useChannel, useChannelEvent } from '@/hooks/use-echo';

function MyComponent() {
  // Listen for events on a public channel
  useChannelEvent(
    'orders',
    'OrderShipped',
    (data) => {
      console.log('Order shipped:', data);
    },
    { enabled: true }
  );

  return <div>Listening for order updates...</div>;
}
```

### Private Channel (Authenticated)

```typescript
import { usePrivateChannel } from '@/hooks/use-echo';
import { useAuth } from '@/contexts/auth-context';

function NotificationsComponent() {
  const { token, user } = useAuth();
  const channel = usePrivateChannel(
    `App.Models.User.${user?.id}`,
    token || '',
    !!user && !!token
  );

  useEffect(() => {
    if (!channel) return;

    channel.notification((notification: any) => {
      console.log('New notification:', notification);
      toast.success(notification.message);
    });
  }, [channel]);

  return <div>Your notifications</div>;
}
```

### Presence Channel (Who's Online)

```typescript
import { usePresenceChannel } from '@/hooks/use-echo';

function ChatRoom({ roomId }: { roomId: number }) {
  const { token } = useAuth();
  const { channel, members } = usePresenceChannel(
    `chat.${roomId}`,
    token || '',
    !!token
  );

  return (
    <div>
      <h3>Online Users: {members.length}</h3>
      <ul>
        {members.map(member => (
          <li key={member.id}>{member.name}</li>
        ))}
      </ul>
    </div>
  );
}
```

### Conversation Messages (Replacing Socket.IO)

```typescript
import { useConversationMessages } from '@/hooks/use-echo';

function ConversationView({ conversationId }: { conversationId: number }) {
  const { token } = useAuth();
  const [messages, setMessages] = useState([]);
  const [typingUsers, setTypingUsers] = useState<number[]>([]);

  const { whisperTyping } = useConversationMessages(
    conversationId,
    token || '',
    (message) => {
      // New message received
      setMessages(prev => [...prev, message]);
    },
    (data) => {
      // Someone is typing
      if (data.is_typing) {
        setTypingUsers(prev => [...prev, data.user_id]);
      } else {
        setTypingUsers(prev => prev.filter(id => id !== data.user_id));
      }
    }
  );

  const handleTyping = (isTyping: boolean) => {
    whisperTyping(isTyping);
  };

  return (
    <div>
      {messages.map(msg => <Message key={msg.id} {...msg} />)}
      {typingUsers.length > 0 && <div>Someone is typing...</div>}
      <MessageInput onTyping={handleTyping} />
    </div>
  );
}
```

## Backend Broadcasting Examples

### Broadcast Notification

```php
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageNotification extends Notification implements ShouldBroadcast
{
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'You have a new message',
            'conversation_id' => $this->conversation->id,
        ]);
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->user->id);
    }
}
```

### Broadcast Event

```php
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'user' => $this->message->user,
            'created_at' => $this->message->created_at,
        ];
    }
}
```

## Files Created

1. `src/lib/echo.ts` - Echo client configuration and utilities
2. `src/hooks/use-echo.ts` - React hooks for Echo integration
3. `docs/LARAVEL_ECHO_MIGRATION.md` - This guide

## Migration Checklist

### Frontend
- [x] Create Echo client wrapper (`src/lib/echo.ts`)
- [x] Create React hooks (`src/hooks/use-echo.ts`)
- [ ] Install `laravel-echo` package
- [ ] Update `.env.local` with Pusher credentials
- [ ] Replace Socket.IO usage in `src/app/messages/page.tsx`
- [ ] Update notification listening in components
- [ ] Remove `socket.io-client` dependency
- [ ] Test real-time features

### Backend
- [ ] Install `pusher/pusher-php-server`
- [ ] Configure broadcasting in `config/broadcasting.php`
- [ ] Add Pusher credentials to `.env`
- [ ] Uncomment `BroadcastServiceProvider` in `config/app.php`
- [ ] Create broadcast events for messages
- [ ] Create broadcast events for notifications
- [ ] Add broadcasting routes (`routes/channels.php`)
- [ ] Test broadcast authorization

### Testing
- [ ] Test public channels
- [ ] Test private channels (auth required)
- [ ] Test presence channels (online status)
- [ ] Test message broadcasting
- [ ] Test notification broadcasting
- [ ] Test typing indicators
- [ ] Test connection/disconnection handling
- [ ] Load test with multiple concurrent users

## Pusher Setup (Production)

1. Sign up at https://pusher.com
2. Create a new Channels app
3. Get credentials from "App Keys" tab
4. Add to production environment variables
5. Configure CORS in Pusher dashboard
6. Set up webhooks for connection events (optional)

## Local Development Alternative

For local development without Pusher, you can use Laravel Websockets:

```bash
composer require beyondcode/laravel-websockets
php artisan websockets:install
php artisan migrate
```

Update broadcasting config to use websockets locally:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY', 'local'),
    'secret' => env('PUSHER_APP_SECRET', 'local'),
    'app_id' => env('PUSHER_APP_ID', 'local'),
    'options' => [
        'host' => env('PUSHER_HOST', '127.0.0.1'),
        'port' => env('PUSHER_PORT', 6001),
        'scheme' => env('PUSHER_SCHEME', 'http'),
        'encrypted' => false,
    ],
],
```

Frontend Echo config:

```typescript
wsHost: process.env.NEXT_PUBLIC_PUSHER_HOST || '127.0.0.1',
wsPort: Number(process.env.NEXT_PUBLIC_PUSHER_PORT) || 6001,
wssPort: Number(process.env.NEXT_PUBLIC_PUSHER_PORT) || 6001,
forceTLS: false,
enabledTransports: ['ws', 'wss'],
```

## Troubleshooting

### Connection Fails
- Check Pusher credentials
- Verify backend broadcasting driver is set to 'pusher'
- Check CORS settings in Pusher dashboard
- Verify auth endpoint is accessible

### Private Channels Not Working
- Ensure auth token is passed to Echo
- Check `routes/channels.php` authorization
- Verify auth headers in Echo configuration
- Check backend logs for auth failures

### Events Not Firing
- Verify event implements `ShouldBroadcast`
- Check event is dispatched with `broadcast()` or `event()`
- Verify channel name matches frontend subscription
- Check Pusher debug console for activity

## Performance Considerations

- Use presence channels sparingly (they're more expensive)
- Batch notifications when possible
- Use channel-specific events instead of broadcasting everything
- Consider using queued broadcasts for non-critical updates
- Monitor Pusher usage/costs in production

## Security Best Practices

- Always use private channels for user-specific data
- Implement proper authorization in `routes/channels.php`
- Don't broadcast sensitive information
- Use HTTPS in production (forceTLS: true)
- Rotate Pusher credentials regularly
- Monitor for unusual connection patterns

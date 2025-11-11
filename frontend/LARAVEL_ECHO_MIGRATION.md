# Laravel Echo Migration Plan
**Real-time Communication: socket.io → Laravel Echo/Reverb**

## Current State
- **Messages page** (`src/app/messages/page.tsx`) uses raw socket.io client
- **Backend** has Laravel broadcasting configured (Reverb/Pusher ready)
- **TODO comment** at line 55: "Replace raw socket.io usage with Laravel Echo"

---

## Migration Steps

### Phase 1: Setup Laravel Echo Client

**1.1 Install dependencies**
```bash
cd frontend
npm install laravel-echo pusher-js
```

**1.2 Create Echo client wrapper**
File: `src/lib/echo-client.ts`

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

let echoInstance: Echo | null = null;

export function getEcho(): Echo {
  if (echoInstance) return echoInstance;

  window.Pusher = Pusher;

  echoInstance = new Echo({
    broadcaster: 'reverb', // or 'pusher'
    key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
    wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
    wsPort: parseInt(process.env.NEXT_PUBLIC_REVERB_PORT || '8080'),
    wssPort: parseInt(process.env.NEXT_PUBLIC_REVERB_PORT || '8080'),
    forceTLS: (process.env.NEXT_PUBLIC_REVERB_SCHEME || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    },
  });

  return echoInstance;
}

export function disconnectEcho() {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}
```

**1.3 Add environment variables**
File: `frontend/.env.local`

```env
NEXT_PUBLIC_REVERB_APP_KEY=your-app-key
NEXT_PUBLIC_REVERB_HOST=localhost
NEXT_PUBLIC_REVERB_PORT=8080
NEXT_PUBLIC_REVERB_SCHEME=http
```

---

### Phase 2: Create useRealtime Hook

**2.1 Abstract real-time logic**
File: `src/hooks/use-realtime.ts`

```typescript
'use client';

import { useEffect, useCallback } from 'react';
import { getEcho } from '@/lib/echo-client';

export interface RealtimeOptions {
  channel: string;
  events: {
    [eventName: string]: (data: any) => void;
  };
  isPrivate?: boolean;
  isPresence?: boolean;
}

export function useRealtime({
  channel,
  events,
  isPrivate = false,
  isPresence = false,
}: RealtimeOptions) {
  useEffect(() => {
    const echo = getEcho();
    let subscription;

    if (isPresence) {
      subscription = echo.join(channel);
      
      // Presence callbacks
      subscription
        .here((users: any[]) => {
          events['presence:here']?.(users);
        })
        .joining((user: any) => {
          events['presence:joining']?.(user);
        })
        .leaving((user: any) => {
          events['presence:leaving']?.(user);
        });
    } else if (isPrivate) {
      subscription = echo.private(channel);
    } else {
      subscription = echo.channel(channel);
    }

    // Listen to custom events
    Object.entries(events).forEach(([event, handler]) => {
      if (!event.startsWith('presence:')) {
        subscription.listen(`.${event}`, handler);
      }
    });

    return () => {
      echo.leave(channel);
    };
  }, [channel, isPrivate, isPresence]);
}
```

---

### Phase 3: Migrate Messages Page

**3.1 Update messages page**
File: `src/app/messages/page.tsx`

**Before:**
```typescript
// socket.io usage
const socket = io(SOCKET_URL);
socket.on('message.new', handleNewMessage);
```

**After:**
```typescript
import { useRealtime } from '@/hooks/use-realtime';

// Inside component
useRealtime({
  channel: `private-user.${user.id}.messages`,
  isPrivate: true,
  events: {
    'MessageSent': (data) => {
      // Handle new message
      setConversations(prev => updateConversation(prev, data.message));
    },
    'MessageRead': (data) => {
      // Mark as read
      markAsRead(data.message_id);
    },
  },
});
```

**3.2 Typing indicator with presence**
```typescript
useRealtime({
  channel: `presence-conversation.${conversationId}`,
  isPresence: true,
  events: {
    'presence:here': (users) => {
      setOnlineUsers(users);
    },
    'TypingStarted': (data) => {
      setTypingUsers(prev => [...prev, data.user_id]);
    },
    'TypingStopped': (data) => {
      setTypingUsers(prev => prev.filter(id => id !== data.user_id));
    },
  },
});
```

---

### Phase 4: Notifications Migration

**4.1 Update notifications context**
File: `src/contexts/notification-context.tsx`

**Add real-time listener:**
```typescript
useRealtime({
  channel: `private-user.${user.id}.notifications`,
  isPrivate: true,
  events: {
    'NotificationCreated': (data) => {
      setNotifications(prev => [data.notification, ...prev]);
      setUnreadCount(prev => prev + 1);
      
      // Toast notification
      toast({
        title: data.notification.title,
        description: data.notification.message,
      });
    },
  },
});
```

---

### Phase 5: Testing & Rollout

**5.1 Test checklist**
- [ ] Messages real-time delivery works
- [ ] Typing indicators show/hide correctly
- [ ] Presence (online/offline) updates
- [ ] Notifications arrive instantly
- [ ] Reconnection logic handles network issues
- [ ] Private channels authenticate correctly
- [ ] Browser tab visibility doesn't break subscriptions

**5.2 Fallback strategy**
Keep socket.io as fallback for 1 release cycle:

```typescript
const USE_ECHO = process.env.NEXT_PUBLIC_USE_ECHO === 'true';

if (USE_ECHO) {
  useRealtime({ /* Echo config */ });
} else {
  // Legacy socket.io
}
```

**5.3 Monitor**
- WebSocket connection stability
- Message delivery latency
- Echo reconnection events
- Error logs for broadcasting auth failures

---

## Backend Requirements

**Ensure backend has:**
1. Broadcasting routes configured (`routes/channels.php`)
2. Reverb/Pusher credentials in `.env`
3. Events fire on message send:
   ```php
   broadcast(new MessageSent($message))->toOthers();
   ```
4. Private channel authorization:
   ```php
   Broadcast::channel('user.{userId}.messages', function ($user, $userId) {
       return (int) $user->id === (int) $userId;
   });
   ```

---

## Rollback Plan

If issues arise:
1. Set `NEXT_PUBLIC_USE_ECHO=false`
2. Revert to socket.io client
3. Investigate Echo logs (`storage/logs/laravel.log`)
4. Check Reverb/Pusher dashboard for errors

---

## Benefits

✅ **Unified broadcasting**: Laravel Events → Frontend seamlessly  
✅ **Presence channels**: See who's online  
✅ **Private/encrypted channels**: Secure user data  
✅ **Auto-reconnection**: Built into Pusher client  
✅ **Scales**: Reverb designed for Laravel at scale  

---

## Timeline

- **Week 1**: Install deps, create Echo client, test basic channel
- **Week 2**: Migrate messages page, typing indicators
- **Week 3**: Notifications real-time updates
- **Week 4**: Full E2E testing, rollout with feature flag
- **Week 5**: Monitor, remove socket.io fallback

---

## Questions?

See:
- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Laravel Echo Docs](https://laravel.com/docs/echo)
- [Reverb Setup Guide](REVERB_SETUP.md) in `/docs`

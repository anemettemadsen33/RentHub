/**
 * Laravel Echo Client Configuration
 * 
 * This module provides a configured Laravel Echo instance for real-time communication
 * with the backend via Pusher. Replaces direct Socket.IO usage.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb client uses standard WebSocket without extra SDK. We can adapt Echo's 'pusher' driver by supplying a fake key if Reverb is enabled.
const USE_REVERB = process.env.NEXT_PUBLIC_USE_REVERB === 'true';

// Make Pusher available globally for Laravel Echo
if (typeof window !== 'undefined') {
  (window as any).Pusher = Pusher;
}

// Dynamic configuration for either Pusher SaaS or Reverb self-hosted
const baseAuthEndpoint = process.env.NEXT_PUBLIC_API_URL 
  ? `${process.env.NEXT_PUBLIC_API_URL}/broadcasting/auth`
  : 'http://localhost:8000/api/broadcasting/auth';

const pusherConfig = USE_REVERB ? {
  // Reverb still uses the pusher protocol, we provide local fake keys
  key: process.env.NEXT_PUBLIC_REVERB_KEY || 'renthub-key',
  cluster: 'mt1',
  forceTLS: false,
  encrypted: false,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
  wsPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
  wssPort: Number(process.env.NEXT_PUBLIC_REVERB_PORT || 8080),
  wsPath: process.env.NEXT_PUBLIC_REVERB_PATH || '',
  disableStats: true,
  enabledTransports: ['ws','wss'],
  authEndpoint: baseAuthEndpoint,
  auth: { headers: { Accept: 'application/json' } },
} : {
  key: process.env.NEXT_PUBLIC_PUSHER_KEY || '',
  cluster: process.env.NEXT_PUBLIC_PUSHER_CLUSTER || 'us2',
  forceTLS: true,
  encrypted: true,
  authEndpoint: baseAuthEndpoint,
  auth: { headers: { Accept: 'application/json' } },
};

let echoInstance: Echo<any> | null = null;

/**
 * Get or create Echo instance
 * @param authToken - Optional auth token for authenticated channels
 */
export function getEcho(authToken?: string): Echo<any> {
  if (echoInstance) {
    // Update auth headers if token changed
    if (authToken && echoInstance.connector.pusher.config.auth) {
      echoInstance.connector.pusher.config.auth.headers = {
        ...echoInstance.connector.pusher.config.auth.headers,
        Authorization: `Bearer ${authToken}`,
      };
    }
    return echoInstance;
  }

  const config: any = {
    broadcaster: 'pusher',
    ...pusherConfig,
  };

  // Add auth token if provided
  if (authToken) {
    config.auth.headers.Authorization = `Bearer ${authToken}`;
  }

  echoInstance = new Echo(config);
  return echoInstance;
}

/**
 * Disconnect Echo instance
 */
export function disconnectEcho(): void {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}

/**
 * Subscribe to a public channel
 */
export function subscribeToChannel(channelName: string, authToken?: string): any {
  const echo = getEcho(authToken);
  return echo.channel(channelName);
}

/**
 * Subscribe to a private channel (requires authentication)
 */
export function subscribeToPrivateChannel(channelName: string, authToken: string): any {
  const echo = getEcho(authToken);
  return echo.private(channelName);
}

/**
 * Subscribe to a presence channel (for online status)
 */
export function subscribeToPresenceChannel(channelName: string, authToken: string): any {
  const echo = getEcho(authToken);
  return echo.join(channelName);
}

/**
 * Leave a channel
 */
export function leaveChannel(channelName: string): void {
  const echo = getEcho();
  echo.leave(channelName);
}

/**
 * Convenience methods for common channel patterns
 */
export const echoChannels = {
  /**
   * User's private notification channel
   */
  userNotifications: (userId: number, authToken: string) => {
    return subscribeToPrivateChannel(`user.${userId}`, authToken);
  },

  /**
   * Conversation messages channel
   */
  conversation: (conversationId: number, authToken: string) => {
    return subscribeToPrivateChannel(`conversation.${conversationId}`, authToken);
  },

  /**
   * Property booking updates
   */
  propertyBookings: (propertyId: number, authToken: string) => {
    return subscribeToPrivateChannel(`property.${propertyId}.bookings`, authToken);
  },

  /**
   * Presence channel for chat (who's online)
   */
  chatPresence: (conversationId: number, authToken: string) => {
    return subscribeToPresenceChannel(`chat.${conversationId}`, authToken);
  },

  /**
   * Public property updates channel
   */
  propertyUpdates: (propertyId: number) => {
    return subscribeToChannel(`property.${propertyId}.updates`);
  },
};

export default getEcho;

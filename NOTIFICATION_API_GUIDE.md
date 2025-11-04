# 📬 RentHub Notification API Guide

Complete guide for integrating RentHub's notification system into your frontend application.

---

## 🚀 Quick Start

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
All notification endpoints require authentication via Bearer token:
```
Authorization: Bearer {your_jwt_token}
```

---

## 📡 API Endpoints

### 1. **Get Notifications**

Retrieve paginated list of user notifications.

**Endpoint**: `GET /notifications`

**Query Parameters**:
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | int | 1 | Page number |
| `per_page` | int | 15 | Items per page |
| `unread_only` | boolean | false | Filter only unread |
| `type` | string | null | Filter by notification type |

**Example Request**:
```bash
curl -X GET "http://localhost:8000/api/v1/notifications?per_page=10&unread_only=true" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": "9d3a5c8f-a1b2-c3d4-e5f6-1234567890ab",
        "type": "App\\Notifications\\Booking\\BookingConfirmedNotification",
        "data": {
          "type": "booking_confirmed",
          "booking_id": 15,
          "property_title": "Beach Villa Miami",
          "message": "Your booking has been confirmed!",
          "action_url": "/dashboard/bookings/15"
        },
        "read_at": null,
        "created_at": "2025-11-02T17:30:00.000000Z"
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

---

### 2. **Get Unread Count**

Get the count of unread notifications for the bell icon.

**Endpoint**: `GET /notifications/unread-count`

**Example Request**:
```bash
curl -X GET "http://localhost:8000/api/v1/notifications/unread-count" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "count": 5
}
```

**Usage**: Poll this endpoint every 30-60 seconds to keep the notification bell updated.

---

### 3. **Mark Single Notification as Read**

Mark a specific notification as read when user clicks on it.

**Endpoint**: `POST /notifications/{id}/read`

**Example Request**:
```bash
curl -X POST "http://localhost:8000/api/v1/notifications/9d3a5c8f-a1b2-c3d4-e5f6-1234567890ab/read" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "message": "Notification marked as read"
}
```

---

### 4. **Mark All Notifications as Read**

Mark all user's notifications as read at once.

**Endpoint**: `POST /notifications/mark-all-read`

**Example Request**:
```bash
curl -X POST "http://localhost:8000/api/v1/notifications/mark-all-read" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "message": "All notifications marked as read"
}
```

**Usage**: Call when user clicks "Mark all as read" button.

---

### 5. **Delete Notification**

Delete a specific notification.

**Endpoint**: `DELETE /notifications/{id}`

**Example Request**:
```bash
curl -X DELETE "http://localhost:8000/api/v1/notifications/9d3a5c8f-a1b2-c3d4-e5f6-1234567890ab" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "message": "Notification deleted"
}
```

---

### 6. **Get Notification Preferences**

Retrieve user's notification preferences for all types.

**Endpoint**: `GET /notifications/preferences`

**Example Request**:
```bash
curl -X GET "http://localhost:8000/api/v1/notifications/preferences" \
  -H "Authorization: Bearer your_token_here"
```

**Example Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "notification_type": "booking",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    },
    {
      "id": 2,
      "user_id": 5,
      "notification_type": "payment",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    },
    {
      "id": 3,
      "user_id": 5,
      "notification_type": "review",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    },
    {
      "id": 4,
      "user_id": 5,
      "notification_type": "account",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    },
    {
      "id": 5,
      "user_id": 5,
      "notification_type": "system",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    }
  ]
}
```

---

### 7. **Update Notification Preferences**

Update user's notification preferences.

**Endpoint**: `PUT /notifications/preferences`

**Request Body**:
```json
{
  "preferences": [
    {
      "notification_type": "booking",
      "channel_email": true,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": true
    },
    {
      "notification_type": "payment",
      "channel_email": false,
      "channel_database": true,
      "channel_sms": false,
      "channel_push": false
    }
  ]
}
```

**Example Request**:
```bash
curl -X PUT "http://localhost:8000/api/v1/notifications/preferences" \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json" \
  -d '{
    "preferences": [
      {
        "notification_type": "booking",
        "channel_email": true,
        "channel_database": true,
        "channel_sms": false,
        "channel_push": true
      }
    ]
  }'
```

**Example Response**:
```json
{
  "success": true,
  "message": "Notification preferences updated successfully",
  "data": [...]
}
```

---

## 📝 Notification Types

### Available Types

| Type | Description | Triggers |
|------|-------------|----------|
| `booking` | Booking-related notifications | Request, Confirm, Reject, Cancel |
| `payment` | Payment notifications | Received, Failed, Receipt |
| `review` | Review notifications | New review, Response, Reminder |
| `account` | Account notifications | Welcome, Verify, Account status |
| `system` | System notifications | Maintenance, Updates, Alerts |

### Notification Data Structure

Each notification has a `data` field with these common properties:

```json
{
  "type": "booking_confirmed",
  "message": "Human-readable message",
  "action_url": "/dashboard/bookings/15",
  // Type-specific fields...
  "booking_id": 15,
  "property_title": "Beach Villa Miami"
}
```

---

## 🎨 Frontend Integration Examples

### React/Next.js Example

#### 1. Notification Bell Component

```typescript
// components/NotificationBell.tsx
'use client';

import { useState, useEffect } from 'react';
import { Bell } from 'lucide-react';
import { useRouter } from 'next/navigation';

export function NotificationBell() {
  const [unreadCount, setUnreadCount] = useState(0);
  const [notifications, setNotifications] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const router = useRouter();

  // Fetch unread count
  const fetchUnreadCount = async () => {
    const response = await fetch('/api/v1/notifications/unread-count', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setUnreadCount(data.count);
  };

  // Fetch recent notifications
  const fetchNotifications = async () => {
    const response = await fetch('/api/v1/notifications?per_page=5', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setNotifications(data.data.data);
  };

  // Mark as read and navigate
  const handleNotificationClick = async (notification) => {
    await fetch(`/api/v1/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    setUnreadCount(prev => Math.max(0, prev - 1));
    router.push(notification.data.action_url);
    setShowDropdown(false);
  };

  // Mark all as read
  const markAllAsRead = async () => {
    await fetch('/api/v1/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    setUnreadCount(0);
    fetchNotifications();
  };

  useEffect(() => {
    fetchUnreadCount();
    fetchNotifications();
    
    // Poll every 30 seconds
    const interval = setInterval(() => {
      fetchUnreadCount();
      fetchNotifications();
    }, 30000);
    
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="relative">
      <button
        onClick={() => setShowDropdown(!showDropdown)}
        className="relative p-2 hover:bg-gray-100 rounded-full"
      >
        <Bell className="w-6 h-6" />
        {unreadCount > 0 && (
          <span className="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {showDropdown && (
        <div className="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
          <div className="p-4 border-b flex justify-between items-center">
            <h3 className="font-semibold">Notifications</h3>
            {unreadCount > 0 && (
              <button
                onClick={markAllAsRead}
                className="text-sm text-blue-600 hover:text-blue-800"
              >
                Mark all read
              </button>
            )}
          </div>
          
          <div className="max-h-96 overflow-y-auto">
            {notifications.length === 0 ? (
              <p className="p-4 text-center text-gray-500">No notifications</p>
            ) : (
              notifications.map(notif => (
                <div
                  key={notif.id}
                  onClick={() => handleNotificationClick(notif)}
                  className={`p-4 border-b cursor-pointer hover:bg-gray-50 ${
                    !notif.read_at ? 'bg-blue-50' : ''
                  }`}
                >
                  <p className="font-medium text-sm">{notif.data.message}</p>
                  <p className="text-xs text-gray-500 mt-1">
                    {new Date(notif.created_at).toLocaleString()}
                  </p>
                </div>
              ))
            )}
          </div>
          
          <div className="p-2 border-t">
            <button
              onClick={() => {
                router.push('/dashboard/notifications');
                setShowDropdown(false);
              }}
              className="w-full text-center text-sm text-blue-600 hover:text-blue-800 py-2"
            >
              View All Notifications
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
```

#### 2. Notification Settings Page

```typescript
// app/settings/notifications/page.tsx
'use client';

import { useState, useEffect } from 'react';

export default function NotificationSettingsPage() {
  const [preferences, setPreferences] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPreferences();
  }, []);

  const fetchPreferences = async () => {
    const response = await fetch('/api/v1/notifications/preferences', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setPreferences(data.data);
    setLoading(false);
  };

  const updatePreference = async (index, field, value) => {
    const updated = [...preferences];
    updated[index][field] = value;
    setPreferences(updated);

    await fetch('/api/v1/notifications/preferences', {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ preferences: updated })
    });
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div className="max-w-4xl mx-auto p-6">
      <h1 className="text-2xl font-bold mb-6">Notification Settings</h1>
      
      <div className="space-y-6">
        {preferences.map((pref, index) => (
          <div key={pref.id} className="bg-white p-6 rounded-lg shadow">
            <h2 className="text-lg font-semibold mb-4 capitalize">
              {pref.notification_type} Notifications
            </h2>
            
            <div className="space-y-3">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={pref.channel_email}
                  onChange={(e) => updatePreference(index, 'channel_email', e.target.checked)}
                  className="mr-3"
                />
                <span>Email Notifications</span>
              </label>
              
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={pref.channel_database}
                  onChange={(e) => updatePreference(index, 'channel_database', e.target.checked)}
                  className="mr-3"
                />
                <span>In-App Notifications</span>
              </label>
              
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={pref.channel_sms}
                  onChange={(e) => updatePreference(index, 'channel_sms', e.target.checked)}
                  className="mr-3"
                  disabled
                />
                <span className="text-gray-400">SMS Notifications (Coming Soon)</span>
              </label>
              
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={pref.channel_push}
                  onChange={(e) => updatePreference(index, 'channel_push', e.target.checked)}
                  className="mr-3"
                />
                <span>Push Notifications</span>
              </label>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
```

#### 3. Custom Hook for Notifications

```typescript
// hooks/useNotifications.ts
import { useState, useEffect } from 'react';

export function useNotifications() {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(true);

  const fetchNotifications = async () => {
    const response = await fetch('/api/v1/notifications', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setNotifications(data.data.data);
    setLoading(false);
  };

  const fetchUnreadCount = async () => {
    const response = await fetch('/api/v1/notifications/unread-count', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    const data = await response.json();
    setUnreadCount(data.count);
  };

  const markAsRead = async (id: string) => {
    await fetch(`/api/v1/notifications/${id}/read`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    setUnreadCount(prev => Math.max(0, prev - 1));
    setNotifications(prev =>
      prev.map(notif =>
        notif.id === id ? { ...notif, read_at: new Date().toISOString() } : notif
      )
    );
  };

  const markAllAsRead = async () => {
    await fetch('/api/v1/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    setUnreadCount(0);
    fetchNotifications();
  };

  const deleteNotification = async (id: string) => {
    await fetch(`/api/v1/notifications/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    setNotifications(prev => prev.filter(notif => notif.id !== id));
  };

  useEffect(() => {
    fetchNotifications();
    fetchUnreadCount();
    
    // Poll every 30 seconds
    const interval = setInterval(() => {
      fetchUnreadCount();
    }, 30000);
    
    return () => clearInterval(interval);
  }, []);

  return {
    notifications,
    unreadCount,
    loading,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    refresh: fetchNotifications
  };
}
```

---

## 🧪 Testing

### Postman Collection

Import this collection to test all endpoints:

```json
{
  "info": {
    "name": "RentHub Notifications",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Notifications",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/notifications",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "notifications"]
        }
      }
    },
    {
      "name": "Get Unread Count",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/notifications/unread-count",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "notifications", "unread-count"]
        }
      }
    }
  ]
}
```

---

## 🚨 Error Handling

### Common Error Responses

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```
**Solution**: Check if token is valid and included in headers.

#### 404 Not Found
```json
{
  "success": false,
  "message": "Notification not found"
}
```
**Solution**: Verify notification ID exists and belongs to user.

#### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "preferences.0.notification_type": [
      "The notification type field is required."
    ]
  }
}
```
**Solution**: Check request body matches required format.

---

## 💡 Best Practices

### 1. Polling Frequency
- **Unread Count**: Poll every 30-60 seconds
- **Notification List**: Load on demand (when dropdown opens)
- **Don't**: Poll every second (too aggressive)

### 2. Caching
```typescript
// Cache unread count for 30 seconds
let cachedCount = null;
let cacheTime = null;

const getUnreadCount = async () => {
  if (cachedCount && Date.now() - cacheTime < 30000) {
    return cachedCount;
  }
  
  const response = await fetch('/api/v1/notifications/unread-count');
  const data = await response.json();
  
  cachedCount = data.count;
  cacheTime = Date.now();
  
  return cachedCount;
};
```

### 3. Optimistic Updates
```typescript
// Mark as read optimistically
const markAsRead = (id) => {
  // Update UI immediately
  setNotifications(prev =>
    prev.map(n => n.id === id ? {...n, read_at: new Date()} : n)
  );
  setUnreadCount(prev => prev - 1);
  
  // Send request in background
  fetch(`/api/v1/notifications/${id}/read`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token}` }
  }).catch(() => {
    // Revert on error
    fetchNotifications();
  });
};
```

### 4. Real-time Updates (Future)
```typescript
// With Laravel Echo & Pusher
import Echo from 'laravel-echo';

const echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.NEXT_PUBLIC_PUSHER_KEY,
  cluster: process.env.NEXT_PUBLIC_PUSHER_CLUSTER
});

echo.private(`user.${userId}`)
  .notification((notification) => {
    setUnreadCount(prev => prev + 1);
    setNotifications(prev => [notification, ...prev]);
  });
```

---

## 📞 Support

Need help? Check these resources:

- **Documentation**: [TASK_1.7_NOTIFICATIONS_COMPLETE.md](./TASK_1.7_NOTIFICATIONS_COMPLETE.md)
- **API Docs**: This file
- **GitHub Issues**: Report bugs
- **Email**: support@renthub.com

---

**Happy Coding! 🚀**

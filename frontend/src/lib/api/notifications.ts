import { apiClient } from './client';

export interface Notification {
  id: string;
  type: string;
  data: {
    title: string;
    message: string;
    action_url?: string;
    [key: string]: any;
  };
  read_at?: string;
  created_at: string;
}

export interface NotificationPreferences {
  email_enabled: boolean;
  push_enabled: boolean;
  sms_enabled: boolean;
  booking_notifications: boolean;
  message_notifications: boolean;
  review_notifications: boolean;
  payment_notifications: boolean;
  marketing_notifications: boolean;
}

export const notificationsApi = {
  // Get all notifications
  getAll: (params?: {
    page?: number;
    per_page?: number;
    unread_only?: boolean;
  }) => apiClient.get<{ data: Notification[] }>('/notifications', { params }),

  // Get unread count
  getUnreadCount: () => 
    apiClient.get<{ data: { count: number } }>('/notifications/unread-count'),

  // Mark notification as read
  markAsRead: (id: string) => 
    apiClient.post(`/notifications/${id}/read`),

  // Mark all as read
  markAllAsRead: () => 
    apiClient.post('/notifications/mark-all-read'),

  // Delete notification
  delete: (id: string) => 
    apiClient.delete(`/notifications/${id}`),

  // Get preferences
  getPreferences: () => 
    apiClient.get<{ data: NotificationPreferences }>('/notifications/preferences'),

  // Update preferences
  updatePreferences: (preferences: Partial<NotificationPreferences>) => 
    apiClient.put<{ data: NotificationPreferences }>('/notifications/preferences', preferences),

  // Test notification (dev only)
  sendTest: (type: string) => 
    apiClient.post('/notifications/test', { type }),
};

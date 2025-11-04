import { apiClient } from './client';

export interface Conversation {
  id: number;
  property_id?: number;
  participants: {
    id: number;
    name: string;
    avatar?: string;
  }[];
  last_message?: {
    content: string;
    created_at: string;
  };
  unread_count: number;
  is_archived: boolean;
  created_at: string;
  updated_at: string;
}

export interface Message {
  id: number;
  conversation_id: number;
  user_id: number;
  content: string;
  attachments?: string[];
  is_read: boolean;
  read_at?: string;
  user?: {
    id: number;
    name: string;
    avatar?: string;
  };
  created_at: string;
  updated_at: string;
}

export interface CreateConversationData {
  participant_id: number;
  property_id?: number;
  message: string;
}

export interface CreateMessageData {
  content: string;
  attachments?: File[];
}

export const conversationsApi = {
  // Get all conversations
  getAll: (params?: {
    archived?: boolean;
    page?: number;
    per_page?: number;
  }) => apiClient.get<{ data: Conversation[] }>('/conversations', { params }),

  // Get single conversation
  getById: (id: number) => 
    apiClient.get<{ data: Conversation }>(`/conversations/${id}`),

  // Create conversation
  create: (data: CreateConversationData) => 
    apiClient.post<{ data: Conversation }>('/conversations', data),

  // Archive conversation
  archive: (id: number) => 
    apiClient.patch(`/conversations/${id}/archive`),

  // Unarchive conversation
  unarchive: (id: number) => 
    apiClient.patch(`/conversations/${id}/unarchive`),

  // Delete conversation
  delete: (id: number) => 
    apiClient.delete(`/conversations/${id}`),

  // Mark all messages as read
  markAllAsRead: (id: number) => 
    apiClient.post(`/conversations/${id}/mark-all-read`),

  // Get messages in conversation
  getMessages: (conversationId: number, params?: {
    page?: number;
    per_page?: number;
  }) => apiClient.get<{ data: Message[] }>(`/conversations/${conversationId}/messages`, { params }),

  // Send message
  sendMessage: (conversationId: number, data: CreateMessageData) => 
    apiClient.post<{ data: Message }>(`/conversations/${conversationId}/messages`, data),

  // Update message
  updateMessage: (messageId: number, content: string) => 
    apiClient.patch<{ data: Message }>(`/messages/${messageId}`, { content }),

  // Delete message
  deleteMessage: (messageId: number) => 
    apiClient.delete(`/messages/${messageId}`),

  // Mark message as read
  markMessageAsRead: (messageId: number) => 
    apiClient.post(`/messages/${messageId}/read`),

  // Upload attachment
  uploadAttachment: (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return apiClient.post<{ data: { url: string } }>('/messages/upload-attachment', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
};

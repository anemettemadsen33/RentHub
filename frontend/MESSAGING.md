# Real-time Messaging System

## Features

### ✅ Implemented

1. **Chat Interface**
   - Conversation list with search
   - Real-time message updates
   - Message history
   - Online/offline status indicators
   - Typing indicators
   - Read receipts (✓ sent, ✓✓ read)

2. **File Sharing**
   - Upload images and documents
   - Multiple file attachments per message
   - File type icons
   - File size display
   - Download functionality
   - Supported formats: images, PDF, DOC, DOCX

3. **Push Notifications**
   - Browser notifications for new messages
   - Shows sender name and message preview
   - Click notification to open conversation
   - Auto-dismiss after 5 seconds
   - Only shows for inactive conversations

4. **Real-time Features (Socket.IO)**
   - Instant message delivery
   - Typing indicators
   - Read receipts
   - Online/offline presence
   - Automatic reconnection

5. **Booking Conversations**
   - Property-linked conversations
   - Display property title
   - Access via URL: `/messages?conversation=ID`

## Technical Stack

- **Frontend**: React 19, Next.js 15, TypeScript
- **UI**: shadcn/ui components
- **Real-time**: Socket.IO client
- **Notifications**: Browser Notification API

## Setup

### 1. Environment Variables

Add to `.env.local`:

```bash
NEXT_PUBLIC_WEBSOCKET_URL=http://localhost:6001
```

### 2. Backend Requirements

The backend should have a Socket.IO server running on port 6001 with the following events:

**Client → Server:**
- `send-message`: Send a new message
- `typing`: User is typing
- `message-read`: Mark message as read

**Server → Client:**
- `new-message`: Receive new message
- `user-typing`: User typing notification
- `message-read`: Message read confirmation
- `users-online`: List of online users

### 3. API Endpoints

Required endpoints:
- `GET /api/v1/conversations` - List conversations
- `GET /api/v1/conversations/:id/messages` - Get messages
- `POST /api/v1/conversations/:id/messages` - Send message (supports FormData for files)
- `POST /api/v1/conversations/:id/mark-all-read` - Mark all messages as read
- `POST /api/v1/messages/:id/mark-read` - Mark single message as read
- `POST /api/v1/conversations/:id/archive` - Archive conversation

## Usage

### Access Messages

```typescript
// Navigate to messages page
router.push('/messages');

// Open specific conversation
router.push('/messages?conversation=123');
```

### Send Message with Files

1. Click paperclip icon to select files
2. Type message (optional if sending files)
3. Press Enter or click Send button

### Enable Push Notifications

Permission is requested automatically on first visit. Users can:
- Accept for real-time notifications
- Deny to use without notifications
- Manage in browser settings later

## File Upload

**Accepted formats:**
- Images: `image/*`
- Documents: `.pdf`, `.doc`, `.docx`

**Features:**
- Multiple file selection
- Preview selected files before sending
- Remove files before sending
- File size display
- Download attachments

## Message Features

- **Text messages**: Markdown-style text
- **File attachments**: Images, documents
- **Read receipts**: ✓ (sent), ✓✓ (read)
- **Timestamps**: Relative time display
- **Online status**: Green dot indicator
- **Typing indicator**: "Typing..." text

## Keyboard Shortcuts

- `Enter`: Send message
- `Shift + Enter`: New line in message
- `Cmd/Ctrl + K`: Open command palette → Messages

## Notes

- WebSocket connection is established automatically
- Messages are loaded on conversation selection
- Unread count updates in real-time
- File uploads use multipart/form-data
- Notifications require user permission
- Works with mock data if backend is unavailable

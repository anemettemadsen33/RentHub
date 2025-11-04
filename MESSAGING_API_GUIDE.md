# Messaging System API Guide

## Overview
Complete API documentation for the messaging system between property owners and tenants.

---

## Base URL
```
http://localhost:8000/api/v1
```

All endpoints require authentication using Bearer token (Sanctum).

---

## Endpoints

### 1. Get Conversations List

**GET** `/conversations`

Get all conversations for the authenticated user.

**Query Parameters:**
- `is_archived` (boolean, optional) - Filter by archived status
- `per_page` (integer, optional, default: 20) - Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "property": {
        "id": 5,
        "title": "Modern Apartment",
        "address": "123 Main St",
        "featured_image": "https://..."
      },
      "other_user": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://..."
      },
      "subject": "Inquiry about Modern Apartment",
      "last_message": {
        "id": 15,
        "sender_id": 2,
        "message": "Is the property still available?",
        "created_at": "2024-11-02T10:30:00Z",
        "read_at": null
      },
      "last_message_at": "2024-11-02T10:30:00Z",
      "is_archived": false,
      "unread_count": 3,
      "created_at": "2024-11-01T08:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

---

### 2. Create New Conversation

**POST** `/conversations`

Start a new conversation about a property.

**Request Body:**
```json
{
  "property_id": 5,
  "booking_id": 10,
  "recipient_id": 2,
  "subject": "Question about availability",
  "message": "Hi, I'm interested in booking this property..."
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Conversation created successfully",
  "data": {
    "id": 1,
    "property_id": 5,
    "booking_id": 10,
    "tenant_id": 3,
    "owner_id": 2,
    "subject": "Question about availability",
    "last_message_at": "2024-11-02T12:00:00Z",
    "is_archived": false,
    "created_at": "2024-11-02T12:00:00Z"
  }
}
```

**Error Response (409 Conflict):**
```json
{
  "success": false,
  "message": "Conversation already exists",
  "data": {
    "conversation_id": 1
  }
}
```

---

### 3. Get Conversation Details

**GET** `/conversations/{id}`

Get detailed information about a specific conversation.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "property": {
      "id": 5,
      "title": "Modern Apartment",
      "address": "123 Main St",
      "featured_image": "https://..."
    },
    "booking": {
      "id": 10,
      "check_in": "2024-11-15",
      "check_out": "2024-11-20"
    },
    "tenant": {
      "id": 3,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "avatar": "https://..."
    },
    "owner": {
      "id": 2,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "https://..."
    },
    "other_user": {
      "id": 2,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar": "https://..."
    },
    "subject": "Question about availability",
    "last_message_at": "2024-11-02T12:00:00Z",
    "is_archived": false,
    "created_at": "2024-11-02T12:00:00Z"
  }
}
```

---

### 4. Get Messages in Conversation

**GET** `/conversations/{conversationId}/messages`

Get all messages in a conversation (paginated).

**Query Parameters:**
- `per_page` (integer, optional, default: 50) - Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "conversation_id": 1,
      "sender_id": 2,
      "sender": {
        "id": 2,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar": "https://..."
      },
      "message": "Yes, the property is available for those dates.",
      "attachments": [
        {
          "name": "floor_plan.pdf",
          "url": "https://...",
          "type": "application/pdf",
          "size": 245678
        }
      ],
      "read_at": "2024-11-02T12:30:00Z",
      "is_system_message": false,
      "created_at": "2024-11-02T12:00:00Z",
      "updated_at": "2024-11-02T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 50,
    "total": 87
  }
}
```

---

### 5. Send Message

**POST** `/conversations/{conversationId}/messages`

Send a new message in a conversation.

**Request Body (multipart/form-data):**
```
message: "Thank you for the information!"
attachments[0]: [File]
attachments[1]: [File]
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 16,
    "conversation_id": 1,
    "sender_id": 3,
    "sender": {
      "id": 3,
      "name": "Jane Smith",
      "email": "jane@example.com",
      "avatar": "https://..."
    },
    "message": "Thank you for the information!",
    "attachments": [
      {
        "name": "id_document.pdf",
        "url": "https://...",
        "path": "message-attachments/xxx.pdf",
        "type": "application/pdf",
        "size": 123456
      }
    ],
    "read_at": null,
    "is_system_message": false,
    "created_at": "2024-11-02T13:00:00Z"
  }
}
```

---

### 6. Update Message

**PATCH** `/messages/{id}`

Edit a message (only within 15 minutes of sending).

**Request Body:**
```json
{
  "message": "Updated message content"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Message updated successfully",
  "data": {
    "id": 16,
    "message": "Updated message content",
    "updated_at": "2024-11-02T13:05:00Z"
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Cannot edit message after 15 minutes"
}
```

---

### 7. Delete Message

**DELETE** `/messages/{id}`

Delete a message (soft delete).

**Response:**
```json
{
  "success": true,
  "message": "Message deleted successfully"
}
```

---

### 8. Mark Message as Read

**POST** `/messages/{id}/read`

Mark a specific message as read.

**Response:**
```json
{
  "success": true,
  "message": "Message marked as read"
}
```

---

### 9. Mark All Messages as Read

**POST** `/conversations/{id}/mark-all-read`

Mark all messages in a conversation as read.

**Response:**
```json
{
  "success": true,
  "message": "All messages marked as read"
}
```

---

### 10. Archive Conversation

**PATCH** `/conversations/{id}/archive`

Archive a conversation.

**Response:**
```json
{
  "success": true,
  "message": "Conversation archived successfully"
}
```

---

### 11. Unarchive Conversation

**PATCH** `/conversations/{id}/unarchive`

Unarchive a conversation.

**Response:**
```json
{
  "success": true,
  "message": "Conversation unarchived successfully"
}
```

---

### 12. Delete Conversation

**DELETE** `/conversations/{id}`

Delete a conversation and all its messages.

**Response:**
```json
{
  "success": true,
  "message": "Conversation deleted successfully"
}
```

---

### 13. Upload Attachment

**POST** `/messages/upload-attachment`

Upload a file attachment before sending a message.

**Request Body (multipart/form-data):**
```
file: [File]
```

**Response:**
```json
{
  "success": true,
  "data": {
    "name": "document.pdf",
    "url": "https://...",
    "path": "message-attachments/xxx.pdf",
    "type": "application/pdf",
    "size": 123456
  }
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "You don't have permission to access this conversation."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Conversation not found."
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "message": [
      "The message field is required."
    ]
  }
}
```

---

## Usage Examples

### Example 1: Start a conversation

```javascript
const response = await fetch('http://localhost:8000/api/v1/conversations', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    property_id: 5,
    recipient_id: 2,
    subject: 'Inquiry about Modern Apartment',
    message: 'Hi, I would like to know more about this property...'
  })
});

const data = await response.json();
console.log(data);
```

### Example 2: Send message with attachment

```javascript
const formData = new FormData();
formData.append('message', 'Here is the requested document');
formData.append('attachments[]', fileInput.files[0]);

const response = await fetch(`http://localhost:8000/api/v1/conversations/${conversationId}/messages`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
});

const data = await response.json();
console.log(data);
```

### Example 3: Get conversations with pagination

```javascript
const response = await fetch('http://localhost:8000/api/v1/conversations?per_page=10&page=1', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  }
});

const data = await response.json();
console.log(data.data); // Array of conversations
console.log(data.meta); // Pagination info
```

---

## Validation Rules

### Create Conversation
- `property_id`: required, must exist in properties table
- `booking_id`: optional, must exist in bookings table if provided
- `recipient_id`: required, must exist in users table
- `subject`: optional, string, max 255 characters
- `message`: required, string, max 5000 characters

### Send Message
- `message`: required, string, max 5000 characters
- `attachments`: optional, array, max 5 files
- `attachments.*`: file, max 10MB, allowed types: jpg, jpeg, png, pdf, doc, docx

### Update Message
- `message`: required, string, max 5000 characters
- Can only edit within 15 minutes of sending
- Can only edit own messages

---

## Security & Permissions

### Authorization
- Users can only view conversations they are part of (tenant or owner)
- Users can only send messages in conversations they participate in
- Users can only edit/delete their own messages
- Admins have full access to all conversations

### Permissions Required
- `send_messages`: Required to create conversations and send messages
- Available for: tenant, owner, admin roles

---

## Next Steps

1. **Real-time Features**: Implement Laravel Echo + Pusher for live updates
2. **Notifications**: Integrate with notification system for new messages
3. **Auto-messages**: System messages for booking updates
4. **Message Templates**: Pre-defined messages for owners
5. **File Management**: Better file handling and previews

---

## Testing

Use Postman collection or test with:

```bash
# Get conversations
curl -X GET http://localhost:8000/api/v1/conversations \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create conversation
curl -X POST http://localhost:8000/api/v1/conversations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"property_id": 1, "recipient_id": 2, "message": "Hello!"}'
```

---

## Support

For issues or questions, please contact the development team.

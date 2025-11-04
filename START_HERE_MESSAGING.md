# 🚀 Quick Start - Messaging System

## ✅ Task 2.1 Implementation Complete!

The messaging system is **fully functional** and ready to use!

---

## 📋 What's Ready

✅ **Backend API** - 13 endpoints  
✅ **Database** - 3 tables (conversations, messages, conversation_participants)  
✅ **Models** - Conversation, Message with full relationships  
✅ **Security** - Policies and validation  
✅ **Admin Panel** - Filament resources for moderation  
✅ **Documentation** - Complete API guide  

---

## 🧪 Quick Test (5 minutes)

### Prerequisites
```bash
# Make sure backend is running
cd backend
php artisan serve
```

### Step 1: Get Authentication Token

Login with existing user or register:

```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'

# Save the token from response
TOKEN="your_token_here"
```

### Step 2: Create a Conversation

```bash
curl -X POST http://localhost:8000/api/v1/conversations \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "recipient_id": 2,
    "message": "Hello! I am interested in your property."
  }'

# Response will include conversation_id
```

### Step 3: Get Conversations

```bash
curl -X GET http://localhost:8000/api/v1/conversations \
  -H "Authorization: Bearer $TOKEN"
```

### Step 4: Send a Message

```bash
curl -X POST http://localhost:8000/api/v1/conversations/1/messages \
  -H "Authorization: Bearer $TOKEN" \
  -F "message=When is the property available?"
```

### Step 5: Get Messages

```bash
curl -X GET http://localhost:8000/api/v1/conversations/1/messages \
  -H "Authorization: Bearer $TOKEN"
```

### Step 6: Send Message with Attachment

```bash
curl -X POST http://localhost:8000/api/v1/conversations/1/messages \
  -H "Authorization: Bearer $TOKEN" \
  -F "message=Here is my ID document" \
  -F "attachments[]=@/path/to/document.pdf"
```

---

## 📱 Test with Postman

### Import Collection

1. Open Postman
2. Import > Raw Text
3. Paste this:

```json
{
  "info": {
    "name": "RentHub - Messaging System",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get Conversations",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/conversations"
      }
    },
    {
      "name": "Create Conversation",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          },
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"property_id\": 1,\n  \"recipient_id\": 2,\n  \"message\": \"Hello!\"\n}"
        },
        "url": "{{base_url}}/conversations"
      }
    },
    {
      "name": "Get Messages",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "url": "{{base_url}}/conversations/1/messages"
      }
    },
    {
      "name": "Send Message",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {
              "key": "message",
              "value": "Test message",
              "type": "text"
            }
          ]
        },
        "url": "{{base_url}}/conversations/1/messages"
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1"
    },
    {
      "key": "token",
      "value": "your_token_here"
    }
  ]
}
```

4. Set variables:
   - `base_url`: http://localhost:8000/api/v1
   - `token`: Your auth token

---

## 🎨 Access Admin Panel

### View Conversations in Filament

1. Open browser: `http://localhost:8000/admin`
2. Login with admin credentials
3. Navigate to **Conversations** menu
4. See all conversations between users
5. View messages inline
6. Archive or delete conversations
7. Navigate to **Messages** for moderation

### Admin Features
- ✅ View all conversations
- ✅ Filter by property, user, status
- ✅ View messages inline
- ✅ Delete inappropriate messages
- ✅ Archive old conversations
- ✅ Monitor system messages

---

## 📊 Database Check

### Verify Tables Created

```bash
cd backend
php artisan db:show

# Or check specific tables
php artisan tinker
>>> DB::table('conversations')->count()
>>> DB::table('messages')->count()
>>> DB::table('conversation_participants')->count()
```

### Create Test Data

```bash
php artisan tinker
```

```php
// Create a conversation
$conversation = \App\Models\Conversation::create([
    'property_id' => 1,
    'tenant_id' => 3,
    'owner_id' => 2,
    'subject' => 'Test Conversation',
    'last_message_at' => now(),
]);

// Add participants
$conversation->participants()->attach([
    3 => ['last_read_at' => now()],
    2 => ['last_read_at' => null],
]);

// Create a message
$message = $conversation->messages()->create([
    'sender_id' => 3,
    'message' => 'This is a test message',
]);

// Verify
echo "Conversation ID: " . $conversation->id;
echo "\nMessage ID: " . $message->id;
```

---

## 🔍 Check Logs

### API Request Logs

```bash
cd backend
tail -f storage/logs/laravel.log
```

### Error Debugging

If something doesn't work:

1. Check Laravel logs: `backend/storage/logs/laravel.log`
2. Check database: `php artisan db:show`
3. Check routes: `php artisan route:list --path=conversations`
4. Test authentication: `curl http://localhost:8000/api/v1/me -H "Authorization: Bearer $TOKEN"`

---

## 📚 Documentation Files

### Quick Reference
- **API Guide**: `MESSAGING_API_GUIDE.md` - All endpoints with examples
- **Summary**: `TASK_2.1_SUMMARY.md` - Quick overview
- **Complete Report**: `TASK_2.1_COMPLETE.md` - Full implementation details

### Implementation Plan
- **Original Plan**: `TASK_2.1_MESSAGING_SYSTEM_PLAN.md` - Step-by-step guide

---

## 🎯 Common Use Cases

### Tenant inquires about a property
```javascript
// 1. Tenant browses properties
// 2. Clicks "Contact Owner" on property page
// 3. Writes message in modal/form
// 4. POST /conversations with property_id, recipient_id, message
// 5. Redirect to conversation page
```

### Owner responds to tenant
```javascript
// 1. Owner receives notification of new message
// 2. Opens "Messages" section
// 3. Clicks on conversation
// 4. Writes reply
// 5. POST /conversations/{id}/messages
```

### View conversation history
```javascript
// 1. User opens "Messages" page
// 2. GET /conversations - List all conversations
// 3. Display conversations with last message preview
// 4. Click on conversation
// 5. GET /conversations/{id}/messages - Load all messages
// 6. POST /conversations/{id}/mark-all-read - Mark as read
```

---

## 🚀 Next Steps

### For Backend Developer
✅ **Done!** Backend is complete and tested

### For Frontend Developer
1. **Create UI Components**:
   - ConversationList component
   - ChatWindow component
   - MessageBubble component
   - MessageInput with file upload
   - AttachmentPreview component

2. **Create Pages**:
   - `/messages` - Conversation list
   - `/messages/[id]` - Chat view

3. **Integrate API**:
   - Use provided endpoints
   - Handle pagination
   - Show unread counts
   - Display attachments

4. **Add Real-time** (optional):
   - Laravel Echo + Pusher
   - Live message updates
   - Typing indicators

### Example Frontend Integration

```typescript
// app/messages/page.tsx
import { messageService } from '@/services/messageService';

export default async function MessagesPage() {
  const conversations = await messageService.getConversations();
  
  return (
    <div className="grid grid-cols-3 gap-4">
      <ConversationList conversations={conversations.data} />
      <ChatWindow />
    </div>
  );
}
```

---

## ✅ Verification Checklist

Before moving to frontend:

- [ ] All 13 API endpoints return correct responses
- [ ] Can create conversations
- [ ] Can send messages
- [ ] Can upload attachments
- [ ] Can mark messages as read
- [ ] Can archive conversations
- [ ] Unread count works
- [ ] Authorization works (users can't access other's conversations)
- [ ] Admin panel shows conversations
- [ ] File uploads work correctly

---

## 🆘 Troubleshooting

### "Unauthenticated" error
- Make sure to include `Authorization: Bearer {token}` header
- Token might be expired, login again
- Check if user exists in database

### "Conversation already exists"
- This is expected behavior to prevent duplicates
- Use the returned conversation_id to continue messaging
- Or check existing conversations first

### "Property not found"
- Verify property_id exists in properties table
- Use `php artisan db:seed` to create sample data

### File upload fails
- Check file size (max 10MB)
- Verify file type (jpg, png, pdf, doc, docx)
- Check storage permissions: `php artisan storage:link`

---

## 🎉 Success!

Your messaging system is now **fully operational**!

**What you have:**
- ✅ Complete backend API
- ✅ Database with relationships
- ✅ Security & authorization
- ✅ Admin moderation panel
- ✅ File attachment support
- ✅ Read/unread tracking
- ✅ Comprehensive documentation

**Next:** Build the frontend UI and enjoy chatting! 💬

---

For more details, see `MESSAGING_API_GUIDE.md` or `TASK_2.1_COMPLETE.md`

**Happy coding! 🚀**

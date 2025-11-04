# Task 2.1 - Messaging System Implementation Plan

## 📋 Overview
Sistem complet de mesagerie între proprietari și chiriași pentru comunicare în timpul procesului de booking și după.

---

## 🎯 Obiective

### Backend (Laravel + Filament v4)
1. **Database & Models**
2. **Filament Resources**
3. **API Endpoints**
4. **Real-time Features**
5. **Notifications Integration**

### Frontend (Next.js)
1. **Chat Interface**
2. **Message List**
3. **Real-time Updates**
4. **File Attachments**
5. **Notifications**

---

## 📊 Step 1: Database Structure

### 1.1 Create Conversations Table
```bash
php artisan make:migration create_conversations_table
```

**Schema:**
```php
Schema::create('conversations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('property_id')->constrained()->cascadeOnDelete();
    $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
    $table->string('subject')->nullable();
    $table->timestamp('last_message_at')->nullable();
    $table->boolean('is_archived')->default(false);
    $table->timestamps();
    
    $table->index(['tenant_id', 'owner_id']);
    $table->index('last_message_at');
});
```

### 1.2 Create Messages Table
```bash
php artisan make:migration create_messages_table
```

**Schema:**
```php
Schema::create('messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
    $table->text('message');
    $table->json('attachments')->nullable(); // [{name, url, type, size}]
    $table->timestamp('read_at')->nullable();
    $table->boolean('is_system_message')->default(false); // Pentru mesaje automate
    $table->timestamps();
    $table->softDeletes();
    
    $table->index('conversation_id');
    $table->index('sender_id');
    $table->index('read_at');
});
```

### 1.3 Create Message Participants Table (pentru group chats - viitor)
```bash
php artisan make:migration create_conversation_participants_table
```

**Schema:**
```php
Schema::create('conversation_participants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('last_read_at')->nullable();
    $table->boolean('is_muted')->default(false);
    $table->timestamps();
    
    $table->unique(['conversation_id', 'user_id']);
});
```

---

## 📦 Step 2: Create Models

### 2.1 Conversation Model
```bash
php artisan make:model Conversation
```

**Location:** `backend/app/Models/Conversation.php`

**Content:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    protected $fillable = [
        'property_id',
        'booking_id',
        'tenant_id',
        'owner_id',
        'subject',
        'last_message_at',
        'is_archived',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'is_muted'])
            ->withTimestamps();
    }

    // Helper Methods
    public function unreadCount(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        
        if (!$participant) {
            return 0;
        }

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where(function ($query) use ($participant) {
                $query->where('created_at', '>', $participant->pivot->last_read_at)
                    ->orWhereNull($participant->pivot->last_read_at);
            })
            ->count();
    }

    public function markAsRead(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    public function getOtherParticipant(User $user): User
    {
        return $user->id === $this->tenant_id ? $this->owner : $this->tenant;
    }
}
```

### 2.2 Message Model
```bash
php artisan make:model Message
```

**Location:** `backend/app/Models/Message.php`

**Content:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message',
        'attachments',
        'read_at',
        'is_system_message',
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime',
        'is_system_message' => 'boolean',
    ];

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Helper Methods
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function hasAttachments(): bool
    {
        return !empty($this->attachments);
    }
}
```

---

## 🔧 Step 3: Update Existing Models

### 3.1 Update User Model
```php
// Add to app/Models/User.php

public function sentMessages(): HasMany
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function conversations(): BelongsToMany
{
    return $this->belongsToMany(Conversation::class, 'conversation_participants')
        ->withPivot(['last_read_at', 'is_muted'])
        ->withTimestamps()
        ->orderBy('last_message_at', 'desc');
}

public function tenantConversations(): HasMany
{
    return $this->hasMany(Conversation::class, 'tenant_id');
}

public function ownerConversations(): HasMany
{
    return $this->hasMany(Conversation::class, 'owner_id');
}
```

---

## 🎨 Step 4: Filament Resources

### 4.1 Create Conversation Resource
```bash
php artisan make:filament-resource Conversation --generate
```

**Location:** `backend/app/Filament/Resources/ConversationResource.php`

**Key Features:**
- View all conversations
- Filter by property, user, archived status
- Search by subject, property name, user name
- Archive/Unarchive conversations
- View messages inline
- Send system messages

### 4.2 Create Message Resource (Optional - pentru moderare)
```bash
php artisan make:filament-resource Message --generate
```

---

## 🚀 Step 5: API Controllers

### 5.1 Create Conversation Controller
```bash
php artisan make:controller Api/ConversationController --api
```

**Endpoints:**
- `GET /api/conversations` - List user's conversations
- `GET /api/conversations/{id}` - Get conversation details
- `POST /api/conversations` - Create new conversation
- `PATCH /api/conversations/{id}/archive` - Archive conversation
- `PATCH /api/conversations/{id}/unarchive` - Unarchive conversation
- `DELETE /api/conversations/{id}` - Delete conversation

### 5.2 Create Message Controller
```bash
php artisan make:controller Api/MessageController --api
```

**Endpoints:**
- `GET /api/conversations/{conversation}/messages` - Get messages (paginated)
- `POST /api/conversations/{conversation}/messages` - Send message
- `PATCH /api/messages/{id}` - Edit message
- `DELETE /api/messages/{id}` - Delete message
- `POST /api/messages/{id}/read` - Mark as read
- `POST /api/conversations/{conversation}/mark-all-read` - Mark all as read

---

## 🔔 Step 6: Events & Notifications

### 6.1 Create Events
```bash
php artisan make:event MessageSent
php artisan make:event MessageRead
php artisan make:event ConversationCreated
```

### 6.2 Create Notifications
```bash
php artisan make:notification NewMessageNotification
php artisan make:notification MessageReadNotification
```

---

## 📡 Step 7: Real-time Features (Laravel Echo + Pusher/Soketi)

### 7.1 Install Dependencies
```bash
composer require pusher/pusher-php-server
npm install --save laravel-echo pusher-js
```

### 7.2 Configure Broadcasting
```bash
php artisan make:channel ConversationChannel
```

### 7.3 Broadcast Events
- MessageSent → broadcast to conversation participants
- MessageRead → broadcast to sender
- UserTyping → broadcast typing indicator

---

## 🎨 Step 8: Frontend (Next.js)

### 8.1 Create Pages & Components

**Pages:**
- `/messages` - Messages list
- `/messages/[conversationId]` - Chat view

**Components:**
- `ConversationList.tsx` - Lista conversații
- `ConversationItem.tsx` - Item conversație
- `ChatWindow.tsx` - Fereastra de chat
- `MessageBubble.tsx` - Mesaj individual
- `MessageInput.tsx` - Input pentru mesaje
- `FileUpload.tsx` - Upload fișiere
- `TypingIndicator.tsx` - Indicator typing

### 8.2 API Integration
```typescript
// services/messageService.ts

export const messageService = {
  getConversations: async () => {},
  getConversation: async (id: string) => {},
  getMessages: async (conversationId: string, page: number) => {},
  sendMessage: async (conversationId: string, data: MessageData) => {},
  markAsRead: async (messageId: string) => {},
  uploadAttachment: async (file: File) => {},
  deleteMessage: async (messageId: string) => {},
};
```

### 8.3 Real-time Integration
```typescript
// hooks/useConversation.ts
import Echo from 'laravel-echo';

export const useConversation = (conversationId: string) => {
  useEffect(() => {
    const channel = Echo.private(`conversation.${conversationId}`)
      .listen('MessageSent', (e) => {
        // Add new message
      })
      .listen('MessageRead', (e) => {
        // Update read status
      })
      .listenForWhisper('typing', (e) => {
        // Show typing indicator
      });

    return () => channel.stopListening();
  }, [conversationId]);
};
```

---

## ✅ Step 9: Testing

### 9.1 Backend Tests
```bash
php artisan make:test ConversationTest
php artisan make:test MessageTest
```

**Test Cases:**
- Create conversation
- Send message
- Mark as read
- Archive conversation
- Delete message
- Real-time events

### 9.2 Frontend Tests
```bash
npm run test
```

**Test Cases:**
- Render conversation list
- Send message
- Receive message
- File upload
- Typing indicator

---

## 🔒 Step 10: Security & Validation

### 10.1 Policies
```bash
php artisan make:policy ConversationPolicy --model=Conversation
php artisan make:policy MessagePolicy --model=Message
```

**Rules:**
- Users can only view their own conversations
- Only conversation participants can send messages
- Users can only delete their own messages
- Owners can moderate messages in their properties

### 10.2 Validation Rules
```php
// SendMessageRequest
'message' => 'required|string|max:5000',
'attachments' => 'nullable|array|max:5',
'attachments.*' => 'file|max:10240|mimes:jpg,png,pdf,doc,docx',
```

---

## 📝 Step 11: Additional Features

### 11.1 Message Templates (pentru owners)
- Welcome message
- Check-in instructions
- House rules reminder

### 11.2 Auto-messages
- Booking confirmed
- Payment received
- Check-in reminder (24h before)
- Check-out reminder
- Review reminder

### 11.3 Message Filtering
- Profanity filter
- Spam detection
- Contact info detection (email, phone)

---

## 🎯 Implementation Order

1. ✅ **Database & Models** (30 min)
2. ✅ **Basic API Endpoints** (1h)
3. ✅ **Filament Resources** (30 min)
4. ✅ **Frontend Components** (2h)
5. ✅ **Real-time Features** (1h)
6. ✅ **Notifications Integration** (30 min)
7. ✅ **File Attachments** (1h)
8. ✅ **Testing** (1h)
9. ✅ **Security & Policies** (30 min)
10. ✅ **Auto-messages** (1h)

**Total Estimated Time: 8-10 hours**

---

## 📚 Dependencies

- Laravel Broadcasting
- Pusher/Soketi
- Laravel Echo (frontend)
- React Query (pentru caching)
- Zustand (pentru state management)

---

## 🚀 Ready to Start?

Să începem cu **Step 1: Database Structure**?

```bash
cd backend
php artisan make:migration create_conversations_table
php artisan make:migration create_messages_table
php artisan make:migration create_conversation_participants_table
```

Apoi vom continua cu models, controllers și API endpoints!

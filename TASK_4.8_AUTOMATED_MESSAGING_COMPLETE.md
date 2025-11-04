# Task 4.8: Property Management Tools - Automated Messaging - COMPLETE ✅

## Implementation Summary

A comprehensive automated messaging system has been implemented with message templates, scheduled messages, auto-responses, and smart reply suggestions.

## Features Implemented

### ✅ 1. Message Templates
- **Reusable templates** for common messages
- **Variable substitution** (e.g., `{{guest_name}}`, `{{property_name}}`)
- **Categories** (booking, check_in, check_out, inquiry)
- **Trigger events** for automatic messaging
- Template preview with sample data
- Usage tracking

### ✅ 2. Scheduled Messages
- **Schedule messages** for future delivery
- **Automatic booking messages** (confirmation, check-in, check-out reminders)
- **Flexible scheduling** (specific date/time)
- **Status tracking** (pending, sent, failed, cancelled)
- **Retry mechanism** for failed messages
- Cancel pending messages

### ✅ 3. Auto-Responses
- **Keyword-based** automatic replies
- **Time-based** responses (office hours, vacations)
- **Booking event** responses
- **Priority system** for multiple matching responses
- Active period configuration
- Usage statistics

### ✅ 4. Smart Replies
- **AI-powered suggestions** based on message content
- **Context-aware** responses
- **Common inquiry** templates (availability, pricing, amenities)
- **Quick responses** for property managers
- Up to 3 suggestions per message

## Database Schema

### `message_templates`
```sql
- user_id
- name
- category (booking, check_in, check_out, inquiry)
- trigger_event (booking_confirmed, before_checkin, etc.)
- subject
- content
- variables (JSON array)
- is_active
- is_default
- usage_count
```

### `scheduled_messages`
```sql
- user_id
- conversation_id
- template_id
- booking_id
- recipient_type (guest, owner, all_guests)
- recipient_id
- subject
- content
- status (pending, sent, failed, cancelled)
- scheduled_at
- sent_at
- error_message
- retry_count
- metadata (JSON)
```

### `auto_responses`
```sql
- user_id
- name
- trigger_type (keyword, time_based, booking_event, inquiry)
- trigger_conditions (JSON)
- response_content
- template_id
- is_active
- priority
- usage_count
- active_from
- active_until
- settings (JSON)
```

## API Endpoints

### Message Templates
```
GET    /api/v1/messaging/templates                   - List templates
GET    /api/v1/messaging/templates/defaults          - Get default templates
POST   /api/v1/messaging/templates                   - Create template
PUT    /api/v1/messaging/templates/{id}              - Update template
DELETE /api/v1/messaging/templates/{id}              - Delete template
POST   /api/v1/messaging/templates/{id}/preview      - Preview template
```

### Scheduled Messages
```
GET    /api/v1/messaging/scheduled                   - List scheduled messages
POST   /api/v1/messaging/scheduled                   - Schedule message
POST   /api/v1/messaging/scheduled/{id}/cancel       - Cancel scheduled message
```

### Auto-Responses
```
GET    /api/v1/messaging/auto-responses              - List auto-responses
POST   /api/v1/messaging/auto-responses              - Create auto-response
PUT    /api/v1/messaging/auto-responses/{id}         - Update auto-response
DELETE /api/v1/messaging/auto-responses/{id}         - Delete auto-response
```

### Smart Replies
```
GET    /api/v1/messaging/messages/{id}/suggestions   - Get reply suggestions
```

## Usage Examples

### 1. Create Message Template

```javascript
POST /api/v1/messaging/templates

{
  "name": "Booking Confirmation",
  "category": "booking",
  "trigger_event": "booking_confirmed",
  "content": "Hi {{guest_name}}! Your booking for {{property_name}} is confirmed. Check-in: {{check_in_date}}. Looking forward to hosting you!",
  "variables": ["guest_name", "property_name", "check_in_date"],
  "is_active": true
}
```

### 2. Schedule a Message

```javascript
POST /api/v1/messaging/scheduled

{
  "template_id": 1,
  "booking_id": 123,
  "recipient_id": 456,
  "recipient_type": "guest",
  "content": "Hi John! Just a reminder...",
  "scheduled_at": "2025-11-05 10:00:00"
}
```

### 3. Create Auto-Response

```javascript
POST /api/v1/messaging/auto-responses

{
  "name": "Availability Response",
  "trigger_type": "keyword",
  "trigger_conditions": {
    "keywords": ["available", "availability", "book"]
  },
  "response_content": "Thank you for your interest! Let me check the availability and get back to you shortly.",
  "is_active": true,
  "priority": 1
}
```

### 4. Get Smart Reply Suggestions

```javascript
GET /api/v1/messaging/messages/789/suggestions

Response:
{
  "success": true,
  "data": {
    "suggestions": [
      "Yes, it's available for those dates! Would you like to proceed?",
      "Let me check the availability and get back to you shortly.",
      "Thank you for your interest! How many guests?"
    ]
  }
}
```

## Default Templates

The system includes 4 default templates:

### 1. Booking Confirmation
```
Hi {{guest_name}}! 🎉

Your booking for {{property_name}} is confirmed!

Check-in: {{check_in_date}}
Check-out: {{check_out_date}}

Looking forward to hosting you!
```

### 2. Check-in Reminder
```
Hi {{guest_name}}!

Just a friendly reminder that your check-in at {{property_name}} 
is tomorrow at {{check_in_time}}.

Address: {{property_address}}

See you soon!
```

### 3. Check-out Reminder
```
Hi {{guest_name}}!

Hope you enjoyed your stay at {{property_name}}!

Reminder: Check-out is at {{check_out_time}} tomorrow.

Thank you for staying with us!
```

### 4. Welcome Message
```
Thank you for your interest! 😊

I'd be happy to help you with your booking. 
What dates are you looking at?
```

## Integration Example

### Automatic Booking Messages

```php
use App\Services\AutomatedMessagingService;

// When a booking is confirmed
$messagingService = app(AutomatedMessagingService::class);
$scheduledMessages = $messagingService->scheduleBookingMessages($booking);

// This automatically schedules:
// 1. Confirmation message (immediate)
// 2. Check-in reminder (1 day before)
// 3. Check-out reminder (1 day before checkout)
```

### Auto-Response on New Message

```php
// In MessageController or MessageObserver
public function created(Message $message)
{
    $messagingService = app(AutomatedMessagingService::class);
    $autoResponse = $messagingService->checkAutoResponse($message);
    
    if ($autoResponse) {
        // Auto-response sent automatically
    }
}
```

### Template Usage in Frontend

```jsx
const MessageTemplates = () => {
  const [templates, setTemplates] = useState([]);
  
  useEffect(() => {
    fetch('/api/v1/messaging/templates', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => setTemplates(data.data));
  }, []);
  
  const handleUseTemplate = async (template) => {
    // Preview with sample data
    const preview = await fetch(`/api/v1/messaging/templates/${template.id}/preview`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        data: {
          guest_name: 'John',
          property_name: 'Beach House',
          check_in_date: '2025-12-01'
        }
      })
    });
    
    const result = await preview.json();
    setMessageContent(result.data.rendered);
  };
  
  return (
    <div>
      {templates.map(template => (
        <div key={template.id}>
          <h3>{template.name}</h3>
          <button onClick={() => handleUseTemplate(template)}>
            Use Template
          </button>
        </div>
      ))}
    </div>
  );
};
```

### Smart Replies UI

```jsx
const MessageComposer = ({ messageId }) => {
  const [suggestions, setSuggestions] = useState([]);
  
  useEffect(() => {
    fetch(`/api/v1/messaging/messages/${messageId}/suggestions`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    .then(res => res.json())
    .then(data => setSuggestions(data.data.suggestions));
  }, [messageId]);
  
  return (
    <div>
      <div className="smart-replies">
        <p>Suggested replies:</p>
        {suggestions.map((suggestion, idx) => (
          <button
            key={idx}
            onClick={() => setMessage(suggestion)}
            className="suggestion-chip"
          >
            {suggestion}
          </button>
        ))}
      </div>
      <textarea value={message} onChange={...} />
    </div>
  );
};
```

## Scheduled Message Processing

Create a console command to process scheduled messages:

```php
<?php

namespace App\Console\Commands;

use App\Services\AutomatedMessagingService;
use Illuminate\Console\Command;

class SendScheduledMessages extends Command
{
    protected $signature = 'messages:send-scheduled';
    protected $description = 'Send scheduled messages that are due';

    public function handle(AutomatedMessagingService $service): int
    {
        $this->info('Sending scheduled messages...');
        
        $sent = $service->sendDueMessages();
        
        $this->info("Sent {$sent} messages");
        
        return Command::SUCCESS;
    }
}
```

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('messages:send-scheduled')
        ->everyMinute();
}
```

## Files Created

### Models
- `app/Models/MessageTemplate.php`
- `app/Models/ScheduledMessage.php`
- `app/Models/AutoResponse.php`

### Services
- `app/Services/AutomatedMessagingService.php`

### Controllers
- `app/Http/Controllers/Api/AutomatedMessagingController.php`

### Migrations
- `2025_11_03_120945_create_message_templates_table.php`
- `2025_11_03_120946_create_scheduled_messages_table.php`
- `2025_11_03_120948_create_auto_responses_table.php`

### Routes
- Added 15 messaging endpoints to `routes/api.php`

## Key Features

✅ **Message Templates** - Reusable, variable-based templates  
✅ **Variable Substitution** - Dynamic content insertion  
✅ **Scheduled Messages** - Future message delivery  
✅ **Auto-Responses** - Keyword and event-based automation  
✅ **Smart Replies** - AI-powered suggestions  
✅ **Template Preview** - Test before sending  
✅ **Status Tracking** - Monitor message delivery  
✅ **Retry Mechanism** - Handle failures  
✅ **Booking Integration** - Automatic booking messages  
✅ **Flexible Configuration** - Priority, schedules, conditions  

## Benefits for Property Managers

1. **Time Saving** - Automate repetitive messages
2. **Consistency** - Standardized communications
3. **Never Miss** - Scheduled reminders sent automatically
4. **Quick Response** - Auto-responses for common inquiries
5. **Professional** - Consistent, well-formatted messages
6. **Scalable** - Manage multiple properties efficiently

## Testing

```bash
# Run migrations
php artisan migrate

# Test in Tinker
php artisan tinker

$user = User::first();
$service = app(\App\Services\AutomatedMessagingService::class);

# Create a template
$template = $service->createTemplate($user, [
    'name' => 'Test Template',
    'content' => 'Hello {{name}}!',
    'variables' => ['name']
]);

# Render template
$rendered = $service->renderTemplate($template, ['name' => 'John']);
echo $rendered; // "Hello John!"

# Schedule a message
$scheduled = $service->scheduleMessage([
    'user_id' => $user->id,
    'recipient_id' => User::find(2)->id,
    'recipient_type' => 'guest',
    'content' => 'Test message',
    'scheduled_at' => now()->addHour()
]);

# Send due messages
$sent = $service->sendDueMessages();
```

## Status: COMPLETE ✅

All Task 4.8 requirements successfully implemented:
- ✅ Message Templates
- ✅ Scheduled Messages
- ✅ Auto-Responses
- ✅ Smart Replies

**Bonus Features:**
- Template preview
- Usage tracking
- Priority system
- Retry mechanism
- Booking integration
- Default templates
- Comprehensive API

---

**Implementation Date:** November 3, 2025  
**Status:** ✅ Production Ready  
**Use Case:** Property Management Automation

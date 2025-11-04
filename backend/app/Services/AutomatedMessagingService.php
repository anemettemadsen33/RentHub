<?php

namespace App\Services;

use App\Models\AutoResponse;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\ScheduledMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AutomatedMessagingService
{
    /**
     * Create a message template
     */
    public function createTemplate(User $user, array $data): MessageTemplate
    {
        return MessageTemplate::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'trigger_event' => $data['trigger_event'] ?? null,
            'subject' => $data['subject'] ?? null,
            'content' => $data['content'],
            'variables' => $data['variables'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Render template with data
     */
    public function renderTemplate(MessageTemplate $template, array $data): string
    {
        return $template->render($data);
    }

    /**
     * Schedule a message
     */
    public function scheduleMessage(array $data): ScheduledMessage
    {
        $scheduledMessage = ScheduledMessage::create([
            'user_id' => $data['user_id'],
            'conversation_id' => $data['conversation_id'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'booking_id' => $data['booking_id'] ?? null,
            'recipient_type' => $data['recipient_type'] ?? 'guest',
            'recipient_id' => $data['recipient_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'content' => $data['content'],
            'scheduled_at' => $data['scheduled_at'],
            'metadata' => $data['metadata'] ?? [],
        ]);

        return $scheduledMessage;
    }

    /**
     * Send scheduled messages that are due
     */
    public function sendDueMessages(): int
    {
        $dueMessages = ScheduledMessage::due()->get();
        $sent = 0;

        foreach ($dueMessages as $message) {
            try {
                $this->sendScheduledMessage($message);
                $sent++;
            } catch (\Exception $e) {
                $message->increment('retry_count');
                $message->markAsFailed($e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * Send a single scheduled message
     */
    protected function sendScheduledMessage(ScheduledMessage $scheduledMessage): void
    {
        DB::transaction(function () use ($scheduledMessage) {
            // Get or create conversation
            $conversation = $this->getOrCreateConversation($scheduledMessage);

            // Send message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $scheduledMessage->user_id,
                'message' => $scheduledMessage->content,
                'is_system_message' => true,
            ]);

            $scheduledMessage->markAsSent();

            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
            ]);
        });
    }

    /**
     * Get or create conversation for scheduled message
     */
    protected function getOrCreateConversation(ScheduledMessage $scheduledMessage): Conversation
    {
        if ($scheduledMessage->conversation_id) {
            return $scheduledMessage->conversation;
        }

        // Create new conversation
        if ($scheduledMessage->booking_id) {
            $booking = $scheduledMessage->booking;

            return Conversation::firstOrCreate([
                'booking_id' => $booking->id,
                'owner_id' => $booking->property->user_id,
                'tenant_id' => $booking->user_id,
            ]);
        }

        // Create general conversation
        return Conversation::create([
            'owner_id' => $scheduledMessage->user_id,
            'tenant_id' => $scheduledMessage->recipient_id,
        ]);
    }

    /**
     * Create auto-response
     */
    public function createAutoResponse(User $user, array $data): AutoResponse
    {
        return AutoResponse::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'trigger_type' => $data['trigger_type'],
            'trigger_conditions' => $data['trigger_conditions'],
            'response_content' => $data['response_content'],
            'template_id' => $data['template_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'priority' => $data['priority'] ?? 0,
            'active_from' => $data['active_from'] ?? null,
            'active_until' => $data['active_until'] ?? null,
            'settings' => $data['settings'] ?? [],
        ]);
    }

    /**
     * Check and send auto-response for message
     */
    public function checkAutoResponse(Message $message): ?Message
    {
        // Don't auto-respond to system messages or our own messages
        if ($message->is_system_message) {
            return null;
        }

        $conversation = $message->conversation;
        $ownerId = $conversation->owner_id ?? $conversation->property?->user_id;

        if (! $ownerId || $message->sender_id == $ownerId) {
            return null;
        }

        // Find matching auto-response
        $autoResponse = AutoResponse::where('user_id', $ownerId)
            ->active()
            ->byPriority()
            ->get()
            ->first(fn ($ar) => $ar->matches($message->message));

        if (! $autoResponse) {
            return null;
        }

        // Send auto-response
        $responseMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $ownerId,
            'message' => $autoResponse->response_content,
            'is_system_message' => true,
        ]);

        $autoResponse->incrementUsage();

        return $responseMessage;
    }

    /**
     * Schedule booking-related messages
     */
    public function scheduleBookingMessages(Booking $booking): array
    {
        $scheduled = [];
        $property = $booking->property;
        $owner = $property->user;

        // Find templates for this owner
        $confirmationTemplate = MessageTemplate::where('user_id', $owner->id)
            ->byTrigger('booking_confirmed')
            ->active()
            ->first();

        $checkInTemplate = MessageTemplate::where('user_id', $owner->id)
            ->byTrigger('before_checkin')
            ->active()
            ->first();

        $checkOutTemplate = MessageTemplate::where('user_id', $owner->id)
            ->byTrigger('before_checkout')
            ->active()
            ->first();

        // Schedule confirmation message (immediate)
        if ($confirmationTemplate) {
            $content = $confirmationTemplate->render([
                'guest_name' => $booking->user->name,
                'property_name' => $property->title,
                'check_in_date' => $booking->check_in->format('Y-m-d'),
                'check_out_date' => $booking->check_out->format('Y-m-d'),
                'booking_id' => $booking->id,
            ]);

            $scheduled[] = $this->scheduleMessage([
                'user_id' => $owner->id,
                'booking_id' => $booking->id,
                'recipient_id' => $booking->user_id,
                'recipient_type' => 'guest',
                'template_id' => $confirmationTemplate->id,
                'content' => $content,
                'scheduled_at' => now(),
            ]);

            $confirmationTemplate->incrementUsage();
        }

        // Schedule check-in reminder (1 day before)
        if ($checkInTemplate) {
            $content = $checkInTemplate->render([
                'guest_name' => $booking->user->name,
                'property_name' => $property->title,
                'check_in_date' => $booking->check_in->format('Y-m-d'),
                'check_in_time' => $property->check_in_time ?? '15:00',
                'property_address' => $property->address,
            ]);

            $scheduled[] = $this->scheduleMessage([
                'user_id' => $owner->id,
                'booking_id' => $booking->id,
                'recipient_id' => $booking->user_id,
                'recipient_type' => 'guest',
                'template_id' => $checkInTemplate->id,
                'content' => $content,
                'scheduled_at' => $booking->check_in->copy()->subDay()->setTime(10, 0),
            ]);
        }

        // Schedule check-out reminder (1 day before checkout)
        if ($checkOutTemplate) {
            $content = $checkOutTemplate->render([
                'guest_name' => $booking->user->name,
                'property_name' => $property->title,
                'check_out_date' => $booking->check_out->format('Y-m-d'),
                'check_out_time' => $property->check_out_time ?? '11:00',
            ]);

            $scheduled[] = $this->scheduleMessage([
                'user_id' => $owner->id,
                'booking_id' => $booking->id,
                'recipient_id' => $booking->user_id,
                'recipient_type' => 'guest',
                'template_id' => $checkOutTemplate->id,
                'content' => $content,
                'scheduled_at' => $booking->check_out->copy()->subDay()->setTime(10, 0),
            ]);
        }

        return $scheduled;
    }

    /**
     * Get suggested replies for a message
     */
    public function getSuggestedReplies(Message $message): array
    {
        $suggestions = [];

        $content = strtolower($message->message);

        // Inquiry responses
        if (str_contains($content, 'available') || str_contains($content, 'book')) {
            $suggestions[] = "Yes, it's available for those dates! Would you like to proceed with the booking?";
            $suggestions[] = 'Let me check the availability and get back to you shortly.';
        }

        // Pricing inquiries
        if (str_contains($content, 'price') || str_contains($content, 'cost') || str_contains($content, 'how much')) {
            $suggestions[] = 'The nightly rate is shown on the listing. Would you like more details about pricing?';
            $suggestions[] = 'I can offer you a special rate for longer stays. How many nights are you planning?';
        }

        // Amenity questions
        if (str_contains($content, 'wifi') || str_contains($content, 'internet')) {
            $suggestions[] = 'Yes, we provide high-speed WiFi. The password will be shared upon check-in.';
        }

        if (str_contains($content, 'parking')) {
            $suggestions[] = 'Yes, free parking is available on the premises.';
            $suggestions[] = 'Street parking is available nearby.';
        }

        // Check-in/out
        if (str_contains($content, 'check in') || str_contains($content, 'check-in')) {
            $suggestions[] = "Check-in is at 3:00 PM. I'll send you detailed instructions closer to your arrival date.";
            $suggestions[] = 'Early check-in may be available. Let me check and confirm.';
        }

        if (str_contains($content, 'check out') || str_contains($content, 'checkout')) {
            $suggestions[] = 'Check-out is at 11:00 AM. Late checkout can be arranged if needed.';
        }

        // General
        if (empty($suggestions)) {
            $suggestions[] = "Thank you for your message! I'll get back to you shortly.";
            $suggestions[] = 'Thanks for reaching out! How can I help you?';
        }

        return array_slice($suggestions, 0, 3); // Return max 3 suggestions
    }

    /**
     * Cancel scheduled message
     */
    public function cancelScheduledMessage(ScheduledMessage $message): bool
    {
        if ($message->isPending()) {
            $message->cancel();

            return true;
        }

        return false;
    }

    /**
     * Get user's templates
     */
    public function getUserTemplates(User $user, ?string $category = null)
    {
        $query = MessageTemplate::where('user_id', $user->id);

        if ($category) {
            $query->byCategory($category);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get default templates
     */
    public function getDefaultTemplates(): array
    {
        return [
            [
                'name' => 'Booking Confirmation',
                'category' => 'booking',
                'trigger_event' => 'booking_confirmed',
                'content' => "Hi {{guest_name}}! 🎉\n\nYour booking for {{property_name}} is confirmed!\n\nCheck-in: {{check_in_date}}\nCheck-out: {{check_out_date}}\n\nLooking forward to hosting you!",
                'variables' => ['guest_name', 'property_name', 'check_in_date', 'check_out_date'],
            ],
            [
                'name' => 'Check-in Reminder',
                'category' => 'check_in',
                'trigger_event' => 'before_checkin',
                'content' => "Hi {{guest_name}}!\n\nJust a friendly reminder that your check-in at {{property_name}} is tomorrow at {{check_in_time}}.\n\nAddress: {{property_address}}\n\nSee you soon!",
                'variables' => ['guest_name', 'property_name', 'check_in_time', 'property_address'],
            ],
            [
                'name' => 'Check-out Reminder',
                'category' => 'check_out',
                'trigger_event' => 'before_checkout',
                'content' => "Hi {{guest_name}}!\n\nHope you enjoyed your stay at {{property_name}}!\n\nReminder: Check-out is at {{check_out_time}} tomorrow.\n\nThank you for staying with us!",
                'variables' => ['guest_name', 'property_name', 'check_out_time'],
            ],
            [
                'name' => 'Welcome Message',
                'category' => 'inquiry',
                'content' => "Thank you for your interest! 😊\n\nI'd be happy to help you with your booking. What dates are you looking at?",
                'variables' => [],
            ],
        ];
    }
}

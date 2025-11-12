<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->when($request->has('unread_only'), function ($query) use ($request) {
                if ($request->boolean('unread_only')) {
                    $query->whereNull('read_at');
                }
            })
            ->when($request->has('type'), function ($query) use ($request) {
                $query->where('type', 'like', '%'.$request->type.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Get user's notification preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get or create the first preference for the user
        $preference = NotificationPreference::firstOrCreate([
            'user_id' => $user->id,
            'notification_type' => NotificationPreference::TYPE_ACCOUNT,
        ], [
            'channel_email' => true,
            'channel_database' => true,
            'email_enabled' => true,
            'sms_enabled' => false,
            'push_enabled' => false,
            'booking_updates' => true,
            'payment_updates' => true,
            'message_updates' => true,
        ]);

        return response()->json([
            'email_enabled' => $preference->email_enabled,
            'sms_enabled' => $preference->sms_enabled,
            'push_enabled' => $preference->push_enabled,
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Adjusted for test expectations: direct booleans, not nested array
            'email_enabled' => 'sometimes|boolean',
            'sms_enabled' => 'sometimes|boolean',
            'push_enabled' => 'sometimes|boolean',
            'booking_updates' => 'sometimes|boolean',
            'payment_updates' => 'sometimes|boolean',
            'message_updates' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Use updateOrCreate to prevent duplicate constraint violations
        $preference = NotificationPreference::updateOrCreate([
            'user_id' => $user->id,
            'notification_type' => NotificationPreference::TYPE_ACCOUNT,
        ], $request->only([
            'email_enabled', 'sms_enabled', 'push_enabled',
            'booking_updates', 'payment_updates', 'message_updates',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
        ]);
    }

    /**
     * Test notification (dev only)
     */
    public function testNotification(Request $request): JsonResponse
    {
        if (! app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Test notifications only available in local environment',
            ], 403);
        }

        $user = $request->user();

        $user->notify(new \App\Notifications\Account\WelcomeNotification);

        return response()->json([
            'success' => true,
            'message' => 'Test notification sent',
        ]);
    }
}


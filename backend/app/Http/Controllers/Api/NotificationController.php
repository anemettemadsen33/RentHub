<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
                $query->where('type', 'like', '%' . $request->type . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $notifications
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
            'count' => $count
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
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
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        
        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Get user's notification preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = [];
        
        foreach (NotificationPreference::types() as $type) {
            $preference = NotificationPreference::getOrCreateDefaults($user->id, $type);
            $preferences[] = $preference;
        }
        
        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'preferences.*.notification_type' => 'required|in:' . implode(',', NotificationPreference::types()),
            'preferences.*.channel_email' => 'boolean',
            'preferences.*.channel_database' => 'boolean',
            'preferences.*.channel_sms' => 'boolean',
            'preferences.*.channel_push' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $updated = [];

        foreach ($request->preferences as $pref) {
            $preference = NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_type' => $pref['notification_type']
                ],
                [
                    'channel_email' => $pref['channel_email'] ?? true,
                    'channel_database' => $pref['channel_database'] ?? true,
                    'channel_sms' => $pref['channel_sms'] ?? false,
                    'channel_push' => $pref['channel_push'] ?? false,
                ]
            );
            
            $updated[] = $preference;
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully',
            'data' => $updated
        ]);
    }

    /**
     * Test notification (dev only)
     */
    public function testNotification(Request $request): JsonResponse
    {
        if (!app()->environment('local')) {
            return response()->json([
                'success' => false,
                'message' => 'Test notifications only available in local environment'
            ], 403);
        }

        $user = $request->user();
        
        $user->notify(new \App\Notifications\Account\WelcomeNotification());
        
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent'
        ]);
    }
}

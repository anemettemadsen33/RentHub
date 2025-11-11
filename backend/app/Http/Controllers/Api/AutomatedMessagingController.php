<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoResponse;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\ScheduledMessage;
use App\Services\AutomatedMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomatedMessagingController extends Controller
{
    protected AutomatedMessagingService $messagingService;

    public function __construct(AutomatedMessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    // MESSAGE TEMPLATES

    /**
     * Get all templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        $user = $request->user();
        $category = $request->query('category');

        $templates = $this->messagingService->getUserTemplates($user, $category);

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get default templates
     */
    public function getDefaultTemplates(): JsonResponse
    {
        $templates = $this->messagingService->getDefaultTemplates();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Create template
     */
    public function createTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'trigger_event' => 'nullable|string|max:100',
            'subject' => 'nullable|string',
            'content' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $template = $this->messagingService->createTemplate($user, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, MessageTemplate $template): JsonResponse
    {
        $this->authorize('update', $template);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'trigger_event' => 'nullable|string|max:100',
            'subject' => 'nullable|string',
            'content' => 'sometimes|string',
            'variables' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $template->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully',
            'data' => $template,
        ]);
    }

    /**
     * Delete template
     */
    public function deleteTemplate(MessageTemplate $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully',
        ]);
    }

    /**
     * Render template preview
     */
    public function previewTemplate(Request $request, MessageTemplate $template): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
        ]);

        $rendered = $this->messagingService->renderTemplate($template, $request->data);

        return response()->json([
            'success' => true,
            'data' => [
                'rendered' => $rendered,
            ],
        ]);
    }

    // SCHEDULED MESSAGES

    /**
     * Get scheduled messages
     */
    public function getScheduledMessages(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status');

        $query = ScheduledMessage::where('user_id', $user->id)
            ->with(['template', 'booking', 'recipient']);

        if ($status) {
            $query->where('status', $status);
        }

        $messages = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * Create scheduled message
     */
    public function createScheduledMessage(Request $request): JsonResponse
    {
        $request->validate([
            'template_id' => 'nullable|exists:message_templates,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'recipient_id' => 'nullable|exists:users,id',
            'recipient_type' => 'required|string|in:guest,owner,all_guests',
            'subject' => 'nullable|string',
            'content' => 'required|string',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $user = $request->user();
        $data = array_merge($request->all(), ['user_id' => $user->id]);

        $message = $this->messagingService->scheduleMessage($data);

        return response()->json([
            'success' => true,
            'message' => 'Message scheduled successfully',
            'data' => $message,
        ], 201);
    }

    /**
     * Cancel scheduled message
     */
    public function cancelScheduledMessage(ScheduledMessage $message): JsonResponse
    {
        $this->authorize('update', $message);

        $cancelled = $this->messagingService->cancelScheduledMessage($message);

        if (! $cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Message cannot be cancelled (already sent or failed)',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message cancelled successfully',
        ]);
    }

    // AUTO-RESPONSES

    /**
     * Get auto-responses
     */
    public function getAutoResponses(Request $request): JsonResponse
    {
        $user = $request->user();

        $autoResponses = AutoResponse::where('user_id', $user->id)
            ->with('template')
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $autoResponses,
        ]);
    }

    /**
     * Create auto-response
     */
    public function createAutoResponse(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|string|in:keyword,time_based,booking_event,inquiry',
            'trigger_conditions' => 'required|array',
            'response_content' => 'required|string',
            'template_id' => 'nullable|exists:message_templates,id',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'active_from' => 'nullable|date',
            'active_until' => 'nullable|date|after:active_from',
            'settings' => 'nullable|array',
        ]);

        $user = $request->user();
        $autoResponse = $this->messagingService->createAutoResponse($user, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Auto-response created successfully',
            'data' => $autoResponse,
        ], 201);
    }

    /**
     * Update auto-response
     */
    public function updateAutoResponse(Request $request, AutoResponse $autoResponse): JsonResponse
    {
        $this->authorize('update', $autoResponse);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'trigger_type' => 'sometimes|string|in:keyword,time_based,booking_event,inquiry',
            'trigger_conditions' => 'sometimes|array',
            'response_content' => 'sometimes|string',
            'template_id' => 'nullable|exists:message_templates,id',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer',
            'active_from' => 'nullable|date',
            'active_until' => 'nullable|date',
            'settings' => 'nullable|array',
        ]);

        $autoResponse->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Auto-response updated successfully',
            'data' => $autoResponse,
        ]);
    }

    /**
     * Delete auto-response
     */
    public function deleteAutoResponse(AutoResponse $autoResponse): JsonResponse
    {
        $this->authorize('delete', $autoResponse);

        $autoResponse->delete();

        return response()->json([
            'success' => true,
            'message' => 'Auto-response deleted successfully',
        ]);
    }

    // SMART REPLIES

    /**
     * Get suggested replies for a message
     */
    public function getSuggestedReplies(Request $request, Message $message): JsonResponse
    {
        $suggestions = $this->messagingService->getSuggestedReplies($message);

        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => $suggestions,
            ],
        ]);
    }
}


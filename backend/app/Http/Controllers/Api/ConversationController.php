<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $conversations = Conversation::where(function ($query) use ($user) {
            $query->where('tenant_id', $user->id)
                ->orWhere('owner_id', $user->id);
        })
            ->with([
                'property:id,title,address,featured_image',
                'tenant:id,name,email,avatar',
                'owner:id,name,email,avatar',
                'latestMessage' => function ($query) {
                    $query->select('id', 'conversation_id', 'sender_id', 'message', 'created_at', 'read_at')
                        ->latest()
                        ->limit(1);
                },
            ])
            ->when($request->is_archived, function ($query) use ($request) {
                $query->where('is_archived', $request->is_archived);
            })
            ->orderBy('last_message_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $conversations->map(function ($conversation) use ($user) {
                return [
                    'id' => $conversation->id,
                    'property' => $conversation->property,
                    'other_user' => $conversation->getOtherParticipant($user),
                    'subject' => $conversation->subject,
                    'last_message' => $conversation->latestMessage->first(),
                    'last_message_at' => $conversation->last_message_at,
                    'is_archived' => $conversation->is_archived,
                    'unread_count' => $conversation->unreadCount($user),
                    'created_at' => $conversation->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        $property = Property::findOrFail($validated['property_id']);

        $tenantId = $user->id;
        $ownerId = $validated['recipient_id'];

        if ($user->id == $property->user_id) {
            $ownerId = $user->id;
            $tenantId = $validated['recipient_id'];
        }

        $existingConversation = Conversation::where('property_id', $property->id)
            ->where(function ($query) use ($tenantId, $ownerId) {
                $query->where('tenant_id', $tenantId)
                    ->where('owner_id', $ownerId);
            })
            ->first();

        if ($existingConversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation already exists',
                'data' => ['conversation_id' => $existingConversation->id],
            ], 409);
        }

        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'property_id' => $property->id,
                'booking_id' => $validated['booking_id'] ?? null,
                'tenant_id' => $tenantId,
                'owner_id' => $ownerId,
                'subject' => $validated['subject'] ?? "Inquiry about {$property->title}",
                'last_message_at' => now(),
            ]);

            $conversation->participants()->attach([
                $tenantId => ['last_read_at' => now()],
                $ownerId => ['last_read_at' => null],
            ]);

            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'message' => $validated['message'],
            ]);

            DB::commit();

            $conversation->load(['property', 'tenant', 'owner', 'messages']);

            return response()->json([
                'id' => $conversation->id,
                'messages' => $conversation->messages,
                'property' => $conversation->property,
                'tenant' => $conversation->tenant,
                'owner' => $conversation->owner,
                'message' => 'Conversation created successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create conversation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->with([
                'property:id,title,address,featured_image',
                'tenant:id,name,email,avatar',
                'owner:id,name,email,avatar',
                'booking',
            ])
            ->firstOrFail();

        $conversation->markAsRead($user);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $conversation->id,
                'property' => $conversation->property,
                'booking' => $conversation->booking,
                'tenant' => $conversation->tenant,
                'owner' => $conversation->owner,
                'other_user' => $conversation->getOtherParticipant($user),
                'subject' => $conversation->subject,
                'last_message_at' => $conversation->last_message_at,
                'is_archived' => $conversation->is_archived,
                'created_at' => $conversation->created_at,
            ],
        ]);
    }

    public function archive(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $conversation->update(['is_archived' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully',
        ]);
    }

    public function unarchive(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $conversation->update(['is_archived' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation unarchived successfully',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully',
        ]);
    }

    public function markAllAsRead(Request $request, $id)
    {
        $user = $request->user();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $conversation->markAsRead($user);

        return response()->json([
            'success' => true,
            'message' => 'All messages marked as read',
        ]);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Property;
use App\Repositories\CachedUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptimizedConversationController extends Controller
{
    protected CachedUserRepository $userRepository;
    
    public function __construct(CachedUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all conversations for the authenticated user with optimized performance
     */
    public function index(Request $request)
    {
        $startTime = microtime(true);
        $user = $request->user();
        $cacheKey = "conversations_user_{$user->id}_page_{$request->get('page', 1)}_archived_{$request->get('is_archived', 'false')}";
        $perPage = $request->get('per_page', 20);
        
        // Use cache for conversations list with 5-minute TTL
        $conversations = Cache::remember($cacheKey, 300, function () use ($user, $request, $perPage) {
            return Conversation::where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->with([
                'property:id,title,address,featured_image,city_id,country_id',
                'property.city:id,name',
                'property.country:id,name',
                'latestMessage' => function ($query) {
                    $query->select('id', 'conversation_id', 'sender_id', 'message', 'created_at', 'read_at')
                        ->latest()
                        ->limit(1);
                },
            ])
            ->when($request->get('is_archived'), function ($query) use ($request) {
                $query->where('is_archived', $request->get('is_archived'));
            })
            ->select([
                'id', 'property_id', 'tenant_id', 'owner_id', 'subject', 
                'last_message_at', 'is_archived', 'created_at', 'updated_at'
            ])
            ->orderBy('last_message_at', 'desc')
            ->paginate($perPage);
        });
        
        // Get user data from cache
        $userIds = collect();
        $conversations->each(function ($conversation) use ($userIds) {
            $userIds->push($conversation->tenant_id);
            $userIds->push($conversation->owner_id);
        });
        
        $cachedUsers = collect();
        $userIds->unique()->each(function ($userId) use (&$cachedUsers) {
            $user = $this->userRepository->findById($userId);
            if ($user) {
                $cachedUsers[$userId] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ];
            }
        });
        
        // Prepare response data
        $responseData = $conversations->map(function ($conversation) use ($user, $cachedUsers) {
            $otherUserId = $conversation->tenant_id === $user->id ? $conversation->owner_id : $conversation->tenant_id;
            $otherUser = $cachedUsers[$otherUserId] ?? null;
            
            return [
                'id' => $conversation->id,
                'property' => [
                    'id' => $conversation->property->id,
                    'title' => $conversation->property->title,
                    'address' => $conversation->property->address,
                    'featured_image' => $conversation->property->featured_image,
                    'city' => $conversation->property->city?->name,
                    'country' => $conversation->property->country?->name,
                ],
                'other_user' => $otherUser,
                'subject' => $conversation->subject,
                'last_message' => $conversation->latestMessage->first(),
                'last_message_at' => $conversation->last_message_at,
                'is_archived' => $conversation->is_archived,
                'unread_count' => $this->getUnreadCountOptimized($conversation, $user->id),
                'created_at' => $conversation->created_at,
            ];
        });
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Optimized conversations index', [
            'user_id' => $user->id,
            'execution_time_ms' => $executionTime,
            'conversations_count' => $conversations->count(),
            'cache_hit' => Cache::has($cacheKey)
        ]);
        
        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'data' => $responseData,
            'meta' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    /**
     * Create a new conversation with optimized performance
     */
    public function store(Request $request)
    {
        $startTime = microtime(true);
        
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        
        // Use cached property data
        $property = Cache::remember("property_{$validated['property_id']}", 3600, function () use ($validated) {
            return Property::with(['city:id,name', 'country:id,name'])
                ->select(['id', 'title', 'address', 'featured_image', 'city_id', 'country_id', 'user_id'])
                ->find($validated['property_id']);
        });
        
        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found',
            ], 404);
        }

        $tenantId = $user->id;
        $ownerId = $validated['recipient_id'];

        if ($user->id == $property->user_id) {
            $ownerId = $user->id;
            $tenantId = $validated['recipient_id'];
        }

        // Check for existing conversation with optimized query
        $existingConversation = Cache::remember(
            "conversation_check_{$property->id}_{$tenantId}_{$ownerId}",
            300,
            function () use ($property, $tenantId, $ownerId) {
                return Conversation::where('property_id', $property->id)
                    ->where('tenant_id', $tenantId)
                    ->where('owner_id', $ownerId)
                    ->select(['id', 'property_id', 'tenant_id', 'owner_id'])
                    ->first();
            }
        );

        if ($existingConversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation already exists',
                'data' => ['conversation_id' => $existingConversation->id],
            ], 409);
        }

        // Create conversation with optimized transaction
        $conversation = DB::transaction(function () use ($validated, $property, $tenantId, $ownerId, $user) {
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

            $conversation->messages()->create([
                'sender_id' => $user->id,
                'message' => $validated['message'],
            ]);

            return $conversation;
        });
        
        // Clear relevant caches
        $this->clearConversationCaches($user->id, $tenantId, $ownerId);
        
        // Load optimized relationships
        $conversation->load([
            'property' => function ($query) {
                $query->select(['id', 'title', 'address', 'featured_image', 'city_id', 'country_id'])
                    ->with(['city:id,name', 'country:id,name']);
            },
            'messages' => function ($query) {
                $query->select(['id', 'conversation_id', 'sender_id', 'message', 'created_at', 'read_at'])
                    ->latest()
                    ->limit(1);
            }
        ]);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Optimized conversation created', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'execution_time_ms' => $executionTime,
            'property_id' => $property->id
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'data' => [
                'id' => $conversation->id,
                'messages' => $conversation->messages,
                'property' => $conversation->property,
                'message' => 'Conversation created successfully',
            ]
        ], 201);
    }

    /**
     * Get a specific conversation with optimized performance
     */
    public function show(Request $request, $id)
    {
        $startTime = microtime(true);
        $user = $request->user();
        $cacheKey = "conversation_{$id}_user_{$user->id}";
        
        // Use cache for individual conversation with 10-minute TTL
        $conversation = Cache::remember($cacheKey, 600, function () use ($id, $user) {
            return Conversation::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('tenant_id', $user->id)
                        ->orWhere('owner_id', $user->id);
                })
                ->with([
                    'property' => function ($query) {
                        $query->select(['id', 'title', 'address', 'featured_image', 'city_id', 'country_id'])
                            ->with(['city:id,name', 'country:id,name']);
                    },
                    'booking:id,property_id,tenant_id,check_in_date,check_out_date,status',
                ])
                ->select([
                    'id', 'property_id', 'booking_id', 'tenant_id', 'owner_id', 
                    'subject', 'last_message_at', 'is_archived', 'created_at', 'updated_at'
                ])
                ->first();
        });
        
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        
        // Mark as read with optimized query
        $this->markAsReadOptimized($conversation, $user);
        
        // Get other user data from cache
        $otherUserId = $conversation->tenant_id === $user->id ? $conversation->owner_id : $conversation->tenant_id;
        $otherUser = $this->userRepository->findById($otherUserId);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Optimized conversation show', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'execution_time_ms' => $executionTime,
            'cache_hit' => Cache::has($cacheKey)
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'data' => [
                'id' => $conversation->id,
                'property' => [
                    'id' => $conversation->property->id,
                    'title' => $conversation->property->title,
                    'address' => $conversation->property->address,
                    'featured_image' => $conversation->property->featured_image,
                    'city' => $conversation->property->city?->name,
                    'country' => $conversation->property->country?->name,
                ],
                'booking' => $conversation->booking,
                'other_user' => $otherUser ? [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'email' => $otherUser->email,
                    'avatar' => $otherUser->avatar,
                ] : null,
                'subject' => $conversation->subject,
                'last_message_at' => $conversation->last_message_at,
                'is_archived' => $conversation->is_archived,
                'created_at' => $conversation->created_at,
            ],
        ]);
    }

    /**
     * Archive a conversation
     */
    public function archive(Request $request, $id)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        $conversation = $this->getUserConversation($user, $id);
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        
        $conversation->update(['is_archived' => true]);
        
        // Clear caches
        $this->clearConversationCaches($user->id, $conversation->tenant_id, $conversation->owner_id);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Conversation archived', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'message' => 'Conversation archived successfully',
        ]);
    }

    /**
     * Unarchive a conversation
     */
    public function unarchive(Request $request, $id)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        $conversation = $this->getUserConversation($user, $id);
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        
        $conversation->update(['is_archived' => false]);
        
        // Clear caches
        $this->clearConversationCaches($user->id, $conversation->tenant_id, $conversation->owner_id);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Conversation unarchived', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'message' => 'Conversation unarchived successfully',
        ]);
    }

    /**
     * Delete a conversation
     */
    public function destroy(Request $request, $id)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        $conversation = $this->getUserConversation($user, $id);
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        
        $conversation->delete();
        
        // Clear caches
        $this->clearConversationCaches($user->id, $conversation->tenant_id, $conversation->owner_id);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Conversation deleted', [
            'user_id' => $user->id,
            'conversation_id' => $id,
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'message' => 'Conversation deleted successfully',
        ]);
    }

    /**
     * Mark all messages in a conversation as read
     */
    public function markAllAsRead(Request $request, $id)
    {
        $startTime = microtime(true);
        $user = $request->user();
        
        $conversation = $this->getUserConversation($user, $id);
        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        
        $this->markAsReadOptimized($conversation, $user);
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::info('Conversation marked as read', [
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'execution_time_ms' => $executionTime
        ]);

        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'message' => 'All messages marked as read',
        ]);
    }

    /**
     * Get conversation statistics for the user
     */
    public function stats(Request $request)
    {
        $startTime = microtime(true);
        $user = $request->user();
        $cacheKey = "conversation_stats_user_{$user->id}";
        
        $stats = Cache::remember($cacheKey, 300, function () use ($user) {
            $totalConversations = Conversation::where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })->count();
            
            $activeConversations = Conversation::where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })->where('is_archived', false)->count();
            
            $archivedConversations = Conversation::where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })->where('is_archived', true)->count();
            
            $unreadConversations = Conversation::where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->whereHas('messages', function ($query) use ($user) {
                $query->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at');
            })
            ->where('is_archived', false)
            ->count();
            
            return [
                'total' => $totalConversations,
                'active' => $activeConversations,
                'archived' => $archivedConversations,
                'unread' => $unreadConversations,
            ];
        });
        
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        
        return response()->json([
            'success' => true,
            'execution_time_ms' => $executionTime,
            'data' => $stats,
        ]);
    }

    /**
     * Optimized method to get unread count
     */
    private function getUnreadCountOptimized($conversation, $userId)
    {
        $cacheKey = "unread_count_{$conversation->id}_{$userId}";
        
        return Cache::remember($cacheKey, 300, function () use ($conversation, $userId) {
            return $conversation->messages()
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();
        });
    }

    /**
     * Optimized method to mark conversation as read
     */
    private function markAsReadOptimized($conversation, $user)
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->first();
            
        if ($participant && !$participant->last_read_at) {
            $participant->update(['last_read_at' => now()]);
            
            // Clear unread count cache
            Cache::forget("unread_count_{$conversation->id}_{$user->id}");
        }
    }

    /**
     * Get user conversation with optimized query
     */
    private function getUserConversation($user, $conversationId)
    {
        return Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->select(['id', 'tenant_id', 'owner_id'])
            ->first();
    }

    /**
     * Clear conversation-related caches
     */
    private function clearConversationCaches($userId, $tenantId, $ownerId)
    {
        // Clear user conversation lists
        Cache::forget("conversations_user_{$userId}_page_1_archived_false");
        Cache::forget("conversations_user_{$userId}_page_1_archived_true");
        
        // Clear other participant's conversation lists
        if ($userId != $tenantId) {
            Cache::forget("conversations_user_{$tenantId}_page_1_archived_false");
            Cache::forget("conversations_user_{$tenantId}_page_1_archived_true");
        }
        
        if ($userId != $ownerId) {
            Cache::forget("conversations_user_{$ownerId}_page_1_archived_false");
            Cache::forget("conversations_user_{$ownerId}_page_1_archived_true");
        }
        
        // Clear stats cache
        Cache::forget("conversation_stats_user_{$userId}");
        Cache::forget("conversation_stats_user_{$tenantId}");
        Cache::forget("conversation_stats_user_{$ownerId}");
    }
}
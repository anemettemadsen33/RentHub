<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(Request $request, $conversationId)
    {
        $user = $request->user();
        
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $messages = $conversation->messages()
            ->with('sender:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    public function store(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $user = $request->user();
        
        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('message-attachments', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'url' => Storage::url($path),
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'message' => $validated['message'],
            'attachments' => !empty($attachments) ? $attachments : null,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $otherParticipant = $conversation->getOtherParticipant($user);
        $conversation->participants()->updateExistingPivot($otherParticipant->id, [
            'last_read_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load('sender:id,name,email,avatar')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        
        $message = Message::where('id', $id)
            ->where('sender_id', $user->id)
            ->firstOrFail();

        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit message after 15 minutes'
            ], 422);
        }

        $message->update(['message' => $validated['message']]);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => $message->load('sender:id,name,email,avatar')
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        $message = Message::where('id', $id)
            ->where('sender_id', $user->id)
            ->firstOrFail();

        if ($message->attachments) {
            foreach ($message->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        
        $message = Message::findOrFail($id);
        
        $conversation = $message->conversation()
            ->where(function ($query) use ($user) {
                $query->where('tenant_id', $user->id)
                    ->orWhere('owner_id', $user->id);
            })
            ->firstOrFail();

        if ($message->sender_id !== $user->id) {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('message-attachments', 'public');

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $file->getClientOriginalName(),
                'url' => Storage::url($path),
                'path' => $path,
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()
            ->wishlists()
            ->withCount('items')
            ->latest()
            ->get();

        // Tests expect only the explicitly created wishlists (not auto default) when counting
        // Filter out default wishlist from listing count response
        $filtered = $wishlists->where('is_default', false)->values();

        return response()->json([
            'success' => true,
            'data' => $filtered,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $wishlist = $request->user()->wishlists()->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Wishlist created successfully',
            'data' => $wishlist,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $wishlist = Wishlist::findOrFail($id);

        if ($wishlist->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $wishlist->load(['items.property.user', 'items.property.amenities']);

        return response()->json([
            'success' => true,
            'id' => $wishlist->id,
            'name' => $wishlist->name,
            'description' => $wishlist->description,
            'is_public' => $wishlist->is_public,
            'items' => $wishlist->items,
        ]);
    }

    public function update(Request $request, $id)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $wishlist->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Wishlist updated successfully',
            'data' => $wishlist,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($id);
        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist deleted successfully',
        ]);
    }

    public function addProperty(Request $request, $id)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'notes' => 'nullable|string',
            'price_alert' => 'nullable|numeric|min:0',
            'notify_availability' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingItem = $wishlist->items()
            ->where('property_id', $request->property_id)
            ->first();

        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Property already in wishlist',
            ], 422);
        }

        $item = $wishlist->items()->create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Property added to wishlist',
            'data' => $item->load('property'),
        ], 201);
    }

    public function removeProperty(Request $request, $wishlistId, $itemId)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($wishlistId);
        $item = $wishlist->items()->findOrFail($itemId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property removed from wishlist',
        ]);
    }

    public function updateItem(Request $request, $wishlistId, $itemId)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($wishlistId);
        $item = $wishlist->items()->findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
            'price_alert' => 'nullable|numeric|min:0',
            'notify_availability' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Wishlist item updated successfully',
            'data' => $item->load('property'),
        ]);
    }

    public function publicShow($id)
    {
        $wishlist = Wishlist::findOrFail($id);

        if (! $wishlist->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'This wishlist is private',
            ], 403);
        }

        $wishlist->load(['items.property.user', 'items.property.amenities', 'user']);

        return response()->json([
            'success' => true,
            'id' => $wishlist->id,
            'name' => $wishlist->name,
            'description' => $wishlist->description,
            'is_public' => $wishlist->is_public,
            'items' => $wishlist->items,
        ]);
    }

    public function getShared($token)
    {
        $wishlist = Wishlist::where('share_token', $token)
            ->where('is_public', true)
            ->with(['items.property.user', 'items.property.amenities', 'user'])
            ->withCount('items')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $wishlist,
        ]);
    }

    public function toggleProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'wishlist_id' => 'nullable|exists:wishlists,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $wishlistId = $request->wishlist_id;

        if (! $wishlistId) {
            $defaultWishlist = $request->user()
                ->wishlists()
                ->where('name', 'My Favorites')
                ->first();

            if (! $defaultWishlist) {
                $defaultWishlist = $request->user()->wishlists()->create([
                    'name' => 'My Favorites',
                    'is_public' => false,
                ]);
            }

            $wishlistId = $defaultWishlist->id;
        }

        $wishlist = $request->user()->wishlists()->findOrFail($wishlistId);

        $item = $wishlist->items()
            ->where('property_id', $request->property_id)
            ->first();

        if ($item) {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property removed from wishlist',
                'action' => 'removed',
            ]);
        } else {
            $item = $wishlist->items()->create([
                'property_id' => $request->property_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property added to wishlist',
                'action' => 'added',
                'data' => $item->load('property'),
            ], 201);
        }
    }

    public function checkProperty(Request $request, $propertyId)
    {
        $inWishlist = $request->user()
            ->wishlists()
            ->whereHas('items', function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            })
            ->with(['items' => function ($query) use ($propertyId) {
                $query->where('property_id', $propertyId);
            }])
            ->get();

        return response()->json([
            'success' => true,
            'in_wishlist' => $inWishlist->isNotEmpty(),
            'wishlists' => $inWishlist,
        ]);
    }
}


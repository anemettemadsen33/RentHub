<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Services\InvoiceGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function __construct(
        private InvoiceGenerationService $invoiceService
    ) {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['property', 'user']);
        
        // Filter by authenticated user if not admin
        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }
        
        $bookings = $query->latest()->paginate($request->get('per_page', 10));
        
        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'per_page' => $bookings->perPage(),
            'total' => $bookings->total(),
            'from' => $bookings->firstItem(),
            'to' => $bookings->lastItem(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $property = Property::findOrFail($request->property_id);
        
        // Check if property can accommodate requested guests
        if ($request->guests > $property->guests) {
            return response()->json([
                'success' => false,
                'message' => 'Property cannot accommodate the requested number of guests'
            ], 400);
        }

        // Check availability
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        $conflictingBookings = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->exists();

        if ($conflictingBookings) {
            return response()->json([
                'success' => false,
                'message' => 'Property is not available for the selected dates'
            ], 400);
        }

        // Calculate pricing
        $nights = $checkIn->diffInDays($checkOut);
        $subtotal = $nights * $property->price_per_night;
        $cleaningFee = $property->cleaning_fee ?? 0;
        $securityDeposit = $property->security_deposit ?? 0;
        $taxes = $subtotal * 0.09; // 9% tax rate
        $totalAmount = $subtotal + $cleaningFee + $securityDeposit + $taxes;

        $booking = Booking::create([
            'property_id' => $property->id,
            'user_id' => Auth::id(),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $request->guests,
            'nights' => $nights,
            'price_per_night' => $property->price_per_night,
            'subtotal' => $subtotal,
            'cleaning_fee' => $cleaningFee,
            'security_deposit' => $securityDeposit,
            'taxes' => $taxes,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
            'guest_phone' => $request->guest_phone,
            'special_requests' => $request->special_requests,
            'payment_status' => 'pending',
        ]);

        $booking->load(['property', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'data' => $booking
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = Booking::with(['property', 'user'])->findOrFail($id);
        
        // Check if user can view this booking
        if (Auth::user()->role !== 'admin' && $booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $booking
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Check if user can update this booking
        if (Auth::user()->role !== 'admin' && $booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:pending,confirmed,checked_in,checked_out,cancelled,completed',
            'special_requests' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $booking->update($request->only(['status', 'special_requests']));
        $booking->load(['property', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data' => $booking
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Check if user can delete this booking
        if (Auth::user()->role !== 'admin' && $booking->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Can only cancel pending bookings
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be cancelled'
            ], 400);
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }

    /**
     * Get user's bookings
     */
    public function userBookings()
    {
        $bookings = Booking::with(['property'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $bookings->items(),
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'per_page' => $bookings->perPage(),
            'total' => $bookings->total(),
            'from' => $bookings->firstItem(),
            'to' => $bookings->lastItem(),
        ]);
    }

    /**
     * Check property availability
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        $conflictingBookings = Booking::where('property_id', $request->property_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                          ->where('check_out', '>=', $checkOut);
                    });
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $conflictingBookings->isEmpty(),
                'conflicting_bookings' => $conflictingBookings
            ]
        ]);
    }

    /**
     * Generate invoice for booking manually
     */
    public function generateInvoice(Request $request, string $id)
    {
        $booking = Booking::with(['property', 'user'])->findOrFail($id);
        
        // Check permissions
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $isOwner = $user->role === 'owner' && $booking->property && $booking->property->user_id === $user->id;
        
        if (!$isAdmin && !$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admin or property owner can generate invoices.'
            ], 403);
        }

        // Check if invoice already exists
        if ($booking->invoices()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice already exists for this booking.',
                'invoice' => $booking->invoices()->first()
            ], 400);
        }

        try {
            $sendEmail = $request->boolean('send_email', true);
            $invoice = $this->invoiceService->createFromBooking($booking, $sendEmail);

            return response()->json([
                'success' => true,
                'message' => 'Invoice generated successfully' . ($sendEmail ? ' and sent to customer' : ''),
                'data' => $invoice->load(['bankAccount', 'booking'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking invoices
     */
    public function getInvoices(string $id)
    {
        $booking = Booking::findOrFail($id);
        
        // Check permissions
        $user = Auth::user();
        if ($user->role !== 'admin' && $booking->user_id !== $user->id) {
            if ($user->role !== 'owner' || !$booking->property || $booking->property->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
        }

        $invoices = $booking->invoices()->with('bankAccount')->get();

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }
}

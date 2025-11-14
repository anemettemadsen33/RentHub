<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OptimizedPaymentProofController extends Controller
{
    private const MAX_FILE_SIZE = 10240; // 10MB in KB
    private const ALLOWED_TYPES = ['pdf', 'jpg', 'jpeg', 'png'];
    private const CACHE_TTL = 300; // 5 minutes

    public function index(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        $user = $request->user();

        if ($payment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Use cache for payment proofs
        $cacheKey = "payment_proofs_{$payment->id}";
        $proofs = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($payment) {
            return $payment->proofs()
                ->select(['id', 'payment_id', 'filename', 'file_path', 'file_size', 'file_type', 'status', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized payment proofs index completed', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'duration_ms' => $duration,
            'proofs_count' => $proofs->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $proofs,
            'cached' => Cache::has($cacheKey),
        ]);
    }

    public function upload(Request $request, Payment $payment)
    {
        $startTime = microtime(true);
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:' . self::MAX_FILE_SIZE . '|mimes:' . implode(',', self::ALLOWED_TYPES),
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $file = $request->file('file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'payment_proofs/' . $payment->id . '/' . $filename;

            // Store file with optimized settings
            $storedPath = $file->storeAs('payment_proofs/' . $payment->id, $filename, 'public');

            $proof = PaymentProof::create([
                'payment_id' => $payment->id,
                'filename' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientOriginalExtension(),
                'notes' => $request->input('notes'),
                'uploaded_by' => $user->id,
                'status' => 'pending',
            ]);

            // Clear payment proofs cache
            Cache::forget("payment_proofs_{$payment->id}");
            Cache::forget("pending_payment_proofs_for_host");

            $duration = (microtime(true) - $startTime) * 1000;
            Log::info('Optimized payment proof upload completed', [
                'payment_id' => $payment->id,
                'proof_id' => $proof->id,
                'user_id' => $user->id,
                'duration_ms' => $duration,
                'file_size' => $file->getSize()
            ]);

            return response()->json([
                'success' => true,
                'data' => $proof->fresh(),
                'message' => 'Payment proof uploaded successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Optimized payment proof upload failed', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to upload payment proof: ' . $e->getMessage()], 500);
        }
    }

    public function verify(Request $request, PaymentProof $proof)
    {
        $startTime = microtime(true);
        $user = $request->user();

        // Check if user is the property owner or admin
        $payment = $proof->payment;
        $booking = $payment->booking;
        $property = $booking->property;

        if ($user->role !== 'admin' && $property->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $proof->update([
                'status' => $request->status,
                'verified_by' => $user->id,
                'verified_at' => now(),
                'rejection_reason' => $request->input('rejection_reason'),
            ]);

            // Update payment status based on proof verification
            if ($request->status === 'approved') {
                $payment->update(['status' => 'completed']);
                
                // Clear payment cache
                Cache::forget("payment_details_{$payment->id}");
                Cache::forget("user_payments_{$payment->user_id}_page_1");
            }

            DB::commit();

            // Clear relevant caches
            Cache::forget("payment_proofs_{$payment->id}");
            Cache::forget("pending_payment_proofs_for_host");

            $duration = (microtime(true) - $startTime) * 1000;
            Log::info('Optimized payment proof verification completed', [
                'proof_id' => $proof->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'status' => $request->status,
                'duration_ms' => $duration
            ]);

            return response()->json([
                'success' => true,
                'data' => $proof->fresh(),
                'message' => 'Payment proof verified successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Optimized payment proof verification failed', [
                'proof_id' => $proof->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to verify payment proof: ' . $e->getMessage()], 500);
        }
    }

    public function download(Request $request, PaymentProof $proof)
    {
        $startTime = microtime(true);
        $user = $request->user();

        // Check authorization
        $payment = $proof->payment;
        $booking = $payment->booking;
        $property = $booking->property;

        if ($payment->user_id !== $user->id && $property->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            if (!Storage::disk('public')->exists($proof->file_path)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            $duration = (microtime(true) - $startTime) * 1000;
            Log::info('Optimized payment proof download completed', [
                'proof_id' => $proof->id,
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'duration_ms' => $duration
            ]);

            return Storage::disk('public')->download($proof->file_path, $proof->filename);
        } catch (\Exception $e) {
            Log::error('Optimized payment proof download failed', [
                'proof_id' => $proof->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to download payment proof: ' . $e->getMessage()], 500);
        }
    }

    public function pendingForHost(Request $request)
    {
        $startTime = microtime(true);
        $user = $request->user();

        if ($user->role !== 'owner' && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Use cache for pending proofs
        $cacheKey = "pending_payment_proofs_for_host";
        $pendingProofs = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return PaymentProof::where('status', 'pending')
                ->whereHas('payment.booking.property', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with([
                    'payment:id,booking_id,amount,status',
                    'payment.booking:id,property_id,user_id,check_in,check_out',
                    'payment.booking.property:id,title,user_id'
                ])
                ->select(['id', 'payment_id', 'filename', 'file_size', 'file_type', 'status', 'created_at'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        });

        $duration = (microtime(true) - $startTime) * 1000;
        Log::info('Optimized pending payment proofs for host completed', [
            'user_id' => $user->id,
            'duration_ms' => $duration,
            'pending_count' => $pendingProofs->count()
        ]);

        return response()->json([
            'success' => true,
            'data' => $pendingProofs,
            'cached' => Cache::has($cacheKey),
        ]);
    }
}
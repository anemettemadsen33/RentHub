<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PaymentProofController extends Controller
{
    /**
     * Upload payment proof (PDF or image)
     */
    public function upload(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Check if user owns this payment
        if ($payment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if payment is pending
        if ($payment->status !== 'pending') {
            return response()->json([
                'error' => 'Payment proof can only be uploaded for pending payments'
            ], 400);
        }

        $request->validate([
            'proof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // Max 10MB
            'transfer_reference' => 'nullable|string|max:255',
            'transfer_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $file = $request->file('proof');
            $fileName = 'payment_proof_' . $payment->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('payment-proofs', $fileName, 'public');

            // Update payment with transfer details
            $payment->update([
                'transfer_reference' => $request->transfer_reference,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
            ]);

            // Create payment proof record
            $proof = PaymentProof::create([
                'payment_id' => $payment->id,
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);

            Log::info('Payment proof uploaded', [
                'payment_id' => $payment->id,
                'proof_id' => $proof->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Payment proof uploaded successfully',
                'proof' => $proof,
                'payment' => $payment->load('proofs'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Payment proof upload failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to upload payment proof'
            ], 500);
        }
    }

    /**
     * Verify payment proof (Host only)
     */
    public function verify(Request $request, $proofId)
    {
        $proof = PaymentProof::with('payment.booking.property')->findOrFail($proofId);

        // Check if user is the property host
        if ($proof->payment->booking->property->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'action' => 'required|in:verify,reject',
            'rejection_reason' => 'required_if:action,reject|string|max:500',
        ]);

        try {
            if ($request->action === 'verify') {
                $proof->markAsVerified(auth()->id());
                
                // Mark payment as completed
                $proof->payment->markAsCompleted();

                Log::info('Payment proof verified', [
                    'proof_id' => $proof->id,
                    'payment_id' => $proof->payment_id,
                    'verified_by' => auth()->id(),
                ]);

                return response()->json([
                    'message' => 'Payment verified successfully',
                    'proof' => $proof->fresh(),
                    'payment' => $proof->payment->fresh(),
                ]);
            } else {
                $proof->markAsRejected(auth()->id(), $request->rejection_reason);

                Log::info('Payment proof rejected', [
                    'proof_id' => $proof->id,
                    'payment_id' => $proof->payment_id,
                    'verified_by' => auth()->id(),
                    'reason' => $request->rejection_reason,
                ]);

                return response()->json([
                    'message' => 'Payment proof rejected',
                    'proof' => $proof->fresh(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Payment proof verification failed', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to process payment proof'
            ], 500);
        }
    }

    /**
     * Get payment proofs for a payment
     */
    public function index($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Check authorization
        if ($payment->user_id !== auth()->id() && 
            $payment->booking->property->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $proofs = $payment->proofs()
            ->with('verifier')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'proofs' => $proofs,
            'payment' => $payment,
        ]);
    }

    /**
     * Download payment proof
     */
    public function download($proofId)
    {
        $proof = PaymentProof::with('payment.booking.property')->findOrFail($proofId);

        // Check authorization
        if ($proof->payment->user_id !== auth()->id() && 
            $proof->payment->booking->property->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!Storage::disk('public')->exists($proof->file_path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($proof->file_path);
    }

    /**
     * Get pending proofs for host (all their properties)
     */
    public function pendingForHost()
    {
        $user = auth()->user();

        $proofs = PaymentProof::with(['payment.booking.property', 'payment.user'])
            ->whereHas('payment.booking.property', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'proofs' => $proofs,
            'total' => $proofs->count(),
        ]);
    }
}

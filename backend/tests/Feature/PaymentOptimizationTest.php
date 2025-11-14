<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Property $property;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->property = Property::factory()->create([
            'user_id' => $this->user->id,
            'cleaning_fee' => 50,
            'security_deposit' => 200,
        ]);
        
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'total_price' => 500,
            'status' => 'confirmed',
        ]);
    }

    public function test_optimized_payment_creation_performance()
    {
        $this->actingAs($this->user);
        
        // Clear cache before test
        Cache::flush();
        
        $startTime = microtime(true);
        
        // Test optimized payment creation
        $response = $this->postJson('/api/v1/optimized/payments', [
            'booking_id' => $this->booking->id,
            'amount' => 500,
            'payment_method' => 'bank_transfer',
            'type' => 'full',
        ]);
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'booking_id', 'amount', 'status', 'payment_method', 'type', 'created_at', 'duration_ms'
        ]);
        
        // Log performance metrics
        Log::info('Optimized Payment Creation Performance', [
            'duration_ms' => $duration,
            'booking_id' => $this->booking->id,
            'amount' => 500,
            'cached' => false,
        ]);
        
        // Verify response includes performance metrics
        $this->assertArrayHasKey('duration_ms', $response->json());
        $this->assertLessThan(2000, $duration, 'Payment creation should complete within 2 seconds');
    }

    public function test_optimized_payment_listing_performance()
    {
        // Create multiple payments for testing
        Payment::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
        ]);
        
        $this->actingAs($this->user);
        Cache::flush();
        
        // First request (no cache)
        $startTime = microtime(true);
        $response1 = $this->getJson('/api/v1/optimized/payments');
        $duration1 = (microtime(true) - $startTime) * 1000;
        
        $response1->assertStatus(200);
        $response1->assertJsonStructure([
            'success', 'data', 'cached'
        ]);
        
        $this->assertFalse($response1->json('cached'));
        
        // Second request (with cache)
        $startTime = microtime(true);
        $response2 = $this->getJson('/api/v1/optimized/payments');
        $duration2 = (microtime(true) - $startTime) * 1000;
        
        $response2->assertStatus(200);
        $this->assertTrue($response2->json('cached'));
        
        // Log performance comparison
        Log::info('Optimized Payment Listing Performance Comparison', [
            'first_request_duration_ms' => $duration1,
            'second_request_duration_ms' => $duration2,
            'cache_improvement_factor' => $duration1 / max($duration2, 1),
            'payments_count' => 10,
        ]);
        
        // Cached request should be significantly faster
        $this->assertLessThan($duration1 * 0.5, $duration2, 'Cached request should be at least 50% faster');
    }

    public function test_optimized_payment_show_performance()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
            'amount' => 500,
            'status' => 'pending',
        ]);
        
        $this->actingAs($this->user);
        Cache::flush();
        
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/optimized/payments/{$payment->id}");
        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id', 'booking_id', 'amount', 'status', 'payment_method', 'booking', 'invoice'
        ]);
        
        Log::info('Optimized Payment Show Performance', [
            'duration_ms' => $duration,
            'payment_id' => $payment->id,
            'cached' => false,
        ]);
        
        $this->assertLessThan(1000, $duration, 'Payment details should load within 1 second');
    }

    public function test_pdf_generation_performance()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
            'amount' => 500,
            'status' => 'completed',
        ]);
        
        $invoice = Invoice::factory()->create([
            'booking_id' => $this->booking->id,
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'invoice_number' => 'INV-001',
            'total_amount' => 500,
            'status' => 'sent',
        ]);
        
        $this->actingAs($this->user);
        
        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/invoices/{$invoice->id}/download");
        $duration = (microtime(true) - $startTime) * 1000;
        
        Log::info('PDF Generation Performance', [
            'duration_ms' => $duration,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
        
        // PDF generation should be reasonably fast with optimizations
        $this->assertLessThan(3000, $duration, 'PDF generation should complete within 3 seconds');
    }

    public function test_payment_proof_upload_performance()
    {
        $payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
            'amount' => 500,
            'status' => 'pending',
        ]);
        
        $this->actingAs($this->user);
        
        // Create a test PDF file
        $testFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($testFile, str_repeat('Test content for PDF upload ', 1000));
        
        $startTime = microtime(true);
        $response = $this->postJson("/api/v1/optimized/payments/{$payment->id}/upload-proof", [
            'file' => new \Illuminate\Http\UploadedFile($testFile, 'test-proof.pdf', 'application/pdf', null, true),
            'notes' => 'Test payment proof upload',
        ]);
        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success', 'data', 'message'
        ]);
        
        Log::info('Payment Proof Upload Performance', [
            'duration_ms' => $duration,
            'payment_id' => $payment->id,
            'file_size' => filesize($testFile),
        ]);
        
        unlink($testFile);
        
        $this->assertLessThan(2000, $duration, 'Payment proof upload should complete within 2 seconds');
    }

    public function test_cache_effectiveness()
    {
        $this->actingAs($this->user);
        Cache::flush();
        
        // Create test data
        Payment::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
        ]);
        
        // First request - populate cache
        $response1 = $this->getJson('/api/v1/optimized/payments');
        $this->assertFalse($response1->json('cached'));
        
        // Second request - should use cache
        $response2 = $this->getJson('/api/v1/optimized/payments');
        $this->assertTrue($response2->json('cached'));
        
        // Verify data consistency
        $this->assertEquals(
            $response1->json('data.total'),
            $response2->json('data.total')
        );
        
        Log::info('Cache Effectiveness Test', [
            'first_request_cached' => $response1->json('cached'),
            'second_request_cached' => $response2->json('cached'),
            'data_consistency' => true,
        ]);
    }
}
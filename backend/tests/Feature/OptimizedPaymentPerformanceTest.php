<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizedPaymentPerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Property $property;
    private Booking $booking;
    private BankAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->property = Property::factory()->create(['user_id' => $this->user->id]);
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'status' => 'confirmed',
            'payment_status' => 'pending'
        ]);
        $this->bankAccount = BankAccount::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true
        ]);
    }

    public function test_optimized_payment_creation_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->postJson('/api/optimized/payments', [
                'booking_id' => $this->booking->id,
                'amount' => 1000.00,
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $this->bankAccount->id,
            ]);

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $this->assertLessThan(2000, $duration, "Payment creation took too long: {$duration}ms");
        
        Log::info('Optimized payment creation performance', [
            'duration_ms' => $duration,
            'booking_id' => $this->booking->id,
            'amount' => 1000.00
        ]);
    }

    public function test_optimized_payment_methods_performance()
    {
        // Clear cache first
        Cache::forget('payment_methods_list');
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/optimized/payment-methods');

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $duration, "Payment methods retrieval took too long: {$duration}ms");
        
        Log::info('Optimized payment methods performance', [
            'duration_ms' => $duration,
            'method_count' => count($response->json())
        ]);
    }

    public function test_optimized_bank_accounts_performance()
    {
        // Clear cache first
        Cache::forget('bank_accounts_list');
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/optimized/bank-accounts');

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(500, $duration, "Bank accounts retrieval took too long: {$duration}ms");
        
        Log::info('Optimized bank accounts performance', [
            'duration_ms' => $duration,
            'account_count' => count($response->json())
        ]);
    }

    public function test_optimized_payment_status_performance()
    {
        // Create a payment first
        $paymentResponse = $this->actingAs($this->user)
            ->postJson('/api/optimized/payments', [
                'booking_id' => $this->booking->id,
                'amount' => 1000.00,
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $this->bankAccount->id,
            ]);

        $payment = $paymentResponse->json('payment');
        
        // Clear cache first
        Cache::forget("payment_status_{$payment['id']}");
        
        $startTime = microtime(true);
        
        $response = $this->getJson("/api/optimized/payments/{$payment['id']}/status");

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(300, $duration, "Payment status retrieval took too long: {$duration}ms");
        
        Log::info('Optimized payment status performance', [
            'duration_ms' => $duration,
            'payment_id' => $payment['id']
        ]);
    }

    public function test_payment_proof_upload_performance()
    {
        // Create a payment first
        $paymentResponse = $this->actingAs($this->user)
            ->postJson('/api/optimized/payments', [
                'booking_id' => $this->booking->id,
                'amount' => 1000.00,
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $this->bankAccount->id,
            ]);

        $payment = $paymentResponse->json('payment');
        
        // Create a test PDF file
        $testFile = $this->createTestPdfFile();
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($this->user)
            ->postJson("/api/optimized/payments/{$payment['id']}/upload-proof", [
                'proof_file' => $testFile,
                'description' => 'Test payment proof upload'
            ]);

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(201);
        $this->assertLessThan(3000, $duration, "Payment proof upload took too long: {$duration}ms");
        
        Log::info('Optimized payment proof upload performance', [
            'duration_ms' => $duration,
            'payment_id' => $payment['id'],
            'file_size' => $testFile->getSize()
        ]);
    }

    public function test_cached_payment_methods_response_time()
    {
        // First request to populate cache
        $this->getJson('/api/optimized/payment-methods');
        
        // Second request should be much faster due to caching
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/optimized/payment-methods');

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(100, $duration, "Cached payment methods retrieval took too long: {$duration}ms");
        
        Log::info('Cached payment methods performance', [
            'duration_ms' => $duration
        ]);
    }

    public function test_cached_bank_accounts_response_time()
    {
        // First request to populate cache
        $this->getJson('/api/optimized/bank-accounts');
        
        // Second request should be much faster due to caching
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/optimized/bank-accounts');

        $duration = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        $this->assertLessThan(100, $duration, "Cached bank accounts retrieval took too long: {$duration}ms");
        
        Log::info('Cached bank accounts performance', [
            'duration_ms' => $duration
        ]);
    }

    private function createTestPdfFile()
    {
        // Create a simple PDF file for testing
        $pdfContent = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n>>\nendobj\nxref\n0 4\n0000000000 65535 f \n0000000010 00000 n \n0000000079 00000 n \n0000000128 00000 n \ntrailer\n<<\n/Size 4\n/Root 1 0 R\n>>\nstartxref\n179\n%%EOF";

        $tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tempFile, $pdfContent);
        
        return new \Illuminate\Http\UploadedFile(
            $tempFile,
            'test_payment_proof.pdf',
            'application/pdf',
            null,
            true
        );
    }

    protected function tearDown(): void
    {
        // Clean up any cached data
        Cache::forget('payment_methods_list');
        Cache::forget('bank_accounts_list');
        
        parent::tearDown();
    }
}
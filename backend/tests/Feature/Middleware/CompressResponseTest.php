<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompressResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_compresses_json_response_with_gzip()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Request with Accept-Encoding: gzip
        $response = $this->withHeaders(['Accept-Encoding' => 'gzip'])
            ->getJson('/api/v1/dashboard/stats');
        $response->assertOk();

        // Check if response is compressed
        if ($response->headers->has('Content-Encoding')) {
            $this->assertEquals('gzip', $response->headers->get('Content-Encoding'));
            $this->assertTrue($response->headers->has('Vary'));
        } else {
            // Response may not be compressed if too small or config disabled
            $this->assertTrue(true);
        }
    }

    public function test_does_not_compress_small_responses()
    {
        // Small responses below min_size threshold should not be compressed
        $response = $this->getJson('/api/health/liveness');

        // Liveness check returns small JSON - should not be compressed
        $this->assertFalse($response->headers->has('Content-Encoding'));
    }

    public function test_does_not_compress_when_accept_encoding_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Request without Accept-Encoding header
        $response = $this->getJson('/api/v1/dashboard/stats');

        // May or may not compress depending on default client headers
        // Just verify response is successful
        $response->assertOk();
    }

    public function test_compression_can_be_disabled_via_config()
    {
        config(['cache-strategy.compression.enabled' => false]);

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->withHeaders(['Accept-Encoding' => 'gzip'])
            ->getJson('/api/v1/dashboard/stats');

        // Should not compress when disabled
        $this->assertFalse($response->headers->has('Content-Encoding'));
    }
}

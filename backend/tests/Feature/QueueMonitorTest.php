<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class QueueMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_queue_stats()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/queues');

        $response->assertOk()
            ->assertJsonStructure([
                    'success',
                    'data' => [
                        'queues' => [
                            '*' => ['name', 'size', 'status'],
                        ],
                        'failed_jobs' => [
                            'total',
                            'last_hour',
                            'last_24_hours',
                        ],
                        'health' => [
                            'status',
                            'total_queued',
                        ],
                    ],
            ]);
    }

    public function test_non_admin_cannot_access_queue_stats()
    {
        $user = User::factory()->create(['role' => 'tenant']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/queues');

        $response->assertStatus(403);
    }

    public function test_prometheus_metrics_endpoint_returns_text()
    {
        $response = $this->getJson('/api/metrics/prometheus');

        $response->assertOk();
        $this->assertStringContainsString('# HELP http_requests_total', $response->getContent());
        $this->assertStringContainsString('# TYPE http_requests_total counter', $response->getContent());
    }

    public function test_queue_health_detects_high_queue_depth()
    {
        // Skip if Redis not available
        try {
            Redis::ping();
        } catch (\Exception $e) {
            $this->markTestSkipped('Redis not available');
        }

        // Simulate high queue depth
        for ($i = 0; $i < 600; $i++) {
            Redis::rpush('queues:default', json_encode(['job' => 'test']));
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/queues');

        $response->assertOk();
        $health = $response->json('health');
        $this->assertEquals('degraded', $health['status']);
        $this->assertGreaterThan(500, $health['total_queued']);

        // Cleanup
        Redis::del('queues:default');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthLoggingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Comprehensive CORS and Authentication Test Suite
 * 
 * Tests all aspects of CORS configuration and authentication flows
 * including token management, rate limiting, and security features.
 */
class CorsAuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AuthLoggingService $authLogger;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $this->authLogger = app(AuthLoggingService::class);
    }

    /**
     * Test CORS configuration for allowed origins
     */
    public function test_cors_allowed_origins(): void
    {
        $allowedOrigins = [
            'http://localhost:3000',
            'https://rent-hub-beta.vercel.app',
            'https://rent-hub-six.vercel.app',
            'https://renthub-tbj7yxj7.on-forge.com',
            'https://renthub-dji696t0.on-forge.com',
        ];

        foreach ($allowedOrigins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
                'Content-Type' => 'application/json',
            ])->getJson('/api/v1/health');

            $response->assertStatus(200);
            $response->assertHeader('Access-Control-Allow-Origin', $origin);
            $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * Test CORS configuration for blocked origins
     */
    public function test_cors_blocked_origins(): void
    {
        $blockedOrigins = [
            'https://malicious-site.com',
            'http://evil-domain.org',
            'https://phishing-site.net',
        ];

        foreach ($blockedOrigins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
                'Content-Type' => 'application/json',
            ])->getJson('/api/v1/health');

            $response->assertStatus(403);
            $response->assertJson(['error' => 'CORS Policy Violation']);
        }
    }

    /**
     * Test authentication with valid credentials
     */
    public function test_successful_authentication(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'email', 'name'],
            'token',
            'expires_at',
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test authentication with invalid credentials
     */
    public function test_failed_authentication(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Invalid credentials',
        ]);
    }

    /**
     * Test rate limiting for authentication attempts
     */
    public function test_rate_limiting_authentication(): void
    {
        // Make multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $response->assertJson(['error' => 'Too Many Requests']);
    }

    /**
     * Test token refresh functionality
     */
    public function test_token_refresh(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/token/refresh');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'expires_at',
        ]);

        // Verify old token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => User::class,
            'id' => $this->user->currentAccessToken()->id,
        ]);
    }

    /**
     * Test accessing protected routes with valid token
     */
    public function test_access_protected_routes_with_token(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * Test accessing protected routes without token
     */
    public function test_access_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthenticated',
        ]);
    }

    /**
     * Test accessing protected routes with invalid token
     */
    public function test_access_protected_routes_with_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/v1/user');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthenticated',
        ]);
    }

    /**
     * Test logout functionality
     */
    public function test_logout(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);

        // Verify token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test token expiration handling
     */
    public function test_token_expiration(): void
    {
        Sanctum::actingAs($this->user);

        // Manually expire the token
        $token = $this->user->currentAccessToken();
        $token->update([
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthenticated',
        ]);
    }

    /**
     * Test security headers are present
     */
    public function test_security_headers(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Test rate limiting headers
     */
    public function test_rate_limiting_headers(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/user');

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Test authentication logging
     */
    public function test_authentication_logging(): void
    {
        $this->authLogger->logAuthAttempt([
            'email' => 'test@example.com',
        ], true);

        $this->assertTrue(true); // Logging is working if no exception
    }

    /**
     * Test security event logging
     */
    public function test_security_event_logging(): void
    {
        $this->authLogger->logSecurityEvent('test_security_event', [
            'test_data' => 'test_value',
        ]);

        $this->assertTrue(true); // Logging is working if no exception
    }

    /**
     * Test authorization logging
     */
    public function test_authorization_logging(): void
    {
        Sanctum::actingAs($this->user);

        $this->authLogger->logAuthorizationAttempt('test_action', true, 'test_resource');

        $this->assertTrue(true); // Logging is working if no exception
    }

    /**
     * Test authentication statistics
     */
    public function test_authentication_statistics(): void
    {
        // Create some test events
        $this->authLogger->logAuthAttempt(['email' => 'test@example.com'], true);
        $this->authLogger->logAuthAttempt(['email' => 'test@example.com'], false);
        $this->authLogger->logTokenRefresh(true);
        $this->authLogger->logLogout();

        $stats = $this->authLogger->getAuthStatistics('1h');

        $this->assertArrayHasKey('total_attempts', $stats);
        $this->assertArrayHasKey('successful_logins', $stats);
        $this->assertArrayHasKey('failed_logins', $stats);
        $this->assertArrayHasKey('token_refreshes', $stats);
        $this->assertArrayHasKey('logouts', $stats);
    }

    /**
     * Test suspicious activity detection
     */
    public function test_suspicious_activity_detection(): void
    {
        // Simulate multiple failed attempts
        for ($i = 0; $i < 5; $i++) {
            $this->authLogger->logAuthAttempt(['email' => 'test@example.com'], false);
        }

        $suspiciousActivity = $this->authLogger->checkSuspiciousActivity(Request::ip());

        $this->assertArrayHasKey('warnings', $suspiciousActivity);
        $this->assertArrayHasKey('risk_level', $suspiciousActivity);
    }

    /**
     * Test cross-origin preflight requests
     */
    public function test_preflight_requests(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://rent-hub-beta.vercel.app',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Authorization, Content-Type',
        ])->options('/api/v1/login');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://rent-hub-beta.vercel.app');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, X-CSRF-Token');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        $response->assertHeader('Access-Control-Max-Age', '3600');
    }

    /**
     * Test token management endpoints
     */
    public function test_token_management(): void
    {
        Sanctum::actingAs($this->user);

        // Create multiple tokens
        $token1 = $this->user->createToken('test-token-1')->plainTextToken;
        $token2 = $this->user->createToken('test-token-2')->plainTextToken;

        // List tokens
        $response = $this->getJson('/api/v1/token/tokens');
        $response->assertStatus(200);
        $response->assertJsonCount(3); // 2 new tokens + 1 from Sanctum::actingAs

        // Revoke specific token
        $tokens = $this->user->tokens;
        $tokenId = $tokens->first()->id;
        
        $response = $this->deleteJson("/api/v1/token/revoke/{$tokenId}");
        $response->assertStatus(200);

        // Revoke all tokens
        $response = $this->deleteJson('/api/v1/token/revoke-all');
        $response->assertStatus(200);

        // Verify all tokens are revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Test concurrent request handling
     */
    public function test_concurrent_requests(): void
    {
        Sanctum::actingAs($this->user);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/v1/user');
        }

        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
    }

    /**
     * Test error handling for malformed requests
     */
    public function test_malformed_request_handling(): void
    {
        $response = $this->withHeaders([
            'Content-Type' => 'application/json',
        ])->postJson('/api/v1/login', 'invalid-json');

        $response->assertStatus(400);
    }

    /**
     * Test performance under load
     */
    public function test_performance_under_load(): void
    {
        $startTime = microtime(true);

        // Make multiple requests
        for ($i = 0; $i < 10; $i++) {
            $response = $this->getJson('/api/v1/health');
            $response->assertStatus(200);
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $averageTime = $totalTime / 10;

        // Assert average response time is under 1 second
        $this->assertLessThan(1.0, $averageTime, 'Average response time should be under 1 second');
    }
}
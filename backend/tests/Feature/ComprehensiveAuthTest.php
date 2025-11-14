<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Comprehensive Authentication System Tests
 * 
 * Tests all aspects of the authentication system including:
 * - Token creation and validation
 * - Token refresh flows
 * - Rate limiting
 * - Session management
 * - Security measures
 */
class ComprehensiveAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Test basic authentication flow
     */
    public function test_basic_authentication_flow(): void
    {
        // Test login
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'access_token',
                'token_type',
            ]);

        $token = $response->json('access_token');

        // Test authenticated request
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'email', 'email_verified_at',
            ]);

        // Test logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        // Verify token is invalid after logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    /**
     * Test token refresh functionality
     */
    public function test_token_refresh_functionality(): void
    {
        // Authenticate user
        Sanctum::actingAs($this->user);
        
        // Get current token
        $oldToken = $this->user->createToken('test_token');

        // Test token refresh
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$oldToken->plainTextToken}",
        ])->postJson('/api/v1/token/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'expires_at',
                'user' => ['id', 'name', 'email'],
            ]);

        $newToken = $response->json('access_token');

        // Verify old token is invalid
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$oldToken->plainTextToken}",
        ])->getJson('/api/v1/me');

        $response->assertStatus(401);

        // Verify new token works
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$newToken}",
        ])->getJson('/api/v1/me');

        $response->assertStatus(200);
    }

    /**
     * Test token listing functionality
     */
    public function test_token_listing_functionality(): void
    {
        Sanctum::actingAs($this->user);
        
        // Create multiple tokens
        $token1 = $this->user->createToken('token_1');
        $token2 = $this->user->createToken('token_2');

        // Test listing tokens
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token1->plainTextToken}",
        ])->getJson('/api/v1/token/tokens');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tokens',
                'total_active',
                'timestamp',
            ]);

        $tokens = $response->json('tokens');
        $this->assertCount(2, $tokens);
    }

    /**
     * Test rate limiting for authentication
     */
    public function test_rate_limiting_for_authentication(): void
    {
        // Test login rate limiting
        for ($i = 0; $i < 25; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Should be rate limited after 20 attempts
        $response->assertStatus(429)
            ->assertJsonStructure([
                'error',
                'message',
                'retry_after',
            ]);
    }

    /**
     * Test unauthenticated access
     */
    public function test_unauthenticated_access(): void
    {
        // Try to access protected endpoint without token
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthenticated',
                'message' => 'Authentication required. Please provide a valid token.',
            ]);
    }

    /**
     * Test invalid token handling
     */
    public function test_invalid_token_handling(): void
    {
        // Use invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_12345',
        ])->getJson('/api/v1/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthenticated',
                'message' => 'Authentication required. Please provide a valid token.',
            ]);
    }
}
<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

trait TestHelper
{
    use RefreshDatabase;

    protected function authenticateUser($role = 'guest', $attributes = [])
    {
        // Map legacy aliases to canonical roles used by middleware
        $canonicalRole = match ($role) {
            'guest' => 'tenant',
            'host' => 'owner',
            default => $role,
        };

        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            // Keep legacy 'role' column in sync for controllers still checking it
            'role' => $canonicalRole,
        ], $attributes));

        $user->assignRole($canonicalRole);
        Sanctum::actingAs($user);

        return $user;
    }

    protected function authenticateHost($attributes = [])
    {
        return $this->authenticateUser('owner', $attributes);
    }

    protected function authenticateGuest($attributes = [])
    {
        return $this->authenticateUser('tenant', $attributes);
    }

    protected function authenticateAdmin($attributes = [])
    {
        return $this->authenticateUser('admin', $attributes);
    }

    protected function getValidToken($user = null)
    {
        if (! $user) {
            $user = User::factory()->create();
        }

        return $user->createToken('test-token')->plainTextToken;
    }

    protected function assertValidationError($response, $field)
    {
        $response->assertStatus(422)
            ->assertJsonValidationErrors($field);
    }

    protected function assertSuccessResponse($response, $message = null)
    {
        $response->assertSuccessful();

        if ($message) {
            $response->assertJson(['message' => $message]);
        }
    }

    protected function assertApiResource($response, $resourceClass)
    {
        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [],
            ]);
    }
}

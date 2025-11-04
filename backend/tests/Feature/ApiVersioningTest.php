<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiVersioningTest extends TestCase
{
    public function test_api_v1_endpoint_works()
    {
        $response = $this->getJson('/api/v1/properties');

        $response->assertStatus(200);
    }

    public function test_api_version_header_works()
    {
        $response = $this->withHeaders([
            'X-API-Version' => 'v1',
        ])->getJson('/api/properties');

        $response->assertStatus(200);
    }

    public function test_invalid_version_returns_error()
    {
        $response = $this->getJson('/api/v99/properties');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Unsupported API version',
            ]);
    }

    public function test_default_version_used_when_not_specified()
    {
        $response = $this->getJson('/api/properties');

        $response->assertStatus(200);
    }
}

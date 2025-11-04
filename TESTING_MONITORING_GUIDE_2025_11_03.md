# 🧪 Complete Testing & Monitoring Guide
## RentHub - Security, Performance & Marketing
**Version:** 2.0.0  
**Date:** November 3, 2025

---

## 📋 Table of Contents
1. [Security Testing](#security-testing)
2. [Performance Testing](#performance-testing)
3. [UI/UX Testing](#uiux-testing)
4. [API Testing](#api-testing)
5. [Monitoring & Logging](#monitoring-logging)
6. [Automated Testing](#automated-testing)

---

## 🔐 Security Testing

### 1. Authentication Tests

```php
// tests/Feature/Auth/OAuthTest.php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

class OAuthTest extends TestCase
{
    public function test_google_oauth_redirect()
    {
        $response = $this->get('/auth/google');
        
        $this->assertEquals(302, $response->status());
        $this->assertStringContainsString('google', $response->headers->get('Location'));
    }

    public function test_google_oauth_callback_creates_user()
    {
        $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
        $abstractUser->shouldReceive('getId')->andReturn('123456');
        $abstractUser->shouldReceive('getName')->andReturn('John Doe');
        $abstractUser->shouldReceive('getEmail')->andReturn('john@example.com');
        
        Socialite::shouldReceive('driver->stateless->user')->andReturn($abstractUser);
        
        $response = $this->get('/auth/google/callback');
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'oauth_provider' => 'google',
        ]);
    }

    public function test_jwt_token_refresh()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->post('/api/auth/refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'expires_at']);
    }

    public function test_invalid_oauth_provider_returns_404()
    {
        $response = $this->get('/auth/invalid-provider');
        
        $response->assertStatus(404);
    }
}
```

### 2. RBAC Tests

```php
// tests/Feature/Auth/RBACTest.php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class RBACTest extends TestCase
{
    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create(['slug' => 'admin']);
        $admin->roles()->attach($adminRole);

        $response = $this->actingAs($admin)
                         ->get('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->get('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_role_has_correct_permissions()
    {
        $role = Role::factory()->create([
            'slug' => 'host',
            'permissions' => ['create_property', 'edit_property']
        ]);

        $this->assertTrue($role->hasPermission('create_property'));
        $this->assertFalse($role->hasPermission('delete_user'));
    }
}
```

### 3. Security Headers Tests

```php
// tests/Feature/Security/SecurityHeadersTest.php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Strict-Transport-Security');
        $response->assertHeader('Referrer-Policy');
    }

    public function test_csp_header_is_set()
    {
        $response = $this->get('/');

        $this->assertTrue($response->headers->has('Content-Security-Policy'));
    }
}
```

### 4. Rate Limiting Tests

```php
// tests/Feature/Security/RateLimitTest.php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class RateLimitTest extends TestCase
{
    public function test_api_rate_limiting()
    {
        $maxAttempts = 60;
        
        for ($i = 0; $i < $maxAttempts + 1; $i++) {
            $response = $this->getJson('/api/properties');
        }

        $this->assertEquals(429, $response->status());
        $response->assertJson(['error' => 'Too Many Requests']);
    }

    public function test_rate_limit_headers_present()
    {
        $response = $this->getJson('/api/properties');

        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }
}
```

### 5. GDPR Compliance Tests

```php
// tests/Feature/Security/GDPRTest.php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Services\GDPRService;

class GDPRTest extends TestCase
{
    protected $gdprService;

    public function setUp(): void
    {
        parent::setUp();
        $this->gdprService = new GDPRService();
    }

    public function test_user_data_can_be_exported()
    {
        $user = User::factory()->create();

        $data = $this->gdprService->exportUserData($user);

        $this->assertArrayHasKey('personal_information', $data);
        $this->assertArrayHasKey('bookings', $data);
        $this->assertArrayHasKey('properties', $data);
    }

    public function test_user_data_can_be_anonymized()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '1234567890'
        ]);

        $result = $this->gdprService->anonymizeUser($user);

        $this->assertTrue($result);
        $this->assertNotEquals('test@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->phone);
    }

    public function test_user_data_can_be_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $result = $this->gdprService->deleteUserData($user);

        $this->assertTrue($result);
        $this->assertSoftDeleted('users', ['id' => $userId]);
    }
}
```

---

## ⚡ Performance Testing

### 1. Database Query Tests

```php
// tests/Feature/Performance/DatabaseTest.php
<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class DatabaseTest extends TestCase
{
    public function test_property_listing_query_count()
    {
        Property::factory()->count(50)->create();

        DB::enableQueryLog();
        
        $response = $this->getJson('/api/properties');
        
        $queryCount = count(DB::getQueryLog());
        
        // Should use eager loading to keep query count low
        $this->assertLessThan(5, $queryCount);
    }

    public function test_n_plus_one_query_prevention()
    {
        Property::factory()->count(10)->create();

        DB::enableQueryLog();
        
        Property::with(['user', 'amenities'])->get();
        
        $queryCount = count(DB::getQueryLog());
        
        // Should be 3 queries max (properties, users, amenities)
        $this->assertLessThanOrEqual(3, $queryCount);
    }
}
```

### 2. Caching Tests

```php
// tests/Feature/Performance/CachingTest.php
<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;

class CachingTest extends TestCase
{
    public function test_properties_are_cached()
    {
        Property::factory()->count(10)->create();

        $response1 = $this->getJson('/api/properties');
        $response2 = $this->getJson('/api/properties');

        $this->assertEquals($response1->json(), $response2->json());
        $this->assertTrue(Cache::has('properties_list'));
    }

    public function test_cache_invalidation_on_update()
    {
        $property = Property::factory()->create();
        Cache::tags(['properties'])->put("property:{$property->id}", $property, 3600);

        $property->update(['title' => 'Updated Title']);

        $this->assertFalse(Cache::tags(['properties'])->has("property:{$property->id}"));
    }

    public function test_redis_connection()
    {
        $result = Cache::store('redis')->put('test_key', 'test_value', 60);
        
        $this->assertTrue($result);
        $this->assertEquals('test_value', Cache::store('redis')->get('test_key'));
    }
}
```

### 3. Response Time Tests

```php
// tests/Feature/Performance/ResponseTimeTest.php
<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\Property;

class ResponseTimeTest extends TestCase
{
    public function test_api_response_time_is_acceptable()
    {
        Property::factory()->count(50)->create();

        $startTime = microtime(true);
        
        $response = $this->getJson('/api/properties');
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);
        $this->assertLessThan(200, $responseTime, 'API response time should be less than 200ms');
    }

    public function test_property_detail_loads_quickly()
    {
        $property = Property::factory()->create();

        $startTime = microtime(true);
        
        $response = $this->getJson("/api/properties/{$property->id}");
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(100, $responseTime);
    }
}
```

### 4. Load Testing Script

```bash
#!/bin/bash
# load-test.sh

echo "🔥 Running Load Tests..."
echo ""

# Install Apache Bench if not installed
if ! command -v ab &> /dev/null; then
    echo "Installing Apache Bench..."
    apt-get install apache2-utils -y
fi

# Test endpoints
ENDPOINTS=(
    "http://localhost:8000/api/properties"
    "http://localhost:8000/api/bookings"
    "http://localhost:8000/api/reviews"
)

REQUESTS=1000
CONCURRENCY=50

for endpoint in "${ENDPOINTS[@]}"; do
    echo "Testing: $endpoint"
    echo "Requests: $REQUESTS, Concurrency: $CONCURRENCY"
    echo ""
    
    ab -n $REQUESTS -c $CONCURRENCY -g results.tsv "$endpoint"
    
    echo ""
    echo "---"
    echo ""
done

echo "✅ Load tests complete!"
echo "Results saved to results.tsv"
```

---

## 🎨 UI/UX Testing

### 1. Accessibility Tests

```typescript
// frontend/tests/accessibility.test.tsx
import { render } from '@testing-library/react';
import { axe, toHaveNoViolations } from 'jest-axe';
import { Button } from '@/components/ui/Button';

expect.extend(toHaveNoViolations);

describe('Accessibility Tests', () => {
  it('Button should have no accessibility violations', async () => {
    const { container } = render(<Button>Click me</Button>);
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  });

  it('Form inputs should have proper labels', async () => {
    const { container } = render(
      <form>
        <label htmlFor="email">Email</label>
        <input id="email" type="email" />
      </form>
    );
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  });

  it('Images should have alt text', async () => {
    const { container } = render(
      <img src="/test.jpg" alt="Test image" />
    );
    const results = await axe(container);
    expect(results).toHaveNoViolations();
  });
});
```

### 2. Component Tests

```typescript
// frontend/tests/components/Button.test.tsx
import { render, screen, fireEvent } from '@testing-library/react';
import { Button } from '@/components/ui/Button';

describe('Button Component', () => {
  it('renders correctly', () => {
    render(<Button>Click me</Button>);
    expect(screen.getByText('Click me')).toBeInTheDocument();
  });

  it('handles click events', () => {
    const handleClick = jest.fn();
    render(<Button onClick={handleClick}>Click me</Button>);
    
    fireEvent.click(screen.getByText('Click me'));
    expect(handleClick).toHaveBeenCalledTimes(1);
  });

  it('shows loading state', () => {
    render(<Button isLoading>Click me</Button>);
    expect(screen.getByText('Loading...')).toBeInTheDocument();
  });

  it('is disabled when loading', () => {
    render(<Button isLoading>Click me</Button>);
    const button = screen.getByRole('button');
    expect(button).toBeDisabled();
  });

  it('applies correct variant classes', () => {
    const { rerender } = render(<Button variant="destructive">Delete</Button>);
    let button = screen.getByRole('button');
    expect(button).toHaveClass('bg-red-600');

    rerender(<Button variant="outline">Cancel</Button>);
    button = screen.getByRole('button');
    expect(button).toHaveClass('border');
  });
});
```

### 3. E2E Tests with Playwright

```typescript
// frontend/e2e/booking-flow.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Booking Flow', () => {
  test('should complete full booking process', async ({ page }) => {
    // Navigate to property listing
    await page.goto('http://localhost:3000/properties');
    
    // Search for property
    await page.fill('[name="search"]', 'New York');
    await page.click('button[type="submit"]');
    
    // Wait for results
    await page.waitForSelector('.property-card');
    
    // Click first property
    await page.click('.property-card:first-child');
    
    // Verify property details loaded
    await expect(page.locator('h1')).toBeVisible();
    
    // Select dates
    await page.fill('[name="checkIn"]', '2025-12-01');
    await page.fill('[name="checkOut"]', '2025-12-05');
    
    // Click book button
    await page.click('button:has-text("Book Now")');
    
    // Verify booking page
    await expect(page).toHaveURL(/.*booking.*/);
    
    // Fill guest information
    await page.fill('[name="name"]', 'John Doe');
    await page.fill('[name="email"]', 'john@example.com');
    await page.fill('[name="phone"]', '1234567890');
    
    // Complete booking
    await page.click('button:has-text("Complete Booking")');
    
    // Verify success
    await expect(page.locator('.success-message')).toBeVisible();
  });

  test('should show validation errors', async ({ page }) => {
    await page.goto('http://localhost:3000/properties/1');
    
    // Click book without selecting dates
    await page.click('button:has-text("Book Now")');
    
    // Verify error message
    await expect(page.locator('.error-message')).toContainText('Please select dates');
  });
});
```

---

## 🔧 API Testing

### Postman Collection

```json
{
  "info": {
    "name": "RentHub Complete API Tests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "OAuth - Google Redirect",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{baseUrl}}/auth/google",
              "host": ["{{baseUrl}}"],
              "path": ["auth", "google"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code is 302', function () {",
                  "    pm.response.to.have.status(302);",
                  "});",
                  "",
                  "pm.test('Redirects to Google', function () {",
                  "    pm.expect(pm.response.headers.get('Location')).to.include('google');",
                  "});"
                ]
              }
            }
          ]
        },
        {
          "name": "Token Refresh",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{accessToken}}"
              }
            ],
            "url": {
              "raw": "{{baseUrl}}/api/auth/refresh",
              "host": ["{{baseUrl}}"],
              "path": ["api", "auth", "refresh"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code is 200', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "",
                  "pm.test('Returns new token', function () {",
                  "    var jsonData = pm.response.json();",
                  "    pm.expect(jsonData).to.have.property('token');",
                  "    pm.environment.set('accessToken', jsonData.token);",
                  "});"
                ]
              }
            }
          ]
        }
      ]
    },
    {
      "name": "Security",
      "item": [
        {
          "name": "Rate Limiting Test",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{baseUrl}}/api/properties",
              "host": ["{{baseUrl}}"],
              "path": ["api", "properties"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "if (pm.response.code === 429) {",
                  "    pm.test('Rate limit triggered', function () {",
                  "        pm.response.to.have.status(429);",
                  "    });",
                  "} else {",
                  "    pm.test('Within rate limit', function () {",
                  "        pm.response.to.have.status(200);",
                  "    });",
                  "}"
                ]
              }
            }
          ]
        },
        {
          "name": "Security Headers Check",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{baseUrl}}/api/properties",
              "host": ["{{baseUrl}}"],
              "path": ["api", "properties"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Security headers present', function () {",
                  "    pm.response.to.have.header('X-Frame-Options');",
                  "    pm.response.to.have.header('X-Content-Type-Options');",
                  "    pm.response.to.have.header('X-XSS-Protection');",
                  "});"
                ]
              }
            }
          ]
        }
      ]
    },
    {
      "name": "Performance",
      "item": [
        {
          "name": "Response Time Test",
          "request": {
            "method": "GET",
            "header": [],
            "url": {
              "raw": "{{baseUrl}}/api/properties",
              "host": ["{{baseUrl}}"],
              "path": ["api", "properties"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Response time is less than 200ms', function () {",
                  "    pm.expect(pm.response.responseTime).to.be.below(200);",
                  "});",
                  "",
                  "pm.test('Response is compressed', function () {",
                  "    pm.response.to.have.header('Content-Encoding');",
                  "});"
                ]
              }
            }
          ]
        }
      ]
    }
  ]
}
```

---

## 📊 Monitoring & Logging

### 1. Application Monitoring

```php
// app/Http/Middleware/PerformanceMonitoring.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PerformanceMonitoring
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000;
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024;

        // Log slow requests
        if ($executionTime > 1000) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_used' => $memoryUsed . 'MB',
                'user_id' => auth()->id(),
            ]);
        }

        $response->headers->set('X-Execution-Time', $executionTime . 'ms');

        return $response;
    }
}
```

### 2. Health Check Endpoint

```php
// app/Http/Controllers/API/HealthController.php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function check()
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'services' => []
        ];

        // Database check
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['database'] = 'unhealthy';
            $health['status'] = 'unhealthy';
        }

        // Redis check
        try {
            Redis::ping();
            $health['services']['redis'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['redis'] = 'unhealthy';
        }

        // Cache check
        try {
            Cache::put('health_check', 'ok', 10);
            $health['services']['cache'] = 'healthy';
        } catch (\Exception $e) {
            $health['services']['cache'] = 'unhealthy';
        }

        $statusCode = $health['status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    public function metrics()
    {
        return response()->json([
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . 'MB',
            'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024 . 'MB',
            'cpu_load' => sys_getloadavg(),
            'uptime' => $this->getUptime(),
            'disk_usage' => $this->getDiskUsage(),
        ]);
    }

    private function getUptime()
    {
        $uptime = file_get_contents('/proc/uptime');
        return explode(' ', $uptime)[0] . ' seconds';
    }

    private function getDiskUsage()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $total - $free;
        
        return [
            'total' => $this->formatBytes($total),
            'used' => $this->formatBytes($used),
            'free' => $this->formatBytes($free),
            'percentage' => round(($used / $total) * 100, 2) . '%'
        ];
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

### 3. Prometheus Monitoring

```php
// config/prometheus.php
<?php

return [
    'namespace' => 'renthub',
    
    'metrics_route_enabled' => true,
    'metrics_route_path' => 'metrics',
    'metrics_route_middleware' => ['api', 'auth:api'],
    
    'collectors' => [
        \App\Prometheus\RequestCollector::class,
        \App\Prometheus\DatabaseCollector::class,
        \App\Prometheus\CacheCollector::class,
    ],
];
```

---

## 🔄 Continuous Integration

### GitHub Actions Workflow

```yaml
# .github/workflows/test.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: renthub_test
        ports:
          - 3306:3306
      
      redis:
        image: redis:7
        ports:
          - 6379:6379

    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql, redis
      
      - name: Install Dependencies
        working-directory: ./backend
        run: composer install --no-interaction
      
      - name: Run Tests
        working-directory: ./backend
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: renthub_test
          REDIS_HOST: 127.0.0.1
        run: php artisan test --parallel
      
      - name: Upload Coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./backend/coverage.xml

  frontend-tests:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      
      - name: Install Dependencies
        working-directory: ./frontend
        run: npm ci
      
      - name: Run Tests
        working-directory: ./frontend
        run: npm run test
      
      - name: Run E2E Tests
        working-directory: ./frontend
        run: npx playwright test

  security-scan:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Run Security Scan
        uses: snyk/actions/php@master
        env:
          SNYK_TOKEN: ${{ secrets.SNYK_TOKEN }}
        with:
          command: test
```

---

## 📈 Success Metrics

### KPIs to Track

1. **Security**
   - Failed authentication attempts
   - Security audit log entries
   - Vulnerability scan results
   - SSL certificate status

2. **Performance**
   - API response time (avg, p95, p99)
   - Database query time
   - Cache hit rate
   - Memory usage

3. **Availability**
   - Uptime percentage
   - Error rate
   - Failed requests
   - Service health status

4. **User Experience**
   - Page load time
   - Time to interactive
   - First contentful paint
   - Cumulative layout shift

---

## 🆘 Support

For issues or questions:
- 📧 Email: support@renthub.com
- 💬 Slack: #renthub-testing
- 📖 Docs: https://docs.renthub.com

---

**Status:** ✅ Comprehensive testing suite implemented  
**Last Updated:** November 3, 2025  
**Version:** 2.0.0

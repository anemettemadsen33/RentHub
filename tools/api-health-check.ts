#!/usr/bin/env tsx
/**
 * API Health Check Script
 * 
 * This script validates all critical API endpoints to ensure they're working correctly.
 * It can run against local development, staging, or production environments.
 * 
 * Usage:
 *   npx tsx tools/api-health-check.ts
 *   npx tsx tools/api-health-check.ts --env production
 *   npx tsx tools/api-health-check.ts --env local
 */

interface EndpointTest {
  name: string;
  method: 'GET' | 'POST' | 'PUT' | 'DELETE';
  path: string;
  expectedStatus: number | number[];
  requiresAuth?: boolean;
  body?: Record<string, any>;
  headers?: Record<string, string>;
  validateResponse?: (data: any) => boolean;
}

interface TestResult {
  endpoint: string;
  passed: boolean;
  status?: number;
  duration?: number;
  error?: string;
  response?: any;
}

const ENVIRONMENTS = {
  local: {
    api: 'http://localhost:8000/api',
    name: 'Local Development',
  },
  production: {
    api: 'https://renthub-tbj7yxj7.on-forge.com/api',
    name: 'Production (Laravel Forge)',
  },
  staging: {
    api: 'http://staging.renthub.local/api',
    name: 'Staging',
  },
};

// Define all critical API endpoints to test
const ENDPOINTS: EndpointTest[] = [
  // Health Check Endpoints
  {
    name: 'Health Check',
    method: 'GET',
    path: '/health',
    expectedStatus: 200,
    validateResponse: (data) => data.status === 'ok' || data.status === 'healthy',
  },
  {
    name: 'Health Liveness',
    method: 'GET',
    path: '/health/liveness',
    expectedStatus: 200,
  },
  {
    name: 'Health Readiness',
    method: 'GET',
    path: '/health/readiness',
    expectedStatus: 200,
  },
  {
    name: 'Production Health',
    method: 'GET',
    path: '/health/production',
    expectedStatus: [200, 500], // May fail if dependencies are down
  },

  // Public API Endpoints
  {
    name: 'Public Settings',
    method: 'GET',
    path: '/v1/settings/public',
    expectedStatus: 200,
    validateResponse: (data) => typeof data === 'object',
  },
  {
    name: 'Languages List',
    method: 'GET',
    path: '/v1/languages',
    expectedStatus: 200,
    validateResponse: (data) => Array.isArray(data) || Array.isArray(data.data),
  },
  {
    name: 'Default Language',
    method: 'GET',
    path: '/v1/languages/default',
    expectedStatus: 200,
  },
  {
    name: 'Properties List',
    method: 'GET',
    path: '/v1/properties',
    expectedStatus: 200,
    validateResponse: (data) => {
      return (
        (Array.isArray(data) || Array.isArray(data.data)) ||
        (typeof data === 'object' && 'data' in data)
      );
    },
  },
  {
    name: 'Properties with API Version Header',
    method: 'GET',
    path: '/properties',
    expectedStatus: 200,
    headers: {
      'X-API-Version': 'v1',
    },
  },
  {
    name: 'Amenities List',
    method: 'GET',
    path: '/v1/amenities',
    expectedStatus: 200,
  },
  {
    name: 'Currencies List',
    method: 'GET',
    path: '/v1/currencies',
    expectedStatus: 200,
  },

  // Authentication Endpoints (expected to return errors without credentials)
  {
    name: 'Login (No Credentials)',
    method: 'POST',
    path: '/v1/auth/login',
    expectedStatus: [422, 401], // Validation error or unauthorized
    body: {},
  },
  {
    name: 'Register (No Data)',
    method: 'POST',
    path: '/v1/auth/register',
    expectedStatus: 422, // Validation error
    body: {},
  },
  {
    name: 'User Profile (No Auth)',
    method: 'GET',
    path: '/v1/user',
    expectedStatus: 401, // Unauthorized
  },

  // Protected Endpoints (should return 401 without auth)
  {
    name: 'Bookings (No Auth)',
    method: 'GET',
    path: '/v1/bookings',
    expectedStatus: 401,
  },
  {
    name: 'Messages (No Auth)',
    method: 'GET',
    path: '/v1/conversations',
    expectedStatus: 401,
  },
  {
    name: 'Favorites (No Auth)',
    method: 'GET',
    path: '/v1/favorites',
    expectedStatus: 401,
  },
  {
    name: 'Dashboard (No Auth)',
    method: 'GET',
    path: '/v1/user/dashboard',
    expectedStatus: 401,
  },
];

class APIHealthChecker {
  private baseUrl: string;
  private envName: string;
  private results: TestResult[] = [];

  constructor(environment: keyof typeof ENVIRONMENTS = 'local') {
    const env = ENVIRONMENTS[environment];
    this.baseUrl = env.api;
    this.envName = env.name;
  }

  private async testEndpoint(endpoint: EndpointTest): Promise<TestResult> {
    const startTime = Date.now();
    const url = `${this.baseUrl}${endpoint.path}`;

    try {
      const response = await fetch(url, {
        method: endpoint.method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...endpoint.headers,
        },
        body: endpoint.body ? JSON.stringify(endpoint.body) : undefined,
      });

      const duration = Date.now() - startTime;
      let data: any;

      try {
        data = await response.json();
      } catch {
        data = await response.text();
      }

      const expectedStatuses = Array.isArray(endpoint.expectedStatus)
        ? endpoint.expectedStatus
        : [endpoint.expectedStatus];

      const statusMatches = expectedStatuses.includes(response.status);
      const validationPasses = endpoint.validateResponse
        ? endpoint.validateResponse(data)
        : true;

      const passed = statusMatches && validationPasses;

      return {
        endpoint: endpoint.name,
        passed,
        status: response.status,
        duration,
        response: data,
        error: !passed
          ? `Expected status ${endpoint.expectedStatus}, got ${response.status}`
          : undefined,
      };
    } catch (error) {
      const duration = Date.now() - startTime;
      return {
        endpoint: endpoint.name,
        passed: false,
        duration,
        error: error instanceof Error ? error.message : String(error),
      };
    }
  }

  async runAllTests(): Promise<void> {
    console.log(`\n${'='.repeat(80)}`);
    console.log(`API Health Check - ${this.envName}`);
    console.log(`Base URL: ${this.baseUrl}`);
    console.log(`${'='.repeat(80)}\n`);

    console.log(`Testing ${ENDPOINTS.length} endpoints...\n`);

    for (const endpoint of ENDPOINTS) {
      process.stdout.write(`Testing ${endpoint.name.padEnd(40, '.')} `);
      const result = await this.testEndpoint(endpoint);
      this.results.push(result);

      if (result.passed) {
        console.log(`✅ PASS (${result.duration}ms)`);
      } else {
        console.log(`❌ FAIL`);
        if (result.error) {
          console.log(`   Error: ${result.error}`);
        }
      }
    }

    this.printSummary();
  }

  private printSummary(): void {
    const passed = this.results.filter((r) => r.passed).length;
    const failed = this.results.filter((r) => !r.passed).length;
    const total = this.results.length;
    const successRate = ((passed / total) * 100).toFixed(1);

    console.log(`\n${'='.repeat(80)}`);
    console.log(`Summary`);
    console.log(`${'='.repeat(80)}`);
    console.log(`Total Tests:    ${total}`);
    console.log(`Passed:         ${passed} (${successRate}%)`);
    console.log(`Failed:         ${failed}`);

    if (failed > 0) {
      console.log(`\nFailed Tests:`);
      this.results
        .filter((r) => !r.passed)
        .forEach((r) => {
          console.log(`  - ${r.endpoint}: ${r.error || 'Unknown error'}`);
        });
    }

    const avgDuration =
      this.results.reduce((sum, r) => sum + (r.duration || 0), 0) / total;
    console.log(`\nAverage Response Time: ${avgDuration.toFixed(0)}ms`);

    console.log(`${'='.repeat(80)}\n`);

    // Exit with error code if any tests failed
    if (failed > 0) {
      process.exit(1);
    }
  }

  async runWithRetry(maxRetries = 3, delayMs = 1000): Promise<void> {
    for (let attempt = 1; attempt <= maxRetries; attempt++) {
      console.log(`\nAttempt ${attempt} of ${maxRetries}...\n`);

      this.results = [];
      await this.runAllTests();

      const failed = this.results.filter((r) => !r.passed).length;

      if (failed === 0) {
        console.log(`✅ All tests passed on attempt ${attempt}!`);
        return;
      }

      if (attempt < maxRetries) {
        console.log(`\n⏳ Waiting ${delayMs}ms before retry...`);
        await new Promise((resolve) => setTimeout(resolve, delayMs));
      }
    }

    console.log(`\n❌ Tests failed after ${maxRetries} attempts.`);
    process.exit(1);
  }
}

// Parse command line arguments
const args = process.argv.slice(2);
const envArg = args.find((arg) => arg.startsWith('--env='))?.split('=')[1];
const envIndex = args.indexOf('--env');
const environment = (envArg || (envIndex !== -1 ? args[envIndex + 1] : 'local')) as keyof typeof ENVIRONMENTS;

const retry = args.includes('--retry');
const continuous = args.includes('--continuous');
const intervalArg = args.find((arg) => arg.startsWith('--interval='))?.split('=')[1];
const interval = intervalArg ? parseInt(intervalArg, 10) : 60000;

if (!ENVIRONMENTS[environment]) {
  console.error(`Invalid environment: ${environment}`);
  console.error(`Valid environments: ${Object.keys(ENVIRONMENTS).join(', ')}`);
  process.exit(1);
}

// Run the health check
async function main() {
  const checker = new APIHealthChecker(environment);

  if (continuous) {
    console.log(`Running continuous health checks every ${interval}ms...`);
    console.log('Press Ctrl+C to stop\n');

    while (true) {
      await checker.runAllTests();
      console.log(`\n⏳ Waiting ${interval}ms until next check...`);
      await new Promise((resolve) => setTimeout(resolve, interval));
    }
  } else if (retry) {
    await checker.runWithRetry();
  } else {
    await checker.runAllTests();
  }
}

main().catch((error) => {
  console.error('Fatal error:', error);
  process.exit(1);
});

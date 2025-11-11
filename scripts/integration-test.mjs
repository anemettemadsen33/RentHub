#!/usr/bin/env node

/**
 * Integration Test Script
 * Tests backend-frontend connectivity and key endpoints
 */

const BASE_URL = 'http://127.0.0.1:8000/api';

async function testEndpoint(name, url, options = {}) {
  try {
    console.log(`\n🧪 Testing: ${name}`);
    console.log(`   URL: ${url}`);
    
    const response = await fetch(url, options);
    const statusOk = response.ok ? '✅' : '❌';
    
    console.log(`   ${statusOk} Status: ${response.status}`);
    
    if (response.ok) {
      const contentType = response.headers.get('content-type');
      
      if (contentType?.includes('application/json')) {
        const data = await response.json();
        console.log(`   ✅ Response: JSON (${Object.keys(data).length} keys)`);
        if (data.success !== undefined) {
          console.log(`   ✅ Success: ${data.success}`);
        }
        return { success: true, data };
      } else if (contentType?.includes('text/plain')) {
        const text = await response.text();
        console.log(`   ✅ Response: Text (${text.split('\n').length} lines)`);
        return { success: true, data: text };
      }
    } else {
      const error = await response.text();
      console.log(`   ❌ Error: ${error.substring(0, 100)}...`);
      return { success: false, error };
    }
  } catch (error) {
    console.log(`   ❌ Failed: ${error.message}`);
    return { success: false, error: error.message };
  }
}

async function runTests() {
  console.log('🚀 RentHub Integration Tests\n');
  console.log('=' .repeat(50));
  
  const results = {
    passed: 0,
    failed: 0,
    tests: []
  };

  // Health Checks
  console.log('\n📊 HEALTH CHECKS');
  console.log('-'.repeat(50));
  
  const healthTests = [
    { name: 'Health Check', url: `${BASE_URL}/health` },
    { name: 'Liveness Probe', url: `${BASE_URL}/health/liveness` },
    { name: 'Readiness Probe', url: `${BASE_URL}/health/readiness` },
  ];

  for (const test of healthTests) {
    const result = await testEndpoint(test.name, test.url);
    results.tests.push({ ...test, ...result });
    result.success ? results.passed++ : results.failed++;
  }

  // Metrics
  console.log('\n📈 METRICS ENDPOINTS');
  console.log('-'.repeat(50));
  
  const metricsTests = [
    { name: 'JSON Metrics', url: `${BASE_URL}/metrics` },
    { name: 'Prometheus Metrics', url: `${BASE_URL}/metrics/prometheus` },
  ];

  for (const test of metricsTests) {
    const result = await testEndpoint(test.name, test.url);
    results.tests.push({ ...test, ...result });
    result.success ? results.passed++ : results.failed++;
  }

  // Public API Endpoints
  console.log('\n🌐 PUBLIC API ENDPOINTS');
  console.log('-'.repeat(50));
  
  const publicTests = [
    { name: 'Featured Properties', url: `${BASE_URL}/v1/properties/featured` },
    { name: 'Property Search', url: `${BASE_URL}/v1/properties/search?location=Paris` },
    { name: 'Languages', url: `${BASE_URL}/v1/languages` },
    { name: 'Currencies', url: `${BASE_URL}/v1/currencies` },
  ];

  for (const test of publicTests) {
    const result = await testEndpoint(test.name, test.url);
    results.tests.push({ ...test, ...result });
    result.success ? results.passed++ : results.failed++;
  }

  // Cache Testing
  console.log('\n💾 CACHE PERFORMANCE TEST');
  console.log('-'.repeat(50));
  
  const cacheUrl = `${BASE_URL}/v1/properties/featured`;
  
  console.log('\n🧪 Testing: Featured Properties (Cache Performance)');
  const start1 = Date.now();
  const firstCall = await fetch(cacheUrl);
  const time1 = Date.now() - start1;
  
  const start2 = Date.now();
  const secondCall = await fetch(cacheUrl);
  const time2 = Date.now() - start2;
  
  console.log(`   ⏱️  First call: ${time1}ms`);
  console.log(`   ⏱️  Second call (cached): ${time2}ms`);
  
  if (time2 < time1) {
    console.log(`   ✅ Cache working! ${((1 - time2/time1) * 100).toFixed(1)}% faster`);
    results.passed++;
  } else {
    console.log(`   ⚠️  Cache might not be active`);
    results.failed++;
  }
  
  results.tests.push({
    name: 'Cache Performance',
    url: cacheUrl,
    success: time2 < time1,
    data: { firstCall: time1, secondCall: time2 }
  });

  // ETag Support
  console.log('\n🏷️  ETAG SUPPORT TEST');
  console.log('-'.repeat(50));
  
  const etagUrl = `${BASE_URL}/v1/dashboard/stats`;
  
  console.log('\n🧪 Testing: Dashboard Stats (ETag)');
  console.log('   ⚠️  Note: Requires authentication, expecting 401');
  
  const etagResponse = await fetch(etagUrl);
  const etag = etagResponse.headers.get('etag');
  
  if (etag) {
    console.log(`   ✅ ETag header present: ${etag.substring(0, 20)}...`);
    results.passed++;
    results.tests.push({ name: 'ETag Support', url: etagUrl, success: true, data: { etag } });
  } else {
    console.log(`   ℹ️  ETag not present (auth required or not implemented)`);
    results.tests.push({ name: 'ETag Support', url: etagUrl, success: false });
  }

  // Compression Test
  console.log('\n🗜️  COMPRESSION TEST');
  console.log('-'.repeat(50));
  
  console.log('\n🧪 Testing: Response Compression');
  const compressResponse = await fetch(`${BASE_URL}/v1/properties/featured`, {
    headers: { 'Accept-Encoding': 'gzip, deflate, br' }
  });
  
  const encoding = compressResponse.headers.get('content-encoding');
  if (encoding) {
    console.log(`   ✅ Compression active: ${encoding}`);
    results.passed++;
    results.tests.push({ name: 'Response Compression', success: true, data: { encoding } });
  } else {
    console.log(`   ℹ️  No compression (response may be too small)`);
    results.tests.push({ name: 'Response Compression', success: false });
  }

  // CORS Headers
  console.log('\n🔒 CORS HEADERS TEST');
  console.log('-'.repeat(50));
  
  console.log('\n🧪 Testing: CORS Configuration');
  const corsResponse = await fetch(`${BASE_URL}/health`, {
    headers: { 'Origin': 'http://localhost:3000' }
  });
  
  const corsHeader = corsResponse.headers.get('access-control-allow-origin');
  if (corsHeader) {
    console.log(`   ✅ CORS configured: ${corsHeader}`);
    results.passed++;
    results.tests.push({ name: 'CORS Headers', success: true, data: { cors: corsHeader } });
  } else {
    console.log(`   ⚠️  CORS headers not found`);
    results.failed++;
    results.tests.push({ name: 'CORS Headers', success: false });
  }

  // Summary
  console.log('\n' + '='.repeat(50));
  console.log('📊 TEST SUMMARY');
  console.log('='.repeat(50));
  console.log(`\n✅ Passed: ${results.passed}`);
  console.log(`❌ Failed: ${results.failed}`);
  console.log(`📈 Success Rate: ${((results.passed / (results.passed + results.failed)) * 100).toFixed(1)}%`);
  
  if (results.failed === 0) {
    console.log('\n🎉 All tests passed! Backend-Frontend integration is working!');
  } else {
    console.log('\n⚠️  Some tests failed. Check the output above for details.');
  }
  
  console.log('\n' + '='.repeat(50));
  
  process.exit(results.failed > 0 ? 1 : 0);
}

// Run tests
runTests().catch(error => {
  console.error('💥 Test suite failed:', error);
  process.exit(1);
});

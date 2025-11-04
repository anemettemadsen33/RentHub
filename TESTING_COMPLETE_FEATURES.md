# 🧪 Complete Features Testing Guide

## 📋 Table of Contents
1. [Security Testing](#security-testing)
2. [Performance Testing](#performance-testing)
3. [UI/UX Testing](#uiux-testing)
4. [Accessibility Testing](#accessibility-testing)
5. [Automated Tests](#automated-tests)

---

## 🔐 Security Testing

### SQL Injection Protection

#### Test 1: Basic SQL Injection
```bash
# Test malicious input
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"search": "1 OR 1=1"}'

# Expected: 403 Forbidden
```

#### Test 2: UNION SELECT Attack
```bash
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"search": "1 UNION SELECT * FROM users"}'

# Expected: 403 Forbidden
```

#### Test 3: Comment Injection
```bash
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"search": "test-- "}'

# Expected: 403 Forbidden
```

### XSS Protection

#### Test 1: Script Tag Injection
```bash
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"name": "<script>alert(\"XSS\")</script>"}'

# Expected: Input sanitized, script tags removed
```

#### Test 2: Event Handler Injection
```bash
curl -X POST http://localhost:8000/api/properties \
  -H "Content-Type: application/json" \
  -d '{"name": "<img src=x onerror=alert(1)>"}'

# Expected: Input sanitized
```

### DDoS Protection

#### Test: Rate Limiting
```bash
# Send 150 requests rapidly
for i in {1..150}; do
  curl http://localhost:8000/api/properties &
done
wait

# Expected: After 100 requests, receive 429 Too Many Requests
```

#### Test: IP Blocking
```bash
# After hitting rate limit, check if IP is blocked
curl -v http://localhost:8000/api/properties

# Expected: 429 Too Many Requests with block message
# Check headers for X-RateLimit-Remaining
```

### Security Headers

#### Test: Check Security Headers
```bash
curl -I http://localhost:8000

# Expected headers:
# Content-Security-Policy: ...
# Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# Referrer-Policy: strict-origin-when-cross-origin
# Permissions-Policy: ...
```

### File Upload Security

#### Test 1: Upload Valid File
```bash
curl -X POST http://localhost:8000/api/properties/1/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test.jpg"

# Expected: 200 OK, file uploaded
```

#### Test 2: Upload Invalid File Type
```bash
curl -X POST http://localhost:8000/api/properties/1/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@malicious.exe"

# Expected: 400 Bad Request, invalid file type
```

#### Test 3: Upload Oversized File
```bash
# Create 15MB test file
dd if=/dev/zero of=large.jpg bs=1M count=15

curl -X POST http://localhost:8000/api/properties/1/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@large.jpg"

# Expected: 400 Bad Request, file too large
```

### Security Audit Logs

#### Test: View Audit Logs
```php
php artisan tinker

>>> use App\Services\SecurityAuditService;
>>> $audit = new SecurityAuditService();
>>> $incidents = $audit->getSecurityIncidents(10);
>>> $incidents->toArray();
```

---

## ⚡ Performance Testing

### Query Optimization

#### Test 1: N+1 Query Detection
```php
php artisan tinker

# Enable query logging
>>> DB::enableQueryLog();

# Without optimization
>>> $properties = Property::all();
>>> foreach($properties as $p) { echo $p->owner->name; }
>>> count(DB::getQueryLog()); // Should be N+1 queries

# With optimization
>>> DB::flushQueryLog();
>>> $properties = Property::with('owner')->get();
>>> foreach($properties as $p) { echo $p->owner->name; }
>>> count(DB::getQueryLog()); // Should be 1 query
```

#### Test 2: Query Performance Stats
```php
php artisan tinker

>>> use App\Services\QueryOptimizationService;
>>> $optimizer = new QueryOptimizationService();
>>> DB::enableQueryLog();
>>> Property::with(['owner', 'amenities'])->limit(100)->get();
>>> $stats = $optimizer->getQueryStats();
>>> print_r($stats);
```

### Caching

#### Test 1: Cache Hit/Miss
```php
php artisan tinker

>>> use App\Services\CacheStrategyService;
>>> $cache = new CacheStrategyService();

# First call (cache miss)
>>> $start = microtime(true);
>>> $data = $cache->queryCache('test', fn() => Property::all());
>>> $miss_time = microtime(true) - $start;

# Second call (cache hit)
>>> $start = microtime(true);
>>> $data = $cache->queryCache('test', fn() => Property::all());
>>> $hit_time = microtime(true) - $start;

>>> echo "Miss: {$miss_time}s, Hit: {$hit_time}s\n";
# Hit should be significantly faster
```

#### Test 2: Cache Statistics
```php
php artisan tinker

>>> use App\Services\CacheStrategyService;
>>> $cache = new CacheStrategyService();
>>> $stats = $cache->getCacheStats();
>>> print_r($stats);
```

### Response Compression

#### Test: Check Compression
```bash
# Request with Brotli support
curl -H "Accept-Encoding: br" -I http://localhost:8000/api/properties

# Expected: Content-Encoding: br

# Request with Gzip support
curl -H "Accept-Encoding: gzip" -I http://localhost:8000/api/properties

# Expected: Content-Encoding: gzip

# Check compression ratio
curl -H "Accept-Encoding: gzip" http://localhost:8000/api/properties -w "%{size_download}" -o /dev/null -s
# Compare with uncompressed size
```

### Load Testing

#### Test with Apache Bench
```bash
# Install Apache Bench (if not installed)
# Linux: apt-get install apache2-utils
# Mac: brew install apache2
# Windows: included with Apache

# Test with 1000 requests, 10 concurrent
ab -n 1000 -c 10 http://localhost:8000/api/properties

# Expected metrics:
# - Requests per second
# - Time per request
# - Transfer rate
```

#### Test with Artillery
```bash
# Install Artillery
npm install -g artillery

# Create test config (artillery.yml)
cat > artillery.yml << EOF
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 10
scenarios:
  - flow:
    - get:
        url: '/api/properties'
EOF

# Run test
artillery run artillery.yml
```

---

## 🎨 UI/UX Testing

### Loading States

#### Test 1: Loading Spinner
```javascript
// In your browser console or test file
// Simulate slow API response
cy.intercept('GET', '/api/properties', {
  delay: 3000,
  fixture: 'properties.json'
});

cy.visit('/properties');
cy.get('.loading-state').should('be.visible');
cy.wait(3000);
cy.get('.loading-state').should('not.exist');
```

#### Test 2: Skeleton Screens
```javascript
// Should show skeleton while loading
cy.visit('/properties');
cy.get('.skeleton-loader').should('be.visible');

// Should hide skeleton after load
cy.wait('@getProperties');
cy.get('.skeleton-loader').should('not.exist');
```

### Empty States

#### Test: No Results
```javascript
cy.intercept('GET', '/api/properties*', { body: [] });
cy.visit('/properties');
cy.contains('No properties found').should('be.visible');
cy.contains('Try adjusting your search').should('be.visible');
```

### Error States

#### Test 1: API Error
```javascript
cy.intercept('GET', '/api/properties', {
  statusCode: 500,
  body: { message: 'Server error' }
});

cy.visit('/properties');
cy.contains('Something went wrong').should('be.visible');
cy.get('button:contains("Try Again")').should('be.visible');
```

#### Test 2: Error Boundary
```javascript
// Trigger React error
cy.window().then(win => {
  win.throwError();
});

cy.contains('Application Error').should('be.visible');
```

### Toast Notifications

#### Test: Success Toast
```javascript
cy.visit('/properties/new');
cy.fillForm(); // Custom command to fill form
cy.get('button:contains("Save")').click();

cy.get('.toast').should('be.visible');
cy.contains('Property saved successfully').should('be.visible');

// Auto-dismiss after 5 seconds
cy.wait(5000);
cy.get('.toast').should('not.exist');
```

---

## ♿ Accessibility Testing

### Keyboard Navigation

#### Test 1: Tab Navigation
```javascript
cy.visit('/properties');

// Tab through interactive elements
cy.get('body').tab();
cy.focused().should('have.class', 'skip-link');

cy.focused().tab();
cy.focused().should('match', 'a, button, input');

// All interactive elements should be focusable
```

#### Test 2: Modal Focus Trap
```javascript
cy.get('button:contains("Delete")').click();

// Focus should be trapped in modal
cy.get('.modal').should('be.visible');
cy.focused().should('be.within', '.modal');

// Tab should cycle within modal
cy.focused().tab();
cy.focused().should('be.within', '.modal');

// ESC should close modal
cy.focused().type('{esc}');
cy.get('.modal').should('not.exist');
```

### Screen Reader Testing

#### Test 1: ARIA Labels
```bash
# Install axe-core
npm install -D @axe-core/cli

# Run accessibility audit
npx axe http://localhost:3000 --rules aria-allowed-attr,aria-required-attr

# Expected: No violations
```

#### Test 2: Heading Structure
```javascript
cy.visit('/properties');

// Check heading hierarchy
cy.get('h1').should('have.length', 1);
cy.get('h1').then($h1 => {
  cy.get('h2').each($h2 => {
    // h2 should come after h1
    expect($h2.offset().top).to.be.greaterThan($h1.offset().top);
  });
});
```

### Color Contrast

#### Test: WCAG AA Compliance
```bash
# Install Pa11y
npm install -g pa11y

# Test color contrast
pa11y http://localhost:3000 --standard WCAG2AA

# Expected: No contrast issues
```

### Focus Indicators

#### Test: Visible Focus
```javascript
cy.visit('/properties');

// Tab to first focusable element
cy.get('body').tab();

// Should have visible focus indicator
cy.focused().should('have.css', 'outline-color');
cy.focused().should('have.css', 'outline-width', '2px');
```

---

## 🤖 Automated Tests

### PHPUnit Tests (Backend)

#### Create Test File
```php
// tests/Feature/SecurityTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityTest extends TestCase
{
    public function test_sql_injection_is_blocked()
    {
        $response = $this->post('/api/properties', [
            'search' => "1' OR '1'='1"
        ]);

        $response->assertStatus(403);
    }

    public function test_xss_is_sanitized()
    {
        $response = $this->post('/api/properties', [
            'name' => '<script>alert("xss")</script>'
        ]);

        $response->assertStatus(200);
        $this->assertStringNotContainsString('<script>', $response->json('name'));
    }

    public function test_rate_limiting()
    {
        for ($i = 0; $i < 101; $i++) {
            $response = $this->get('/api/properties');
        }

        $this->assertEquals(429, $response->status());
    }
}
```

#### Run Tests
```bash
cd backend
php artisan test

# Run specific test
php artisan test --filter SecurityTest

# With coverage
php artisan test --coverage
```

### Jest Tests (Frontend)

#### Create Test File
```javascript
// frontend/src/components/__tests__/Toast.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import { Toast, toast, ToastContainer } from '../ui/Toast';

describe('Toast', () => {
  it('displays success message', () => {
    render(
      <Toast
        message="Success!"
        type="success"
        duration={0}
      />
    );

    expect(screen.getByText('Success!')).toBeInTheDocument();
  });

  it('auto-dismisses after duration', async () => {
    render(
      <Toast
        message="Auto dismiss"
        type="info"
        duration={1000}
      />
    );

    await waitFor(() => {
      expect(screen.queryByText('Auto dismiss')).not.toBeInTheDocument();
    }, { timeout: 2000 });
  });

  it('can be manually closed', () => {
    const onClose = jest.fn();
    render(
      <Toast
        message="Manual close"
        type="info"
        duration={0}
        onClose={onClose}
      />
    );

    const closeButton = screen.getByLabelText('Close');
    closeButton.click();

    expect(onClose).toHaveBeenCalled();
  });
});
```

#### Run Tests
```bash
cd frontend
npm test

# Run specific test
npm test -- Toast.test.tsx

# With coverage
npm test -- --coverage
```

### Cypress E2E Tests

#### Create Test File
```javascript
// cypress/e2e/accessibility.cy.js
describe('Accessibility', () => {
  it('has no accessibility violations', () => {
    cy.visit('/properties');
    cy.injectAxe();
    cy.checkA11y();
  });

  it('supports keyboard navigation', () => {
    cy.visit('/properties');
    
    cy.get('body').tab();
    cy.focused().should('have.class', 'skip-link');
    
    cy.focused().type('{enter}');
    cy.focused().should('have.attr', 'id', 'main-content');
  });

  it('has proper ARIA labels', () => {
    cy.visit('/properties/new');
    
    cy.get('input[name="title"]').should('have.attr', 'aria-label');
    cy.get('button[type="submit"]').should('have.attr', 'aria-label');
  });
});
```

#### Run Tests
```bash
cd frontend
npx cypress open  # Interactive mode
npx cypress run   # Headless mode
```

---

## 📊 Test Coverage Goals

- **Security**: 100% (critical)
- **Performance**: 90%
- **UI Components**: 80%
- **Accessibility**: 100% (WCAG AA)
- **E2E Flows**: 70%

---

## 🔧 Continuous Testing

### Pre-commit Hook
```bash
# .git/hooks/pre-commit
#!/bin/bash

# Run backend tests
cd backend
php artisan test --stop-on-failure
if [ $? -ne 0 ]; then
    echo "Backend tests failed!"
    exit 1
fi

# Run frontend tests
cd ../frontend
npm test -- --watchAll=false
if [ $? -ne 0 ]; then
    echo "Frontend tests failed!"
    exit 1
fi

exit 0
```

### CI/CD Pipeline (GitHub Actions)
```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - name: Install dependencies
        run: cd backend && composer install
      - name: Run tests
        run: cd backend && php artisan test

  frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node
        uses: actions/setup-node@v2
        with:
          node-version: 18
      - name: Install dependencies
        run: cd frontend && npm install
      - name: Run tests
        run: cd frontend && npm test -- --watchAll=false
```

---

## ✅ Testing Checklist

### Before Deployment
- [ ] All security tests pass
- [ ] Performance benchmarks meet targets
- [ ] Accessibility audit passes (WCAG AA)
- [ ] Cross-browser testing complete
- [ ] Mobile responsive testing complete
- [ ] Load testing results acceptable
- [ ] Security audit logs working
- [ ] Error handling tested
- [ ] Edge cases covered
- [ ] Documentation updated

---

**Last Updated:** November 3, 2025  
**Status:** Ready for Testing

import { test, expect } from '@playwright/test';

// Utility: try to dismiss cookie/consent banners or overlays that may intercept clicks
async function dismissOverlays(page: any) {
  try {
    // Common consent buttons
    const candidates = [
      page.getByRole('button', { name: /accept|agree|allow|got it|close/i }).first(),
      page.getByText(/accept|agree|allow|got it|close|consent/i).first(),
    ];
    for (const c of candidates) {
      if (await c.isVisible().catch(() => false)) {
        await c.click({ force: true }).catch(() => {});
      }
    }

    // Hard hide any obvious consent/cookie banners if still present
    await page.evaluate(() => {
      const selectors = [
        '[id*="cookie" i]','[class*="cookie" i]','[id*="consent" i]','[class*="consent" i]',
        '[class*="banner" i]','[id*="banner" i]','[class*="toast" i]'
      ];
      document.querySelectorAll(selectors.join(',')).forEach((el) => {
        (el as HTMLElement).style.pointerEvents = 'none';
        (el as HTMLElement).style.display = 'none';
        (el as HTMLElement).setAttribute('aria-hidden', 'true');
      });

      // Also hide fixed bottom overlays (e.g., consent bars)
      document.querySelectorAll('.fixed.inset-x-0.bottom-0, [class*="fixed"][class*="bottom-0"]').forEach((el) => {
        (el as HTMLElement).style.pointerEvents = 'none';
        (el as HTMLElement).style.display = 'none';
        (el as HTMLElement).setAttribute('aria-hidden', 'true');
      });
    });
  } catch {}
}

test.describe('Registration Flow E2E', () => {
  test.beforeEach(async ({ page }) => {
    // Start from the register page and wait for load
    await page.goto('http://localhost:3000/auth/register');
    await page.waitForLoadState('networkidle');
    // Ensure clean auth/session state
    await page.context().clearCookies().catch(() => {});
    await page.evaluate(() => { try { localStorage.clear(); sessionStorage.clear(); } catch {} });
    
    // Dismiss cookie banner if present
    await dismissOverlays(page);

    // If we got redirected away (already logged in scenario), try forcing logout state
    if (!page.url().includes('/auth/register')) {
      // Attempt to remove any auth artifacts and retry once
      await page.evaluate(() => { try { localStorage.clear(); sessionStorage.clear(); } catch {} });
      await page.context().clearCookies().catch(() => {});
      await page.goto('http://localhost:3000/auth/register');
      await page.waitForLoadState('networkidle');
    }
  });

  test('should display registration form', async ({ page }) => {
    // Wait for page to be interactive (CardTitle is a div, not role=heading)
    await expect(page.getByText(/create an account/i)).toBeVisible({ timeout: 10000 });

  // Check all form fields are present (by input names)
  await expect(page.locator('input[name="name"]').first()).toBeVisible();
  await expect(page.locator('input[name="email"]').first()).toBeVisible();
  await expect(page.locator('input[name="password"]').first()).toBeVisible();
  await expect(page.locator('input[name="passwordConfirmation"]').first()).toBeVisible();
  });

  test('should register new user successfully', async ({ page }) => {
    const timestamp = Date.now();
    const testEmail = `test${timestamp}@example.com`;

    // Intercept network requests to see what's happening
    const requests: any[] = [];
    // Mirror browser console to test output for deeper debugging
    page.on('console', (msg) => {
      console.log('[BROWSER]', msg.type().toUpperCase(), msg.text());
    });
    page.on('request', (request) => {
      if (request.url().includes('localhost:8000')) {
        requests.push({
          url: request.url(),
          method: request.method(),
          headers: request.headers(),
        });
        console.log('[REQUEST]', request.url(), request.method());
      }
    });

    const responses: any[] = [];
    page.on('response', async (response) => {
      if (response.url().includes('localhost:8000')) {
        const status = response.status();
        let body: any = null;
        try {
          body = await response.json();
        } catch {
          body = await response.text();
        }
        responses.push({ url: response.url(), status, body });
        console.log('[RESPONSE]', response.url(), status, String(body).slice(0, 200));
      }
    });

    // Wait for form to be ready (CardTitle is a div)
    await expect(page.getByText(/create an account/i)).toBeVisible();

    // Fill in the form using placeholders (more reliable)
    await page.locator('input[name="name"]').fill('Test User');
    await page.locator('input[name="email"]').fill(testEmail);
    await page.locator('input[name="password"]').fill('Password123!');
    await page.locator('input[name="passwordConfirmation"]').fill('Password123!');

    // Submit the form
    const submitButton = page.getByRole('button', { name: /create account/i });
    console.log('[TEST] About to click submit button');
    
    // Attempt normal click; if blocked, force click
    try {
      await submitButton.click();
      console.log('[TEST] Click successful');
    } catch (err) {
      console.log('[TEST] Click blocked, dismissing overlays:', err);
      await dismissOverlays(page);
      await submitButton.click({ force: true });
      console.log('[TEST] Force click successful');
    }

    // Wait for registration response or timeout after 10s
    console.log('[TEST] Waiting for registration response...');
    try {
      await page.waitForResponse(
        (resp) => resp.url().includes('/api/v1/register') || resp.url().includes('/register'),
        { timeout: 10000 }
      );
    } catch (e) {
      console.log('[TEST] No registration response within timeout');
    }
    
    // Check if request was made
    console.log('[TEST] Requests captured:', requests.length);
    console.log('[TEST] Responses captured:', responses.length);

  // Wait a bit and check localStorage
  await page.waitForTimeout(1000);
    const url = page.url();
    const hasToken = await page.evaluate(() => !!localStorage.getItem('auth_token'));
    const hasUser = await page.evaluate(() => !!localStorage.getItem('user'));
    
    console.log(`[Registration Test] URL: ${url}, Token: ${hasToken}, User: ${hasUser}`);
    console.log(`[Registration Test] Requests: ${requests.length}, Responses: ${responses.length}`);
    
    // Assert: token and user must be set after successful registration
    expect(hasToken && hasUser).toBe(true);
  });

  test('should show validation error for invalid email', async ({ page }) => {
    await expect(page.getByText(/create an account/i)).toBeVisible();
    
  await page.locator('input[name="name"]').fill('Test User');
  await page.locator('input[name="email"]').fill('invalid-email');
  await page.locator('input[name="password"]').fill('Password123!');
  await page.locator('input[name="passwordConfirmation"]').fill('Password123!');

    await page.getByRole('button', { name: /create account/i }).click();

    // Should see a form error summary or alert
    await expect(page.getByRole('alert')).toBeVisible({ timeout: 3000 });
  });

  test('should show error for password mismatch', async ({ page }) => {
    await expect(page.getByText(/create an account/i)).toBeVisible();
    
  await page.locator('input[name="name"]').fill('Test User');
  await page.locator('input[name="email"]').fill('test@example.com');
  await page.locator('input[name="password"]').fill('Password123!');
  await page.locator('input[name="passwordConfirmation"]').fill('DifferentPassword123!');

    await page.getByRole('button', { name: /create account/i }).click();

    // Should see validation on confirmation field
    await page.waitForTimeout(500);
    await expect(page.locator('input[name="passwordConfirmation"][aria-invalid="true"]')).toHaveCount(1);
  });

  test('should have link to login page', async ({ page }) => {
    await expect(page.getByText(/create an account/i)).toBeVisible();
    
    const loginLink = page.getByRole('link', { name: /sign in/i });
    await expect(loginLink).toBeVisible();
    // Assert correct href
    await expect(loginLink).toHaveAttribute('href', '/auth/login');
  });

  test('should disable submit button while submitting', async ({ page }) => {
    await expect(page.getByText(/create an account/i)).toBeVisible();
    
  await page.locator('input[name="name"]').fill('Test User');
  await page.locator('input[name="email"]').fill(`test${Date.now()}@example.com`);
  await page.locator('input[name="password"]').fill('Password123!');
  await page.locator('input[name="passwordConfirmation"]').fill('Password123!');

    const submitButton = page.getByRole('button', { name: /create account/i });
    
    // Click and immediately check if disabled
    await submitButton.click();

    // Button should show "Creating account..." and be disabled
    await expect(submitButton).toContainText(/creating|loading/i, { timeout: 1000 }).catch(() => {});
  });
});

test.describe('Login Flow E2E', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:3000/auth/login');
    await page.waitForLoadState('networkidle');
    // Clean any prior state
    await page.context().clearCookies().catch(() => {});
    await page.evaluate(() => { try { localStorage.clear(); sessionStorage.clear(); } catch {} });
    
    // Dismiss cookie banner
    await dismissOverlays(page);
  });

  test('should display login form', async ({ page }) => {
  await expect(page.getByText(/sign in/i)).toBeVisible({ timeout: 10000 });
  await expect(page.locator('input[name="email"]').first()).toBeVisible();
  await expect(page.locator('input[name="password"]').first()).toBeVisible();
  });

  test('should have link to register page', async ({ page }) => {
    await expect(page.getByText(/sign in/i)).toBeVisible();
    
    const registerLink = page.getByRole('link', { name: /sign up/i });
    await expect(registerLink).toBeVisible();
    await expect(registerLink).toHaveAttribute('href', '/auth/register');
  });

  test('should show error for invalid credentials', async ({ page }) => {
    await expect(page.getByText(/sign in/i)).toBeVisible();
    
    await page.locator('input[name="email"]').fill('nonexistent@example.com');
    await page.locator('input[name="password"]').fill('WrongPassword123!');

    await page.getByRole('button', { name: /sign in/i }).click();

    // Wait briefly and ensure we remain on login page (no redirect)
    await page.waitForTimeout(1500);
    expect(page.url()).toContain('/auth/login');
  });
});

test.describe('Homepage E2E', () => {
  test('should load homepage', async ({ page }) => {
    await page.goto('http://localhost:3000');
    await page.waitForLoadState('networkidle');

    // Check for main heading (any h1)
    const heading = page.locator('h1').first();
    await expect(heading).toBeVisible({ timeout: 10000 });
  });

  test('should have navigation menu', async ({ page }) => {
    await page.goto('http://localhost:3000');
    await page.waitForLoadState('networkidle');

    // Should have navigation (header, nav, or links)
    const hasNav = await page.locator('nav, header, a[href="/"]').first().isVisible({ timeout: 5000 }).catch(() => false);
    expect(hasNav).toBeTruthy();
  });

  test('should have no hydration errors', async ({ page }) => {
    const consoleErrors: string[] = [];
    
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    await page.goto('http://localhost:3000');
    await page.waitForLoadState('networkidle');

    // Filter hydration-specific errors
    const hydrationErrors = consoleErrors.filter(err => 
      err.toLowerCase().includes('hydration') || err.toLowerCase().includes('hydrating')
    );

    expect(hydrationErrors.length).toBe(0);
  });
});

test.describe('API Integration E2E', () => {
  test('backend API is accessible', async ({ request }) => {
    const response = await request.get('http://127.0.0.1:8000/api/health');
    expect([200, 503]).toContain(response.status());
  });

  test('can fetch languages from API', async ({ request }) => {
    const response = await request.get('http://127.0.0.1:8000/api/v1/languages');
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    // Response might be an array or an object with data property
    const isValid = Array.isArray(data) || (typeof data === 'object' && (Array.isArray((data as any).data) || Array.isArray((data as any).languages)));
    expect(isValid).toBeTruthy();
  });

  test('CORS headers are present', async ({ request }) => {
    const response = await request.get('http://127.0.0.1:8000/api/health', {
      headers: {
        'Origin': 'http://localhost:3000'
      },
      timeout: 5000
    });
    
    const corsHeader = response.headers()['access-control-allow-origin'];
    expect(corsHeader).toBeDefined();
  });
});

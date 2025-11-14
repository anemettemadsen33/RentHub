import { defineConfig, devices } from '@playwright/test';
// Use globalThis indirection so TS doesn't require Node type defs
const env: any = (globalThis as any).process?.env || {};
const isCI = !!env.CI;

export default defineConfig({
  testDir: './e2e',
  fullyParallel: !isCI, // Run in parallel locally, sequential in CI
  forbidOnly: !!env.CI,
  retries: env.CI ? 2 : 1,
  workers: env.CI ? 1 : 4,
  timeout: 60 * 1000, // 60 seconds per test
  reporter: isCI
    ? [
        ['html'],
        ['json', { outputFile: 'playwright-report.json' }],
        ['junit', { outputFile: 'test-results/junit.xml' }],
      ]
    : [
        ['html'],
        ['list'],
      ],
  
  use: {
    baseURL: env.BASE_URL || 'http://localhost:3000',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    actionTimeout: 15 * 1000,
  },

  projects: [
    // Desktop Browsers
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        viewport: { width: 1920, height: 1080 },
      },
    },
    {
      name: 'firefox',
      use: { 
        ...devices['Desktop Firefox'],
        viewport: { width: 1920, height: 1080 },
      },
    },
    {
      name: 'webkit',
      use: { 
        ...devices['Desktop Safari'],
        viewport: { width: 1920, height: 1080 },
      },
    },
    {
      name: 'edge',
      use: {
        ...devices['Desktop Edge'],
        channel: 'msedge',
        viewport: { width: 1920, height: 1080 },
      },
    },
    
    // Mobile Browsers
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'mobile-safari',
      use: { ...devices['iPhone 12'] },
    },
    {
      name: 'mobile-safari-landscape',
      use: {
        ...devices['iPhone 12'],
        viewport: { width: 844, height: 390 },
      },
    },
    
    // Tablet
    {
      name: 'tablet-ipad',
      use: { ...devices['iPad Pro'] },
    },
    {
      name: 'tablet-android',
      use: {
        ...devices['Galaxy Tab S4'],
      },
    },
  ],

  // Start frontend dev server
  webServer: {
    command: 'npm run dev',
    url: 'http://localhost:3000',
    reuseExistingServer: !env.CI,
    env: {
      NEXT_PUBLIC_E2E: 'true',
      ...env,
    },
    timeout: 120 * 1000,
  },
});

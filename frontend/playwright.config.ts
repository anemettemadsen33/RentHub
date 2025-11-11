import { defineConfig, devices } from '@playwright/test';
// Use globalThis indirection so TS doesn't require Node type defs
const env: any = (globalThis as any).process?.env || {};
const isCI = !!env.CI;

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!env.CI,
  retries: env.CI ? 2 : 0,
  workers: env.CI ? 1 : undefined,
  reporter: isCI
    ? [
        ['html'],
        ['json', { outputFile: 'playwright-report.json' }],
      ]
    : 'html',
  
  use: {
    baseURL: 'http://localhost:3000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: isCI
    ? [
        {
          name: 'chromium',
          use: { ...devices['Desktop Chrome'] },
        },
      ]
    : [
        {
          name: 'chromium',
          use: { ...devices['Desktop Chrome'] },
        },
        {
          name: 'firefox',
          use: { ...devices['Desktop Firefox'] },
        },
        {
          name: 'webkit',
          use: { ...devices['Desktop Safari'] },
        },
        // Mobile viewports
        {
          name: 'Mobile Chrome',
          use: { ...devices['Pixel 5'] },
        },
        {
          name: 'Mobile Safari',
          use: { ...devices['iPhone 12'] },
        },
      ],

  // Start both frontend and backend; Playwright supports an array of webServer configs.
  webServer: [
    {
      command: 'npm run dev',
      url: 'http://localhost:3000',
  reuseExistingServer: !env.CI,
      env: {
  NEXT_PUBLIC_E2E: 'true',
  ...env,
      },
      timeout: 120 * 1000,
    },
    {
      // Start backend - wrapper exits after confirming ready, server runs detached
      command: 'node ../scripts/playwright-start-backend.js',
      port: 8000,
  reuseExistingServer: !env.CI,
      timeout: 180 * 1000, // Extended for migrations
    },
  ],
});

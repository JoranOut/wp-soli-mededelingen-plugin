const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './e2e',
  // Single worker: tests log in as the same admin user, and parallel logins
  // race on WordPress's session_tokens user meta, invalidating each other.
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [['html', { open: 'never' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8889',
    screenshot: 'only-on-failure',
    video: process.env.CI ? 'retain-on-failure' : 'on',
    trace: 'retain-on-failure',
  },
  outputDir: 'test-results',
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: 'npm run wp-env:start',
    url: process.env.BASE_URL || 'http://localhost:8889',
    // CI pre-starts wp-env in the workflow; always reuse a running instance
    reuseExistingServer: true,
    timeout: 120 * 1000,
  },
});

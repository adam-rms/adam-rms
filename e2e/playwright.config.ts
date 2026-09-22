import fs from "fs";
import { defineConfig, devices } from "@playwright/test";
import { APP_ENV, BASE_URL, PHP_BINARY, PORT } from "./env";

/**
 * E2E Test Configuration
 *
 * Tests run against the PHP app served by PHP's built-in server (started by
 * Playwright below) and a real MySQL database. globalSetup migrates and seeds
 * the database before the suite starts.
 *
 * Prerequisites:
 *   - PHP 8.3 (set PHP_BINARY if it isn't `php` on your PATH) with the extensions in the Dockerfile, and `composer install` run
 *   - MySQL 8 reachable with the credentials in e2e/env.ts
 *   - `npm install` in e2e/, then `npx playwright install chromium`
 *
 * Run tests (from e2e/):
 *   npm test
 */

/**
 * Use a pre-installed Chromium where Playwright can't download browsers
 * (e.g. Claude Code). Falls back to Playwright's own managed browser.
 */
function resolveChromiumExecutable(): string | undefined {
  if (process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH) {
    return process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH;
  }
  try {
    const resolved = fs.realpathSync("/opt/pw-browsers/chromium");
    if (fs.existsSync(resolved)) return resolved;
  } catch {
    // not found
  }
  return undefined;
}

const chromiumExecutable = resolveChromiumExecutable();
// Route the browser through HTTPS_PROXY when one is set (e.g. network-restricted
// sandboxes) so the CDN-hosted jQuery/Bootstrap the app depends on can load. Those
// proxies re-sign TLS, so certificate errors are only ignored in that case.
const httpsProxy = process.env.HTTPS_PROXY ?? process.env.https_proxy;
const launchOptions = {
  ...(chromiumExecutable && {
    executablePath: chromiumExecutable,
    args: ["--no-sandbox", "--disable-gpu"],
  }),
  ...(httpsProxy && { proxy: { server: httpsProxy, bypass: "127.0.0.1,localhost" } }),
};

export default defineConfig({
  testDir: ".",
  globalSetup: "./globalSetup.ts",
  // The suite shares one database, so run tests one at a time until tests
  // are written to be independent of each other.
  workers: 1,
  // Each page pulls ~30 assets from CDNs, which is slow through a sandbox proxy
  timeout: httpsProxy ? 120_000 : 30_000,
  expect: { timeout: httpsProxy ? 30_000 : 5_000 },
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  reporter: [
    ["html", { outputFolder: "playwright-report", open: "never" }],
    ["list"],
    ["json", { outputFile: "playwright-report/results.json" }],
    ...(process.env.CI ? [["github"] as ["github"]] : []),
  ],

  use: {
    baseURL: BASE_URL,
    ignoreHTTPSErrors: !!httpsProxy,
    trace: "retain-on-failure",
    video: "retain-on-failure",
    screenshot: "on",
  },

  webServer: {
    command: `${PHP_BINARY} -S 127.0.0.1:${PORT} -t ../src`,
    url: `${BASE_URL}/login/`,
    env: APP_ENV,
    reuseExistingServer: !process.env.CI,
  },

  projects: [
    {
      name: "public",
      testMatch: /public\/.+\.spec\.ts/,
      use: { ...devices["Desktop Chrome"], launchOptions },
    },
    {
      name: "authenticated",
      testMatch: /authenticated\/.+\.spec\.ts/,
      use: { ...devices["Desktop Chrome"], launchOptions },
    },
  ],
});

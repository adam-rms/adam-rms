import { test as base, expect, type Page } from "@playwright/test";
import { TEST_USER } from "./env";

export async function login(page: Page, email = TEST_USER.email, password = TEST_USER.password) {
  await page.goto("/login/", { waitUntil: "domcontentloaded" });
  await page.locator("#emailInput").fill(email);
  await page.locator("#passwordInput").fill(password);
  await page.getByRole("button", { name: "Login" }).click();
  await page.waitForURL((url) => !url.pathname.startsWith("/login"), {
    waitUntil: "domcontentloaded",
  });
}

/** A test whose `page` is already logged in as the seeded super admin. */
export const test = base.extend({
  page: async ({ page }, use) => {
    await login(page);
    await use(page);
  },
});

export { expect };

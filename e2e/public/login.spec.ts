import { test, expect } from "@playwright/test";
import { TEST_USER } from "../env";

// The suite runs with DEV_MODE=true (as the devcontainer does), where headSecure.php
// shows the auth failure and a login link instead of redirecting.
test("unauthenticated visitors are pointed to the login page", async ({ page }) => {
  await page.goto("/");
  await page.getByRole("link", { name: /\/login\/$/ }).click();
  await expect(page.locator("#emailInput")).toBeVisible();
});

test("a wrong password shows an error and stays on the login page", async ({ page }) => {
  await page.goto("/login/");
  await page.locator("#emailInput").fill(TEST_USER.email);
  await page.locator("#passwordInput").fill("not-the-password");
  await page.getByRole("button", { name: "Login" }).click();
  await expect(page.locator("#errorMessageBox")).toBeVisible();
  await expect(page).toHaveURL(/\/login\/?/);
});

test("the API rejects requests without a session", async ({ request }) => {
  const response = await request.post("/api/account/basicDetails.php");
  expect(await response.json()).toEqual({
    result: false,
    error: { message: "AUTH FAIL - No token found" },
  });
});

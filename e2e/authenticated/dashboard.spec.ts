import { test, expect } from "../fixtures";

test("the seeded super admin can log in and reach the app", async ({ page }) => {
  await page.goto("/", { waitUntil: "domcontentloaded" });
  await expect(page).not.toHaveURL(/\/login/);
  // The seeded user belongs to no business yet, so they land on the "join or create" page
  await expect(page.getByText("has separate areas for separate businesses")).toBeVisible();
});

test("logging out returns to the login page", async ({ page }) => {
  await page.goto("/login/?logout");
  await page.goto("/");
  await expect(page.getByText("No token found")).toBeVisible();
});

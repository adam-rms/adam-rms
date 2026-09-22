import { test, expect } from "../fixtures";

test("the seeded super admin can log in and reach the app", async ({ page }) => {
  await page.goto("/", { waitUntil: "domcontentloaded" });
  await expect(page).not.toHaveURL(/\/login/);
  // The super admin belongs to no business, so headSecure.php drops them into the first business on the
  // server as a server admin (globalSetup always creates at least the tenant isolation businesses). Which
  // one that is depends on the database, so only check they reach a business's dashboard.
  await expect(page).toHaveTitle(/Dashboard/);
});

test("logging out returns to the login page", async ({ page }) => {
  await page.goto("/login/?logout");
  await page.goto("/");
  await expect(page.getByText("No token found")).toBeVisible();
});

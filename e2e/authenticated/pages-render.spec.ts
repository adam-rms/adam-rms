import { cacheCdn } from "../cdn";
import { login } from "../fixtures";
import { TEST_USER } from "../env";
import { businessPages, placeholders, problemsOpening, serverPages } from "../pages";
import { expect, test } from "../tenants";

/**
 * Opens every page in a real browser and fails on anything that shows the page is broken: a PHP fatal
 * error or uncaught exception (DEV_MODE prints them), a 5xx, the 404 page, or an uncaught JavaScript
 * error. Most pages are driven by inline jQuery that no other test runs, so this is what notices a PR
 * that breaks a script or a template on a page nobody clicked through.
 *
 * The pages are listed in ../pages.ts.
 */
test.describe("as business A's full user", () => {
  test.beforeEach(async ({ page, tenants: { a, password } }) => {
    await cacheCdn(page.context());
    await login(page, a.users.full.email, password);
  });
  for (const c of businessPages) {
    (c.fixme ? test.fixme : test)(`${c.url(placeholders)} renders without errors`, async ({ page, tenants: { a } }) => {
      expect(await problemsOpening(page, c.url(a))).toEqual([]);
    });
  }
});

test.describe("as the super admin", () => {
  test.beforeEach(async ({ page }) => {
    await cacheCdn(page.context());
    await login(page, TEST_USER.email, TEST_USER.password);
  });
  for (const url of serverPages) {
    test(`${url} renders without errors`, async ({ page }) => {
      expect(await problemsOpening(page, url)).toEqual([]);
    });
  }
});

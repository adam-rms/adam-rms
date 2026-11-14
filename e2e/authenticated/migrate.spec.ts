import { createHmac } from "crypto";
import { APP_ENV, BASE_URL } from "../env";
import { test, expect, type Session } from "../tenants";

const STATE = "e2e-migrate-state-0123456789";
const CALLBACK = "http://127.0.0.1:9/migrate-callback";
const URL = `/instances/migrate.php?state=${STATE}&redirect_uri=${encodeURIComponent(CALLBACK)}`;

/** Chooses a business on the migrate page, without following the redirect back to the new version */
const choose = (session: Session, instanceId: number) =>
  session.request.post(URL, { form: { migrate_instances_id: instanceId }, maxRedirects: 0 });

test("instances/migrate.php sends the user back with a signed statement of the business they chose", async ({ asA, tenants: { a, b } }) => {
  const page = await asA.page(URL);
  expect(page.body).toContain(`value="${a.instanceId}"`);
  expect(page.body).not.toContain(`value="${b.instanceId}"`);

  const response = await choose(asA, a.instanceId);
  expect(response.status()).toBe(302);
  const location = new globalThis.URL(response.headers()["location"]);
  expect(location.origin + location.pathname).toBe(CALLBACK);
  expect(location.searchParams.get("state")).toBe(STATE);

  const [header, payload, signature] = location.searchParams.get("assertion")!.split(".");
  expect(JSON.parse(Buffer.from(header, "base64url").toString()).alg).toBe("HS256");
  expect(signature).toBe(createHmac("sha256", APP_ENV.CONFIG_MIGRATE_SIGNING_KEY).update(`${header}.${payload}`).digest("base64url"));
  const claims = JSON.parse(Buffer.from(payload, "base64url").toString());
  expect(claims).toMatchObject({
    iss: BASE_URL,
    aud: CALLBACK,
    sub: String(a.users.full.id),
    instances_id: a.instanceId,
    state: STATE,
  });
  expect(claims.exp - claims.iat).toBe(60);
});

test("instances/migrate.php refuses a business the user can't migrate", async ({ asA, asLimitedA, tenants: { a, b } }) => {
  // Another business
  expect((await choose(asA, b.instanceId)).status()).toBe(200);
  // Their own business, without the permission
  expect((await asLimitedA.page(URL)).body).not.toContain(`value="${a.instanceId}"`);
  expect((await choose(asLimitedA, a.instanceId)).status()).toBe(200);
});

test("instances/migrate.php needs a state and somewhere to send it back to", async ({ asA }) => {
  for (const url of [
    `/instances/migrate.php?redirect_uri=${encodeURIComponent(CALLBACK)}`,
    `/instances/migrate.php?state=${STATE}`,
    `/instances/migrate.php?state=${STATE}&redirect_uri=${encodeURIComponent("javascript:alert(1)")}`,
  ]) {
    expect((await asA.page(url)).body, url).not.toContain("migrate_instances_id");
  }
});

test("instances/migrate.php comes back after signing in", async ({ browser, tenants }) => {
  const page = await browser.newPage();
  await page.goto(URL);
  // In dev mode the login redirect is a link, to show why the user was sent to it
  await page.getByRole("link", { name: /\/login\// }).click();
  await page.locator("#emailInput").fill(tenants.a.users.full.email);
  await page.locator("#passwordInput").fill(tenants.password);
  await page.getByRole("button", { name: "Login" }).click();
  await expect(page.locator(`button[name="migrate_instances_id"][value="${tenants.a.instanceId}"]`)).toBeVisible();
  expect(page.url()).toContain(`state=${STATE}`);
  await page.close();
});

import { test, expect, dbQuery, mentions, seedTenants, succeeded } from "../tenants";

test.afterEach(() => {
  seedTenants();
});

test("api/instances/switch.php only switches to the user's own businesses", async ({ asA, tenants: { a, b } }) => {
  expect(succeeded(await asA.api("/api/instances/switch.php", { instances_id: b.instanceId }))).toBe(false);
  expect(succeeded(await asA.api("/api/instances/switch.php", { instances_id: a.instanceId })), "control: A's own").toBe(true);
  const list = await asA.api("/api/instances/list.php");
  expect(list.json.response).toEqual([expect.objectContaining({ instances_id: a.instanceId, this: true })]);
  expect(mentions(list, b.marker)).toBe(false);
});

test("api/instances/searchUser.php finds a user to add only by their exact email", async ({ asA, tenants: { a, b } }) => {
  // Adding someone from another business by their email address is how businesses share staff
  const exact = await asA.api("/api/instances/searchUser.php", { term: b.users.full.email });
  expect(exact.json.response).toEqual([expect.objectContaining({ users_userid: b.users.full.id })]);
  expect((await asA.api("/api/instances/searchUser.php", { term: "e2e_tenant_b" })).json.response).toEqual([]);
  // Nobody already in the business, and no deleted accounts
  expect((await asA.api("/api/instances/searchUser.php", { term: a.users.limited.email })).json.response).toEqual([]);
  expect((await asA.api("/api/instances/searchUser.php", { term: b.users.deleted.email })).json.response).toEqual([]);
});

test("api/instances/signupCodes/taken.php says whether a code is in use anywhere", async ({ asA }) => {
  // Codes are global, so it has to look at every business's
  expect((await asA.api("/api/instances/signupCodes/taken.php", { signupCode: "e2e-tenant-b-signup" }, "GET")).json.response).toEqual({ taken: true });
  expect((await asA.api("/api/instances/signupCodes/taken.php", { signupCode: `e2e-unused-${Date.now()}` }, "GET")).json.response).toEqual({ taken: false });
});

test("api/instances/calendar-export.php needs the business's calendar key", async ({ request, tenants: { a, b } }) => {
  const calendar = (id: number, key: string) => request.post("/api/instances/calendar-export.php", { form: { id, key } }).then((r) => r.text());
  const own = await calendar(a.instanceId, "e2e-calendar-hash-a");
  expect(own).toContain("BEGIN:VCALENDAR");
  expect(own).toContain(`${a.marker} project`);
  expect(own).not.toContain(b.marker);
  expect(await calendar(b.instanceId, "e2e-calendar-hash-a")).toBe("404");
  expect(await calendar(a.instanceId, "")).toBe("404");
});

test.describe("billing", () => {
  test("only the billing contact can manage a subscription", async ({ asA }) => {
    // The seeded businesses have no billing contact, so nobody gets as far as Stripe
    for (const endpoint of ["/api/instances/billing/billingPortal.php", "/api/instances/billing/subscribe.php"]) {
      const response = await asA.api(endpoint, { price_id: "price_e2e", currency: "gbp" });
      expect(response.body, endpoint).toContain("Sorry, you are not the billing contact for this business");
    }
  });

  test("postSubscribeDelay.php sends the user back to the app", async ({ asA }) => {
    expect((await asA.api("/api/instances/billing/postSubscribeDelay.php")).body).toContain("Please wait a moment whilst we process your subscription");
  });

  test("getPrices.php and webhooks.php change nothing without Stripe configured", async ({ request, tenants: { a } }) => {
    const before = dbQuery("SELECT * FROM instances WHERE instances_id = ?", [a.instanceId]);
    expect((await (await request.get("/api/instances/billing/getPrices.php")).text())).not.toContain('"result":true');
    const webhook = await request.post("/api/instances/billing/webhooks.php", {
      data: { type: "customer.subscription.updated", data: { object: { status: "active", metadata: { instance_id: a.instanceId } } } },
    });
    expect(await webhook.text()).not.toContain('"result":true');
    expect(dbQuery("SELECT * FROM instances WHERE instances_id = ?", [a.instanceId])).toEqual(before);
  });
});

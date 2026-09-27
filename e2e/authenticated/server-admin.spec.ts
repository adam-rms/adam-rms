import { test, expect, dbQuery, formData, mentions, newSession, seedTenants, succeeded, type Session } from "../tenants";
import { BASE_URL, TEST_USER } from "../env";

/**
 * Server permissions: a business admin (A's full-access user, whose only server permission is USE-DEV) is refused
 * everything that needs a server permission, and the seeded super admin can do it.
 */

let admin: Session;
test.beforeAll(async ({ playwright }) => {
  const request = await playwright.request.newContext({ baseURL: BASE_URL });
  admin = await newSession(request, TEST_USER.email, TEST_USER.password);
});
test.afterEach(() => {
  seedTenants();
});

const NOT_FOUND = "Oops! Page not found.";
const pages = [
  "server/analytics/index.php", "server/analytics/pageViews.php", "server/analytics/tables.php", "server/auditLog.php",
  "server/config.php", "server/instances.php", "server/permissions.php", "server/users.php",
];
for (const file of pages) {
  test(`${file} is only for server admins`, async ({ asA }) => {
    expect(mentions(await asA.page(`/${file}`), NOT_FOUND)).toBe(true);
    const control = await admin.page(`/${file}`);
    expect(mentions(control, NOT_FOUND), "control: the super admin can").toBe(false);
    expect(control.status).toBe(200);
  });
}

test("server/auditLog.php filters by user", async ({ tenants: { a } }) => {
  expect((await admin.page(`/server/auditLog.php?userby=${a.users.full.id}&userto=${a.users.full.id}&q=e2e`)).status).toBe(200);
});

test("api/server/users.php lists users for server admins only", async ({ asA, tenants: { a } }) => {
  const refused = await asA.api("/api/server/users.php", { search: { value: a.users.full.email } });
  expect(refused.status).toBe(403);
  expect(mentions(refused, a.users.full.email)).toBe(false);
  const listed = await admin.api("/api/server/users.php", { search: { value: a.users.full.email } });
  expect(mentions(listed, a.users.full.email)).toBe(true);
});

test("account endpoints that need a server permission refuse a business admin", async ({ asA, tenants: { a, b } }) => {
  const target = b.users.limited.id;
  const user = () => dbQuery("SELECT users_suspended, users_deleted, users_thumbnail, users_notificationSettings FROM users WHERE users_userid = ?", [target])[0];
  const positions = () => dbQuery("SELECT * FROM userPositions WHERE users_userid = ? ORDER BY userPositions_id", [target]);
  const tokens = () => dbQuery("SELECT authTokens_id, authTokens_valid FROM authTokens WHERE users_userid = ? ORDER BY authTokens_id", [target]);
  const before = { user: user(), positions: positions(), tokens: tokens() };

  await asA.api("/api/account/suspend.php", { userid: target, suspendval: 1 });
  await asA.api("/api/account/softDelete.php", { users_userid: target });
  await asA.api("/api/account/destroyTokens.php", { userid: target });
  await asA.api("/api/account/permissions.php", { action: "EDIT", users_userid: target, userPositions_id: "new", positions_id: 1, userPositions_start: "2024-01-01", userPositions_end: "2034-01-01", userPositions_show: 1 });
  // These two change the caller's own account instead when they may not change another's
  await asA.api("/api/account/thumbnail.php", { users_userid: target, thumbnail: 123456 });
  await asA.api("/api/account/notifications.php", { users_userid: target, settings: { e2e: true } });
  expect({ user: user(), positions: positions(), tokens: tokens() }).toEqual(before);

  const viewAs = await asA.api("/api/account/viewSiteAs.php", { userid: target });
  expect(viewAs.body).toContain("Sorry - you can't access this page");
  const emails = await asA.api("/api/account/emailViewer.php", { email: "1" });
  expect(emails.body).toContain("Sorry you don't have access to this");
  expect(dbQuery("SELECT users_thumbnail FROM users WHERE users_userid = ?", [a.users.full.id])[0].users_thumbnail).toBe(123456);
});

test("a server admin can suspend, delete and change another user's settings", async ({ tenants: { a } }) => {
  const target = a.users.limited.id;
  const user = () => dbQuery<Record<string, any>>("SELECT users_suspended, users_deleted, users_thumbnail, users_notificationSettings FROM users WHERE users_userid = ?", [target])[0];
  expect((await admin.api("/api/account/suspend.php", { userid: target, suspendval: 1 })).body).toBe("1");
  expect(user().users_suspended).toBe(1);
  expect((await admin.api("/api/account/destroyTokens.php", { userid: target })).body).toBe("1");
  expect(succeeded(await admin.api("/api/account/thumbnail.php", { users_userid: target, thumbnail: 123456 }))).toBe(true);
  expect(succeeded(await admin.api("/api/account/notifications.php", { users_userid: target, settings: { e2e: "1" } }))).toBe(true);
  expect(succeeded(await admin.api("/api/account/softDelete.php", { users_userid: target }))).toBe(true);
  expect(user()).toMatchObject({ users_deleted: 1, users_thumbnail: 123456, users_notificationSettings: JSON.stringify({ e2e: "1" }) });

  const added = await admin.api("/api/account/permissions.php", { action: "EDIT", users_userid: target, userPositions_id: "new", positions_id: 1, userPositions_start: "2024-01-01", userPositions_end: "2034-01-01", userPositions_show: 1 });
  expect(succeeded(added), added.body.slice(0, 300)).toBe(true);
  const [position] = dbQuery<{ userPositions_id: number }>("SELECT userPositions_id FROM userPositions WHERE users_userid = ? AND positions_id = 1", [target]);
  expect(succeeded(await admin.api("/api/account/permissions.php", { action: "DELETE", users_userid: target, userPositions_id: position.userPositions_id }))).toBe(true);
  expect(dbQuery("SELECT userPositions_id FROM userPositions WHERE userPositions_id = ?", [position.userPositions_id])).toHaveLength(0);

  const emails = await admin.api("/api/account/emailViewer.php", {});
  expect(emails.body).toContain("Nothing to see here!");
});

test("a server admin can view the site as another user, and go back", async ({ playwright, tenants: { a } }) => {
  const request = await playwright.request.newContext({ baseURL: BASE_URL });
  const session = await newSession(request, TEST_USER.email, TEST_USER.password);
  await session.request.post("/api/account/viewSiteAs.php", { form: { userid: a.users.limited.id }, maxRedirects: 0 });
  // user.php with no ID shows the logged-in user's own account
  expect(mentions(await session.page("/user.php"), a.users.limited.email)).toBe(true);
  await session.request.get("/api/account/viewSiteAs_terminate.php", { maxRedirects: 0 });
  const back = await session.page("/user.php");
  expect(mentions(back, TEST_USER.email)).toBe(true);
  expect(mentions(back, a.users.limited.email)).toBe(false);
  await request.dispose();
});

test("api/account/emailViewer.php only takes a list of email IDs", async () => {
  // This would error if it reached the database as SQL
  const response = await admin.api("/api/account/emailViewer.php", { email: "(SELECT 1 FROM e2e_no_such_table)" });
  expect(response.body).toBe("E-Mail not found");
});

test("api/account/viewSiteAs_terminate.php does nothing for someone not viewing as another user", async ({ asA }) => {
  expect((await asA.api("/api/account/viewSiteAs_terminate.php")).body).toBe("404");
});

test("api/permissions/permissionsEditor.php edits server positions for server admins only", async ({ asA }) => {
  const [group] = dbQuery<{ positionsGroups_id: number }>("SELECT positionsGroups_id FROM positionsGroups WHERE positionsGroups_name = 'E2E dev site access'");
  const actions = () => dbQuery<{ positionsGroups_actions: string }>("SELECT positionsGroups_actions FROM positionsGroups WHERE positionsGroups_id = ?", [group.positionsGroups_id])[0].positionsGroups_actions.split(",");
  expect((await asA.api("/api/permissions/permissionsEditor.php", { position: group.positionsGroups_id, addpermission: "USERS:VIEW" })).body).toBe("404");
  expect(actions()).not.toContain("USERS:VIEW");
  expect((await admin.api("/api/permissions/permissionsEditor.php", { position: group.positionsGroups_id, addpermission: "USERS:VIEW" })).body).toBe("1");
  expect(actions()).toContain("USERS:VIEW");
});

test("businesses are edited and deleted by server admins only", async ({ asA, tenants: { a } }) => {
  // Anyone can make a business while NEW_INSTANCE_ENABLED is on (the default); the super admin makes this one
  const name = `E2E server admin business ${Date.now()}`;
  const created = await admin.api("/api/instances/new.php", { instances_name: name, role: "e2e" });
  expect(succeeded(created), created.body.slice(0, 300)).toBe(true);
  const [{ instances_id }] = dbQuery<{ instances_id: number }>("SELECT instances_id FROM instances WHERE instances_name = ?", [name]);
  try {
    const state = () => dbQuery<Record<string, any>>("SELECT instances_name, instances_deleted FROM instances WHERE instances_id = ?", [instances_id])[0];
    await asA.api("/api/instances/editInstanceServerAdmin.php", { formData: formData({ instances_id, instances_name: `${name} renamed` }) });
    await asA.api("/api/instances/delete.php", { instances_id });
    expect(state()).toEqual({ instances_name: name, instances_deleted: 0 });

    expect(succeeded(await admin.api("/api/instances/editInstanceServerAdmin.php", { formData: formData({ instances_id, instances_name: `${name} renamed` }) }))).toBe(true);
    expect(succeeded(await admin.api("/api/instances/delete.php", { instances_id }))).toBe(true);
    expect(state()).toEqual({ instances_name: `${name} renamed`, instances_deleted: 1 });
    await asA.api("/api/instances/unDelete.php", { instances_id });
    await asA.api("/api/instances/permanentlyDelete.php", { instances_id });
    expect(state()).toEqual({ instances_name: `${name} renamed`, instances_deleted: 1 });
    expect(succeeded(await admin.api("/api/instances/unDelete.php", { instances_id }))).toBe(true);
    expect(state().instances_deleted).toBe(0);
    // Only a deleted business can be permanently deleted
    expect(succeeded(await admin.api("/api/instances/permanentlyDelete.php", { instances_id }))).toBe(false);
    await admin.api("/api/instances/delete.php", { instances_id });
    expect(succeeded(await admin.api("/api/instances/permanentlyDelete.php", { instances_id }))).toBe(true);
    expect(state()).toBeUndefined();
    expect(dbQuery("SELECT instances_id FROM instances WHERE instances_name = ?", [name])).toHaveLength(0);
  } finally {
    // The super admin's membership of the business they made
    dbQuery("UPDATE userInstances SET userInstances_deleted = 1 WHERE instancePositions_id IN (SELECT instancePositions_id FROM instancePositions WHERE instances_id = ?)", [instances_id]);
    dbQuery("UPDATE instances SET instances_deleted = 1 WHERE instances_id = ?", [instances_id]);
  }
  expect(mentions(await asA.api("/api/instances/list.php"), `${a.marker} Ltd`)).toBe(true);
});

test("a business can only be made with a real currency", async () => {
  const name = `E2E currency business ${Date.now()}`;
  const bad = await admin.api("/api/instances/new.php", { instances_name: `${name} bad`, role: "e2e", instances_config_currency: "NOTACURRENCY" });
  expect(bad.json).toMatchObject({ result: false });
  expect(dbQuery("SELECT instances_id FROM instances WHERE instances_name = ?", [`${name} bad`])).toHaveLength(0);

  expect(succeeded(await admin.api("/api/instances/new.php", { instances_name: name, role: "e2e", instances_config_currency: "EUR" }))).toBe(true);
  const [{ instances_id, currency }] = dbQuery<{ instances_id: number; currency: string }>(
    "SELECT instances_id, instances_config_currency currency FROM instances WHERE instances_name = ?", [name],
  );
  expect(currency).toBe("EUR");
  dbQuery("UPDATE userInstances SET userInstances_deleted = 1 WHERE instancePositions_id IN (SELECT instancePositions_id FROM instancePositions WHERE instances_id = ?)", [instances_id]);
  dbQuery("UPDATE instances SET instances_deleted = 1 WHERE instances_id = ?", [instances_id]);
});

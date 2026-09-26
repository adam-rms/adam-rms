import { test, expect, dbQuery, newSession, seedTenants, succeeded } from "../tenants";
import { BASE_URL } from "../env";

/** A user changing their own account. Uses A's limited user, and B's to check nobody else's account changes. */

test.afterEach(() => {
  seedTenants();
});

const user = (id: number) => dbQuery<Record<string, any>>("SELECT * FROM users WHERE users_userid = ?", [id])[0];

test("the account settings endpoints only change the caller's own account", async ({ asLimitedA, tenants: { a, b } }) => {
  const other = user(b.users.limited.id);
  expect(succeeded(await asLimitedA.api("/api/account/acceptTOS.php"))).toBe(true);
  expect(succeeded(await asLimitedA.api("/api/account/theme.php", { dark: 1 }))).toBe(true);
  expect(succeeded(await asLimitedA.api("/api/account/widgetToggle.php", { widgetName: "e2eWidget" }))).toBe(true);
  // Without USERS:EDIT:NOTIFICATION_SETTINGS / USERS:EDIT:THUMBNAIL, another user's ID is ignored
  expect(succeeded(await asLimitedA.api("/api/account/notifications.php", { users_userid: b.users.limited.id, settings: { e2e: "1" } }))).toBe(true);
  expect(succeeded(await asLimitedA.api("/api/account/thumbnail.php", { users_userid: b.users.limited.id, thumbnail: 123456 }))).toBe(true);

  const own = user(a.users.limited.id);
  expect(own.users_termsAccepted).not.toBeNull();
  expect(own).toMatchObject({ users_dark_mode: 1, users_notificationSettings: JSON.stringify({ e2e: "1" }), users_thumbnail: 123456 });
  expect(own.users_widgets.split(",")).toContain("e2eWidget");
  expect(user(b.users.limited.id)).toEqual(other);

  await asLimitedA.api("/api/account/theme.php");
  await asLimitedA.api("/api/account/widgetToggle.php", { widgetName: "e2eWidget" });
  expect(user(a.users.limited.id).users_dark_mode).toBe(0);
  expect((user(a.users.limited.id).users_widgets ?? "").split(",")).not.toContain("e2eWidget");
});

test("api/account/disconnectOAuth.php only disconnects the caller's own sign-in", async ({ asLimitedA, tenants: { a, b } }) => {
  dbQuery("UPDATE users SET users_oauth_googleid = 'e2e-google', users_oauth_microsoftid = 'e2e-microsoft' WHERE users_userid IN (?, ?)", [a.users.limited.id, b.users.limited.id]);
  expect(succeeded(await asLimitedA.api("/api/account/disconnectOAuth.php", { users_userid: b.users.limited.id, provider: "google" }))).toBe(false);
  expect(succeeded(await asLimitedA.api("/api/account/disconnectOAuth.php", { users_userid: a.users.limited.id, provider: "other" }))).toBe(false);
  expect(succeeded(await asLimitedA.api("/api/account/disconnectOAuth.php", { users_userid: a.users.limited.id, provider: "google" }))).toBe(true);
  expect(succeeded(await asLimitedA.api("/api/account/disconnectOAuth.php", { users_userid: a.users.limited.id, provider: "microsoft" }))).toBe(true);
  expect(user(b.users.limited.id)).toMatchObject({ users_oauth_googleid: "e2e-google", users_oauth_microsoftid: "e2e-microsoft" });
  expect(user(a.users.limited.id)).toMatchObject({ users_oauth_googleid: null, users_oauth_microsoftid: null });
});

test("linking Google or Microsoft does nothing while they aren't configured", async ({ asLimitedA, tenants: { a } }) => {
  for (const endpoint of ["/api/account/oauth-link/google.php", "/api/account/oauth-link/microsoft.php"]) {
    await asLimitedA.request.get(endpoint, { maxRedirects: 0 });
  }
  expect(user(a.users.limited.id)).toMatchObject({ users_oauth_googleid: null, users_oauth_microsoftid: null });
});

test("api/account/changePass.php needs the current password and a long enough new one", async ({ playwright, tenants: { a, password } }) => {
  const request = await playwright.request.newContext({ baseURL: BASE_URL });
  const session = await newSession(request, a.users.limited.email, password);
  const newPassword = "e2e new password 123";
  expect(succeeded(await session.api("/api/account/changePass.php", { oldpass: "wrong password", newpass: newPassword }))).toBe(false);
  expect(succeeded(await session.api("/api/account/changePass.php", { oldpass: password, newpass: "short" }))).toBe(false);
  expect(succeeded(await session.api("/api/account/changePass.php", { oldpass: password, newpass: newPassword }))).toBe(true);
  await request.dispose();

  const login = async (pass: string) => {
    const context = await playwright.request.newContext({ baseURL: BASE_URL });
    const result = await (await context.post("/api/login/login.php", { form: { formInput: a.users.limited.email, password: pass } })).json();
    await context.dispose();
    return result.result;
  };
  expect(await login(newPassword)).toBe(true);
  expect(await login(password)).toBe(false);
});

test("api/account/forcePasswordChange.php only works when a password change is due", async ({ asLimitedA, tenants: { a } }) => {
  expect((await asLimitedA.api("/api/account/forcePasswordChange.php", { pass: "e2e new password 123" })).body).toBe("Error");
  dbQuery("UPDATE users SET users_changepass = 1 WHERE users_userid = ?", [a.users.limited.id]);
  expect((await asLimitedA.api("/api/account/forcePasswordChange.php", { pass: "short" })).body).toContain("Password too short");
  expect((await asLimitedA.api("/api/account/forcePasswordChange.php", { pass: "e2e new password 123" })).body).toBe("1");
  expect(user(a.users.limited.id).users_changepass).toBe(0);
});

test("api/account/reSendVerificationEmail.php makes a new code only while the email is unverified", async ({ asLimitedA, tenants: { a } }) => {
  const codes = () => dbQuery("SELECT emailVerificationCodes_id FROM emailVerificationCodes WHERE users_userid = ?", [a.users.limited.id]).length;
  const before = codes();
  await asLimitedA.api("/api/account/reSendVerificationEmail.php");
  expect(codes(), "already verified").toBe(before);
  dbQuery("UPDATE users SET users_emailVerified = 0 WHERE users_userid = ?", [a.users.limited.id]);
  // No email provider is configured, so sending fails, but the code is made first
  await asLimitedA.api("/api/account/reSendVerificationEmail.php");
  expect(codes()).toBe(before + 1);
});

test("api/account/calendar-export.php needs the user's calendar key", async ({ request, tenants: { a } }) => {
  dbQuery("UPDATE users SET users_calendarHash = 'e2e-user-calendar' WHERE users_userid = ?", [a.users.full.id]);
  const calendar = (uid: number, key: string) => request.post("/api/account/calendar-export.php", { form: { uid, key } }).then((r) => r.text());
  // The seeded crew assignment puts A's full user on A's project
  const own = await calendar(a.users.full.id, "e2e-user-calendar");
  expect(own).toContain("BEGIN:VCALENDAR");
  expect(own).toContain(`${a.marker} project`);
  expect(await calendar(a.users.limited.id, "e2e-user-calendar")).toBe("404");
  expect(await calendar(a.users.full.id, "")).toBe("404");
});

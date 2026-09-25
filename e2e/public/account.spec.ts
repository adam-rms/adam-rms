import { test, expect } from "@playwright/test";
import { dbQuery, seedTenants, type Tenants } from "../tenants";

/** Signing up, logging in and account recovery, without a session. Uses business A's limited user. */

let tenants: Tenants;
test.beforeAll(() => {
  tenants = seedTenants();
});
test.afterEach(() => {
  seedTenants();
});

const user = (id: number) => dbQuery<Record<string, any>>("SELECT * FROM users WHERE users_userid = ?", [id])[0];

test("login/index.php shows the login form", async ({ request }) => {
  const page = await (await request.get("/login/index.php")).text();
  expect(page).toContain('id="emailInput"');
  expect(page).toContain('id="passwordInput"');
});

test("signing in with Google or Microsoft does nothing while they aren't configured", async ({ request }) => {
  for (const provider of ["/login/oauth/google.php", "/login/oauth/microsoft.php"]) {
    const response = await request.get(provider, { maxRedirects: 0 });
    const body = await response.text();
    expect(response.status(), provider).toBeLessThan(500);
    expect(body, provider).not.toContain("Fatal error");
    // Either the login page again, or a redirect back to it
    if (response.status() === 302) expect(response.headers()["location"]).toContain("/login");
    else expect(body).toContain('id="emailInput"');
  }
});

test("api/account/isDefaultAccountEnabled.php says whether the default admin password is still set", async ({ request }) => {
  const response = await (await request.get("/api/account/isDefaultAccountEnabled.php")).json();
  expect(response.result).toBe(true);
  expect(typeof response.response.enabled).toBe("boolean");
});

test.describe("password reset", () => {
  const newCode = (userId: number, timestamp = new Date()) => {
    const code = `e2e-reset-${Date.now()}-${Math.random().toString(36).slice(2)}`;
    dbQuery("INSERT INTO passwordResetCodes (passwordResetCodes_code, passwordResetCodes_used, passwordResetCodes_timestamp, passwordResetCodes_valid, users_userid) VALUES (?, 0, ?, 1, ?)", [code, timestamp.toISOString().slice(0, 19).replace("T", " "), userId]);
    return code;
  };

  test("api/login/forgotPassword.php makes a reset code, and doesn't say whether the account exists", async ({ request }) => {
    const { a } = tenants;
    const codes = () => dbQuery("SELECT passwordResetCodes_id FROM passwordResetCodes WHERE users_userid = ?", [a.users.limited.id]).length;
    const before = codes();
    const unknown = await (await request.post("/api/login/forgotPassword.php", { form: { formInput: "nobody-e2e@example.com" } })).json();
    const known = await (await request.post("/api/login/forgotPassword.php", { form: { formInput: a.users.limited.email } })).json();
    expect(unknown).toEqual(known);
    expect(codes()).toBe(before + 1);
  });

  test("api/account/passwordReset.php asks before resetting, then resets once", async ({ request }) => {
    const { a } = tenants;
    const code = newCode(a.users.limited.id);
    const confirm = await request.get(`/api/account/passwordReset.php?code=${code}`);
    expect(await confirm.text()).toContain("Reset My Password");
    expect(user(a.users.limited.id).users_changepass).toBe(0);

    const reset = await request.post("/api/account/passwordReset.php", { form: { code }, maxRedirects: 0 });
    expect(reset.status()).toBe(302);
    expect(user(a.users.limited.id)).toMatchObject({ users_password: "RESET", users_changepass: 1 });
    expect(dbQuery("SELECT passwordResetCodes_valid, passwordResetCodes_used FROM passwordResetCodes WHERE passwordResetCodes_code = ?", [code])[0]).toEqual({ passwordResetCodes_valid: 0, passwordResetCodes_used: 1 });

    // The code can't be used again
    dbQuery("UPDATE users SET users_password = 'e2e', users_changepass = 0 WHERE users_userid = ?", [a.users.limited.id]);
    await request.post("/api/account/passwordReset.php", { form: { code }, maxRedirects: 0 });
    expect(user(a.users.limited.id)).toMatchObject({ users_password: "e2e", users_changepass: 0 });
  });

  test("api/account/passwordReset.php won't use an expired or unknown code", async ({ request }) => {
    const { a } = tenants;
    const expired = newCode(a.users.limited.id, new Date(Date.now() - 3 * 24 * 60 * 60 * 1000));
    const response = await request.post("/api/account/passwordReset.php", { form: { code: expired }, maxRedirects: 0 });
    expect(await response.text()).toContain("Your code has expired");
    const unknown = await request.post("/api/account/passwordReset.php", { form: { code: "e2e-not-a-code" }, maxRedirects: 0 });
    expect(unknown.status()).toBe(302);
    expect(user(a.users.limited.id).users_changepass).toBe(0);
  });
});

test("api/account/verifyEmail.php verifies the email the code was sent for", async ({ request }) => {
  const { a } = tenants;
  dbQuery("UPDATE users SET users_emailVerified = 0 WHERE users_userid = ?", [a.users.limited.id]);
  const code = `e2e-verify-${Date.now()}`;
  dbQuery("INSERT INTO emailVerificationCodes (emailVerificationCodes_code, emailVerificationCodes_used, emailVerificationCodes_timestamp, emailVerificationCodes_valid, users_userid) VALUES (?, 0, NOW(), 1, ?)", [code, a.users.limited.id]);
  await request.get("/api/account/verifyEmail.php?code=e2e-not-a-code", { maxRedirects: 0 });
  expect(user(a.users.limited.id).users_emailVerified).toBe(0);
  await request.get(`/api/account/verifyEmail.php?code=${code}`, { maxRedirects: 0 });
  expect(user(a.users.limited.id).users_emailVerified).toBe(1);
  expect(dbQuery("SELECT emailVerificationCodes_valid FROM emailVerificationCodes WHERE emailVerificationCodes_code = ?", [code])[0]).toEqual({ emailVerificationCodes_valid: 0 });
});

test("api/login/magicLogin.php refuses unknown emails", async ({ request }) => {
  const response = await (await request.post("/api/login/magicLogin.php", { form: { formInput: "nobody-e2e@example.com", redirect: "" } })).json();
  expect(response.result).toBe(false);
  expect(await (await request.post("/api/login/magicLogin.php")).text()).toBe("404");
});

test.describe("signing up", () => {
  const email = `e2e-signup-${Date.now()}@example.com`;
  const form = (overrides: Record<string, string> = {}) => ({ name1: "E2E", name2: "Signup", username: `e2esignup${Date.now()}`, email, password: "e2e long password 123", ...overrides });
  test.afterEach(() => {
    dbQuery("DELETE FROM users WHERE users_email = ?", [email]);
    dbQuery("DELETE FROM config WHERE config_key = 'AUTH_SIGNUP_ENABLED'");
  });

  test("api/login/signup.php makes an unverified account", async ({ request }) => {
    const { a } = tenants;
    expect((await (await request.post("/api/login/signup.php", { form: form({ password: "short" }) })).json()).result).toBe(false);
    expect((await (await request.post("/api/login/signup.php", { form: form({ email: a.users.limited.email }) })).json()).result).toBe(false);
    expect((await (await request.post("/api/login/signup.php", { form: form({ username: "e2e_tenant_a_limited" }) })).json()).result).toBe(false);
    expect(dbQuery("SELECT users_userid FROM users WHERE users_email = ?", [email])).toHaveLength(0);

    expect((await (await request.post("/api/login/signup.php", { form: form() })).json()).result).toBe(true);
    const [created] = dbQuery<Record<string, any>>("SELECT users_emailVerified, users_deleted FROM users WHERE users_email = ?", [email]);
    expect(created).toEqual({ users_emailVerified: 0, users_deleted: 0 });
  });

  test("api/login/signup.php is off when AUTH_SIGNUP_ENABLED is", async ({ request }) => {
    dbQuery("REPLACE INTO config (config_key, config_value) VALUES ('AUTH_SIGNUP_ENABLED', 'Disabled')");
    expect(await (await request.post("/api/login/signup.php", { form: form() })).text()).toBe("404");
    expect(dbQuery("SELECT users_userid FROM users WHERE users_email = ?", [email])).toHaveLength(0);
  });
});

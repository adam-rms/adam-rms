import { test, expect, formData, dbQuery, seedTenants, newSession, type Tenant } from "../tenants";
import { BASE_URL } from "../env";

/**
 * Roles and memberships: an admin of business A mustn't be able to give anyone a role in business B.
 * Each case also does the same thing with A's own role as a control.
 */

const isMember = (userId: number, instanceId: number) =>
  Number(dbQuery<{ n: number }>("SELECT COUNT(*) n FROM userInstances JOIN instancePositions USING (instancePositions_id) WHERE users_userid = ? AND instances_id = ? AND userInstances_deleted = 0", [userId, instanceId])[0].n) > 0;
const membershipId = (userId: number) =>
  dbQuery<{ userInstances_id: number }>("SELECT userInstances_id FROM userInstances WHERE users_userid = ? AND userInstances_deleted = 0", [userId])[0].userInstances_id;
const actions = (positionId: number) =>
  dbQuery<{ instancePositions_actions: string }>("SELECT instancePositions_actions FROM instancePositions WHERE instancePositions_id = ?", [positionId])[0].instancePositions_actions.split(",");

test.afterEach(() => {
  seedTenants();
});

test("editUser.php won't put A's own membership in B's role", async ({ asA, tenants: { a, b } }) => {
  await asA.api("/api/instances/editUser.php", { userinstanceid: membershipId(a.users.full.id), position: b.positions.full, label: "e2e" });
  expect(isMember(a.users.full.id, b.instanceId)).toBe(false);

  // Control: a role in A
  await asA.api("/api/instances/editUser.php", { userinstanceid: membershipId(a.users.limited.id), position: a.positions.full, label: "e2e" });
  expect(dbQuery("SELECT instancePositions_id FROM userInstances WHERE userInstances_id = ?", [membershipId(a.users.limited.id)])[0]).toEqual({ instancePositions_id: a.positions.full });
});

test("editUser.php won't change the role of B's users", async ({ asA, tenants: { b } }) => {
  await asA.api("/api/instances/editUser.php", { userinstanceid: membershipId(b.users.limited.id), position: b.positions.full, label: "e2e" });
  expect(dbQuery("SELECT instancePositions_id FROM userInstances WHERE userInstances_id = ?", [membershipId(b.users.limited.id)])[0]).toEqual({ instancePositions_id: b.positions.limited });
});

test("instancePermissionsEditor.php won't change B's roles", async ({ asA, tenants: { a, b } }) => {
  await asA.api("/api/permissions/instancePermissionsEditor.php", { position: b.positions.limited, addpermission: "PROJECTS:DELETE" });
  expect(actions(b.positions.limited)).not.toContain("PROJECTS:DELETE");

  await asA.api("/api/permissions/instancePermissionsEditor.php", { position: a.positions.limited, addpermission: "PROJECTS:DELETE" });
  expect(actions(a.positions.limited), "control: A's own role").toContain("PROJECTS:DELETE");
});

/** Logs in as A's limited user after taking them out of A, so they can join a business as someone new would */
async function outsider(playwright: any, a: Tenant) {
  dbQuery("UPDATE userInstances SET userInstances_deleted = 1 WHERE users_userid = ?", [a.users.limited.id]);
  const request = await playwright.request.newContext({ baseURL: BASE_URL });
  return { session: await newSession(request, a.users.limited.email, "password!"), dispose: () => request.dispose() };
}

for (const endpoint of ["new.php", "edit.php"]) {
  test(`a signup code made with signupCodes/${endpoint} can't give B's role`, async ({ asA, playwright, tenants: { a, b } }) => {
    const codes = { b: `e2e-code-b-${Date.now()}`, a: `e2e-code-a-${Date.now()}` };
    for (const [key, position] of [["b", b.positions.full], ["a", a.positions.limited]] as const) {
      if (endpoint === "new.php") {
        await asA.api("/api/instances/signupCodes/new.php", { formData: formData({ signupCodes_name: codes[key], signupCodes_role: "e2e", instancePositions_id: position }) });
      } else {
        await asA.api("/api/instances/signupCodes/new.php", { formData: formData({ signupCodes_name: codes[key], signupCodes_role: "e2e", instancePositions_id: a.positions.limited }) });
        const [{ signupCodes_id }] = dbQuery<{ signupCodes_id: number }>("SELECT signupCodes_id FROM signupCodes WHERE signupCodes_name = ?", [codes[key]]);
        await asA.api("/api/instances/signupCodes/edit.php", { formData: formData({ signupCodes_id, instancePositions_id: position }) });
      }
    }
    const { session, dispose } = await outsider(playwright, a);
    await session.api("/api/instances/addUserFromCode.php", { signupCodes_name: codes.b });
    const joinedB = isMember(a.users.limited.id, b.instanceId);
    await session.api("/api/instances/addUserFromCode.php", { signupCodes_name: codes.a });
    const joinedA = isMember(a.users.limited.id, a.instanceId);
    await dispose();
    dbQuery("UPDATE signupCodes SET signupCodes_deleted = 1 WHERE signupCodes_name IN (?, ?)", [codes.a, codes.b]);
    dbQuery("UPDATE userInstances SET userInstances_deleted = 1 WHERE users_userid = ? AND signupCodes_id IS NOT NULL", [a.users.limited.id]);

    expect(joinedB).toBe(false);
    expect(joinedA, "control: a code for A's own role").toBe(true);
  });
}

test("a signup code can't be moved into B", async ({ asA, tenants: { a, b } }) => {
  const code = `e2e-code-move-${Date.now()}`;
  await asA.api("/api/instances/signupCodes/new.php", { formData: formData({ signupCodes_name: code, signupCodes_role: "e2e", instancePositions_id: a.positions.limited, instances_id: b.instanceId }) });
  const [{ signupCodes_id }] = dbQuery<{ signupCodes_id: number }>("SELECT signupCodes_id FROM signupCodes WHERE signupCodes_name = ?", [code]);
  await asA.api("/api/instances/signupCodes/edit.php", { formData: formData({ signupCodes_id, instances_id: b.instanceId }) });
  const [row] = dbQuery<{ instances_id: number }>("SELECT instances_id FROM signupCodes WHERE signupCodes_id = ?", [signupCodes_id]);
  dbQuery("UPDATE signupCodes SET signupCodes_deleted = 1 WHERE signupCodes_id = ?", [signupCodes_id]);
  expect(row.instances_id).toBe(a.instanceId);
});

test("trusted domains can't give B's role", async ({ asA, playwright, tenants: { a, b } }) => {
  const join = async (position: number, instanceId: number) => {
    // The users' emails are all @example.com
    await asA.api("/api/instances/editInstanceTrustedDomains.php", { formData: formData({ domains: "example.com", instancePositions_id: position, userInstances_label: "e2e" }) });
    const { session, dispose } = await outsider(playwright, a);
    await session.api("/api/instances/addUserFromTrustedDomain.php", { instances_id: a.instanceId });
    const joined = isMember(a.users.limited.id, instanceId);
    await dispose();
    dbQuery("UPDATE instances SET instances_trustedDomains = NULL WHERE instances_id = ?", [a.instanceId]);
    return joined;
  };
  expect(await join(b.positions.full, b.instanceId)).toBe(false);
  // The endpoint refused B's role, so check a role already saved (from before the fix) can't be used either
  dbQuery("UPDATE instances SET instances_trustedDomains = ? WHERE instances_id = ?", [JSON.stringify({ domains: ["example.com"], instancePositions_id: b.positions.full, userInstances_label: "e2e" }), a.instanceId]);
  const { session, dispose } = await outsider(playwright, a);
  await session.api("/api/instances/addUserFromTrustedDomain.php", { instances_id: a.instanceId });
  const joinedWithSaved = isMember(a.users.limited.id, b.instanceId);
  await dispose();
  dbQuery("UPDATE instances SET instances_trustedDomains = NULL WHERE instances_id = ?", [a.instanceId]);
  expect(joinedWithSaved).toBe(false);

  seedTenants();
  expect(await join(a.positions.limited, a.instanceId), "control: A's own role").toBe(true);
});

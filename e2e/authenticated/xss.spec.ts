import type { Page } from "@playwright/test";
import { cacheCdn } from "../cdn";
import { TEST_USER } from "../env";
import { login } from "../fixtures";
import { businessPages, placeholders, serverPages } from "../pages";
import { dbQuery, expect, test, type Tenant } from "../tenants";

/**
 * Stored cross-site scripting: anything a user types into a name, note or comment is shown to other users,
 * so a page that prints it without escaping it lets one user run script in another's browser. This gives
 * business A's records names and notes that contain script, opens every page, and fails if any of it runs
 * (or if one breaks a page's own script, which means it landed inside a <script> unescaped).
 *
 * Only plain-text fields are set; ones meant to hold HTML (CMS pages, training steps) are left alone. The fields'
 * values are put back afterwards.
 */

type Field = { table: string; idColumn: string; column: string; id: (a: Tenant) => number };
const f = (table: string, idColumn: string, column: string, id: (a: Tenant) => number): Field => ({ table, idColumn, column, id });
const FIELDS: Field[] = [
  f("projects", "projects_id", "projects_name", (a) => a.projectId),
  f("projects", "projects_id", "projects_description", (a) => a.projectId),
  f("projects", "projects_id", "projects_name", (a) => a.subProjectId),
  f("clients", "clients_id", "clients_name", (a) => a.clientId),
  f("clients", "clients_id", "clients_notes", (a) => a.clientId),
  f("locations", "locations_id", "locations_name", (a) => a.locationId),
  f("locations", "locations_id", "locations_address", (a) => a.locationId),
  f("manufacturers", "manufacturers_id", "manufacturers_name", (a) => a.manufacturerId),
  f("assetTypes", "assetTypes_id", "assetTypes_name", (a) => a.assetTypeId),
  f("assetCategories", "assetCategories_id", "assetCategories_name", (a) => a.categoryId),
  f("assetCategoriesGroups", "assetCategoriesGroups_id", "assetCategoriesGroups_name", (a) => a.categoryGroupId),
  f("assetGroups", "assetGroups_id", "assetGroups_name", (a) => a.assetGroupId),
  f("assets", "assets_id", "assets_notes", (a) => a.assetId),
  f("assetsAssignments", "assetsAssignments_id", "assetsAssignments_comment", (a) => a.assignmentId),
  f("assetsAssignmentsStatus", "assetsAssignmentsStatus_id", "assetsAssignmentsStatus_name", (a) => a.assignmentStatusId),
  f("projectsStatuses", "projectsStatuses_id", "projectsStatuses_name", (a) => a.projectStatusIds.first),
  f("projectsStatuses", "projectsStatuses_id", "projectsStatuses_name", (a) => a.projectStatusIds.second),
  f("projectsTypes", "projectsTypes_id", "projectsTypes_name", (a) => a.projectTypeId),
  f("projectsNotes", "projectsNotes_id", "projectsNotes_title", (a) => a.noteId),
  f("payments", "payments_id", "payments_reference", (a) => a.paymentId),
  f("payments", "payments_id", "payments_supplier", (a) => a.paymentId),
  f("crewAssignments", "crewAssignments_id", "crewAssignments_role", (a) => a.crewAssignmentId),
  f("projectsVacantRoles", "projectsVacantRoles_id", "projectsVacantRoles_name", (a) => a.vacantRoleId),
  f("maintenanceJobs", "maintenanceJobs_id", "maintenanceJobs_title", (a) => a.maintenanceJobId),
  f("maintenanceJobs", "maintenanceJobs_id", "maintenanceJobs_faultDescription", (a) => a.maintenanceJobId),
  f("cmsPages", "cmsPages_id", "cmsPages_name", (a) => a.cmsPageId),
  f("modules", "modules_id", "modules_name", (a) => a.moduleId),
  f("modulesSteps", "modulesSteps_id", "modulesSteps_name", (a) => a.moduleStepId),
  f("instancePositions", "instancePositions_id", "instancePositions_displayName", (a) => a.positions.limited),
  f("signupCodes", "signupCodes_id", "signupCodes_role", (a) => a.signupCodeId),
  f("users", "users_userid", "users_name1", (a) => a.users.limited.id),
  f("users", "users_userid", "users_name2", (a) => a.users.limited.id),
  f("users", "users_userid", "users_name2", (a) => a.users.full.id),
];

/** Runs __xss(n) if it's printed unescaped into HTML, an attribute or a script; short enough for a varchar(100) */
const payload = (n: number) => `x<img src=x onerror=__xss(${n})>'"</script><script>__xss(${n})</script>`;
const fieldName = (n: number) => `${FIELDS[n].table}.${FIELDS[n].column}`;

async function scriptsRunOpening(page: Page, url: string) {
  const problems: string[] = [];
  const onError = (error: Error) => problems.push(`a record's text broke the page's script: ${error.message}`);
  page.on("pageerror", onError);
  await page.goto(url, { waitUntil: "load" });
  await page.waitForLoadState("networkidle", { timeout: 10_000 }).catch(() => {});
  page.off("pageerror", onError);
  const ran: number[] = await page.evaluate(() => (window as unknown as { __xssRan: number[] }).__xssRan);
  for (const n of new Set(ran)) problems.push(`script in ${fieldName(n)} ran`);
  return problems;
}

test.describe("record names and notes containing script", () => {
  let originals: { field: Field; id: number; value: unknown }[] = [];

  test.beforeAll(({ seeded: { a } }) => {
    originals = FIELDS.map((field) => {
      const id = field.id(a);
      const [row] = dbQuery<Record<string, unknown>>(`SELECT \`${field.column}\` v FROM \`${field.table}\` WHERE \`${field.idColumn}\` = ?`, [id]);
      return { field, id, value: row?.v ?? null };
    });
    FIELDS.forEach((field, n) => {
      dbQuery(`UPDATE \`${field.table}\` SET \`${field.column}\` = ? WHERE \`${field.idColumn}\` = ?`, [payload(n), field.id(a)]);
    });
  });
  test.afterAll(() => {
    for (const { field, id, value } of originals) {
      dbQuery(`UPDATE \`${field.table}\` SET \`${field.column}\` = ? WHERE \`${field.idColumn}\` = ?`, [value, id]);
    }
  });

  test.beforeEach(async ({ page }) => {
    await cacheCdn(page.context());
    await page.addInitScript(() => {
      const w = window as unknown as { __xssRan: number[]; __xss: (n: number) => void };
      w.__xssRan = [];
      w.__xss = (n) => w.__xssRan.push(n);
    });
  });

  for (const c of businessPages) {
    test(`${c.url(placeholders)} doesn't run it`, async ({ page, tenants: { a, password } }) => {
      await login(page, a.users.full.email, password);
      expect(await scriptsRunOpening(page, c.url(a))).toEqual([]);
    });
  }
  for (const url of serverPages) {
    test(`${url} doesn't run it`, async ({ page }) => {
      await login(page, TEST_USER.email, TEST_USER.password);
      expect(await scriptsRunOpening(page, url)).toEqual([]);
    });
  }
});

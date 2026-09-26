import fs from "fs";
import path from "path";
import { BASE_URL } from "../env";
import { dbQuery, expect, newSession, type Params, seedTenants, Session, test, type Tenant } from "../tenants";

/**
 * Sends every API endpoint a value that breaks out of an SQL string, in each request parameter the endpoint
 * reads, and fails if MySQL then reports a syntax error — which means the value was pasted into a query
 * instead of being passed as a parameter, so it could be made to run SQL of the caller's choosing. Nearly every
 * query goes through $DBLIB's parameterised methods; this catches a new one that doesn't.
 *
 * The parameters are found by scanning each file for $_POST['x'], $_GET['x'], $_REQUEST['x'] and $array['x']
 * (the array most endpoints build from the formData they're sent). Each is sent both on its own and inside
 * formData, alongside real IDs from business A for the endpoint's other parameters, so the request gets past
 * the "not found" checks to the queries that use the value. Requests change A's data, so A and B are re-seeded
 * after each endpoint; each endpoint gets a fresh login, in case one ended the last session.
 *
 * Some endpoints save whatever fields they're sent, using the field names as column names, which $DBLIB wraps in
 * backticks but doesn't escape. So each endpoint is also sent an extra field whose name contains a backtick.
 */

const PAYLOAD = `1'"\`) OR (1=1`;
/** A field name that breaks out of the backticks around a column name */
const PAYLOAD_NAME = "e2e_probe` = 1, `e2e_probe";
/** What MySQL says when a value has broken the statement it was pasted into */
const SQL_ERROR = /You have an error in your SQL syntax|mysqli_sql_exception: Unknown column/;

const apiDir = path.join(__dirname, "..", "..", "src", "api");
/** Included by endpoints rather than requested */
const NOT_ENDPOINTS = new Set(["apiHead.php", "apiHeadSecure.php", "openApiYAMLHeaders.php", "login/loginAjaxHead.php", "notifications/main.php", "notifications/notificationTypes.php", "notifications/email/email.php"]);

function endpoints() {
  const found: { file: string; params: string[] }[] = [];
  const walk = (dir: string) => {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
      const full = path.join(dir, entry.name);
      if (entry.isDirectory()) walk(full);
      else if (entry.name.endsWith(".php")) {
        const file = path.relative(apiDir, full).split(path.sep).join("/");
        if (NOT_ENDPOINTS.has(file)) continue;
        const code = fs.readFileSync(full, "utf8");
        const params = new Set<string>();
        for (const match of code.matchAll(/\$(?:_POST|_GET|_REQUEST|array)\[['"](\w+)['"]\]/g)) params.add(match[1]);
        params.delete("formData"); // The payload goes inside it, as well as at the top level
        found.push({ file, params: [...params].sort() });
      }
    }
  };
  walk(apiDir);
  return found.sort((x, y) => x.file.localeCompare(y.file));
}

/** A real record of business A for each ID parameter, so requests get past "not found" checks */
function realValue(param: string, a: Tenant): string | number {
  const ids: Record<string, number | string> = {
    instances_id: a.instanceId, projects_id: a.projectId, assets_id: a.spareAssetId, assetTypes_id: a.assetTypeId,
    clients_id: a.clientId, locations_id: a.locationId, manufacturers_id: a.manufacturerId, assetCategories_id: a.categoryId,
    assetCategoriesGroups_id: a.categoryGroupId, assetGroups_id: a.assetGroupId, assetsAssignments_id: a.assignmentId,
    maintenanceJobs_id: a.maintenanceJobId, maintenanceJobsMessages_id: a.maintenanceMessageId, cmsPages_id: a.cmsPageId,
    modules_id: a.moduleId, modulesSteps_id: a.moduleStepId, payments_id: a.paymentId, crewAssignments_id: a.crewAssignmentId,
    projectsVacantRoles_id: a.vacantRoleId, projectsVacantRolesApplications_id: a.vacancyApplicationId, users_userid: a.users.limited.id,
    instancePositions_id: a.positions.limited, projectsStatuses_id: a.projectStatusIds.second, projectsTypes_id: a.projectTypeId,
    projectsType_id: a.projectTypeId, s3files_id: a.fileId, assetsBarcodes_id: a.barcodeId, barcodes_id: a.barcodeId,
    assetsAssignmentsStatus_id: a.assignmentStatusId, signupCodes_id: a.signupCodeId, projectsNotes_id: a.noteId,
    userModulesCertifications_id: a.certificationId, assets_tag: a.assetTag, text: a.assetTag,
  };
  return ids[param] ?? 1;
}

/** Every parameter set to a real value, except `injected`, which gets the payload — top level and in formData */
function request(params: string[], injected: string, a: Tenant): Params {
  const values = Object.fromEntries(params.map((p) => [p, p === injected ? PAYLOAD : realValue(p, a)]));
  // instances_id picks the business (Auth reads it from $_POST); only send it in formData if the endpoint reads it from there
  return { instances_id: a.instanceId, ...values, formData: Object.entries(values).map(([name, value]) => ({ name, value })) };
}

const all = endpoints();

// instances/new.php makes a new business each time it's probed; delete them afterwards, and their memberships
let lastBusiness = 0;
test.beforeAll(() => {
  lastBusiness = dbQuery<{ id: number }>("SELECT COALESCE(MAX(instances_id), 0) id FROM instances")[0].id;
});
test.afterAll(() => {
  dbQuery("UPDATE userInstances SET userInstances_deleted = 1 WHERE instancePositions_id IN (SELECT instancePositions_id FROM instancePositions WHERE instances_id > ?)", [lastBusiness]);
  dbQuery("UPDATE instances SET instances_deleted = 1 WHERE instances_id > ?", [lastBusiness]);
});

test.describe("logged in as business A's full user", () => {
  test.afterEach(() => {
    seedTenants();
  });
  for (const { file, params } of all.filter((e) => !e.file.startsWith("login/"))) {
    test(`api/${file} doesn't paste its parameters into SQL`, async ({ playwright, seeded, tenants: { a } }) => {
      const context = await playwright.request.newContext({ baseURL: BASE_URL });
      const session = await newSession(context, seeded.a.users.full.email, seeded.password);
      const broken: string[] = [];
      for (const param of params) {
        for (const method of ["POST", "GET"] as const) {
          const response = await session.api(`/api/${file}`, request(params, param, a), method);
          if (SQL_ERROR.test(response.body)) broken.push(`${method} ${param}: ${response.body.match(/[^\n]*(SQL syntax|Unknown column)[^\n]*/)?.[0]}`);
        }
      }
      const valid = request(params, "", a);
      const named = await session.api(`/api/${file}`, {
        ...valid, [PAYLOAD_NAME]: 1, formData: [...(valid.formData as Params[]), { name: PAYLOAD_NAME, value: 1 }],
      });
      if (SQL_ERROR.test(named.body)) broken.push(`a field named ${PAYLOAD_NAME}: ${named.body.match(/[^\n]*(SQL syntax|Unknown column)[^\n]*/)?.[0]}`);
      await context.dispose();
      expect(broken).toEqual([]);
    });
  }
});

test.describe("logged out", () => {
  for (const { file, params } of all) {
    if (!file.startsWith("login/") && !file.startsWith("account/") && file !== "search/search.php") continue;
    test(`api/${file} doesn't paste its parameters into SQL for a visitor`, async ({ playwright, tenants: { a } }) => {
      const context = await playwright.request.newContext({ baseURL: BASE_URL });
      const session = new Session(context);
      const broken: string[] = [];
      for (const param of params) {
        const response = await session.api(`/api/${file}`, request(params, param, a));
        if (SQL_ERROR.test(response.body)) broken.push(`${param}: ${response.body.match(/[^\n]*(SQL syntax|Unknown column)[^\n]*/)?.[0]}`);
      }
      await context.dispose();
      expect(broken).toEqual([]);
    });
  }
});

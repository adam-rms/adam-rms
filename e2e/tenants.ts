import { execFileSync } from "child_process";
import path from "path";
import { test as base, expect, type APIRequestContext, type APIResponse } from "@playwright/test";
import { APP_ENV, BASE_URL, PHP_BINARY } from "./env";

/**
 * Two businesses, A and B, each with a full-access user, a limited user and one record of every
 * major type — see setup/tenants.php. Used by the tenant isolation and permission specs.
 */
export type Tenant = {
  /** Appears in the name of every record in the business: finding it in a response means the record leaked */
  marker: string;
  instanceId: number;
  positions: { full: number; limited: number };
  /** `deleted` is a soft-deleted account that still has its membership, as account/softDelete.php leaves it */
  users: { full: TenantUser; limited: TenantUser; deleted: TenantUser };
  deletedMembershipId: number;
  clientId: number;
  locationId: number;
  locationBarcodeValue: string;
  locationBarcodeId: number;
  manufacturerId: number;
  categoryGroupId: number;
  categoryId: number;
  assetTypeId: number;
  assetGroupId: number;
  assetTag: string;
  assetId: number;
  spareAssetTag: string;
  spareAssetId: number;
  barcodeValue: string;
  barcodeId: number;
  fileId: number;
  projectTypeId: number;
  projectStatusIds: { first: number; second: number };
  assignmentStatusId: number;
  projectId: number;
  subProjectId: number;
  assignmentId: number;
  noteId: number;
  paymentId: number;
  crewAssignmentId: number;
  vacantRoleId: number;
  vacancyApplicationId: number;
  maintenanceJobId: number;
  maintenanceMessageId: number;
  cmsPageId: number;
  cmsPageDraftId: number;
  moduleId: number;
  moduleStepId: number;
  certificationId: number;
  signupCodeId: number;
};
type TenantUser = { id: number; email: string };
export type Tenants = {
  password: string;
  a: Tenant;
  b: Tenant;
  /** Has full access to both A and B */
  sharedUser: TenantUser;
};

const repoRoot = path.resolve(__dirname, "..");
function php(script: string, args: string[] = []) {
  return execFileSync(PHP_BINARY, [path.join(__dirname, "setup", script), ...args], {
    cwd: repoRoot,
    env: { ...process.env, ...APP_ENV },
    encoding: "utf8",
  });
}

let seeded: Tenants | undefined;
/**
 * Creates the two businesses, or puts their records back how setup/tenants.php defines them. Passes the
 * IDs from the last call back in, so records are found even if a test renamed them, and IDs don't change.
 */
export function seedTenants(): Tenants {
  seeded = JSON.parse(php("tenants.php", seeded ? [JSON.stringify(seeded)] : []));
  return seeded!;
}

/** Every row belonging to a business (`data`), plus its project history (`history`, the audit log shown on project pages) */
export function snapshot(instanceId: number): { data: Record<string, unknown[]>; history: unknown[] } {
  return JSON.parse(php("db.php", ["snapshot", String(instanceId)]));
}

export function dbQuery<T = Record<string, unknown>>(sql: string, params: unknown[] = []): T[] {
  return JSON.parse(php("db.php", ["query", sql, JSON.stringify(params)]));
}

type Scalar = string | number | boolean | null;
export type Params = { [key: string]: Scalar | Params | Scalar[] | Params[] };

/** Flattens nested params into PHP's bracket syntax (formData[0][name]=...), which the API reads from $_POST */
function toForm(params: Params, prefix = "", out: Record<string, string> = {}) {
  for (const [key, value] of Object.entries(params)) {
    const name = prefix ? `${prefix}[${key}]` : key;
    if (value === null) out[name] = "";
    else if (typeof value === "object") toForm(value as Params, name, out);
    else out[name] = String(value);
  }
  return out;
}

/** The `formData` array many endpoints read their fields from ({name, value} pairs, as jQuery's serializeArray makes) */
export function formData(fields: Record<string, Scalar>): Params[] {
  return Object.entries(fields).map(([name, value]) => ({ name, value }));
}

export type Result = { status: number; body: string; json: any };
async function result(response: APIResponse): Promise<Result> {
  const body = await response.text();
  let json: any = null;
  try {
    json = JSON.parse(body);
  } catch {
    // die("404") and friends return plain text
  }
  return { status: response.status(), body, json };
}

/** A logged-in HTTP session. The browser isn't needed to call the API or read a page's HTML, and this is much faster. */
export class Session {
  constructor(readonly request: APIRequestContext) {}

  async api(endpoint: string, params: Params = {}, method: "POST" | "GET" = "POST") {
    if (method === "GET") return result(await this.request.get(endpoint, { params: toForm(params) }));
    return result(await this.request.post(endpoint, { form: toForm(params) }));
  }

  /** A multipart POST, for endpoints that take a file upload */
  async upload(endpoint: string, files: Record<string, { name: string; mimeType: string; buffer: Buffer }>) {
    return result(await this.request.post(endpoint, { multipart: files }));
  }

  async page(url: string) {
    return result(await this.request.get(url));
  }
}

export async function newSession(request: APIRequestContext, email: string, password: string) {
  const login = await request.post("/api/login/login.php", { form: { formInput: email, password } });
  expect(await login.json(), `logging in as ${email}`).toMatchObject({ result: true });
  return new Session(request);
}

/** True if the API reported success. Refusals are finish(false, ...) or a bare die("404") */
export function succeeded(response: Result) {
  return response.json?.result === true;
}

/** Whether `marker` appears anywhere in the response, ignoring case (usernames and emails are lower-cased) */
export function mentions(response: Result, marker: string) {
  return response.body.toLowerCase().includes(marker.toLowerCase());
}

/**
 * A `test` with the two businesses seeded and logged-in sessions for their users. The sessions are
 * shared by every test in the worker, so logging in happens once, not once per test.
 */
export const test = base.extend<
  { tenants: Tenants },
  { seeded: Tenants; asA: Session; asLimitedA: Session }
>({
  seeded: [async ({}, use) => use(seedTenants()), { scope: "worker" }],
  tenants: async ({ seeded }, use) => use(seeded),
  asA: [
    async ({ playwright, seeded }, use) => {
      const request = await playwright.request.newContext({ baseURL: BASE_URL });
      await use(await newSession(request, seeded.a.users.full.email, seeded.password));
      await request.dispose();
    },
    { scope: "worker" },
  ],
  asLimitedA: [
    async ({ playwright, seeded }, use) => {
      const request = await playwright.request.newContext({ baseURL: BASE_URL });
      await use(await newSession(request, seeded.a.users.limited.email, seeded.password));
      await request.dispose();
    },
    { scope: "worker" },
  ],
});

export { expect };

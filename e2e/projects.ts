import { dbQuery, expect, formData, type Session, type Tenant } from "./tenants";

/** Helpers for tests that create projects and assets and book one onto the other, through the API */

export type Dates = { start: string; end: string };

/** Creates a project in `t` delivering on `dates`, as `session` (which must have full access to `t`) */
export async function newProject(session: Session, t: Tenant, managerId: number, dates: Dates) {
  const created = await session.api("/api/projects/new.php", {
    instances_id: t.instanceId, projects_name: `Booking test ${dates.start}`, projects_manager: managerId, projectsType_id: t.projectTypeId,
  });
  expect(created.json, "creating the project").toMatchObject({ result: true });
  const id: number = created.json.response.projects_id;
  const moved = await session.api("/api/projects/changeProjectDeliverDates.php", {
    instances_id: t.instanceId, projects_id: id, projects_dates_deliver_start: dates.start, projects_dates_deliver_end: dates.end,
  });
  expect(moved.json, "setting the project's dates").toMatchObject({ result: true, response: { changed: true } });
  return id;
}

export async function newAsset(session: Session, t: Tenant) {
  const created = await session.api("/api/assets/newAssetFromType.php", {
    instances_id: t.instanceId, formData: formData({ assetTypes_id: t.assetTypeId }),
  });
  expect(created.json, "creating the asset").toMatchObject({ result: true });
  return created.json.response.assets_id as number;
}

export function assign(session: Session, t: Tenant, projectId: number, assetId: number) {
  return session.api("/api/projects/assets/assign.php", { instances_id: t.instanceId, projects_id: projectId, assets_id: assetId });
}

/** The projects `assetId` is booked on */
export function bookings(assetId: number) {
  return dbQuery<{ projects_id: number }>(
    "SELECT projects_id FROM assetsAssignments WHERE assets_id = ? AND assetsAssignments_deleted = 0 ORDER BY projects_id",
    [assetId],
  ).map((row) => row.projects_id);
}

/** Adds a status to `t` that releases its projects' assets (like "Completed" or "Cancelled") */
export function releasedStatus(t: Tenant) {
  dbQuery(
    `INSERT INTO projectsStatuses (instances_id, projectsStatuses_name, projectsStatuses_description, projectsStatuses_foregroundColour,
      projectsStatuses_backgroundColour, projectsStatuses_rank, projectsStatuses_assetsReleased, projectsStatuses_deleted)
     VALUES (?, 'Released by e2e', 'Assets released', '#000000', '#ffffff', 99, 1, 0)`,
    [t.instanceId],
  );
  return dbQuery<{ id: number }>(
    "SELECT MAX(projectsStatuses_id) id FROM projectsStatuses WHERE instances_id = ? AND projectsStatuses_assetsReleased = 1",
    [t.instanceId],
  )[0].id;
}

/** A project's money, in pence */
export type Finances = {
  subTotal: number; discounts: number; total: number;
  received: number; sales: number; subHire: number; staff: number;
  /** equipment total + sales + sub-hire + staff - received: what's left to pay */
  grandTotal: number;
  value: number; mass: number;
};

type Cache = Record<string, number>;
function latestCache(projectId: number): Cache {
  const [row] = dbQuery<Record<string, string>>(
    `SELECT projectsFinanceCache_equipmentSubTotal subTotal, projectsFinanceCache_equiptmentDiscounts discounts,
       projectsFinanceCache_equiptmentTotal total, projectsFinanceCache_salesTotal sales, projectsFinanceCache_staffTotal staff,
       projectsFinanceCache_externalHiresTotal subHire, projectsFinanceCache_paymentsReceived received,
       projectsFinanceCache_grandTotal grandTotal, projectsFinanceCache_value value, projectsFinanceCache_mass mass
     FROM projectsFinanceCache WHERE projects_id = ? ORDER BY projectsFinanceCache_timestamp DESC, projectsFinanceCache_id DESC LIMIT 1`,
    [projectId],
  );
  return Object.fromEntries(Object.entries(row ?? {}).map(([key, value]) => [key, Number(value)]));
}

function cacheRows(projectId: number) {
  return dbQuery<{ n: number }>("SELECT COUNT(*) n FROM projectsFinanceCache WHERE projects_id = ?", [projectId])[0].n;
}

/**
 * Checks the finances projects/data.php works out for the project, and that the running totals in projectsFinanceCache
 * (which endpoints adjust by the difference each change makes) already agreed with them before data.php looked.
 */
export async function expectFinances(session: Session, t: Tenant, projectId: number, expected: Finances) {
  const cacheBefore = latestCache(projectId);
  const rowsBefore = cacheRows(projectId);

  const response = await session.api("/api/projects/data.php", { instances_id: t.instanceId, id: projectId });
  expect(response.json?.result, "projects/data.php").toBe(true);
  const f = response.json.response.FINANCIALS;
  const amount = (money: { amount: string }) => Number(money.amount);
  const worked: Finances = {
    subTotal: amount(f.prices.subTotal), discounts: amount(f.prices.discounts), total: amount(f.prices.total),
    received: amount(f.payments.received.total), sales: amount(f.payments.sales.total),
    subHire: amount(f.payments.subHire.total), staff: amount(f.payments.staff.total),
    grandTotal: amount(f.payments.total), value: amount(f.value), mass: Number(f.mass),
  };
  expect(worked, "the finances projects/data.php works out").toEqual(expected);
  if (rowsBefore === 0) {
    // A new project has no running totals until it's first opened, when data.php starts them off
    expect(latestCache(projectId), "the running totals data.php started").toEqual(expected);
    return;
  }
  expect(cacheBefore, "the running totals in projectsFinanceCache").toEqual(expected);
  expect(cacheRows(projectId), "data.php found no mismatch to correct").toBe(rowsBefore);
}

/**
 * The totals table on a project's invoice or quote (project/projectInvoice.php builds the PDF in the browser, from
 * the same numbers as data.php), as { label: amount as printed }.
 */
export async function documentSummary(session: Session, projectId: number, type: "invoice" | "quote") {
  const page = await session.page(`/project/projectInvoice.php?id=${projectId}&type=${type}&finance=1`);
  expect(page.status, `opening the ${type}`).toBe(200);
  const summary: Record<string, string> = {};
  const row = /\{ text: "([^"]+)"(?:, style: \{[^}]*\})? \},\s*\{ text: "([^"]*)", style: \{ alignment: 'right' \} \}/g;
  for (const [, label, value] of page.body.matchAll(row)) summary[label] = JSON.parse(`"${value}"`);
  return summary;
}

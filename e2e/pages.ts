import type { Page } from "@playwright/test";
import type { Tenant } from "./tenants";

/**
 * Every page, with the records setup/tenants.php seeds for the IDs it needs: business pages (opened as business A's
 * full user) and server administration pages (opened as the super admin). Used by authenticated/pages-render.spec.ts
 * and authenticated/xss.spec.ts. Add new pages here.
 */

/** `fixme`: a note on a known bug that breaks the page, which marks its test test.fixme until it's fixed */
export type PageCase = { url: (t: Tenant) => string; fixme?: string };

export const businessPages: PageCase[] = [
  { url: () => "/" },
  { url: (t) => `/asset.php?id=${t.assetTypeId}` },
  { url: (t) => `/asset.php?id=${t.assetTypeId}&asset=${t.assetId}` },
  { url: () => "/assets.php" },
  { url: (t) => `/assets.php?project=${t.projectId}` },
  { url: () => "/clients.php" },
  { url: () => "/ledger.php" },
  { url: () => "/manufacturers.php" },
  { url: () => "/newAsset.php" },
  { url: () => "/search.php?term=E2E_TENANT" },
  { url: (t) => `/user.php?id=${t.users.limited.id}` },
  { url: () => "/cms/customDashboards.php" },
  { url: (t) => `/cms/?p=${t.cmsPageId}` },
  { url: (t) => `/cms/edit.php?p=${t.cmsPageId}` },
  { url: () => "/cms/list.php" },
  { url: (t) => `/cms/log.php?p=${t.cmsPageId}` },
  { url: (t) => `/cms/stats.php?p=${t.cmsPageId}` },
  { url: () => "/instances/billing.php" },
  { url: () => "/instances/calendar.php" },
  { url: () => "/instances/calendarSettings.php" },
  { url: () => "/instances/configuration/asset-status.php" },
  { url: () => "/instances/configuration/barcodes.php" },
  { url: () => "/instances/configuration/invoices.php" },
  { url: () => "/instances/configuration/logo.php" },
  { url: () => "/instances/customCategories.php" },
  { url: () => "/instances/groups.php" },
  { url: () => "/instances/importAssets.php" },
  { url: () => "/instances/join.php" },
  { url: () => "/instances/navigation.php" },
  { url: () => "/instances/new.php" },
  { url: () => "/instances/permissions.php" },
  { url: () => "/instances/projectStatuses.php" },
  { url: () => "/instances/projectTypes.php" },
  { url: () => "/instances/public.php" },
  { url: () => "/instances/settings.php" },
  { url: () => "/instances/signupCodes.php" },
  { url: () => "/instances/stats.php" },
  { url: () => "/instances/trustedDomains.php" },
  { url: () => "/instances/users.php" },
  { url: () => "/location/" },
  { url: (t) => `/location/?id=${t.locationId}&files` },
  { url: (t) => `/location/barcode.php?location=${t.locationId}` },
  { url: () => "/maintenance/" },
  { url: (t) => `/maintenance/job.php?id=${t.maintenanceJobId}` },
  { url: () => "/maintenance/barcode.php" },
  { url: () => "/maintenance/barcodeGenerator.php?all" },
  { url: (t) => `/maintenance/barcodePrint.php?ids=${t.assetId}` },
  { url: () => "/project/new.php" },
  { url: () => "/project/list.php" },
  { url: (t) => `/project/?id=${t.projectId}` },
  { url: (t) => `/project/?id=${t.projectId}&list` },
  { url: (t) => `/project/?id=${t.subProjectId}` },
  { url: (t) => `/project/noteExport.php?id=${t.projectId}` },
  { url: (t) => `/project/projectInvoice.php?id=${t.projectId}` },
  { url: (t) => `/project/projectInvoice.php?id=${t.projectId}&type=quote` },
  { url: (t) => `/project/projectInvoice.php?id=${t.projectId}&type=deliveryNote` },
  { url: () => "/project/crew/vacancies.php" },
  { url: (t) => `/project/crew/vacantCrew.php?id=${t.projectId}` },
  { url: (t) => `/project/crew/vacancy.php?id=${t.vacantRoleId}` },
  { url: (t) => `/project/crew/applications.php?id=${t.vacantRoleId}` },
  { url: () => "/training/" },
  { url: (t) => `/training/module.php?id=${t.moduleId}` },
  { url: (t) => `/training/module.php?id=${t.moduleId}&steps` },
  { url: (t) => `/training/module.php?id=${t.moduleId}&users` },
];

export const serverPages: string[] = [
  "/server/analytics/",
  "/server/analytics/pageViews.php",
  "/server/analytics/tables.php",
  "/server/auditLog.php",
  "/server/config.php",
  "/server/instances.php",
  "/server/permissions.php",
  "/server/users.php",
];

/** A Tenant whose IDs are their own names, to title each test with its URL: /project/?id={projectId} */
export const placeholders = new Proxy({} as Tenant, {
  get: (_, key): unknown => (key === "users" ? { limited: { id: "{users.limited.id}" } } : `{${String(key)}}`),
});

/** Opens `url` and returns everything that shows the page is broken */
export async function problemsOpening(page: Page, url: string) {
  const problems: string[] = [];
  const onError = (error: Error) => problems.push(`JavaScript error: ${error.message}`);
  page.on("pageerror", onError);
  const response = await page.goto(url, { waitUntil: "load" });
  // Let scripts that run on document ready or after a first AJAX call finish
  await page.waitForLoadState("networkidle", { timeout: 10_000 }).catch(() => {});
  page.off("pageerror", onError);

  const status = response?.status() ?? 0;
  if (status >= 500) problems.push(`HTTP ${status}`);
  const html = await page.content();
  for (const text of ["Fatal error", "Uncaught ", "Parse error", "Oops! Page not found."]) {
    if (html.includes(text)) problems.push(`page contains "${text}"`);
  }
  return problems;
}

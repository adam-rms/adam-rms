import { cacheCdn } from "../cdn";
import { login } from "../fixtures";
import { assign, bookings, newAsset, newProject } from "../projects";
import { dbQuery, expect, formData, test } from "../tenants";

/**
 * The everyday jobs, done through the pages as a user would: clicking the buttons and filling in the forms.
 * The API tests check the endpoints; these check the pages still call them with the right things. Records are
 * set up through the API where the page under test isn't the one that creates them.
 */

test.beforeEach(async ({ page, tenants: { a, password } }) => {
  await cacheCdn(page.context());
  await login(page, a.users.full.email, password);
});

test("creating a project from the New Project page", async ({ page, tenants: { a } }) => {
  const name = `Journey project ${Date.now()}`;
  await page.goto("/project/new.php");
  await page.locator("#projects_name").fill(name);
  await page.locator("#projects_description").fill("Made by clicking through");
  await page.locator("#saveButton").click();

  await page.waitForURL(/\/project\/\?id=\d+/);
  const id = Number(new URL(page.url()).searchParams.get("id"));
  await expect(page.locator('a[href="#details-view"]')).toHaveText(name);
  expect(dbQuery("SELECT projects_name, projects_description, projects_manager, instances_id FROM projects WHERE projects_id = ?", [id])).toEqual([
    { projects_name: name, projects_description: "Made by clicking through", projects_manager: a.users.full.id, instances_id: a.instanceId },
  ]);
});

test("adding an asset to a project from the assets page", async ({ page, asA, tenants: { a } }) => {
  // A type with only this asset, so the page shows the asset's own "Add to project" button
  const typeName = `Journey type ${Date.now()}`;
  const type = await asA.api("/api/assets/newAssetType.php", { instances_id: a.instanceId, formData: formData({
    manufacturers_id: a.manufacturerId, assetCategories_id: a.categoryId, assetTypes_name: typeName, assetTypes_dayRate: "1.00",
  }) });
  const asset = (await asA.api("/api/assets/newAssetFromType.php", { instances_id: a.instanceId, formData: formData({ assetTypes_id: type.json.response.assetTypes_id }) })).json.response.assets_id as number;
  const project = await newProject(asA, a, a.users.full.id, { start: "2036-07-01 09:00:00", end: "2036-07-02 18:00:00" });

  await page.goto(`/assets.php?project=${project}&keyword[]=${encodeURIComponent(typeName)}`);
  await page.locator(`.addToBasketAssetButton[data-assetid="${asset}"]`).click();
  await expect(page.locator(`.removeFromBasketAssetButton[data-assetid="${asset}"]`)).toBeVisible();
  expect(bookings(asset)).toEqual([project]);

  // ...and it's on the project's asset list
  await page.goto(`/project/?id=${project}`);
  await page.locator('a[href="#assets-view"]').click();
  await expect(page.locator("#assets-view")).toContainText(typeName);
});

test("recording a payment from the project's Finance tab", async ({ page, asA, tenants: { a } }) => {
  const project = await newProject(asA, a, a.users.full.id, { start: "2036-07-03 09:00:00", end: "2036-07-04 18:00:00" });
  await page.goto(`/project/?id=${project}`);
  await page.locator('a[href="#payments-view"]').click();
  await page.locator('button.newPayment[data-type="1"]').click();
  const modal = page.locator("#newPaymentModal");
  await expect(modal).toBeVisible();
  await modal.locator('input[name="payments_reference"]').fill("JOURNEY-REF");
  await modal.locator('input[name="payments_amount"]').fill("12.50");
  await page.locator("#newPaymentModal-button").click();

  await page.waitForLoadState("load");
  await expect.poll(() => dbQuery("SELECT payments_type, payments_amount, payments_reference FROM payments WHERE projects_id = ? AND payments_deleted = 0", [project]))
    .toEqual([{ payments_type: 1, payments_amount: 1250, payments_reference: "JOURNEY-REF" }]);
  await page.locator('a[href="#payments-view"]').click();
  await expect(page.locator("#payments-view")).toContainText("£12.50");
});

test("dispatching an asset on the Asset Dispatch board, with the arrows and by typing its tag", async ({ page, asA, tenants: { a } }) => {
  // Two statuses of our own, after the business's others, so the "»" arrow on the first goes to the second
  const statuses = ["Journey out", "Journey back"].map((name, i) => {
    dbQuery("INSERT INTO assetsAssignmentsStatus (instances_id, assetsAssignmentsStatus_name, assetsAssignmentsStatus_order, assetsAssignmentsStatus_deleted) VALUES (?, ?, ?, 0)",
      [a.instanceId, name, 900 + i]);
    return dbQuery<{ id: number }>("SELECT MAX(assetsAssignmentsStatus_id) id FROM assetsAssignmentsStatus WHERE instances_id = ?", [a.instanceId])[0].id;
  });
  try {
    const asset = await newAsset(asA, a);
    const [{ tag }] = dbQuery<{ tag: string }>("SELECT assets_tag tag FROM assets WHERE assets_id = ?", [asset]);
    const project = await newProject(asA, a, a.users.full.id, { start: "2036-07-05 09:00:00", end: "2036-07-06 18:00:00" });
    await assign(asA, a, project, asset);
    const [{ id: assignment }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);
    await asA.api("/api/projects/assets/setStatus.php", { instances_id: a.instanceId, assetsAssignments_id: assignment, assetsAssignments_status: statuses[0] });
    const status = () => dbQuery<{ s: number }>("SELECT assetsAssignmentsStatus_id s FROM assetsAssignments WHERE assetsAssignments_id = ?", [assignment])[0].s;

    await page.goto(`/project/?id=${project}`);
    await page.locator('a[href="#assets-board"]').click();
    const card = page.locator(`.assetDispatchAssetList-card[data-asset="${asset}"]`);
    await expect(page.locator(`.assetDispatchAssetList[data-statusid="${statuses[0]}"]`)).toContainText(tag);
    await card.locator(".assetDispatchAssetList-statusNextButton").click();
    await expect(page.locator(`.assetDispatchAssetList[data-statusid="${statuses[1]}"]`)).toContainText(tag);
    await expect.poll(status).toBe(statuses[1]);

    await page.locator("#navItemQuickDispatch button").click();
    await page.locator(".assetStatusSelectorQuickDispatch:visible").selectOption(String(statuses[0]));
    await page.locator("#assetTagQuickDispatch").fill(tag);
    await page.locator("#goQuickDispatch").click();
    await expect.poll(status).toBe(statuses[0]);
  } finally {
    dbQuery(`UPDATE assetsAssignmentsStatus SET assetsAssignmentsStatus_deleted = 1 WHERE assetsAssignmentsStatus_id IN (${statuses.join(",")})`);
  }
});

test("reporting a fault on an asset from the New Maintenance Job page", async ({ page, asA, tenants: { a } }) => {
  const asset = await newAsset(asA, a);
  const [{ tag }] = dbQuery<{ tag: string }>("SELECT assets_tag tag FROM assets WHERE assets_id = ?", [asset]);
  await page.goto("/maintenance/job.php");
  await page.locator('input[name="maintenanceJobs_title"]').fill("Journey fault");
  await page.locator('textarea[name="maintenanceJobs_faultDescription"]').fill("It rattles");
  // The asset picker searches as you type (api/maintenance/searchAsset.php)
  await page.locator("#addAssetModal-assets + .select2 .select2-selection").click();
  await page.locator(".select2-search__field").fill(tag);
  await page.locator(".select2-results__option", { hasText: tag }).click();
  await page.getByRole("button", { name: "Create" }).click();

  await page.waitForURL(/\/maintenance\/job\.php\?id=\d+/);
  const id = Number(new URL(page.url()).searchParams.get("id"));
  expect(dbQuery("SELECT maintenanceJobs_title, maintenanceJobs_faultDescription, maintenanceJobs_assets, instances_id FROM maintenanceJobs WHERE maintenanceJobs_id = ?", [id])).toEqual([
    { maintenanceJobs_title: "Journey fault", maintenanceJobs_faultDescription: "It rattles", maintenanceJobs_assets: String(asset), instances_id: a.instanceId },
  ]);
  await expect(page.locator("body")).toContainText("Journey fault");
});

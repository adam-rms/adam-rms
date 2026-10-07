import { cacheCdn } from "../cdn";
import { login } from "../fixtures";
import { bookings, newProject } from "../projects";
import { dbQuery, expect, formData, test, type Session, type Tenant } from "../tenants";

/**
 * The asset search page: the keyword box searches as you type, the Advanced filters combine with it, and the
 * add-to-project buttons keep working after a search has swapped the results in without a page load.
 */

test.beforeEach(async ({ page, tenants: { a, password } }) => {
  await cacheCdn(page.context());
  await login(page, a.users.full.email, password);
});

// Two asset types, each with one asset, sharing a word in their names so one keyword finds both
async function twoTypes(asA: Session, a: Tenant) {
  const stamp = `Searchable${Date.now()}`;
  const made: { name: string; asset: number; tag: string }[] = [];
  for (const suffix of ["Alpha", "Beta"]) {
    const name = `${stamp} ${suffix}`;
    const type = await asA.api("/api/assets/newAssetType.php", { instances_id: a.instanceId, formData: formData({
      manufacturers_id: a.manufacturerId, assetCategories_id: a.categoryId, assetTypes_name: name, assetTypes_dayRate: "1.00",
    }) });
    const asset = (await asA.api("/api/assets/newAssetFromType.php", { instances_id: a.instanceId, formData: formData({ assetTypes_id: type.json.response.assetTypes_id }) })).json.response.assets_id as number;
    const [{ tag }] = dbQuery<{ tag: string }>("SELECT assets_tag tag FROM assets WHERE assets_id = ?", [asset]);
    made.push({ name, asset, tag });
  }
  return { stamp, alpha: made[0], beta: made[1] };
}

test("the keyword box searches as you type, without reloading the page", async ({ page, asA, tenants: { a } }) => {
  const { stamp, alpha, beta } = await twoTypes(asA, a);
  await page.goto("/assets.php");
  await page.evaluate(() => { (window as any).notReloaded = true; });

  const results = page.locator("#assetSearchResults");
  await page.locator("#assetSearchSimpleKeyword").fill(stamp);
  await expect(results).toContainText(alpha.name);
  await expect(results).toContainText(beta.name);

  // A second word narrows the results
  await page.locator("#assetSearchSimpleKeyword").fill(`${stamp} Beta`);
  await expect(results).not.toContainText(alpha.name);
  await expect(results).toContainText(beta.name);

  expect(new URL(page.url()).searchParams.get("simple_keyword")).toBe(`${stamp} Beta`);
  expect(await page.evaluate(() => (window as any).notReloaded)).toBe(true);
});

test("advanced filters in the link start open, and hiding them clears them", async ({ page, asA, tenants: { a } }) => {
  const { stamp, alpha, beta } = await twoTypes(asA, a);
  await page.goto(`/assets.php?simple=1&simple_keyword=${stamp}&tags[]=${encodeURIComponent(alpha.tag)}`);

  const results = page.locator("#assetSearchResults");
  await expect(page.locator("#assetSearchAdvanced")).toBeVisible();
  await expect(results).toContainText(alpha.name);
  await expect(results).not.toContainText(beta.name);

  await page.locator("#assetSearchAdvancedToggle").click();
  await expect(page.locator("#assetSearchAdvanced")).toBeHidden();
  await expect(results).toContainText(beta.name);
  await expect(results).toContainText(alpha.name);
  expect(new URL(page.url()).searchParams.has("tags[]")).toBe(false);
  expect(new URL(page.url()).searchParams.get("simple_keyword")).toBe(stamp);
});

test("choosing a project after the page has loaded, then adding an asset to it", async ({ page, asA, tenants: { a } }) => {
  const { alpha } = await twoTypes(asA, a);
  const project = await newProject(asA, a, a.users.full.id, { start: "2036-08-01 09:00:00", end: "2036-08-02 18:00:00" });
  await page.goto(`/assets.php?simple=1&simple_keyword=${encodeURIComponent(alpha.name)}`);
  await expect(page.locator("#assetSearchResults")).toContainText(alpha.name);

  // The project picker sits beside the keyword box, not in the collapsed Advanced filters
  await expect(page.locator('#assetSearchScopeBar label[for="assetSearchProject"]')).toBeVisible();
  // Choosing a project searches again, drawing the add-to-project buttons
  await page.locator('#assetSearchForm select[name="project"]').selectOption(String(project), { force: true });
  await page.locator(`.addToBasketAssetButton[data-assetid="${alpha.asset}"]`).click();
  await expect(page.locator(`.removeFromBasketAssetButton[data-assetid="${alpha.asset}"]`)).toBeVisible();
  expect(bookings(alpha.asset)).toEqual([project]);
});

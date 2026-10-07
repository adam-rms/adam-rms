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

test("the keyword matches description, manufacturer, category, category group and asset tag", async ({ page, asA, tenants: { a } }) => {
  // Each field gets a word found nowhere else, so each search can only match through that field
  const { stamp, alpha } = await twoTypes(asA, a);
  const [{ type }] = dbQuery<{ type: number }>("SELECT assetTypes_id type FROM assets WHERE assets_id = ?", [alpha.asset]);
  const id = (sql: string, params: unknown[]) => dbQuery<{ id: number }>(sql, params)[0].id;
  dbQuery("INSERT INTO manufacturers (manufacturers_name, instances_id) VALUES (?, ?)", [`Maker${stamp}`, a.instanceId]);
  const manufacturer = id("SELECT manufacturers_id id FROM manufacturers WHERE manufacturers_name = ?", [`Maker${stamp}`]);
  dbQuery("INSERT INTO assetCategoriesGroups (assetCategoriesGroups_name, assetCategoriesGroups_order, instances_id, assetCategoriesGroups_deleted) VALUES (?, 0, ?, 0)", [`Grp${stamp}`, a.instanceId]);
  const group = id("SELECT assetCategoriesGroups_id id FROM assetCategoriesGroups WHERE assetCategoriesGroups_name = ?", [`Grp${stamp}`]);
  dbQuery("INSERT INTO assetCategories (assetCategories_name, assetCategories_rank, assetCategoriesGroups_id, instances_id, assetCategories_deleted) VALUES (?, 0, ?, ?, 0)", [`Cat${stamp}`, group, a.instanceId]);
  const category = id("SELECT assetCategories_id id FROM assetCategories WHERE assetCategories_name = ?", [`Cat${stamp}`]);
  try {
    dbQuery("UPDATE assetTypes SET assetTypes_description = ?, manufacturers_id = ?, assetCategories_id = ? WHERE assetTypes_id = ?", [`Desc${stamp}`, manufacturer, category, type]);
    dbQuery("UPDATE assets SET assets_tag = ? WHERE assets_id = ?", [`Tag${stamp}`, alpha.asset]);

    const results = page.locator("#assetSearchResults");
    for (const keyword of [`Desc${stamp}`, `Maker${stamp}`, `Cat${stamp}`, `Grp${stamp}`, `Tag${stamp}`]) {
      await page.goto(`/assets.php?simple=1&simple_keyword=${keyword}`);
      await expect(results, keyword).toContainText(alpha.name);
    }
    await page.goto(`/assets.php?simple=1&simple_keyword=Nowhere${stamp}`);
    await expect(results).not.toContainText(alpha.name);
  } finally {
    dbQuery("UPDATE assetTypes SET manufacturers_id = ?, assetCategories_id = ? WHERE assetTypes_id = ?", [a.manufacturerId, a.categoryId, type]);
    dbQuery("DELETE FROM assetCategories WHERE assetCategories_id = ?", [category]);
    dbQuery("DELETE FROM assetCategoriesGroups WHERE assetCategoriesGroups_id = ?", [group]);
    dbQuery("DELETE FROM manufacturers WHERE manufacturers_id = ?", [manufacturer]);
  }
});

test("the keyword's tag match only looks at this business's assets", async ({ page, asA, tenants: { a, b } }) => {
  // A shared (catalogue) asset type with an asset in each business; only B's asset has the tag searched for
  const stamp = `Shared${Date.now()}`;
  dbQuery("INSERT INTO assetTypes (assetTypes_name, instances_id, manufacturers_id, assetCategories_id, assetTypes_inserted) VALUES (?, NULL, ?, ?, NOW())", [stamp, a.manufacturerId, a.categoryId]);
  const [{ type }] = dbQuery<{ type: number }>("SELECT assetTypes_id type FROM assetTypes WHERE assetTypes_name = ?", [stamp]);
  try {
    const newAsset = async () => (await asA.api("/api/assets/newAssetFromType.php", { instances_id: a.instanceId, formData: formData({ assetTypes_id: type }) })).json.response.assets_id as number;
    await newAsset();
    const bAsset = await newAsset();
    dbQuery("UPDATE assets SET instances_id = ?, assets_tag = ? WHERE assets_id = ?", [b.instanceId, `OtherTag${stamp}`, bAsset]);

    const results = page.locator("#assetSearchResults");
    await page.goto(`/assets.php?simple=1&simple_keyword=${stamp}`);
    await expect(results).toContainText(stamp); // A has an asset of the type, so its name finds it
    await page.goto(`/assets.php?simple=1&simple_keyword=OtherTag${stamp}`);
    await expect(results).not.toContainText(stamp);
  } finally {
    dbQuery("DELETE FROM assets WHERE assetTypes_id = ?", [type]);
    dbQuery("DELETE FROM assetTypes WHERE assetTypes_id = ?", [type]);
  }
});

test("an old keyword[] link fills the keyword box, and later searches keep it", async ({ page, asA, tenants: { a } }) => {
  const { stamp, alpha, beta } = await twoTypes(asA, a);
  await page.goto(`/assets.php?keyword[]=${encodeURIComponent(`${stamp} Alpha`)}`);
  await expect(page.locator("#assetSearchSimpleKeyword")).toHaveValue(`${stamp} Alpha`);
  const results = page.locator("#assetSearchResults");
  await expect(results).toContainText(alpha.name);
  await expect(results).not.toContainText(beta.name);

  // Any change searches again without a page load, still with the keyword
  await page.locator("#assetSearchSort").selectOption("alphabet-d", { force: true });
  await expect.poll(() => new URL(page.url()).searchParams.get("sort")).toBe("alphabet-d");
  await expect(results).toContainText(alpha.name);
  await expect(results).not.toContainText(beta.name);
  expect(new URL(page.url()).searchParams.get("simple_keyword")).toBe(`${stamp} Alpha`);
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

test("switching project while an add is in flight books the first project and leaves the new results alone", async ({ page, asA, tenants: { a } }) => {
  const { alpha } = await twoTypes(asA, a);
  const first = await newProject(asA, a, a.users.full.id, { start: "2036-09-01 09:00:00", end: "2036-09-02 18:00:00" });
  const second = await newProject(asA, a, a.users.full.id, { start: "2036-10-01 09:00:00", end: "2036-10-02 18:00:00" });
  await page.goto(`/assets.php?project=${first}&simple=1&simple_keyword=${encodeURIComponent(alpha.name)}`);

  // Hold the add back until the results have been swapped for the second project
  let release!: () => void;
  const held = new Promise<void>((resolve) => { release = resolve; });
  await page.route("**/api/projects/assets/assign.php", async (route) => { await held; await route.continue(); });
  await page.locator(`.addToBasketAssetButton[data-assetid="${alpha.asset}"]`).click();
  await page.locator('#assetSearchForm select[name="project"]').selectOption(String(second), { force: true });
  await expect(page.locator("#assetSearchResultsProject")).toHaveAttribute("data-project-id", String(second));
  release();

  await expect.poll(() => bookings(alpha.asset)).toEqual([first]);
  await expect(page.locator(".swal2-title")).toHaveText("Added to Booking test 2036-09-01 09:00:00");
  // The second project's results still offer to add the asset
  await expect(page.locator(`.addToBasketAssetButton[data-assetid="${alpha.asset}"]`)).toBeVisible();
  await expect(page.locator(`.removeFromBasketAssetButton[data-assetid="${alpha.asset}"]`)).toBeHidden();
});

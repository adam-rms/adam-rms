import { dbQuery, expect, formData, test } from "../tenants";
import { login } from "../fixtures";

test("saves an asset tag pattern and uses its padded counter for new assets", async ({ asA, page, tenants: { a, password } }) => {
  const original = dbQuery<{ pattern: string | null }>(
    "SELECT instances_assetTagPattern pattern FROM instances WHERE instances_id = ?", [a.instanceId],
  )[0].pattern;
  const pattern = `E2E-${Date.now()}-{5}-AV`;

  try {
    await login(page, a.users.full.email, password);
    await page.goto("/instances/configuration/barcodes.php");
    const patternInput = page.getByLabel("Pattern");
    await expect(patternInput).toBeVisible();
    await patternInput.fill(pattern);
    await page.getByRole("button", { name: "Save" }).first().click();
    await expect.poll(() => dbQuery<{ pattern: string }>(
      "SELECT instances_assetTagPattern pattern FROM instances WHERE instances_id = ?", [a.instanceId],
    )[0].pattern).toBe(pattern);

    const created = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: a.instanceId,
      formData: formData({ assetTypes_id: a.assetTypeId }),
    });
    expect(created.json).toMatchObject({ result: true });
    expect(created.json.response.assets_tag).toMatch(new RegExp(`^E2E-${pattern.split("-")[1]}-[0-9]{5}-AV$`));
    const assetId = created.json.response.assets_id;
    const barcode = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'QR_CODE'",
      [assetId],
    )[0];
    expect(barcode.value).toBe(created.json.response.assets_tag);
  } finally {
    dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [original, a.instanceId]);
  }
});
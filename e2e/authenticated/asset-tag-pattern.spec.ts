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

test("the same asset tag keeps matching barcodes in different instances", async ({ asA, tenants: { a, b, password } }) => {
  const originals = dbQuery<{ instances_id: number; pattern: string | null }>(
    "SELECT instances_id, instances_assetTagPattern pattern FROM instances WHERE instances_id IN (?, ?)", [a.instanceId, b.instanceId],
  );
  const pattern = `E2E-COLLISION-${Date.now()}-{5}`;

  try {
    dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id IN (?, ?)", [pattern, a.instanceId, b.instanceId]);
    const createdA = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: a.instanceId,
      formData: formData({ assetTypes_id: a.assetTypeId }),
    });
    expect(createdA.json).toMatchObject({ result: true });
    const assetA = createdA.json.response;
    const printedA = await asA.page(`/maintenance/barcodePrint.php?ids=${assetA.assets_id}&groups=&blanks=0&barcodeType=CODE_128`);
    expect(printedA.status).toBe(200);
    const assetABarcode = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'CODE_128'",
      [assetA.assets_id],
    )[0];
    expect(assetABarcode.value).toBe(assetA.assets_tag);

    const loginB = await asA.request.post("/api/login/login.php", {
      form: { formInput: b.users.full.email, password },
    });
    expect(await loginB.json()).toMatchObject({ result: true });
    const createdB = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: b.instanceId,
      formData: formData({ assetTypes_id: b.assetTypeId }),
    });
    expect(createdB.json).toMatchObject({ result: true });
    const assetB = createdB.json.response;
    expect(assetA.assets_tag).toBe(assetB.assets_tag);
    const assetBQr = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'QR_CODE'",
      [assetB.assets_id],
    )[0];
    expect(assetBQr.value).toBe(assetB.assets_tag);

    const printedB = await asA.page(`/maintenance/barcodePrint.php?ids=${assetB.assets_id}&groups=&blanks=0&barcodeType=CODE_128`);
    expect(printedB.status).toBe(200);
    const assetBBarcode = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'CODE_128'",
      [assetB.assets_id],
    )[0];
    expect(assetBBarcode.value).toBe(assetB.assets_tag);
  } finally {
    for (const instance of originals) {
      dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [instance.pattern, instance.instances_id]);
    }
    	
    await asA.request.post("/api/login/login.php", {
      form: { formInput: a.users.full.email, password },
    });
  }
});
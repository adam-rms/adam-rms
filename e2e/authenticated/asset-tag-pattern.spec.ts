import { dbQuery, expect, formData, newSession, test } from "../tenants";
import { login } from "../fixtures";
import { BASE_URL } from "../env";

test("rejects asset tag patterns that could exceed the database column as the counter grows", async ({ asA, tenants: { a } }) => {
  const tooLongAtMaximumCounter = `${"A".repeat(197)}{1}`;
  const original = dbQuery<{ pattern: string | null }>(
    "SELECT instances_assetTagPattern pattern FROM instances WHERE instances_id = ?", [a.instanceId],
  )[0].pattern;

  const response = await asA.api("/api/instances/editInstance.php", {
    formData: formData({ instances_assetTagPattern: tooLongAtMaximumCounter }),
  });

  expect(response.json).toMatchObject({ result: false });
  expect(response.json.error.message).toBe("Enter a pattern with one counter such as E-{7}. The maximum length is 200 characters in total.");
  expect(dbQuery<{ pattern: string | null }>(
    "SELECT instances_assetTagPattern pattern FROM instances WHERE instances_id = ?", [a.instanceId],
  )[0].pattern).toBe(original);
});

test("reports counter exhaustion before creating an asset", async ({ asA, tenants: { a } }) => {
  const pattern = `E2E-OVERFLOW-${Date.now()}-{1}`;
  const prefix = pattern.slice(0, pattern.indexOf("{"));
  const highestTag = `${prefix}${"9".repeat(18)}`;
  const originalPattern = dbQuery<{ pattern: string | null }>(
    "SELECT instances_assetTagPattern pattern FROM instances WHERE instances_id = ?", [a.instanceId],
  )[0].pattern;
  const originalAssetTag = dbQuery<{ tag: string }>(
    "SELECT assets_tag tag FROM assets WHERE assets_id = ?", [a.assetId],
  )[0].tag;
  const originalAssetCount = dbQuery<{ count: number }>(
    "SELECT COUNT(*) count FROM assets WHERE instances_id = ?", [a.instanceId],
  )[0].count;

  try {
    dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [pattern, a.instanceId]);
    dbQuery("UPDATE assets SET assets_tag = ? WHERE assets_id = ?", [highestTag, a.assetId]);

    const response = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: a.instanceId,
      formData: formData({ assetTypes_id: a.assetTypeId }),
    });

    expect(response.json).toMatchObject({ result: false, error: { code: "TAG-COUNTER-EXHAUSTED" } });
    expect(dbQuery<{ count: number }>(
      "SELECT COUNT(*) count FROM assets WHERE instances_id = ?", [a.instanceId],
    )[0].count).toBe(originalAssetCount);
  } finally {
    dbQuery("UPDATE assets SET assets_tag = ? WHERE assets_id = ?", [originalAssetTag, a.assetId]);
    dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [originalPattern, a.instanceId]);
  }
});

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

    const timestamp = pattern.split("-")[1];

    // Create two assets to confirm the MAX/increment counter actually starts at 1 and advances, not just that it pads to 5 digits.
    const createdFirst = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: a.instanceId,
      formData: formData({ assetTypes_id: a.assetTypeId }),
    });
    expect(createdFirst.json).toMatchObject({ result: true });
    expect(createdFirst.json.response.assets_tag).toBe(`E2E-${timestamp}-00001-AV`);
    const firstAssetId = createdFirst.json.response.assets_id;
    const firstBarcode = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'QR_CODE'",
      [firstAssetId],
    )[0];
    expect(firstBarcode.value).toBe(createdFirst.json.response.assets_tag);

    const createdSecond = await asA.api("/api/assets/newAssetFromType.php", {
      instances_id: a.instanceId,
      formData: formData({ assetTypes_id: a.assetTypeId }),
    });
    expect(createdSecond.json).toMatchObject({ result: true });
    expect(createdSecond.json.response.assets_tag).toBe(`E2E-${timestamp}-00002-AV`);
    const secondAssetId = createdSecond.json.response.assets_id;
    const secondBarcode = dbQuery<{ value: string }>(
      "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'QR_CODE'",
      [secondAssetId],
    )[0];
    expect(secondBarcode.value).toBe(createdSecond.json.response.assets_tag);
  } finally {
    dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [original, a.instanceId]);
  }
});

test("the same asset tag keeps matching barcodes in different instances", async ({ asA, playwright, tenants: { a, b, password } }) => {
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

    // A separate request context for B, so logging in doesn't touch the shared, worker-scoped `asA` session.
    const requestB = await playwright.request.newContext({ baseURL: BASE_URL });
    try {
      const asB = await newSession(requestB, b.users.full.email, password);
      const createdB = await asB.api("/api/assets/newAssetFromType.php", {
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

      const printedB = await asB.page(`/maintenance/barcodePrint.php?ids=${assetB.assets_id}&groups=&blanks=0&barcodeType=CODE_128`);
      expect(printedB.status).toBe(200);
      const assetBBarcode = dbQuery<{ value: string }>(
        "SELECT assetsBarcodes_value value FROM assetsBarcodes WHERE assets_id = ? AND assetsBarcodes_type = 'CODE_128'",
        [assetB.assets_id],
      )[0];
      expect(assetBBarcode.value).toBe(assetB.assets_tag);
    } finally {
      await requestB.dispose();
    }
  } finally {
    for (const instance of originals) {
      dbQuery("UPDATE instances SET instances_assetTagPattern = ? WHERE instances_id = ?", [instance.pattern, instance.instances_id]);
    }
  }
});
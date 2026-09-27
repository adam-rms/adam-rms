import type { Page } from "@playwright/test";
import { cacheCdn } from "../cdn";
import { login } from "../fixtures";
import { dbQuery, expect, test, type Tenant } from "../tenants";

/**
 * Scanning an asset's barcode records where it was scanned: at a location's barcode, next to another asset, or at a
 * place typed in by hand (api/assets/barcodes/search.php, used by maintenance/barcode.php and the asset page).
 */

type Scan = { locationsBarcodes_id: number | null; location_assets_id: number | null };
function latestScan(t: Tenant): Scan | undefined {
  return dbQuery<Scan>(
    "SELECT locationsBarcodes_id, location_assets_id FROM assetsBarcodesScans WHERE assetsBarcodes_id = ? ORDER BY assetsBarcodesScans_id DESC LIMIT 1",
    [t.barcodeId],
  )[0];
}
function scanCount(t: Tenant) {
  return dbQuery<{ n: number }>("SELECT COUNT(*) n FROM assetsBarcodesScans WHERE assetsBarcodes_id = ?", [t.barcodeId])[0].n;
}

for (const type of ["UNKNOWN", "CODE_128"]) {
  test.describe(`scanning an asset's barcode (type ${type})`, () => {
    // Seen in production: the barcode page sent "undefined" after the location was changed mid-scan, and the scan
    // failed with "Incorrect integer value: 'undefined' for column 'locationsBarcodes_id'"
    test("with a location that isn't an ID still records the scan, without a location", async ({ asA, tenants: { a } }) => {
      const before = scanCount(a);
      const response = await asA.api("/api/assets/barcodes/search.php", {
        instances_id: a.instanceId, text: a.barcodeValue, type, scanned: "true", locationType: "barcode", location: "undefined",
      });
      expect(response.json, response.body.slice(0, 300)).toMatchObject({ result: true, response: { asset: { assets_id: a.assetId } } });
      expect(scanCount(a)).toBe(before + 1);
      expect(latestScan(a)).toEqual({ locationsBarcodes_id: null, location_assets_id: null });
    });

    test("records a location barcode or asset of the business, but not another business's", async ({ asA, tenants: { a, b } }) => {
      const scan = (locationType: string, location: number) => asA.api("/api/assets/barcodes/search.php", {
        instances_id: a.instanceId, text: a.barcodeValue, type, scanned: "true", locationType, location,
      });
      await scan("barcode", a.locationBarcodeId);
      expect(latestScan(a)).toEqual({ locationsBarcodes_id: a.locationBarcodeId, location_assets_id: null });
      await scan("barcode", b.locationBarcodeId);
      expect(latestScan(a)).toEqual({ locationsBarcodes_id: null, location_assets_id: null });

      await scan("asset", a.spareAssetId);
      expect(latestScan(a)).toEqual({ locationsBarcodes_id: null, location_assets_id: a.spareAssetId });
      await scan("asset", b.spareAssetId);
      expect(latestScan(a)).toEqual({ locationsBarcodes_id: null, location_assets_id: null });
    });
  });
}

/** Feeds a scan to the page the way the scanner widget does */
async function scanOnPage(page: Page, value: string) {
  await page.evaluate((v) => (window as unknown as { mainBarcodeScanned: (value: string, type: string) => void }).mainBarcodeScanned(v, "UNKNOWN"), value);
}

test("on the barcode page, changing location by scanning a location barcode mid-way records the new location", async ({ page, tenants: { a, password } }) => {
  await cacheCdn(page.context());
  await login(page, a.users.full.email, password);
  await page.goto("/maintenance/barcode.php");
  const alert = page.locator(".bootbox-alert");
  const confirm = page.locator(".bootbox-confirm");

  // Set the location by scanning its barcode, then scan an asset there
  await scanOnPage(page, a.locationBarcodeValue);
  await expect(alert).toContainText("Location set to");
  await alert.getByRole("button", { name: "OK" }).click();
  await expect(page.locator(".bootbox")).toHaveCount(0);
  const before = scanCount(a);
  await scanOnPage(page, a.barcodeValue);
  await expect.poll(() => scanCount(a)).toBe(before + 1);
  expect(latestScan(a)).toEqual({ locationsBarcodes_id: a.locationBarcodeId, location_assets_id: null });

  // Scanning the location barcode while scanning assets asks whether to move there
  await scanOnPage(page, a.locationBarcodeValue);
  await expect(confirm).toContainText("Would you like to change your location");
  await confirm.getByRole("button", { name: "Yes" }).click();
  await expect(alert).toContainText("Location set to");
  await alert.getByRole("button", { name: "OK" }).click();
  await expect(page.locator(".bootbox")).toHaveCount(0);

  await scanOnPage(page, a.barcodeValue);
  await expect.poll(() => scanCount(a)).toBe(before + 2);
  expect(latestScan(a)).toEqual({ locationsBarcodes_id: a.locationBarcodeId, location_assets_id: null });
});

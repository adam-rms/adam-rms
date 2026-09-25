import { test, expect } from "@playwright/test";

// Barcode images are generated for anything passed in, without a session, so labels can be printed
test("api/assets/barcodes/index.php draws a barcode without logging in", async ({ request }) => {
  for (const type of ["CODE_128", "QR_CODE"]) {
    const response = await request.post("/api/assets/barcodes/index.php", { form: { type, barcode: "E2E-0001" } });
    expect(response.status()).toBe(200);
    expect(response.headers()["content-type"]).toContain("image/svg+xml");
    expect(await response.text()).toContain("<svg");
  }
});

test("api/assets/searchAssetsBarcode.php redirects to barcodes/search.php", async ({ request }) => {
  const response = await request.get("/api/assets/searchAssetsBarcode.php", { maxRedirects: 0 });
  expect(response.status()).toBe(302);
  expect(response.headers()["location"]).toBe("barcodes/search.php");
});

import { dbQuery, expect, test } from "../tenants";

test("the location list links to assets stored at that location", async ({ asA, tenants: { a } }) => {
  dbQuery("UPDATE assets SET assets_storageLocation = ? WHERE assets_id = ?", [a.locationId, a.assetId]);
  try {
    const locations = await asA.page("/location/");
    expect(locations.body).toContain(`/assets.php?location=${a.locationId}`);

    const assets = await asA.page(`/assets.php?location=${a.locationId}`);
    expect(assets.status).toBe(200);
    expect(assets.body).toContain(`Assets stored at ${a.marker} location`);
    expect(assets.body).toContain(`/asset.php?id=${a.assetTypeId}&amp;asset=${a.assetId}`);
  } finally {
    dbQuery("UPDATE assets SET assets_storageLocation = NULL WHERE assets_id = ?", [a.assetId]);
  }
});

import { documentSummary, expectFinances, type Finances } from "../projects";
import { BASE_URL } from "../env";
import { dbQuery, expect, formData, newSession, type Session, test } from "../tenants";

/**
 * A project's money is worked out twice: projects/data.php (behind the project page, quotes and invoices)
 * adds everything up from scratch, while every endpoint that changes a price, discount, date or payment
 * nudges a running total kept in projectsFinanceCache (behind project lists, the dashboard and stats) by the
 * difference. If an endpoint gets its nudge wrong the two drift apart, and data.php logs a "Project finance
 * cache mismatch" warning when it next notices. So one project is walked through each kind of change, and
 * after every step the test checks both the numbers data.php works out and that the running total
 * already agreed with them before data.php looked.
 *
 * The seeded asset type costs £10.00 a day or £30.00 a week, and each asset is worth £100.00 and weighs 1kg.
 * Amounts are in pence.
 */

async function ok(response: Promise<{ json: any }>, what: string) {
  expect((await response).json, what).toMatchObject({ result: true });
}

test("a project's finances add up, and its running totals keep up, through every kind of change", async ({ asA, tenants: { a } }) => {
  const api = (endpoint: string, params: Parameters<Session["api"]>[1]) => asA.api(endpoint, { instances_id: a.instanceId, ...params });

  const created = await api("/api/projects/new.php", { projects_name: "Finance test", projects_manager: a.users.full.id, projectsType_id: a.projectTypeId });
  const project: number = created.json.response.projects_id;
  // Three days: 1st 09:00 to 3rd 18:00 counts as the 1st, 2nd and 3rd
  await ok(api("/api/projects/changeProjectDeliverDates.php", {
    projects_id: project, projects_dates_deliver_start: "2036-01-01 09:00:00", projects_dates_deliver_end: "2036-01-03 18:00:00",
  }), "setting the dates");
  const zero: Finances = { subTotal: 0, discounts: 0, total: 0, received: 0, sales: 0, subHire: 0, staff: 0, grandTotal: 0, value: 0, mass: 0 };
  // Opening the project creates its running totals
  await expectFinances(asA, a, project, zero);

  const assets: number[] = [];
  for (let i = 0; i < 2; i++) {
    const asset = await api("/api/assets/newAssetFromType.php", { formData: formData({ assetTypes_id: a.assetTypeId }) });
    assets.push(asset.json.response.assets_id);
    await ok(api("/api/projects/assets/assign.php", { projects_id: project, assets_id: assets[i] }), "assigning an asset");
  }
  const [first, second] = dbQuery<{ id: number }>(
    "SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ? ORDER BY assets_id", [project],
  ).map((row) => row.id);
  // 2 assets x 3 days x £10.00
  let now: Finances = { ...zero, subTotal: 6000, total: 6000, grandTotal: 6000, value: 20000, mass: 2 };
  await expectFinances(asA, a, project, now);

  await test.step("50% discount on the first asset", async () => {
    await ok(api("/api/projects/assets/setDiscount.php", { assetsAssignments: [first], assetsAssignments_discount: 50 }), "setting a discount");
    now = { ...now, discounts: 1500, total: 4500, grandTotal: 4500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("custom price of £20.00 on the second asset", async () => {
    await ok(api("/api/projects/assets/setPrice.php", { assetsAssignments: [second], assetsAssignments_customPrice: "20.00" }), "setting a custom price");
    now = { ...now, subTotal: 5000, total: 3500, grandTotal: 3500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("dates moved to seven days: the first asset's price (and discount) follow, the custom price doesn't", async () => {
    await ok(api("/api/projects/changeProjectDeliverDates.php", {
      projects_id: project, projects_dates_deliver_start: "2036-01-01 09:00:00", projects_dates_deliver_end: "2036-01-07 18:00:00",
    }), "changing the dates");
    now = { ...now, subTotal: 9000, discounts: 3500, total: 5500, grandTotal: 5500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("charged as 1 week and 2 days instead of by the dates", async () => {
    await ok(api("/api/projects/changeProjectFinanceDurationMaths.php", {
      projects_id: project, projects_dates_finances_days: 2, projects_dates_finances_weeks: 1,
    }), "setting custom days and weeks");
    // First asset: £30.00 + 2 x £10.00 = £50.00, half off
    now = { ...now, subTotal: 7000, discounts: 2500, total: 4500, grandTotal: 4500 };
    await expectFinances(asA, a, project, now);
  });

  const payments: Record<string, number> = {};
  await test.step("payments of each type", async () => {
    const pay = async (name: string, type: number, amount: string, quantity: number) => {
      await ok(api("/api/projects/newPayment.php", { formData: formData({
        projects_id: project, payments_type: type, payments_amount: amount, payments_quantity: quantity,
        payments_date: "2036-01-01", payments_reference: `e2e ${name}`, payments_method: "", payments_supplier: "", payments_comment: "",
      }) }), `adding a ${name} payment`);
      payments[name] = dbQuery<{ id: number }>("SELECT MAX(payments_id) id FROM payments WHERE projects_id = ?", [project])[0].id;
    };
    await pay("received", 1, "10.00", 1);
    await pay("sales", 2, "5.00", 2);
    await pay("sub-hire", 3, "7.00", 1);
    await pay("staff", 4, "3.00", 1);
    // 45.00 + 10.00 + 7.00 + 3.00 - 10.00
    now = { ...now, received: 1000, sales: 1000, subHire: 700, staff: 300, grandTotal: 5500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("the invoice and quote show the same totals", async () => {
    expect(await documentSummary(asA, project, "invoice")).toEqual({
      "Equipment SubTotal": "£70.00", "Sales": "£10.00", "Discounts": "-£25.00", "Staffing": "£3.00", "Equipment Total": "£45.00",
      "Sub Hires": "£7.00", "SubTotal": "£65.00", "Payments Received to Date": "-£10.00", "Grand Total Outstanding": "£55.00",
    });
    // The quote's Grand Total takes off payments received too, though it doesn't list them
    expect(await documentSummary(asA, project, "quote")).toEqual({
      "Equipment SubTotal": "£70.00", "Sales": "£10.00", "Discounts": "-£25.00", "Staffing": "£3.00", "Equipment Total": "£45.00",
      "Sub Hires": "£7.00", "Grand Total": "£55.00",
    });
  });

  await test.step("a payment deleted", async () => {
    await ok(api("/api/projects/deletePayment.php", { payments_id: payments["sales"] }), "deleting a payment");
    now = { ...now, sales: 0, grandTotal: 4500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("the first asset's own day rate changed to £20.00", async () => {
    await ok(api("/api/assets/editAsset.php", { formData: formData({ assets_id: assets[0], assets_dayRate: "20.00" }) }), "editing the asset");
    // £30.00 + 2 x £20.00 = £70.00, half off
    now = { ...now, subTotal: 9000, discounts: 3500, total: 5500, grandTotal: 5500 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("the discounted asset unassigned", async () => {
    await ok(api("/api/projects/assets/unassign.php", { assetsAssignments: [first] }), "unassigning an asset");
    // Only the £20.00 custom-priced asset is left
    now = { ...now, subTotal: 2000, discounts: 0, total: 2000, grandTotal: 2000, value: 10000, mass: 1 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("the remaining asset deleted", async () => {
    await ok(api("/api/assets/delete.php", { assets_id: assets[1] }), "deleting the asset");
    now = { ...now, subTotal: 0, total: 0, grandTotal: 0, value: 0, mass: 0 };
    await expectFinances(asA, a, project, now);
  });

  await test.step("back to charging by the dates", async () => {
    await ok(api("/api/projects/changeProjectFinanceDurationMaths.php", {
      projects_id: project, projects_dates_finances_days: -1, projects_dates_finances_weeks: -1,
    }), "removing custom days and weeks");
    // Nothing is left to charge by the day, so nothing changes
    await expectFinances(asA, a, project, now);
  });
});

test("custom days and weeks can't be negative, and -1 for both goes back to charging by the dates", async ({ asA, tenants: { a } }) => {
  const api = (endpoint: string, params: Parameters<Session["api"]>[1]) => asA.api(endpoint, { instances_id: a.instanceId, ...params });
  const created = await api("/api/projects/new.php", { projects_name: "Duration test", projects_manager: a.users.full.id, projectsType_id: a.projectTypeId });
  const project: number = created.json.response.projects_id;
  await ok(api("/api/projects/changeProjectDeliverDates.php", {
    projects_id: project, projects_dates_deliver_start: "2036-02-01 09:00:00", projects_dates_deliver_end: "2036-02-03 18:00:00",
  }), "setting the dates");
  const duration = () => dbQuery<{ days: number | null; weeks: number | null }>(
    "SELECT projects_dates_finances_days days, projects_dates_finances_weeks weeks FROM projects WHERE projects_id = ?", [project],
  )[0];

  await ok(api("/api/projects/changeProjectFinanceDurationMaths.php", { projects_id: project, projects_dates_finances_days: 2, projects_dates_finances_weeks: 1 }), "setting days and weeks");
  expect(duration()).toEqual({ days: 2, weeks: 1 });

  // -1 days alone used to clear both, whatever the weeks were
  const negative = await api("/api/projects/changeProjectFinanceDurationMaths.php", { projects_id: project, projects_dates_finances_days: -1, projects_dates_finances_weeks: 3 });
  expect(negative.json).toMatchObject({ result: false });
  expect(duration()).toEqual({ days: 2, weeks: 1 });

  await ok(api("/api/projects/changeProjectFinanceDurationMaths.php", { projects_id: project, projects_dates_finances_days: -1, projects_dates_finances_weeks: -1 }), "going back to the dates");
  expect(duration()).toEqual({ days: null, weeks: null });
});

test("changing an asset type's rates, value and mass updates the projects its assets are on", async ({ asA, tenants: { a } }) => {
  const api = (endpoint: string, params: Parameters<Session["api"]>[1]) => asA.api(endpoint, { instances_id: a.instanceId, ...params });
  const type = { manufacturers_id: a.manufacturerId, assetCategories_id: a.categoryId, assetTypes_name: "Rate change test type" };
  const createdType = await api("/api/assets/newAssetType.php", { formData: formData({
    ...type, assetTypes_dayRate: "5.00", assetTypes_weekRate: "15.00", assetTypes_value: "50.00", assetTypes_mass: 2,
  }) });
  const typeId: number = createdType.json.response.assetTypes_id;
  const newAsset = async () => (await api("/api/assets/newAssetFromType.php", { formData: formData({ assetTypes_id: typeId }) })).json.response.assets_id as number;
  const byType = await newAsset();
  const ownRate = await newAsset();
  await ok(api("/api/assets/editAsset.php", { formData: formData({ assets_id: ownRate, assets_dayRate: "8.00" }) }), "giving an asset its own rate");

  const created = await api("/api/projects/new.php", { projects_name: "Rate change test", projects_manager: a.users.full.id, projectsType_id: a.projectTypeId });
  const project: number = created.json.response.projects_id;
  await ok(api("/api/projects/changeProjectDeliverDates.php", {
    projects_id: project, projects_dates_deliver_start: "2036-03-01 09:00:00", projects_dates_deliver_end: "2036-03-03 18:00:00",
  }), "setting the dates");
  await expectFinances(asA, a, project, { subTotal: 0, discounts: 0, total: 0, received: 0, sales: 0, subHire: 0, staff: 0, grandTotal: 0, value: 0, mass: 0 });
  for (const asset of [byType, ownRate]) await ok(api("/api/projects/assets/assign.php", { projects_id: project, assets_id: asset }), "assigning");
  const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ? AND assets_id = ?", [project, byType]);
  await ok(api("/api/projects/assets/setDiscount.php", { assetsAssignments: [id], assetsAssignments_discount: 50 }), "discounting");
  // 3 x £5.00 (half off) + 3 x £8.00
  const none = { received: 0, sales: 0, subHire: 0, staff: 0 };
  await expectFinances(asA, a, project, { ...none, subTotal: 3900, discounts: 750, total: 3150, grandTotal: 3150, value: 10000, mass: 4 });

  await ok(api("/api/assets/editAssetType.php", { formData: formData({
    ...type, assetTypes_id: typeId, assetTypes_dayRate: "10.00", assetTypes_weekRate: "15.00", assetTypes_value: "60.00", assetTypes_mass: 3,
  }) }), "editing the asset type");
  // The asset with its own rate keeps it: 3 x £10.00 (half off) + 3 x £8.00
  await expectFinances(asA, a, project, { ...none, subTotal: 5400, discounts: 1500, total: 3900, grandTotal: 3900, value: 12000, mass: 6 });
});

// transfer.php copies the asset into the other business as a new asset and archives the original, which stays on the
// projects it was booked on, priced as before. (So the item can then be booked in the other business on the same dates.)
test("transferring an asset that's on a project to another business leaves that project's finances as they were", async ({ playwright, seeded, tenants: { a, b, sharedUser } }) => {
  const context = await playwright.request.newContext({ baseURL: BASE_URL });
  const asShared = await newSession(context, sharedUser.email, seeded.password);
  const inA = (endpoint: string, params: Parameters<Session["api"]>[1]) => asShared.api(endpoint, { instances_id: a.instanceId, ...params });
  // B has its own type for the asset to become, at a different rate
  const typeInB = await asShared.api("/api/assets/newAssetType.php", { instances_id: b.instanceId, formData: formData({
    manufacturers_id: b.manufacturerId, assetCategories_id: b.categoryId, assetTypes_name: "Transfer test type",
    assetTypes_dayRate: "20.00", assetTypes_weekRate: "60.00", assetTypes_value: "300.00", assetTypes_mass: 5,
  }) });
  const bTypeId: number = typeInB.json.response.assetTypes_id;

  const asset: number = (await inA("/api/assets/newAssetFromType.php", { formData: formData({ assetTypes_id: a.assetTypeId }) })).json.response.assets_id;
  const project: number = (await inA("/api/projects/new.php", { projects_name: "Transfer test", projects_manager: sharedUser.id, projectsType_id: a.projectTypeId })).json.response.projects_id;
  await ok(inA("/api/projects/changeProjectDeliverDates.php", {
    projects_id: project, projects_dates_deliver_start: "2036-04-01 09:00:00", projects_dates_deliver_end: "2036-04-03 18:00:00",
  }), "setting the dates");
  const none = { discounts: 0, received: 0, sales: 0, subHire: 0, staff: 0 };
  await expectFinances(asShared, a, project, { ...none, subTotal: 0, total: 0, grandTotal: 0, value: 0, mass: 0 });
  await ok(inA("/api/projects/assets/assign.php", { projects_id: project, assets_id: asset }), "assigning");
  await expectFinances(asShared, a, project, { ...none, subTotal: 3000, total: 3000, grandTotal: 3000, value: 10000, mass: 1 });

  await ok(inA("/api/assets/transfer.php", { assets_id: asset, new_instances_id: b.instanceId, assetTypes_id: bTypeId }), "transferring");
  expect(dbQuery("SELECT assetTypes_id FROM assets WHERE instances_id = ? AND assets_id > ? AND assetTypes_id = ?", [b.instanceId, asset, bTypeId])).toHaveLength(1);
  expect(dbQuery<{ archived: string | null }>("SELECT assets_archived archived FROM assets WHERE assets_id = ?", [asset])[0].archived).not.toBeNull();
  await expectFinances(asShared, a, project, { ...none, subTotal: 3000, total: 3000, grandTotal: 3000, value: 10000, mass: 1 });
  await context.dispose();
});

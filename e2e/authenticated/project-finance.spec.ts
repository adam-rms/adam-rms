import { expectFinances, type Finances } from "../projects";
import { dbQuery, expect, formData, type Session, test } from "../tenants";

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

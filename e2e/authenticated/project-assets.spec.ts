import { assign, bookings, type Dates, expectFinances, newAsset, newProject } from "../projects";
import { dbQuery, expect, formData, test } from "../tenants";

/**
 * How assets behave once they're on a project: linked assets travel with the asset they're linked to,
 * swapping one asset for another of the same type, dispatch statuses, and sub-projects that follow their
 * parent's status. Every test makes its own projects and assets.
 */

const DATES: Dates = { start: "2036-06-01 09:00:00", end: "2036-06-03 18:00:00" }; // 3 days

function assignmentsOn(projectId: number) {
  return dbQuery<{ assets_id: number; linkedTo: number | null; discount: number; status: number | null }>(
    `SELECT assets_id, assetsAssignments_linkedTo linkedTo, assetsAssignments_discount discount, assetsAssignmentsStatus_id status
     FROM assetsAssignments WHERE projects_id = ? AND assetsAssignments_deleted = 0 ORDER BY assets_id`,
    [projectId],
  );
}

test.describe("linked assets", () => {
  test("assigning an asset brings the assets linked to it, at the business's linked-asset discount, and unassigning it takes them away", async ({ asA, tenants: { a } }) => {
    const parent = await newAsset(asA, a);
    const child = await newAsset(asA, a);
    await expect(asA.api("/api/assets/editAsset.php", { instances_id: a.instanceId, formData: formData({ assets_id: child, assets_linkedTo: parent }) }))
      .resolves.toMatchObject({ json: { result: true } });
    const [{ discount }] = dbQuery<{ discount: number }>("SELECT instances_config_linkedDefaultDiscount discount FROM instances WHERE instances_id = ?", [a.instanceId]);

    const project = await newProject(asA, a, a.users.full.id, DATES);
    expect(await assign(asA, a, project, parent)).toMatchObject({ json: { result: true } });
    const [parentRow, childRow] = assignmentsOn(project);
    expect(parentRow).toMatchObject({ assets_id: parent, linkedTo: null, discount: 0 });
    const [{ id: parentAssignment }] = dbQuery<{ id: number }>(
      "SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ? AND assets_id = ?", [project, parent],
    );
    expect(childRow).toMatchObject({ assets_id: child, linkedTo: parentAssignment, discount: Number(discount) });

    // Two assets at 3 x £10.00, the linked one discounted
    const childDiscount = 3000 * Number(discount) / 100;
    await expectFinances(asA, a, project, {
      subTotal: 6000, discounts: childDiscount, total: 6000 - childDiscount, grandTotal: 6000 - childDiscount,
      received: 0, sales: 0, subHire: 0, staff: 0, value: 20000, mass: 2,
    });

    await expect(asA.api("/api/projects/assets/unassign.php", { instances_id: a.instanceId, assetsAssignments: [parentAssignment] }))
      .resolves.toMatchObject({ json: { result: true } });
    expect(assignmentsOn(project)).toEqual([]);
    await expectFinances(asA, a, project, { subTotal: 0, discounts: 0, total: 0, grandTotal: 0, received: 0, sales: 0, subHire: 0, staff: 0, value: 0, mass: 0 });
  });

  test("a linked asset that's booked elsewhere is left behind, and the asset it's linked to is still assigned", async ({ asA, tenants: { a } }) => {
    const parent = await newAsset(asA, a);
    const child = await newAsset(asA, a);
    await asA.api("/api/assets/editAsset.php", { instances_id: a.instanceId, formData: formData({ assets_id: child, assets_linkedTo: parent }) });
    const elsewhere = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, elsewhere, child);

    const project = await newProject(asA, a, a.users.full.id, DATES);
    const response = await assign(asA, a, project, parent);
    expect(response.json).toMatchObject({ result: true });
    expect(response.json.response.failed).toContainEqual({ assets_id: child });
    expect(bookings(parent)).toEqual([project]);
    expect(bookings(child)).toEqual([elsewhere]);
  });
});

test.describe("swapping an asset", () => {
  test("replaces it with a free asset of the same type", async ({ asA, tenants: { a } }) => {
    const booked = await newAsset(asA, a);
    const free = await newAsset(asA, a);
    const project = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, booked);
    const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);

    await expect(asA.api("/api/projects/assets/swap.php", { instances_id: a.instanceId, assetsAssignments_id: id, assets_id: free }))
      .resolves.toMatchObject({ json: { result: true } });
    expect(bookings(free)).toEqual([project]);
    expect(bookings(booked)).toEqual([]);
  });

  test("won't swap in an asset that's booked on another project on those dates", async ({ asA, tenants: { a } }) => {
    const mine = await newAsset(asA, a);
    const theirs = await newAsset(asA, a);
    const project = await newProject(asA, a, a.users.full.id, DATES);
    const other = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, mine);
    await assign(asA, a, other, theirs);
    const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);

    await expect(asA.api("/api/projects/assets/swap.php", { instances_id: a.instanceId, assetsAssignments_id: id, assets_id: theirs }))
      .resolves.toMatchObject({ json: { result: false } });
    expect(bookings(theirs)).toEqual([other]);
    expect(bookings(mine)).toEqual([project]);
  });

  test("keeps the project's running totals right when the new asset has its own rates, value and mass", async ({ asA, tenants: { a } }) => {
    const booked = await newAsset(asA, a);
    const pricier = await newAsset(asA, a);
    await expect(asA.api("/api/assets/editAsset.php", {
      instances_id: a.instanceId, formData: formData({ assets_id: pricier, assets_dayRate: "20.00", assets_value: "250.00", assets_mass: 3 }),
    })).resolves.toMatchObject({ json: { result: true } });
    const project = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, booked);
    const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);
    await asA.api("/api/projects/assets/setDiscount.php", { instances_id: a.instanceId, assetsAssignments: [id], assetsAssignments_discount: 50 });
    const none = { received: 0, sales: 0, subHire: 0, staff: 0 };
    // 3 days at £10.00, half off
    await expectFinances(asA, a, project, { ...none, subTotal: 3000, discounts: 1500, total: 1500, grandTotal: 1500, value: 10000, mass: 1 });

    await expect(asA.api("/api/projects/assets/swap.php", { instances_id: a.instanceId, assetsAssignments_id: id, assets_id: pricier }))
      .resolves.toMatchObject({ json: { result: true } });
    // 3 days at £20.00, still half off
    await expectFinances(asA, a, project, { ...none, subTotal: 6000, discounts: 3000, total: 3000, grandTotal: 3000, value: 25000, mass: 3 });
  });

  test("keeps a custom price when swapping", async ({ asA, tenants: { a } }) => {
    const booked = await newAsset(asA, a);
    const pricier = await newAsset(asA, a);
    await asA.api("/api/assets/editAsset.php", { instances_id: a.instanceId, formData: formData({ assets_id: pricier, assets_dayRate: "20.00" }) });
    const project = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, booked);
    const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);
    await asA.api("/api/projects/assets/setPrice.php", { instances_id: a.instanceId, assetsAssignments: [id], assetsAssignments_customPrice: "15.00" });
    const custom = { discounts: 0, received: 0, sales: 0, subHire: 0, staff: 0, subTotal: 1500, total: 1500, grandTotal: 1500, value: 10000, mass: 1 };
    await expectFinances(asA, a, project, custom);

    await asA.api("/api/projects/assets/swap.php", { instances_id: a.instanceId, assetsAssignments_id: id, assets_id: pricier });
    await expectFinances(asA, a, project, custom);
  });
});

test.describe("dispatch statuses", () => {
  test("an assignment's status can be set by its ID or by scanning the asset's tag", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    const [{ tag }] = dbQuery<{ tag: string }>("SELECT assets_tag tag FROM assets WHERE assets_id = ?", [asset]);
    const project = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, asset);
    const [{ id }] = dbQuery<{ id: number }>("SELECT assetsAssignments_id id FROM assetsAssignments WHERE projects_id = ?", [project]);
    expect(assignmentsOn(project)[0].status).toBeNull();

    await expect(asA.api("/api/projects/assets/setStatus.php", { instances_id: a.instanceId, assetsAssignments_id: id, assetsAssignments_status: a.assignmentStatusId }))
      .resolves.toMatchObject({ json: { result: true } });
    expect(assignmentsOn(project)[0].status).toBe(a.assignmentStatusId);

    dbQuery("UPDATE assetsAssignments SET assetsAssignmentsStatus_id = NULL WHERE assetsAssignments_id = ?", [id]);
    await expect(asA.api("/api/projects/assets/setStatusByTag.php", { instances_id: a.instanceId, projects_id: project, text: tag, assetsAssignments_status: a.assignmentStatusId }))
      .resolves.toMatchObject({ json: { result: true, response: { assets_id: asset } } });
    expect(assignmentsOn(project)[0].status).toBe(a.assignmentStatusId);
  });

  test("scanning a tag that isn't on the project changes nothing", async ({ asA, tenants: { a } }) => {
    const onProject = await newAsset(asA, a);
    const notOnProject = await newAsset(asA, a);
    const [{ tag }] = dbQuery<{ tag: string }>("SELECT assets_tag tag FROM assets WHERE assets_id = ?", [notOnProject]);
    const project = await newProject(asA, a, a.users.full.id, DATES);
    await assign(asA, a, project, onProject);

    const response = await asA.api("/api/projects/assets/setStatusByTag.php", { instances_id: a.instanceId, projects_id: project, text: tag, assetsAssignments_status: a.assignmentStatusId });
    expect(response.json).toMatchObject({ result: false });
    expect(assignmentsOn(project)[0].status).toBeNull();
  });
});

test.describe("sub-projects", () => {
  test("a sub-project that follows its parent's status changes with it; one that doesn't, doesn't", async ({ asA, tenants: { a } }) => {
    const parent = await newProject(asA, a, a.users.full.id, DATES);
    const newSub = async (name: string) => {
      const created = await asA.api("/api/projects/new.php", {
        instances_id: a.instanceId, projects_name: name, projects_manager: a.users.full.id, projectsType_id: a.projectTypeId, projects_parent_project_id: parent,
      });
      return created.json.response.projects_id as number;
    };
    const following = await newSub("Following sub-project");
    const independent = await newSub("Independent sub-project");
    await expect(asA.api("/api/projects/followParentStatus.php", { instances_id: a.instanceId, projects_id: following, follow: "true" }))
      .resolves.toMatchObject({ json: { result: true } });

    await expect(asA.api("/api/projects/changeStatus.php", { instances_id: a.instanceId, projects_id: parent, projectsStatuses_id: a.projectStatusIds.second }))
      .resolves.toMatchObject({ json: { result: true, response: { changed: true } } });
    const status = (id: number) => dbQuery<{ s: number }>("SELECT projectsStatuses_id s FROM projects WHERE projects_id = ?", [id])[0].s;
    expect(status(parent)).toBe(a.projectStatusIds.second);
    expect(status(following)).toBe(a.projectStatusIds.second);
    expect(status(independent)).not.toBe(a.projectStatusIds.second);
  });
});

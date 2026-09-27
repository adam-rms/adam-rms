import { BASE_URL } from "../env";
import { assign, bookings, type Dates, newAsset, newProject, releasedStatus } from "../projects";
import { dbQuery, expect, formData, newSession, Session, test, type Tenant } from "../tenants";

/**
 * An asset can only be in one place at a time: it mustn't end up booked on two projects whose delivery
 * dates overlap, whichever business each project belongs to. There are three ways a project can come to
 * hold an asset on some dates — assigning it (assets/assign.php), moving the project's dates
 * (changeProjectDeliverDates.php) and moving the project out of a status that releases its assets
 * (changeStatus.php) — and each one has its own clash check, so each is tested here.
 *
 * Every test books a new asset, so bookings left by other tests can't get in the way.
 */

/** The booking every test starts from */
const BOOKED: Dates = { start: "2035-03-10 09:00:00", end: "2035-03-15 18:00:00" };

const overlapping: { name: string; dates: Dates }[] = [
  { name: "the same dates", dates: BOOKED },
  { name: "dates inside the booking", dates: { start: "2035-03-11 09:00:00", end: "2035-03-12 18:00:00" } },
  { name: "dates either side of the booking", dates: { start: "2035-03-01 09:00:00", end: "2035-03-20 18:00:00" } },
  { name: "dates overlapping its start", dates: { start: "2035-03-05 09:00:00", end: "2035-03-11 18:00:00" } },
  { name: "dates overlapping its end", dates: { start: "2035-03-14 09:00:00", end: "2035-03-25 18:00:00" } },
];
const clear: { name: string; dates: Dates }[] = [
  { name: "dates ending before the booking starts", dates: { start: "2035-03-01 09:00:00", end: "2035-03-10 08:00:00" } },
  { name: "dates starting after the booking ends", dates: { start: "2035-03-15 19:00:00", end: "2035-03-20 18:00:00" } },
];

test.describe("within one business", () => {
  for (const c of overlapping) {
    test(`an asset can't be assigned to a second project with ${c.name}`, async ({ asA, tenants: { a } }) => {
      const asset = await newAsset(asA, a);
      const first = await newProject(asA, a, a.users.full.id, BOOKED);
      expect(await assign(asA, a, first, asset)).toMatchObject({ json: { result: true } });

      const second = await newProject(asA, a, a.users.full.id, c.dates);
      const response = await assign(asA, a, second, asset);
      expect(response.json).toMatchObject({ result: false, error: { message: "Asset wanted not available" } });
      expect(bookings(asset)).toEqual([first]);
    });
  }

  for (const c of clear) {
    test(`an asset can be assigned to a second project with ${c.name}`, async ({ asA, tenants: { a } }) => {
      const asset = await newAsset(asA, a);
      const first = await newProject(asA, a, a.users.full.id, BOOKED);
      await assign(asA, a, first, asset);

      const second = await newProject(asA, a, a.users.full.id, c.dates);
      expect(await assign(asA, a, second, asset)).toMatchObject({ json: { result: true } });
      expect(bookings(asset)).toEqual([first, second]);
    });
  }

  test("an asset is free again once it's unassigned from the project it was booked on", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    const first = await newProject(asA, a, a.users.full.id, BOOKED);
    await assign(asA, a, first, asset);
    const [{ id: assignment }] = dbQuery<{ id: number }>(
      "SELECT assetsAssignments_id id FROM assetsAssignments WHERE assets_id = ? AND projects_id = ?", [asset, first],
    );
    expect(await asA.api("/api/projects/assets/unassign.php", { instances_id: a.instanceId, assetsAssignments: [assignment] })).toMatchObject({ json: { result: true } });

    const second = await newProject(asA, a, a.users.full.id, BOOKED);
    expect(await assign(asA, a, second, asset)).toMatchObject({ json: { result: true } });
    expect(bookings(asset)).toEqual([second]);
  });

  test("an asset is free again once the project it was booked on is deleted", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    const first = await newProject(asA, a, a.users.full.id, BOOKED);
    await assign(asA, a, first, asset);
    expect(await asA.api("/api/projects/delete.php", { instances_id: a.instanceId, projects_id: first })).toMatchObject({ json: { result: true } });

    const second = await newProject(asA, a, a.users.full.id, BOOKED);
    expect(await assign(asA, a, second, asset)).toMatchObject({ json: { result: true } });
  });

  test("an asset is free while the project it's booked on is in a status that releases assets", async ({ asA, tenants: { a } }) => {
    const released = releasedStatus(a);
    const asset = await newAsset(asA, a);
    const first = await newProject(asA, a, a.users.full.id, BOOKED);
    await assign(asA, a, first, asset);
    const moved = await asA.api("/api/projects/changeStatus.php", { instances_id: a.instanceId, projects_id: first, projectsStatuses_id: released });
    expect(moved.json).toMatchObject({ result: true, response: { changed: true } });

    const second = await newProject(asA, a, a.users.full.id, BOOKED);
    expect(await assign(asA, a, second, asset)).toMatchObject({ json: { result: true } });

    // ...and the first project can't then take its assets back, because that would double-book it
    const back = await asA.api("/api/projects/changeStatus.php", { instances_id: a.instanceId, projects_id: first, projectsStatuses_id: a.projectStatusIds.first });
    expect(back.json).toMatchObject({ result: true, response: { changed: false, assets: [{ assets_id: asset, projects_id: second }] } });
    expect(dbQuery("SELECT projectsStatuses_id FROM projects WHERE projects_id = ?", [first])).toEqual([{ projectsStatuses_id: released }]);
  });

  test("a project's dates can't be moved onto dates its assets are booked elsewhere", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    const first = await newProject(asA, a, a.users.full.id, BOOKED);
    await assign(asA, a, first, asset);
    const second = await newProject(asA, a, a.users.full.id, { start: "2035-04-01 09:00:00", end: "2035-04-05 18:00:00" });
    await assign(asA, a, second, asset);
    expect(bookings(asset)).toEqual([first, second]);

    const moved = await asA.api("/api/projects/changeProjectDeliverDates.php", {
      instances_id: a.instanceId, projects_id: second, projects_dates_deliver_start: "2035-03-14 09:00:00", projects_dates_deliver_end: "2035-03-20 18:00:00",
    });
    expect(moved.json).toMatchObject({ result: true, response: { changed: false, assets: [{ assets_id: asset, projects_id: first }] } });
    expect(dbQuery("SELECT projects_dates_deliver_start start FROM projects WHERE projects_id = ?", [second])).toEqual([{ start: "2035-04-01 09:00:00" }]);

    // Control: moving it to other free dates works
    const control = await asA.api("/api/projects/changeProjectDeliverDates.php", {
      instances_id: a.instanceId, projects_id: second, projects_dates_deliver_start: "2035-05-01 09:00:00", projects_dates_deliver_end: "2035-05-05 18:00:00",
    });
    expect(control.json).toMatchObject({ result: true, response: { changed: true } });
  });

  test("assigning every asset of a type skips the ones already booked and reports them", async ({ asA, tenants: { a } }) => {
    const booked = await newAsset(asA, a);
    const free = await newAsset(asA, a);
    const first = await newProject(asA, a, a.users.full.id, BOOKED);
    await assign(asA, a, first, booked);

    const second = await newProject(asA, a, a.users.full.id, BOOKED);
    const response = await asA.api("/api/projects/assets/assign.php", { instances_id: a.instanceId, projects_id: second, assetTypes_id: a.assetTypeId });
    expect(response.json.result).toBe(true);
    expect(response.json.response.failed).toContainEqual({ assets_id: booked });
    expect(bookings(booked)).toEqual([first]);
    expect(bookings(free)).toContain(second);
  });
});

test.describe("maintenance", () => {
  async function newJob(session: Session, t: Tenant, assetId: number, change: "changeBlock" | "changeFlag") {
    const created = await session.api("/api/maintenance/newJob.php", {
      instances_id: t.instanceId, formData: formData({ maintenanceJobs_assets: String(assetId), maintenanceJobs_title: "Broken", maintenanceJobs_priority: 5 }),
    });
    expect(created.json, "creating the job").toMatchObject({ result: true });
    const job: number = created.json.response.maintenanceJobs_id;
    const field = change === "changeBlock" ? "maintenanceJobs_blockAssets" : "maintenanceJobs_flagAssets";
    await expect(session.api(`/api/maintenance/job/${change}.php`, { instances_id: t.instanceId, maintenanceJobs_id: job, [field]: 1 }))
      .resolves.toMatchObject({ json: { result: true } });
    return job;
  }

  test("an asset blocked by a maintenance job can't be assigned until the job is closed", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    const job = await newJob(asA, a, asset, "changeBlock");
    const project = await newProject(asA, a, a.users.full.id, BOOKED);
    expect((await assign(asA, a, project, asset)).json).toMatchObject({ result: false, error: { message: "Asset wanted not available" } });

    await expect(asA.api("/api/maintenance/job/changeJobStatus.php", { instances_id: a.instanceId, maintenanceJobs_id: job, maintenanceJobsStatuses_id: 2 }))
      .resolves.toMatchObject({ json: { result: true } });
    expect(await assign(asA, a, project, asset)).toMatchObject({ json: { result: true } });
  });

  test("an asset only flagged by a maintenance job can still be assigned", async ({ asA, tenants: { a } }) => {
    const asset = await newAsset(asA, a);
    await newJob(asA, a, asset, "changeFlag");
    const project = await newProject(asA, a, a.users.full.id, BOOKED);
    expect(await assign(asA, a, project, asset)).toMatchObject({ json: { result: true } });
  });
});

test.describe("across businesses", () => {
  /** Logged in as the user with full access to both A and B */
  let asShared: Session;
  test.beforeAll(async ({ playwright, seeded }) => {
    const request = await playwright.request.newContext({ baseURL: BASE_URL });
    asShared = await newSession(request, seeded.sharedUser.email, seeded.password);
  });
  test.afterAll(async () => {
    await asShared.request.dispose();
  });

  for (const c of overlapping) {
    test(`an asset booked in business A can't be assigned to business B's project with ${c.name}`, async ({ tenants: { a, b, sharedUser } }) => {
      const asset = await newAsset(asShared, a);
      const inA = await newProject(asShared, a, sharedUser.id, BOOKED);
      expect(await assign(asShared, a, inA, asset)).toMatchObject({ json: { result: true } });

      const inB = await newProject(asShared, b, sharedUser.id, c.dates);
      const response = await assign(asShared, b, inB, asset);
      expect(response.json).toMatchObject({ result: false, error: { message: "Asset wanted not available" } });
      expect(bookings(asset)).toEqual([inA]);
    });
  }

  test("an asset lent to business B's project can't also be assigned to business A's own project on the same dates", async ({ tenants: { a, b, sharedUser } }) => {
    const asset = await newAsset(asShared, a);
    const inB = await newProject(asShared, b, sharedUser.id, BOOKED);
    expect(await assign(asShared, b, inB, asset)).toMatchObject({ json: { result: true } });

    const inA = await newProject(asShared, a, sharedUser.id, BOOKED);
    expect((await assign(asShared, a, inA, asset)).json).toMatchObject({ result: false, error: { message: "Asset wanted not available" } });
    expect(bookings(asset)).toEqual([inB]);
  });

  test("an asset booked in business A can be assigned to business B's project on other dates", async ({ tenants: { a, b, sharedUser } }) => {
    const asset = await newAsset(asShared, a);
    const inA = await newProject(asShared, a, sharedUser.id, BOOKED);
    await assign(asShared, a, inA, asset);

    const inB = await newProject(asShared, b, sharedUser.id, clear[1].dates);
    expect(await assign(asShared, b, inB, asset)).toMatchObject({ json: { result: true } });
    expect(bookings(asset)).toEqual([inA, inB]);
  });

  test("business B's project can't be moved onto dates its asset is booked in business A", async ({ tenants: { a, b, sharedUser } }) => {
    const asset = await newAsset(asShared, a);
    const inA = await newProject(asShared, a, sharedUser.id, BOOKED);
    await assign(asShared, a, inA, asset);
    const inB = await newProject(asShared, b, sharedUser.id, clear[1].dates);
    await assign(asShared, b, inB, asset);

    const moved = await asShared.api("/api/projects/changeProjectDeliverDates.php", {
      instances_id: b.instanceId, projects_id: inB, projects_dates_deliver_start: BOOKED.start, projects_dates_deliver_end: BOOKED.end,
    });
    expect(moved.json).toMatchObject({ result: true, response: { changed: false, assets: [{ assets_id: asset, projects_id: inA }] } });
  });

  test("a user only in business A can't assign business B's asset at all", async ({ asA, tenants: { a, b } }) => {
    const project = await newProject(asA, a, a.users.full.id, { start: "2037-01-01 09:00:00", end: "2037-01-02 18:00:00" });
    await assign(asA, a, project, b.spareAssetId);
    expect(dbQuery("SELECT COUNT(*) n FROM assetsAssignments WHERE projects_id = ? AND assetsAssignments_deleted = 0", [project])).toEqual([{ n: 0 }]);
  });
});

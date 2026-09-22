import { test, expect, formData, mentions, seedTenants, snapshot, succeeded, type Params, type Tenant } from "../tenants";

/**
 * Instance permissions: a user whose position lacks the permission an endpoint needs is refused, and
 * nothing changes. The limited user in business A only has the view permissions listed in
 * setup/tenants.php (LIMITED_PERMISSIONS). Each case also runs as A's full-access user as a control, so a
 * malformed request can't pass by failing for another reason; the tenants are re-seeded afterwards.
 *
 * Characterisation tests: a case that currently lets the limited user through is marked test.fixme.
 */

type ApiCase = { endpoint: string; permission: string; params: (a: Tenant) => Params; fixme?: string };

const writeCases: ApiCase[] = [
  // Projects
  { endpoint: "/api/projects/new.php", permission: "PROJECTS:CREATE", params: (a) => ({ projects_name: "New by e2e", projects_manager: a.users.full.id, projectsType_id: a.projectTypeId }) },
  { endpoint: "/api/projects/changeName.php", permission: "PROJECTS:EDIT:NAME", params: (a) => ({ projects_id: a.projectId, projects_name: "Renamed by e2e" }) },
  { endpoint: "/api/projects/changeStatus.php", permission: "PROJECTS:EDIT:STATUS", params: (a) => ({ projects_id: a.projectId, projectsStatuses_id: a.projectStatusIds.second }) },
  { endpoint: "/api/projects/archive.php", permission: "PROJECTS:ARCHIVE", params: (a) => ({ projects_id: a.projectId }) },
  { endpoint: "/api/projects/delete.php", permission: "PROJECTS:DELETE", params: (a) => ({ projects_id: a.projectId }) },
  { endpoint: "/api/projects/newNote.php", permission: "PROJECTS:PROJECT_NOTES:CREATE:NOTES", params: (a) => ({ projects_id: a.projectId, projectsNotes_title: "Note by e2e" }) },
  { endpoint: "/api/projects/newPayment.php", permission: "PROJECTS:PROJECT_PAYMENTS:CREATE", params: (a) => ({ formData: formData({ projects_id: a.projectId, payments_amount: "1.00", payments_type: 1, payments_date: "2024-02-01" }) }) },
  { endpoint: "/api/projects/deletePayment.php", permission: "PROJECTS:PROJECT_PAYMENTS:DELETE", params: (a) => ({ payments_id: a.paymentId }) },
  { endpoint: "/api/projects/assets/setComment.php", permission: "PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMNET_COMMENT", params: (a) => ({ assetsAssignments: [a.assignmentId], assetsAssignments_comment: "Changed by e2e" }) },
  { endpoint: "/api/projects/assets/unassign.php", permission: "PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN", params: (a) => ({ assetsAssignments: [a.assignmentId] }) },
  { endpoint: "/api/projects/crew/edit.php", permission: "PROJECTS:PROJECT_CREW:EDIT", params: (a) => ({ crewAssignments_id: a.crewAssignmentId, crewAssignments_comment: "Changed by e2e" }) },
  // Assets
  { endpoint: "/api/assets/newAssetFromType.php", permission: "ASSETS:CREATE", params: (a) => ({ formData: formData({ assetTypes_id: a.assetTypeId }) }) },
  { endpoint: "/api/assets/editAsset.php", permission: "ASSETS:EDIT", params: (a) => ({ assets_id: a.assetId, assets_notes: "Changed by e2e" }) },
  { endpoint: "/api/assets/delete.php", permission: "ASSETS:DELETE", params: (a) => ({ assets_id: a.assetId }) },
  { endpoint: "/api/assets/editAssetType.php", permission: "ASSETS:ASSET_TYPES:EDIT", params: (a) => ({ formData: formData({ assetTypes_id: a.assetTypeId, assetTypes_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/assets/barcodes/delete.php", permission: "ASSETS:ASSET_BARCODES:DELETE", params: (a) => ({ barcodes_id: a.barcodeId }) },
  { endpoint: "/api/groups/edit.php", permission: "ASSETS:ASSET_GROUPS:EDIT", params: (a) => ({ formData: formData({ assetGroups_id: a.assetGroupId, assetGroups_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/categories/edit.php", permission: "ASSETS:ASSET_CATEGORIES:EDIT", params: (a) => ({ formData: formData({ assetCategories_id: a.categoryId, assetCategories_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/manufacturer/edit.php", permission: "ASSETS:MANUFACTURERS:EDIT", params: (a) => ({ formData: formData({ manufacturers_id: a.manufacturerId, manufacturers_name: "Renamed by e2e" }) }) },
  // Clients
  { endpoint: "/api/clients/new.php", permission: "CLIENTS:CREATE", params: () => ({ clients_name: "New by e2e" }) },
  { endpoint: "/api/clients/edit.php", permission: "CLIENTS:EDIT", params: (a) => ({ formData: formData({ clients_id: a.clientId, clients_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/clients/archive.php", permission: "CLIENTS:EDIT", params: (a) => ({ clients_id: a.clientId }) },
  // Locations
  { endpoint: "/api/locations/new.php", permission: "LOCATIONS:CREATE", params: () => ({ formData: formData({ locations_name: "New by e2e" }) }) },
  { endpoint: "/api/locations/edit.php", permission: "LOCATIONS:EDIT", params: (a) => ({ formData: formData({ locations_id: a.locationId, locations_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/locations/archive.php", permission: "LOCATIONS:EDIT", params: (a) => ({ locations_id: a.locationId }) },
  // Maintenance
  { endpoint: "/api/maintenance/job/changeName.php", permission: "MAINTENANCE_JOBS:EDIT:NAME", params: (a) => ({ maintenanceJobs_id: a.maintenanceJobId, maintenanceJobs_title: "Renamed by e2e" }) },
  { endpoint: "/api/maintenance/job/deleteJob.php", permission: "MAINTENANCE_JOBS:DELETE", params: (a) => ({ maintenanceJobs_id: a.maintenanceJobId }) },
  { endpoint: "/api/maintenance/job/sendMessage.php", permission: "MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB", params: (a) => ({ maintenanceJobs_id: a.maintenanceJobId, maintenanceJobsMessages_text: "Message by e2e" }) },
];

// Pages the limited user must get the 404 page for
const refusedPages = [
  { file: "project/new.php", url: "/project/new.php", permission: "PROJECTS:CREATE" },
  { file: "newAsset.php", url: "/newAsset.php", permission: "ASSETS:CREATE" },
  { file: "ledger.php", url: "/ledger.php", permission: "FINANCE:PAYMENTS_LEDGER:VIEW" },
  { file: "instances/users.php", url: "/instances/users.php", permission: "BUSINESS:USERS:VIEW:LIST" },
  { file: "instances/settings.php", url: "/instances/settings.php", permission: "BUSINESS:BUSINESS_SETTINGS:VIEW" },
  { file: "instances/permissions.php", url: "/instances/permissions.php", permission: "BUSINESS:ROLES_AND_PERMISSIONS:VIEW" },
];
const NOT_FOUND = "Oops! Page not found.";

const maybeFixme = (fixme: string | undefined) => (fixme ? test.fixme : test);

test.describe("a user without the permission", () => {
  for (const c of writeCases) {
    maybeFixme(c.fixme)(`is refused ${c.endpoint} (${c.permission})`, async ({ asA, asLimitedA, tenants: { a } }) => {
      const before = snapshot(a.instanceId);
      const response = await asLimitedA.api(c.endpoint, c.params(a));
      expect(succeeded(response), response.body.slice(0, 500)).toBe(false);
      expect(snapshot(a.instanceId)).toEqual(before);

      const control = await asA.api(c.endpoint, c.params(a));
      expect(snapshot(a.instanceId), `control: the full-access user can\n${control.body.slice(0, 500)}`).not.toEqual(before);
      seedTenants();
    });
  }

  for (const c of refusedPages) {
    test(`gets the 404 page for ${c.file} (${c.permission})`, async ({ asA, asLimitedA }) => {
      expect(mentions(await asLimitedA.page(c.url), NOT_FOUND)).toBe(true);
      expect(mentions(await asA.page(c.url), NOT_FOUND), "control: the full-access user can").toBe(false);
    });
  }
});

test.describe("a user with only view permissions", () => {
  test("can view projects (PROJECTS:VIEW)", async ({ asLimitedA, tenants: { a } }) => {
    expect(mentions(await asLimitedA.api("/api/projects/data.php", { id: a.projectId }), `${a.marker} project description`)).toBe(true);
    expect(mentions(await asLimitedA.page(`/project/?id=${a.projectId}`), `${a.marker} project description`)).toBe(true);
  });

  test("can view maintenance jobs (MAINTENANCE_JOBS:VIEW)", async ({ asLimitedA, tenants: { a } }) => {
    expect(mentions(await asLimitedA.page(`/maintenance/job.php?id=${a.maintenanceJobId}`), `${a.marker} maintenance job`)).toBe(true);
  });
});

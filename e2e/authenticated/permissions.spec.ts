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
  { endpoint: "/api/projects/crew/sortRank.php", permission: "PROJECTS:PROJECT_CREW:EDIT:CREW_RANKS", params: (a) => ({ projects_id: a.projectId, order: [a.crewAssignmentId] }) },
  { endpoint: "/api/projects/crew/crewRoles/apply.php", permission: "PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES", params: (a) => ({ formData: formData({ projectsVacantRoles_id: a.vacantRoleId }) }) },
  { endpoint: "/api/projects/changeProjectDeliverDates.php", permission: "PROJECTS:EDIT:DATES", params: (a) => ({ projects_id: a.projectId, projects_dates_deliver_start: "2031-01-01 09:00", projects_dates_deliver_end: "2031-01-05 09:00" }) },
  { endpoint: "/api/projects/changeProjectFinanceDurationMaths.php", permission: "PROJECTS:EDIT:DATES", params: (a) => ({ projects_id: a.projectId, projects_dates_finances_days: 3, projects_dates_finances_weeks: 1 }) },
  { endpoint: "/api/projects/assets/setStatusByTag.php", permission: "PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS", params: (a) => ({ projects_id: a.projectId, text: a.assetTag, assetsAssignments_status: a.assignmentStatusId }) },
  { endpoint: "/api/projects/assets/setStatusBarcode.php", permission: "PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS", params: (a) => ({ projects_id: a.projectId, text: a.barcodeValue, type: "CODE_128", assetsAssignments_status: a.assignmentStatusId }) },
  { endpoint: "/api/projects/assets/assign.php", permission: "PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN", params: (a) => ({ projects_id: a.projectId, assets_id: a.spareAssetId }) },
  { endpoint: "/api/projects/assets/swap.php", permission: "PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN", params: (a) => ({ assetsAssignments_id: a.assignmentId, assets_id: a.spareAssetId }) },
  // Assets
  { endpoint: "/api/assets/newAssetFromType.php", permission: "ASSETS:CREATE", params: (a) => ({ formData: formData({ assetTypes_id: a.assetTypeId }) }) },
  { endpoint: "/api/assets/editAsset.php", permission: "ASSETS:EDIT", params: (a) => ({ assets_id: a.assetId, assets_notes: "Changed by e2e" }) },
  { endpoint: "/api/assets/delete.php", permission: "ASSETS:DELETE", params: (a) => ({ assets_id: a.assetId }) },
  { endpoint: "/api/assets/editAssetType.php", permission: "ASSETS:ASSET_TYPES:EDIT", params: (a) => ({ formData: formData({ assetTypes_id: a.assetTypeId, assetTypes_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/assets/barcodes/delete.php", permission: "ASSETS:ASSET_BARCODES:DELETE", params: (a) => ({ barcodes_id: a.barcodeId }) },
  { endpoint: "/api/groups/edit.php", permission: "ASSETS:ASSET_GROUPS:EDIT", params: (a) => ({ formData: formData({ assetGroups_id: a.assetGroupId, assetGroups_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/categories/edit.php", permission: "ASSETS:ASSET_CATEGORIES:EDIT", params: (a) => ({ formData: formData({ assetCategories_id: a.categoryId, assetCategories_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/manufacturer/edit.php", permission: "ASSETS:MANUFACTURERS:EDIT", params: (a) => ({ formData: formData({ manufacturers_id: a.manufacturerId, manufacturers_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/manufacturer/new.php", permission: "ASSETS:MANUFACTURERS:CREATE", params: () => ({ manufacturers_name: "E2E_TENANT_A_SECRET manufacturer by e2e" }) },
  { endpoint: "/api/assets/newAssetType.php", permission: "ASSETS:ASSET_TYPES:CREATE", params: (a) => ({ formData: formData({ assetTypes_name: "E2E_TENANT_A_SECRET type by e2e", manufacturers_id: a.manufacturerId, assetCategories_id: a.categoryId }) }) },
  { endpoint: "/api/groups/new.php", permission: "ASSETS:ASSET_GROUPS:CREATE", params: () => ({ formData: formData({ assetGroups_name: "E2E_TENANT_A_SECRET group by e2e" }) }) },
  { endpoint: "/api/categories/new.php", permission: "ASSETS:ASSET_CATEGORIES:EDIT", params: (a) => ({ formData: formData({ assetCategories_name: "E2E_TENANT_A_SECRET category by e2e", assetCategoriesGroups_id: a.categoryGroupId, assetCategories_rank: 99 }) }) },
  { endpoint: "/api/categories/groups/new.php", permission: "ASSETS:ASSET_CATEGORIES:EDIT", params: () => ({ formData: formData({ assetCategoriesGroups_name: "E2E_TENANT_A_SECRET category group by e2e" }) }) },
  // CMS
  { endpoint: "/api/cms/editPageConfig.php", permission: "CMS:CMS_PAGES:EDIT", params: (a) => ({ formData: formData({ cmsPages_id: a.cmsPageId, cmsPages_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/cms/editPageContent.php", permission: "CMS:CMS_PAGES:EDIT", params: (a) => ({ cmsPages_id: a.cmsPageId, pageData: { cards: [{ content: "<p>Changed by e2e</p>" }] }, changelog: "e2e" }) },
  { endpoint: "/api/cms/editPageRank.php", permission: "CMS:CMS_PAGES:EDIT", params: (a) => ({ order: [a.cmsPageId] }) },
  { endpoint: "/api/cms/setCustomDashboard.php", permission: "CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS", params: (a) => ({ instancePositions_id: a.positions.limited, cmsPages_id: a.cmsPageId }) },
  // Training
  { endpoint: "/api/modules/new.php", permission: "TRAINING:CREATE", params: () => ({ formData: formData({ modules_name: "E2E_TENANT_A_SECRET module by e2e" }) }) },
  { endpoint: "/api/modules/edit.php", permission: "TRAINING:EDIT", params: (a) => ({ formData: formData({ modules_id: a.moduleId, modules_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/modules/steps/new.php", permission: "TRAINING:EDIT", params: (a) => ({ formData: formData({ modules_id: a.moduleId, modulesSteps_name: "Step by e2e", modulesSteps_type: 1, modulesSteps_order: 50, modulesSteps_locked: 0 }) }) },
  { endpoint: "/api/modules/steps/edit.php", permission: "TRAINING:EDIT", params: (a) => ({ formData: formData({ modulesSteps_id: a.moduleStepId, modulesSteps_name: "Renamed by e2e", modulesSteps_content: "Changed by e2e" }) }) },
  { endpoint: "/api/modules/steps/sortRank.php", permission: "TRAINING:EDIT", params: (a) => ({ order: [a.moduleStepId] }) },
  { endpoint: "/api/training/certify.php", permission: "TRAINING:EDIT:CERTIFY_USER", params: (a) => ({ userid: a.users.limited.id, modules_id: a.moduleId, comment: "Certified by e2e" }) },
  { endpoint: "/api/training/revokeAll.php", permission: "TRAINING:EDIT:REVOKE_USER_CERTIFICATION", params: (a) => ({ userid: a.users.limited.id, modules_id: a.moduleId }) },
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
const refusedPages: { file: string; url: string | ((a: Tenant) => string); permission: string }[] = [
  { file: "project/new.php", url: "/project/new.php", permission: "PROJECTS:CREATE" },
  { file: "newAsset.php", url: "/newAsset.php", permission: "ASSETS:CREATE" },
  { file: "ledger.php", url: "/ledger.php", permission: "FINANCE:PAYMENTS_LEDGER:VIEW" },
  { file: "instances/users.php", url: "/instances/users.php", permission: "BUSINESS:USERS:VIEW:LIST" },
  { file: "instances/settings.php", url: "/instances/settings.php", permission: "BUSINESS:BUSINESS_SETTINGS:VIEW" },
  { file: "instances/permissions.php", url: "/instances/permissions.php", permission: "BUSINESS:ROLES_AND_PERMISSIONS:VIEW" },
  { file: "instances/importAssets.php", url: "/instances/importAssets.php", permission: "ASSETS:IMPORT" },
  { file: "maintenance/barcode.php", url: "/maintenance/barcode.php", permission: "ASSETS:ASSET_BARCODES:VIEW" },
  { file: "maintenance/barcodeGenerator.php", url: "/maintenance/barcodeGenerator.php", permission: "ASSETS:ASSET_BARCODES:VIEW" },
  { file: "maintenance/barcodePrint.php", url: (a) => `/maintenance/barcodePrint.php?ids=${a.assetId}`, permission: "ASSETS:ASSET_BARCODES:VIEW" },
  { file: "location/barcode.php", url: (a) => `/location/barcode.php?location=${a.locationId}`, permission: "LOCATIONS:LOCATION_BARCODES:VIEW" },
  { file: "cms/list.php", url: "/cms/list.php", permission: "CMS:CMS_PAGES:CREATE" },
  { file: "cms/stats.php", url: (a) => `/cms/stats.php?p=${a.cmsPageId}`, permission: "CMS:CMS_PAGES:CREATE" },
  { file: "cms/edit.php", url: (a) => `/cms/edit.php?p=${a.cmsPageId}`, permission: "CMS:CMS_PAGES:EDIT" },
  { file: "cms/log.php", url: (a) => `/cms/log.php?p=${a.cmsPageId}`, permission: "CMS:CMS_PAGES:VIEW:ACCESS_LOG" },
  { file: "cms/customDashboards.php", url: "/cms/customDashboards.php", permission: "CMS:CMS_PAGES:EDIT:CUSTOM_DASHBOARDS" },
  { file: "training/index.php", url: "/training/", permission: "TRAINING:VIEW" },
  { file: "project/crew/vacancies.php", url: "/project/crew/vacancies.php", permission: "PROJECTS:PROJECT_CREW:VIEW:VIEW_AND_APPLY_FOR_CREW_ROLES" },
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
    test(`gets the 404 page for ${c.file} (${c.permission})`, async ({ asA, asLimitedA, tenants: { a } }) => {
      const url = typeof c.url === "string" ? c.url : c.url(a);
      expect(mentions(await asLimitedA.page(url), NOT_FOUND)).toBe(true);
      expect(mentions(await asA.page(url), NOT_FOUND), "control: the full-access user can").toBe(false);
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

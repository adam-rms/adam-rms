import { test, expect, formData, mentions, newSession, seedTenants, snapshot, dbQuery, succeeded, type Params, type Tenant } from "../tenants";
import { BASE_URL, TEST_USER } from "../env";

/**
 * Tenant isolation: a user of business A must not be able to read or change business B's records by
 * passing B's IDs. Every case is sent twice as A's full-access user:
 *   - with B's IDs, asserting nothing of B's comes back / B's rows are unchanged, and
 *   - with A's own IDs (the control), asserting it does work — so a malformed request can't pass by doing nothing.
 * Controls change A's records, so the tenants are re-seeded after each one (setup/tenants.php resets them).
 *
 * A new case that finds a leak can be marked test.fixme (set `fixme` to a note saying how it leaks)
 * until the endpoint is fixed; e2e/COVERAGE.md flags those endpoints.
 */

type ReadCase = {
  endpoint: string;
  params: (t: Tenant) => Params;
  /** Text the control response must contain. Defaults to the business's marker. */
  shows?: (t: Tenant) => string;
  /** Text that means the target's data leaked. Defaults to the business's marker. */
  hides?: (t: Tenant) => string;
  /** Endpoints that read $_GET rather than $_POST */
  method?: "GET";
  fixme?: string;
};

type WriteCase = {
  endpoint: string;
  /** `target` owns the record being changed; `self` is the caller's own business (always A) */
  params: (target: Tenant, self: Tenant) => Params;
  /** false when the request is a no-op on A's seeded state (e.g. un-archiving something not archived) */
  control?: false;
  fixme?: string;
  /** SQL that undoes the request if it moved A's record into B, so later tests still find it */
  restore?: (a: Tenant) => [string, unknown[]];
};

type LinkCase = {
  endpoint: string;
  /** Tells apart cases for the same endpoint */
  label?: string;
  /** Point one of `self`'s records at `target`'s record */
  params: (target: Tenant, self: Tenant) => Params;
  /** SQL counting rows in which A's data refers to B's record */
  linked: (b: Tenant, a: Tenant) => [string, unknown[]];
  fixme?: string;
};

type PageCase = { file: string; label?: string; url: (t: Tenant) => string; shows: (t: Tenant) => string; hides?: (t: Tenant) => string; fixme?: string };

const readCases: ReadCase[] = [
  { endpoint: "/api/projects/list.php", params: () => ({}) },
  { endpoint: "/api/projects/data.php", params: (t) => ({ id: t.projectId }), shows: (t) => `${t.marker} project description` },
  { endpoint: "/api/projects/assets/statusList.php", params: (t) => ({ projects_id: t.projectId }), shows: (t) => `${t.marker} assignment comment` },
  { endpoint: "/api/projects/crew/crewRoles/list.php", params: (t) => ({ projects_id: t.projectId }), shows: (t) => `${t.marker} vacancy` },
  { endpoint: "/api/projects/crew/crewRoles/applicationList.php", params: (t) => ({ projectsVacantRoles_id: t.vacantRoleId }), shows: (t) => `${t.marker} application` },
  { endpoint: "/api/projects/crew/searchUser.php", params: (t) => ({ projects_id: t.projectId, term: "E2E_TENANT" }) },
  { endpoint: "/api/assets/list.php", params: (t) => ({ assetTypes_id: t.assetTypeId }), shows: (t) => `${t.marker} asset type` },
  { endpoint: "/api/assets/searchType.php", params: () => ({ term: "E2E_TENANT" }), shows: (t) => `${t.marker} asset type` },
  { endpoint: "/api/assets/barcodes/search.php", params: (t) => ({ text: t.barcodeValue, type: "CODE_128" }), shows: (t) => t.assetTag },
  { endpoint: "/api/barcodes/searchAsset.php", params: (t) => ({ term: t.assetTag }), shows: (t) => t.assetTag },
  { endpoint: "/api/maintenance/searchAsset.php", params: (t) => ({ term: t.assetTag }), shows: (t) => t.assetTag },
  { endpoint: "/api/maintenance/searchUser.php", params: () => ({ term: "E2E_TENANT" }) },
  { endpoint: "/api/groups/search.php", params: () => ({ term: "E2E_TENANT" }) },
  { endpoint: "/api/categories/search.php", params: () => ({ term: "E2E_TENANT" }) },
  { endpoint: "/api/manufacturer/search.php", params: () => ({ term: "E2E_TENANT" }) },
  { endpoint: "/api/search/search.php", params: () => ({ term: "E2E_TENANT" }), method: "GET" },
  { endpoint: "/api/assets/searchAssets.php", params: () => ({ term: "E2E_TENANT" }) },
  { endpoint: "/api/assets/export.php", params: () => ({ csv: 1 }) },
  { endpoint: "/api/assets/getAssetTypeData.php", params: (t) => ({ term: t.assetTypeId }), shows: (t) => `"assetTypes_id":${t.assetTypeId}`, hides: (t) => `"assetTypes_id":${t.assetTypeId}` },
  { endpoint: "/api/assets/substitutions.php", params: (t) => ({ assetsAssignments_id: t.assignmentId }), shows: (t) => t.spareAssetTag, hides: (t) => t.spareAssetTag },
  { endpoint: "/api/projects/getComments.php", params: (t) => ({ projects_id: t.projectId }), shows: (t) => `${t.marker} quick comment` },
];

const pageCases: PageCase[] = [
  { file: "project/index.php", url: (t) => `/project/?id=${t.projectId}`, shows: (t) => `${t.marker} project description` },
  { file: "project/noteExport.php", url: (t) => `/project/noteExport.php?id=${t.projectId}`, shows: (t) => `${t.marker} note` },
  { file: "project/projectInvoice.php", url: (t) => `/project/projectInvoice.php?id=${t.projectId}`, shows: (t) => `${t.marker}\\u0020project` }, // the PDF data is JS-escaped
  { file: "project/crew/vacantCrew.php", url: (t) => `/project/crew/vacantCrew.php?id=${t.projectId}`, shows: (t) => `${t.marker} vacancy` },
  { file: "project/crew/vacancy.php", url: (t) => `/project/crew/vacancy.php?id=${t.vacantRoleId}`, shows: (t) => `${t.marker} vacancy` },
  { file: "project/crew/applications.php", url: (t) => `/project/crew/applications.php?id=${t.vacantRoleId}`, shows: (t) => `${t.marker} application` },
  { file: "project/list.php", url: () => `/project/list.php`, shows: (t) => `${t.marker} project` },
  { file: "asset.php", url: (t) => `/asset.php?id=${t.assetTypeId}`, shows: (t) => `${t.marker} asset type` },
  { file: "assets.php", url: () => `/assets.php`, shows: (t) => `${t.marker} asset type` },
  { file: "clients.php", url: () => `/clients.php`, shows: (t) => `${t.marker} client` },
  { file: "location/index.php", url: () => `/location/`, shows: (t) => `${t.marker} location` },
  { file: "maintenance/index.php", url: () => `/maintenance/`, shows: (t) => `${t.marker} maintenance job` },
  { file: "maintenance/job.php", url: (t) => `/maintenance/job.php?id=${t.maintenanceJobId}`, shows: (t) => `${t.marker} maintenance job` },
  { file: "cms/index.php", url: (t) => `/cms/?p=${t.cmsPageId}`, shows: (t) => `${t.marker} page` },
  { file: "training/module.php", url: (t) => `/training/module.php?id=${t.moduleId}`, shows: (t) => `${t.marker} training module` },
  { file: "user.php", url: (t) => `/user.php?id=${t.users.limited.id}`, shows: (t) => t.users.limited.email },
  { file: "manufacturers.php", url: () => `/manufacturers.php`, shows: (t) => `${t.marker} manufacturer` },
  { file: "manufacturers.php", label: "search", url: () => `/manufacturers.php?q=E2E_TENANT`, shows: (t) => `${t.marker} manufacturer` },
  { file: "instances/importAssets.php", url: () => `/instances/importAssets.php`, shows: (t) => `${t.marker} category` },
  { file: "project/crew/vacancies.php", url: () => `/project/crew/vacancies.php`, shows: (t) => `${t.marker} vacancy` },
  { file: "location/barcode.php", url: (t) => `/location/barcode.php?location=${t.locationId}`, shows: (t) => t.locationBarcodeValue, hides: (t) => t.locationBarcodeValue },
  { file: "maintenance/barcodeGenerator.php", url: (t) => `/maintenance/barcodeGenerator.php?type=${t.assetTypeId}&category=${t.categoryId}&manufacturer=${t.manufacturerId}`, shows: (t) => t.assetTag, hides: (t) => t.assetTag },
  { file: "maintenance/barcodeGenerator.php", label: "all", url: () => `/maintenance/barcodeGenerator.php?all`, shows: (t) => t.assetTag, hides: (t) => t.assetTag },
  { file: "maintenance/barcodePrint.php", url: (t) => `/maintenance/barcodePrint.php?ids=${t.assetId}&groups=${t.assetGroupId}`, shows: (t) => t.assetTag, hides: (t) => t.assetTag },
  { file: "search.php", url: () => `/search.php?term=E2E_TENANT`, shows: (t) => `${t.marker} project` },
];

const writeCases: WriteCase[] = [
  // Projects
  { endpoint: "/api/projects/changeName.php", params: (t) => ({ projects_id: t.projectId, projects_name: "Renamed by e2e" }) },
  { endpoint: "/api/projects/changeDescription.php", params: (t) => ({ projects_id: t.projectId, projects_description: "Changed by e2e" }) },
  { endpoint: "/api/projects/changeDeliveryNotes.php", params: (t) => ({ projects_id: t.projectId, projects_deliveryNotes: "Changed by e2e" }) },
  { endpoint: "/api/projects/changeInvoiceNotes.php", params: (t) => ({ projects_id: t.projectId, projects_invoiceNotes: "Changed by e2e" }) },
  { endpoint: "/api/projects/changeProjectDates.php", params: (t) => ({ projects_id: t.projectId, projects_dates_use_start: "2031-01-01 09:00", projects_dates_use_end: "2031-01-02 09:00" }) },
  { endpoint: "/api/projects/archive.php", params: (t) => ({ projects_id: t.projectId }) },
  { endpoint: "/api/projects/unArchive.php", params: (t) => ({ projects_id: t.projectId }), control: false },
  { endpoint: "/api/projects/delete.php", params: (t) => ({ projects_id: t.projectId }) },
  { endpoint: "/api/projects/changeStatus.php", params: (t) => ({ projects_id: t.projectId, projectsStatuses_id: t.projectStatusIds.second }) },
  { endpoint: "/api/projects/changeSubProject.php", params: (t) => ({ projects_id: t.subProjectId, projects_parent_project_id: -1 }) },
  // Valid references from the caller's own business, applied to the target's project
  { endpoint: "/api/projects/changeProjectManager.php", params: (t, self) => ({ projects_id: t.projectId, users_userid: self.users.full.id }) },
  { endpoint: "/api/projects/changeClient.php", params: (t, self) => ({ projects_id: t.projectId, clients_id: self.clientId }) },
  { endpoint: "/api/projects/changeVenue.php", params: (t, self) => ({ projects_id: t.projectId, locations_id: self.locationId }) },
  { endpoint: "/api/projects/changeProjectType.php", params: (t, self) => ({ projects_id: t.projectId, projectsTypes_id: self.projectTypeId }) },
  { endpoint: "/api/projects/followParentStatus.php", params: (t) => ({ projects_id: t.subProjectId, follow: "true" }) },
  { endpoint: "/api/projects/newNote.php", params: (t) => ({ projects_id: t.projectId, projectsNotes_title: "Note by e2e" }) },
  { endpoint: "/api/projects/editNote.php", params: (t) => ({ projects_id: t.projectId, projectsNotes_id: t.noteId, projectsNotes_text: "Changed by e2e" }) },
  { endpoint: "/api/projects/newPayment.php", params: (t) => ({ formData: formData({ projects_id: t.projectId, payments_amount: "1.00", payments_type: 1, payments_date: "2024-02-01" }) }) },
  { endpoint: "/api/projects/deletePayment.php", params: (t) => ({ payments_id: t.paymentId }) },
  { endpoint: "/api/projects/newQuickComment.php", params: (t) => ({ projects_id: t.projectId, text: "Comment by e2e" }) },
  { endpoint: "/api/projects/assets/setComment.php", params: (t) => ({ assetsAssignments: [t.assignmentId], assetsAssignments_comment: "Changed by e2e" }) },
  { endpoint: "/api/projects/assets/setDiscount.php", params: (t) => ({ assetsAssignments: [t.assignmentId], assetsAssignments_discount: 10 }) },
  { endpoint: "/api/projects/assets/setPrice.php", params: (t) => ({ assetsAssignments: [t.assignmentId], assetsAssignments_customPrice: "5.00" }) },
  { endpoint: "/api/projects/assets/setStatus.php", params: (t) => ({ assetsAssignments_id: t.assignmentId, assetsAssignments_status: t.assignmentStatusId }) },
  { endpoint: "/api/projects/assets/unassign.php", params: (t) => ({ assetsAssignments: [t.assignmentId] }) },
  { endpoint: "/api/projects/crew/edit.php", params: (t) => ({ crewAssignments_id: t.crewAssignmentId, crewAssignments_comment: "Changed by e2e" }) },
  { endpoint: "/api/projects/crew/unassign.php", params: (t) => ({ crewAssignments_id: t.crewAssignmentId }) },
  {
    endpoint: "/api/projects/crew/crewRoles/edit.php",
    // The project must be the caller's own; the vacancy is the target's
    params: (t, self) => ({ formData: formData({ projects_id: self.projectId, projectsVacantRoles_id: t.vacantRoleId, projectsVacantRoles_name: "Renamed by e2e" }) }),
  },
  { endpoint: "/api/projects/crew/crewRoles/accept.php", params: (t) => ({ projectsVacantRolesApplications_id: t.vacancyApplicationId }) },
  { endpoint: "/api/projects/crew/crewRoles/reject.php", params: (t) => ({ projectsVacantRolesApplications_id: t.vacancyApplicationId }) },
  { endpoint: "/api/projects/crew/crewRoles/apply.php", params: (t) => ({ formData: formData({ projectsVacantRoles_id: t.vacantRoleId, projectsVacantRolesApplications_applicantComment: "Applied by e2e" }) }) },
  { endpoint: "/api/projects/crew/sortRank.php", params: (t) => ({ projects_id: t.projectId, order: [t.crewAssignmentId] }) },
  { endpoint: "/api/projects/changeProjectDeliverDates.php", params: (t) => ({ projects_id: t.projectId, projects_dates_deliver_start: "2031-01-01 09:00", projects_dates_deliver_end: "2031-01-05 09:00" }) },
  { endpoint: "/api/projects/changeProjectFinanceDurationMaths.php", params: (t) => ({ projects_id: t.projectId, projects_dates_finances_days: 3, projects_dates_finances_weeks: 1 }) },
  { endpoint: "/api/projects/assets/setStatusByTag.php", params: (t) => ({ projects_id: t.projectId, text: t.assetTag, assetsAssignments_status: t.assignmentStatusId }) },
  { endpoint: "/api/projects/assets/setStatusBarcode.php", params: (t) => ({ projects_id: t.projectId, text: t.barcodeValue, type: "CODE_128", assetsAssignments_status: t.assignmentStatusId }) },
  { endpoint: "/api/projects/assets/assign.php", params: (t, self) => ({ projects_id: t.projectId, assets_id: self.spareAssetId }) },
  { endpoint: "/api/projects/assets/swap.php", params: (t) => ({ assetsAssignments_id: t.assignmentId, assets_id: t.spareAssetId }) },
  // Assets
  { endpoint: "/api/assets/editAsset.php", params: (t) => ({ assets_id: t.assetId, assets_notes: "Changed by e2e", assets_value: "9999.00" }) },
  { endpoint: "/api/assets/archive.php", params: (t) => ({ assets_id: t.assetId, reason: "e2e", date: "2024-02-01" }) },
  { endpoint: "/api/assets/delete.php", params: (t) => ({ assets_id: t.assetId }) },
  { endpoint: "/api/assets/editAssetType.php", params: (t) => ({ formData: formData({ assetTypes_id: t.assetTypeId, assetTypes_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/assets/editAssetTypeDefinableFields.php", params: (t) => ({ formData: formData({ assetTypes_id: t.assetTypeId, asset_definableFields_1: "e2e field" }) }) },
  { endpoint: "/api/assets/barcodes/assign.php", params: (t) => ({ id: t.assetId, text: `E2E${Date.now()}`, type: "CODE_128" }) },
  { endpoint: "/api/assets/barcodes/delete.php", params: (t) => ({ barcodes_id: t.barcodeId }) },
  { endpoint: "/api/groups/edit.php", params: (t) => ({ formData: formData({ assetGroups_id: t.assetGroupId, assetGroups_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/groups/removeAsset.php", params: (t) => ({ assetGroups_id: t.assetGroupId, assets_id: t.assetId }) },
  { endpoint: "/api/categories/edit.php", params: (t) => ({ formData: formData({ assetCategories_id: t.categoryId, assetCategories_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/categories/groups/edit.php", params: (t) => ({ formData: formData({ assetCategoriesGroups_id: t.categoryGroupId, assetCategoriesGroups_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/manufacturer/edit.php", params: (t) => ({ formData: formData({ manufacturers_id: t.manufacturerId, manufacturers_name: "Renamed by e2e" }) }) },
  // A isn't a member of B, so there's nowhere for the control to transfer A's asset to
  { endpoint: "/api/assets/transfer.php", params: (t, self) => ({ assets_id: self.assetId, new_instances_id: t.instanceId, assetTypes_id: t.assetTypeId }), control: false },
  // Clients
  { endpoint: "/api/clients/edit.php", params: (t) => ({ formData: formData({ clients_id: t.clientId, clients_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/clients/archive.php", params: (t) => ({ clients_id: t.clientId }) },
  { endpoint: "/api/clients/unarchive.php", params: (t) => ({ clients_id: t.clientId }), control: false },
  // Locations
  { endpoint: "/api/locations/edit.php", params: (t) => ({ formData: formData({ locations_id: t.locationId, locations_name: "Renamed by e2e" }) }) },
  { endpoint: "/api/locations/archive.php", params: (t) => ({ locations_id: t.locationId }) },
  { endpoint: "/api/locations/unarchive.php", params: (t) => ({ locations_id: t.locationId }), control: false },
  // Maintenance
  { endpoint: "/api/maintenance/job/changeName.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_title: "Renamed by e2e" }) },
  { endpoint: "/api/maintenance/job/changePriority.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_priority: 1 }) },
  { endpoint: "/api/maintenance/job/changeDueDate.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_timestamp_due: "2031-01-01" }) },
  { endpoint: "/api/maintenance/job/changeFlag.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_flagAssets: 1 }) },
  { endpoint: "/api/maintenance/job/changeBlock.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_blockAssets: 1 }) },
  { endpoint: "/api/maintenance/job/changeJobStatus.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobsStatuses_id: 2 }) },
  { endpoint: "/api/maintenance/job/removeAsset.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, assets_id: t.assetId }) },
  { endpoint: "/api/maintenance/job/sendMessage.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobsMessages_text: "Message by e2e" }) },
  { endpoint: "/api/maintenance/job/changeJobAssigned.php", params: (t, self) => ({ maintenanceJobs_id: t.maintenanceJobId, users_userid: self.users.full.id }) },
  { endpoint: "/api/maintenance/job/tagUser.php", params: (t, self) => ({ maintenanceJobs_id: t.maintenanceJobId, users_userid: self.users.limited.id }) },
  // The seeded job has nobody tagged, so there's nothing for the control to untag
  { endpoint: "/api/maintenance/job/unTagUser.php", params: (t, self) => ({ maintenanceJobs_id: t.maintenanceJobId, users_userid: self.users.limited.id }), control: false },
  { endpoint: "/api/maintenance/job/addAsset.php", params: (t, self) => ({ maintenanceJobs_id: t.maintenanceJobId, maintenanceJobs_assets: [self.assetId] }) },
  { endpoint: "/api/maintenance/job/deleteJob.php", params: (t) => ({ maintenanceJobs_id: t.maintenanceJobId }) },
];

// Endpoints that save the submitted form fields as they are: can a user move their own record into another business?
const moveCases: WriteCase[] = (
  [
    ["/api/clients/edit.php", "clients", "clients_id", "clientId"],
    ["/api/locations/edit.php", "locations", "locations_id", "locationId"],
    ["/api/groups/edit.php", "assetGroups", "assetGroups_id", "assetGroupId"],
    ["/api/categories/edit.php", "assetCategories", "assetCategories_id", "categoryId"],
    ["/api/categories/groups/edit.php", "assetCategoriesGroups", "assetCategoriesGroups_id", "categoryGroupId"],
  ] as const
).map(([endpoint, table, field, key]) => ({
  endpoint,
  params: (t: Tenant, self: Tenant) => ({ formData: formData({ [field]: self[key], instances_id: t.instanceId }) }),
  control: false as const,
  restore: (a: Tenant) => [`UPDATE ${table} SET instances_id = ? WHERE ${field} = ?`, [a.instanceId, a[key]]],
}));

const count = ([sql, params]: [string, unknown[]]) => Number(dbQuery<{ n: number }>(sql, params)[0].n);
const linkCases: LinkCase[] = [
  { endpoint: "/api/projects/changeClient.php", params: (t, a) => ({ projects_id: a.projectId, clients_id: t.clientId }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND clients_id = ?", [a.projectId, b.clientId]] },
  { endpoint: "/api/projects/changeVenue.php", params: (t, a) => ({ projects_id: a.projectId, locations_id: t.locationId }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND locations_id = ?", [a.projectId, b.locationId]] },
  { endpoint: "/api/projects/changeProjectType.php", params: (t, a) => ({ projects_id: a.projectId, projectsTypes_id: t.projectTypeId }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND projectsTypes_id = ?", [a.projectId, b.projectTypeId]] },
  { endpoint: "/api/projects/changeStatus.php", params: (t, a) => ({ projects_id: a.projectId, projectsStatuses_id: t.projectStatusIds.second }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND projectsStatuses_id = ?", [a.projectId, b.projectStatusIds.second]] },
  { endpoint: "/api/projects/changeSubProject.php", params: (t, a) => ({ projects_id: a.subProjectId, projects_parent_project_id: t.projectId }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND projects_parent_project_id = ?", [a.subProjectId, b.projectId]] },
  { endpoint: "/api/projects/changeProjectManager.php", params: (t, a) => ({ projects_id: a.projectId, users_userid: t.users.full.id }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE projects_id = ? AND projects_manager = ?", [a.projectId, b.users.full.id]] },
  { endpoint: "/api/projects/new.php", params: (t, a) => ({ projects_name: "New by e2e", projects_manager: a.users.full.id, projectsType_id: t.projectTypeId }), linked: (b, a) => ["SELECT COUNT(*) n FROM projects WHERE instances_id = ? AND projectsTypes_id = ?", [a.instanceId, b.projectTypeId]] },
  { endpoint: "/api/projects/crew/assign.php", params: (t, a) => ({ formData: formData({ projects_id: a.projectId, crewAssignments_role: "e2e role" }), users: [t.users.limited.id] }), linked: (b, a) => ["SELECT COUNT(*) n FROM crewAssignments WHERE projects_id = ? AND users_userid = ? AND crewAssignments_deleted = 0", [a.projectId, b.users.limited.id]] },
  // Assets are shared only between businesses the user belongs to, and A's user isn't in B
  { endpoint: "/api/projects/assets/assign.php", params: (t, a) => ({ projects_id: a.projectId, assets_id: t === a ? a.spareAssetId : t.assetId }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetsAssignments WHERE projects_id = ? AND assets_id = ? AND assetsAssignments_deleted = 0", [a.projectId, b.assetId]] },
  { endpoint: "/api/assets/editAsset.php", params: (t, a) => ({ assets_id: a.assetId, assetTypes_id: t.assetTypeId }), linked: (b, a) => ["SELECT COUNT(*) n FROM assets WHERE assets_id = ? AND assetTypes_id = ?", [a.assetId, b.assetTypeId]] },
  { endpoint: "/api/assets/editAssetType.php", params: (t, a) => ({ formData: formData({ assetTypes_id: a.assetTypeId, assetTypes_name: `${a.marker} asset type`, manufacturers_id: t.manufacturerId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetTypes WHERE assetTypes_id = ? AND manufacturers_id = ?", [a.assetTypeId, b.manufacturerId]] },
  { endpoint: "/api/categories/edit.php", params: (t, a) => ({ formData: formData({ assetCategories_id: a.categoryId, assetCategoriesGroups_id: t.categoryGroupId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetCategories WHERE assetCategories_id = ? AND assetCategoriesGroups_id = ?", [a.categoryId, b.categoryGroupId]] },
  { endpoint: "/api/groups/addAsset.php", params: (t, a) => ({ assets_id: a.assetId, assetGroups_id: t.assetGroupId }), linked: (b, a) => ["SELECT COUNT(*) n FROM assets WHERE assets_id = ? AND FIND_IN_SET(?, assets_assetGroups)", [a.assetId, b.assetGroupId]] },
  { endpoint: "/api/locations/edit.php", params: (t, a) => ({ formData: formData({ locations_id: a.locationId, clients_id: t.clientId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM locations WHERE locations_id = ? AND clients_id = ?", [a.locationId, b.clientId]] },
  { endpoint: "/api/maintenance/newJob.php", params: (t) => ({ formData: formData({ maintenanceJobs_title: "Job by e2e", maintenanceJobs_assets: String(t.assetId) }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM maintenanceJobs WHERE instances_id = ? AND FIND_IN_SET(?, maintenanceJobs_assets)", [a.instanceId, b.assetId]] },
  { endpoint: "/api/maintenance/job/addAsset.php", params: (t, a) => ({ maintenanceJobs_id: a.maintenanceJobId, maintenanceJobs_assets: [t.assetId] }), linked: (b, a) => ["SELECT COUNT(*) n FROM maintenanceJobs WHERE maintenanceJobs_id = ? AND FIND_IN_SET(?, maintenanceJobs_assets)", [a.maintenanceJobId, b.assetId]] },
  { endpoint: "/api/maintenance/job/tagUser.php", params: (t, a) => ({ maintenanceJobs_id: a.maintenanceJobId, users_userid: t.users.full.id }), linked: (b, a) => ["SELECT COUNT(*) n FROM maintenanceJobs WHERE maintenanceJobs_id = ? AND FIND_IN_SET(?, maintenanceJobs_user_tagged)", [a.maintenanceJobId, b.users.full.id]] },
  { endpoint: "/api/maintenance/job/changeJobAssigned.php", params: (t, a) => ({ maintenanceJobs_id: a.maintenanceJobId, users_userid: t.users.full.id }), linked: (b, a) => ["SELECT COUNT(*) n FROM maintenanceJobs WHERE maintenanceJobs_id = ? AND maintenanceJobs_user_assignedTo = ?", [a.maintenanceJobId, b.users.full.id]] },
  { endpoint: "/api/assets/newAssetType.php", label: "manufacturer", fixme: "manufacturers_id isn't checked, so the new type shows B's manufacturer name", params: (t, a) => ({ formData: formData({ assetTypes_name: "Type by e2e", manufacturers_id: t.manufacturerId, assetCategories_id: a.categoryId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetTypes WHERE instances_id = ? AND manufacturers_id = ?", [a.instanceId, b.manufacturerId]] },
  { endpoint: "/api/assets/newAssetType.php", label: "category", fixme: "assetCategories_id isn't checked, so the new type shows B's category name", params: (t, a) => ({ formData: formData({ assetTypes_name: "Type by e2e", manufacturers_id: a.manufacturerId, assetCategories_id: t.categoryId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetTypes WHERE instances_id = ? AND assetCategories_id = ?", [a.instanceId, b.categoryId]] },
  { endpoint: "/api/categories/new.php", fixme: "assetCategoriesGroups_id isn't checked, so the new category sits in B's category group", params: (t) => ({ formData: formData({ assetCategories_name: "Category by e2e", assetCategoriesGroups_id: t.categoryGroupId }) }), linked: (b, a) => ["SELECT COUNT(*) n FROM assetCategories WHERE instances_id = ? AND assetCategoriesGroups_id = ?", [a.instanceId, b.categoryGroupId]] },
  // Watchers are notified when assets are added to or removed from the group
  { endpoint: "/api/groups/watch.php", fixme: "assetGroups_id isn't checked, so A's user is notified of B's asset tags and group name when B changes the group", params: (t) => ({ assetGroups_id: t.assetGroupId }), linked: (b, a) => ["SELECT COUNT(*) n FROM users WHERE users_userid = ? AND FIND_IN_SET(?, users_assetGroupsWatching)", [a.users.full.id, b.assetGroupId]] },
];

const maybeFixme = (fixme: string | undefined) => (fixme ? test.fixme : test);

test.describe("reading another business's records", () => {
  for (const c of readCases) {
    maybeFixme(c.fixme)(`${c.endpoint} returns nothing of B's`, async ({ asA, tenants: { a, b } }) => {
      const control = await asA.api(c.endpoint, c.params(a), c.method);
      expect(mentions(control, (c.shows ?? ((t) => t.marker))(a)), `control: A's own data\n${control.body.slice(0, 500)}`).toBe(true);
      const response = await asA.api(c.endpoint, c.params(b), c.method);
      expect(mentions(response, (c.hides ?? ((t) => t.marker))(b)), response.body.slice(0, 1000)).toBe(false);
    });
  }
  for (const c of pageCases) {
    maybeFixme(c.fixme)(`page ${c.file}${c.label ? ` (${c.label})` : ""} shows nothing of B's`, async ({ asA, tenants: { a, b } }) => {
      const control = await asA.page(c.url(a));
      expect(mentions(control, c.shows(a)), `control: A's own page`).toBe(true);
      const response = await asA.page(c.url(b));
      expect(mentions(response, (c.hides ?? ((t) => t.marker))(b))).toBe(false);
    });
  }
});

test.describe("changing another business's records", () => {
  for (const c of [...writeCases, ...moveCases.map((m) => ({ ...m, move: true }))]) {
    const name = "move" in c ? `${c.endpoint} can't move A's record into B` : `${c.endpoint} leaves B's records alone`;
    maybeFixme(c.fixme)(name, async ({ asA, tenants: { a, b } }) => {
      const before = snapshot(b.instanceId);
      await asA.api(c.endpoint, c.params(b, a));
      const after = snapshot(b.instanceId);
      if (c.restore) dbQuery(...c.restore(a));
      expect(after.data).toEqual(before.data);

      if (c.control !== false) {
        const ownBefore = snapshot(a.instanceId);
        await asA.api(c.endpoint, c.params(a, a));
        expect(snapshot(a.instanceId), "control: the same request changes A's own records").not.toEqual(ownBefore);
      }
      seedTenants();
    });
  }
});

test.describe("pointing A's records at B's", () => {
  for (const c of linkCases) {
    maybeFixme(c.fixme)(`${c.endpoint} won't link A's data to B's${c.label ? ` (${c.label})` : ""}`, async ({ asA, tenants: { a, b } }) => {
      const before = count(c.linked(b, a));
      await asA.api(c.endpoint, c.params(b, a));
      expect(count(c.linked(b, a))).toBe(before);

      const control = await asA.api(c.endpoint, c.params(a, a));
      expect(succeeded(control), `control: A's own records are accepted\n${control.body.slice(0, 500)}`).toBe(true);
      seedTenants();
    });
  }
});

// Not isolation as such, but the same membership check (bCMS::userIsInInstance) guards it
test.describe("a deleted account that is still a member", () => {
  test("can't be made a project manager or assigned a maintenance job", async ({ asA, tenants: { a } }) => {
    const deleted = a.users.deleted.id;
    await asA.api("/api/projects/changeProjectManager.php", { projects_id: a.projectId, users_userid: deleted });
    await asA.api("/api/maintenance/job/changeJobAssigned.php", { maintenanceJobs_id: a.maintenanceJobId, users_userid: deleted });
    expect(dbQuery("SELECT projects_manager FROM projects WHERE projects_id = ?", [a.projectId])[0].projects_manager).not.toBe(deleted);
    expect(dbQuery("SELECT maintenanceJobs_user_assignedTo FROM maintenanceJobs WHERE maintenanceJobs_id = ?", [a.maintenanceJobId])[0].maintenanceJobs_user_assignedTo).not.toBe(deleted);
    seedTenants();
  });
});

test.describe("a sub-project of a project whose manager has since joined another business", () => {
  test("is managed by its creator, not the other business's user", async ({ asA, tenants: { a, b } }) => {
    dbQuery("UPDATE projects SET projects_manager = ? WHERE projects_id = ?", [b.users.full.id, a.projectId]);
    // What the project page's "new sub-project" button sends
    const response = await asA.api("/api/projects/new.php", { projects_name: "Sub-project by e2e", projectsType_id: a.projectTypeId, projects_manager: b.users.full.id, projects_parent_project_id: a.projectId });
    expect(succeeded(response), response.body.slice(0, 500)).toBe(true);
    const [project] = dbQuery<{ projects_manager: number }>("SELECT projects_manager FROM projects WHERE projects_id = ?", [response.json.response.projects_id]);
    expect(project.projects_manager).toBe(a.users.full.id);
    seedTenants();
  });
});

test.describe("importing assets", () => {
  // The columns import.php expects, in order (see $CSVHEADERS there)
  const headers = ["assetTypes_name", "assetTypes_description", "assetTypes_productLink", "assetTypes_mass", "assetTypes_dayRate", "assetTypes_weekRate", "assetTypes_value", "assetCategories_name", "manufacturers_name", "assets_tag", "assets_notes", "assets_storageLocation", "assets_dayRate", "assets_WeekRate", "assets_value", "assets_mass", ...Array.from({ length: 10 }, (_, i) => `assetType_definableFieldsName_${i + 1}`), ...Array.from({ length: 10 }, (_, i) => `asset_definableFields_${i + 1}`)];
  const csv = (row: Record<string, string>) => Buffer.from([headers.join(","), headers.map((h) => row[h] ?? "").join(",")].join("\n") + "\n");
  const upload = (tag: string, typeName: string, manufacturerName: string, categoryName: string) => ({
    csvFile: { name: "assets.csv", mimeType: "text/csv", buffer: csv({ assetTypes_name: typeName, assetCategories_name: categoryName, manufacturers_name: manufacturerName, assets_tag: tag }) },
  });

  test("matches types, manufacturers and categories by name only within the business", async ({ asA, tenants: { a, b } }) => {
    const tag = `E2E-IMPORT-${Date.now()}`;
    // B's names, apart from the category: an unknown category fails the row, and so should B's
    const typeName = `Imported ${Date.now()}`;
    dbQuery("UPDATE assetTypes SET assetTypes_name = ? WHERE assetTypes_id = ?", [typeName, b.assetTypeId]);
    const manufacturerName = `Imported manufacturer ${Date.now()}`;
    dbQuery("UPDATE manufacturers SET manufacturers_name = ? WHERE manufacturers_id = ?", [manufacturerName, b.manufacturerId]);
    const response = await asA.upload("/api/assets/import.php", upload(tag, typeName, manufacturerName, `${a.marker} category`));
    const [asset] = dbQuery<{ instances_id: number; assetTypes_id: number; manufacturers_id: number }>(
      "SELECT assets.instances_id, assets.assetTypes_id, assetTypes.manufacturers_id FROM assets JOIN assetTypes USING (assetTypes_id) WHERE assets_tag = ?", [tag]);
    const withBCategory = await asA.upload("/api/assets/import.php", upload(`${tag}-B`, `${typeName} 2`, manufacturerName, `${b.marker} category`)); // A new type, so the category is looked up
    const withBCategoryImported = dbQuery("SELECT assets_id FROM assets WHERE assets_tag = ?", [`${tag}-B`]).length;
    // Tidy up before asserting: the new type and manufacturer are named like B's
    dbQuery("DELETE FROM assets WHERE assets_tag LIKE ?", [`${tag}%`]);
    dbQuery("DELETE FROM assetTypes WHERE assetTypes_name IN (?, ?) AND instances_id = ?", [typeName, `${typeName} 2`, a.instanceId]);
    dbQuery("DELETE FROM manufacturers WHERE manufacturers_name = ? AND instances_id = ?", [manufacturerName, a.instanceId]);
    seedTenants();

    expect(asset, response.body.slice(0, 500)).toBeDefined();
    expect(asset.instances_id).toBe(a.instanceId);
    expect(asset.assetTypes_id).not.toBe(b.assetTypeId);
    expect(asset.manufacturers_id).not.toBe(b.manufacturerId);
    expect(withBCategoryImported, withBCategory.body.slice(0, 500)).toBe(0);
  });

  test("needs ASSETS:IMPORT", async ({ asLimitedA, tenants: { a } }) => {
    const tag = `E2E-IMPORT-${Date.now()}`;
    const response = await asLimitedA.upload("/api/assets/import.php", upload(tag, `${a.marker} asset type`, `${a.marker} manufacturer`, `${a.marker} category`));
    expect(succeeded(response)).toBe(false);
    expect(dbQuery("SELECT assets_id FROM assets WHERE assets_tag = ?", [tag])).toHaveLength(0);
  });
});

test.describe("a server admin with ASSETS:EDIT:ANY_ASSET_TYPE", () => {
  test("can edit another business's asset type and keep its own manufacturer", async ({ playwright, tenants: { b } }) => {
    const request = await playwright.request.newContext({ baseURL: BASE_URL });
    const admin = await newSession(request, TEST_USER.email, TEST_USER.password);
    await admin.page("/"); // A server admin with no business of their own is put in the first one on the server, which isn't B
    const response = await admin.api("/api/assets/editAssetType.php", {
      formData: formData({ assetTypes_id: b.assetTypeId, assetTypes_name: `${b.marker} asset type`, manufacturers_id: b.manufacturerId, assetCategories_id: b.categoryId }),
    });
    expect(succeeded(response), response.body.slice(0, 500)).toBe(true);
    await request.dispose();
    seedTenants();
  });
});

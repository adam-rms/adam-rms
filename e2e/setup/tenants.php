<?php
// Test-only: creates (or resets) two businesses, A and B, each with its own users and one record of
// every major type, for the tenant isolation and permission tests. Never run this against a real database.
//
// Every run puts the rows back to the values below (un-deleting, un-archiving, renaming...), so it's
// idempotent and also undoes what a test did to them. Rows are found by a natural key (a name, plus the business), or
// by the ID the previous run printed when that's passed in, so their IDs stay the same between runs on the same
// database even after a test renames a record. Rows tests add (notes, payments...) are left alone.
//
// Prints the IDs as JSON for e2e/tenants.ts. Every name in business X contains the marker
// "E2E_TENANT_X_SECRET", so a test can spot X's data in any response by searching for it.
$db = new PDO(
    "mysql:host=" . getenv('DB_HOSTNAME') . ";port=" . (getenv('DB_PORT') ?: 3306) . ";dbname=" . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
require __DIR__ . '/../../src/common/libs/Auth/instanceActions.php';

// The IDs printed by the previous run in this test run (passed back by e2e/tenants.ts), so a row is found
// again even after a test has changed the columns it would otherwise be found by
$previous = isset($argv[1]) ? json_decode($argv[1], true) : null;

/**
 * Finds the row — by the ID it had last time ($rememberedId) if it still exists, else by matching $key —
 * or inserts one, then sets $key and $values on it. Returns its ID.
 */
function upsert(string $table, string $idColumn, array $key, array $values = [], ?int $rememberedId = null): int {
    global $db;
    $id = false;
    if ($rememberedId) {
        $statement = $db->prepare("SELECT `$idColumn` FROM `$table` WHERE `$idColumn` = ?");
        $statement->execute([$rememberedId]);
        $id = $statement->fetchColumn();
        if ($id !== false) $values = $key + $values;
    }
    if ($id === false) {
        $where = implode(" AND ", array_map(fn($column) => "`$column` <=> ?", array_keys($key)));
        $statement = $db->prepare("SELECT `$idColumn` FROM `$table` WHERE $where ORDER BY `$idColumn` LIMIT 1");
        $statement->execute(array_values($key));
        $id = $statement->fetchColumn();
    }
    if ($id === false) {
        $row = $key + $values;
        $columns = implode(",", array_map(fn($column) => "`$column`", array_keys($row)));
        $db->prepare("INSERT INTO `$table` ($columns) VALUES (" . implode(",", array_fill(0, count($row), "?")) . ")")
            ->execute(array_values($row));
        return (int) $db->lastInsertId();
    }
    if ($values) {
        $set = implode(",", array_map(fn($column) => "`$column` = ?", array_keys($values)));
        $db->prepare("UPDATE `$table` SET $set WHERE `$idColumn` = ?")->execute([...array_values($values), $id]);
    }
    return (int) $id;
}

// Same salts and hash as the super admin in seed.php, so every user's password is "password!"
$credentials = [
    "users_salty1" => "8smqAFD9",
    "users_password" => "fa5a51baef12914c7f2e0e1176a030bf086d26edae298c25d5f84c90bc72ecd7",
    "users_salty2" => "uOhfrOCW",
    "users_hash" => "sha256",
    "users_changepass" => 0, "users_suspended" => 0, "users_deleted" => 0, "users_emailVerified" => 1,
];

// With DEV_MODE=true every page and API call needs the USE-DEV server permission, so give the
// tenant users a server position with only that (the super admin's position has everything)
$devGroup = upsert("positionsGroups", "positionsGroups_id", ["positionsGroups_name" => "E2E dev site access"], ["positionsGroups_actions" => "USE-DEV"]);
$devPosition = upsert("positions", "positions_id", ["positions_displayName" => "E2E tenant user"], ["positions_positionsGroups" => (string) $devGroup, "positions_rank" => 99]);

function user(string $email, string $name1, string $name2): int {
    global $db, $credentials, $devPosition;
    $id = upsert("users", "users_userid", ["users_email" => $email], [
        "users_username" => strstr($email, "@", true), "users_name1" => $name1, "users_name2" => $name2,
        "users_created" => "2024-01-01 00:00:00", "users_selectedInstanceIDLast" => null, "users_assetGroupsWatching" => null,
        "users_thumbnail" => null, "users_notificationSettings" => null, "users_oauth_googleid" => null, "users_oauth_microsoftid" => null, "users_dark_mode" => 0, "users_widgets" => null, "users_calendarHash" => null, "users_termsAccepted" => null,
    ] + $credentials);
    // Too many recent failed logins (e.g. from a manual attempt) would block the tests from logging in
    $db->prepare("DELETE FROM loginAttempts WHERE loginAttempts_textEntered = ?")->execute([$email]);
    // Auth adds up every server position a user has (it ignores userPositions_end), so remove any others
    $db->prepare("DELETE FROM userPositions WHERE users_userid = ? AND (positions_id IS NULL OR positions_id != ?)")->execute([$id, $devPosition]);
    upsert("userPositions", "userPositions_id", ["users_userid" => $id, "positions_id" => $devPosition], [
        "userPositions_start" => "2024-01-01 00:00:00", "userPositions_end" => null, "userPositions_show" => 1, "userPositions_extraPermissions" => null,
    ]);
    return $id;
}

// Permissions for the "limited" user in each business: enough to view projects and assets, nothing that changes anything
const LIMITED_PERMISSIONS = ["PROJECTS:VIEW", "ASSETS:ASSET_TYPES:VIEW", "CLIENTS:VIEW", "LOCATIONS:VIEW", "MAINTENANCE_JOBS:VIEW"];

function tenant(string $letter): array {
    global $db, $instanceActions, $previous;
    $marker = "E2E_TENANT_{$letter}_SECRET";
    $lower = strtolower($letter);
    $t = ["marker" => $marker];
    $remembered = $previous[$lower] ?? [];

    $t['instanceId'] = $instance = upsert("instances", "instances_id", ["instances_name" => "$marker Ltd"], [
        "instances_deleted" => 0, "instances_suspended" => 0, "instances_suspendedReason" => null, "instances_suspendedReasonType" => null,
        "instances_address" => "$marker address", "instances_email" => "e2e-tenant-$lower@example.com",
        "instances_config_currency" => "GBP", "instances_storageLimit" => 0, "instances_storageEnabled" => 1,
        "instances_assetLimit" => 0, "instances_userLimit" => 0, "instances_projectLimit" => 0, "instances_planName" => "",
        "instances_calendarConfig" => '{"showProjectStatus":true,"showSubProjects":true,"useCustomWeekNumbers":true,"defaultView":"dayGridMonth"}',
        "instances_calendarHash" => "e2e-calendar-hash-$lower", "instances_publicConfig" => null, "instances_trustedDomains" => null,
    ]);

    $fullPosition = upsert("instancePositions", "instancePositions_id", ["instances_id" => $instance, "instancePositions_displayName" => "$marker full access"], [
        "instancePositions_rank" => 1, "instancePositions_deleted" => 0, "instancePositions_actions" => implode(",", array_keys($instanceActions)), "cmsPages_id" => null,
    ]);
    $limitedPosition = upsert("instancePositions", "instancePositions_id", ["instances_id" => $instance, "instancePositions_displayName" => "$marker limited"], [
        "instancePositions_rank" => 2, "instancePositions_deleted" => 0, "instancePositions_actions" => implode(",", LIMITED_PERMISSIONS), "cmsPages_id" => null,
    ]);
    $t['positions'] = ["full" => $fullPosition, "limited" => $limitedPosition];

    $t['users'] = [];
    // "deleted" is a soft-deleted account, which (like src/api/account/softDelete.php leaves it) still has its membership
    foreach (["full" => $fullPosition, "limited" => $limitedPosition, "deleted" => $limitedPosition] as $kind => $position) {
        $email = "e2e_tenant_{$lower}_{$kind}@example.com";
        $userId = user($email, ucfirst($kind), "$marker user");
        // Remove any other membership (another business, or another position in this one), then add the intended one
        $db->prepare("UPDATE userInstances SET userInstances_deleted = 1 WHERE users_userid = ? AND instancePositions_id != ?")
            ->execute([$userId, $position]);
        $membershipId = upsert("userInstances", "userInstances_id", ["users_userid" => $userId, "instancePositions_id" => $position], [
            "userInstances_deleted" => 0, "userInstances_archived" => null, "userInstances_extraPermissions" => null, "userInstances_label" => "$marker $kind",
        ], $kind === "deleted" ? ($remembered['deletedMembershipId'] ?? null) : null);
        $t['users'][$kind] = ["id" => $userId, "email" => $email];
        if ($kind === "deleted") $t['deletedMembershipId'] = $membershipId;
    }
    $db->prepare("UPDATE users SET users_deleted = 1 WHERE users_userid = ?")->execute([$t['users']['deleted']['id']]);
    $manager = $t['users']['full']['id'];

    $t['clientId'] = upsert("clients", "clients_id", ["instances_id" => $instance, "clients_email" => "client-$lower@example.com"], [
        "clients_name" => "$marker client", "clients_deleted" => 0, "clients_archived" => 0, "clients_notes" => "$marker client notes",
        "clients_website" => null, "clients_address" => null, "clients_phone" => null,
    ], $remembered['clientId'] ?? null);
    $t['locationId'] = upsert("locations", "locations_id", ["instances_id" => $instance, "locations_notes" => "$marker location notes"], [
        "locations_name" => "$marker location", "clients_id" => $t['clientId'], "locations_deleted" => 0, "locations_archived" => 0,
        "locations_subOf" => null, "locations_address" => "$marker location address",
    ], $remembered['locationId'] ?? null);
    $t['locationBarcodeValue'] = "E2E{$letter}LOCATION0001";
    $t['locationBarcodeId'] = upsert("locationsBarcodes", "locationsBarcodes_id", ["locationsBarcodes_value" => $t['locationBarcodeValue']], [
        "locations_id" => $t['locationId'], "locationsBarcodes_type" => "CODE_128", "locationsBarcodes_notes" => "$marker location barcode",
        "locationsBarcodes_added" => "2024-01-01 00:00:00", "locationsBarcodes_deleted" => 0,
    ], $remembered['locationBarcodeId'] ?? null);
    $t['manufacturerId'] = upsert("manufacturers", "manufacturers_id", ["instances_id" => $instance, "manufacturers_notes" => "e2e-$lower"], [
        "manufacturers_name" => "$marker manufacturer", "manufacturers_website" => null,
    ], $remembered['manufacturerId'] ?? null);
    $t['categoryGroupId'] = upsert("assetCategoriesGroups", "assetCategoriesGroups_id", ["instances_id" => $instance, "assetCategoriesGroups_fontAwesome" => "e2e-$lower"], [
        "assetCategoriesGroups_name" => "$marker category group", "assetCategoriesGroups_order" => 99, "assetCategoriesGroups_deleted" => 0,
    ], $remembered['categoryGroupId'] ?? null);
    $t['categoryId'] = upsert("assetCategories", "assetCategories_id", ["instances_id" => $instance, "assetCategories_fontAwesome" => "e2e-$lower"], [
        "assetCategories_name" => "$marker category", "assetCategoriesGroups_id" => $t['categoryGroupId'], "assetCategories_rank" => 99, "assetCategories_deleted" => 0,
    ], $remembered['categoryId'] ?? null);
    $t['assetTypeId'] = upsert("assetTypes", "assetTypes_id", ["instances_id" => $instance, "assetTypes_productLink" => "https://example.com/e2e-$lower"], [
        "assetTypes_name" => "$marker asset type", "assetTypes_description" => "$marker asset type description",
        "assetCategories_id" => $t['categoryId'], "manufacturers_id" => $t['manufacturerId'], "assetTypes_definableFields" => ",,,,,,,,,",
        "assetTypes_mass" => 1, "assetTypes_value" => 10000, "assetTypes_dayRate" => 1000, "assetTypes_weekRate" => 3000, "assetTypes_inserted" => "2024-01-01 00:00:00",
    ], $remembered['assetTypeId'] ?? null);
    $t['assetGroupId'] = upsert("assetGroups", "assetGroups_id", ["instances_id" => $instance, "assetGroups_description" => "e2e-$lower"], [
        "assetGroups_name" => "$marker asset group", "assetGroups_deleted" => 0, "users_userid" => null,
    ], $remembered['assetGroupId'] ?? null);
    $t['assetTag'] = "E2E-$letter-0001";
    $t['assetId'] = upsert("assets", "assets_id", ["instances_id" => $instance, "assets_tag" => $t['assetTag']], [
        "assetTypes_id" => $t['assetTypeId'], "assets_notes" => "$marker asset notes", "assets_deleted" => 0, "assets_archived" => null,
        "assets_endDate" => null, "assets_linkedTo" => null, "assets_assetGroups" => (string) $t['assetGroupId'], "assets_storageLocation" => null,
        "assets_value" => null, "assets_dayRate" => null, "assets_weekRate" => null, "assets_mass" => null, "assets_showPublic" => 0,
        "asset_definableFields_1" => null, "assets_inserted" => "2024-01-01 00:00:00",
    ], $remembered['assetId'] ?? null);
    // Same type, not on any project: what swap.php and substitutions.php offer in place of the asset above
    $t['spareAssetTag'] = "E2E-$letter-0002";
    $t['spareAssetId'] = upsert("assets", "assets_id", ["instances_id" => $instance, "assets_tag" => $t['spareAssetTag']], [
        "assetTypes_id" => $t['assetTypeId'], "assets_notes" => "$marker spare asset notes", "assets_deleted" => 0, "assets_archived" => null,
        "assets_endDate" => null, "assets_linkedTo" => null, "assets_assetGroups" => null, "assets_storageLocation" => null,
        "assets_value" => null, "assets_dayRate" => null, "assets_weekRate" => null, "assets_mass" => null, "assets_showPublic" => 0,
        "asset_definableFields_1" => null, "assets_inserted" => "2024-01-01 00:00:00",
    ], $remembered['spareAssetId'] ?? null);
    $t['barcodeValue'] = "E2E{$letter}BARCODE0001";
    $t['barcodeId'] = upsert("assetsBarcodes", "assetsBarcodes_id", ["assetsBarcodes_value" => $t['barcodeValue']], [
        "assets_id" => $t['assetId'], "assetsBarcodes_type" => "CODE_128", "assetsBarcodes_notes" => "$marker barcode",
        "assetsBarcodes_added" => "2024-01-01 00:00:00", "assetsBarcodes_deleted" => 0,
    ], $remembered['barcodeId'] ?? null);

    // A file attached to the asset type (s3files type 3). Nothing is uploaded: the tests only read and change the row.
    $t['fileId'] = upsert("s3files", "s3files_id", ["instances_id" => $instance, "s3files_filename" => "e2e-tenant-$lower-file"], [
        "s3files_path" => "e2e", "s3files_name" => "$marker file", "s3files_extension" => "pdf", "s3files_original_name" => "e2e.pdf",
        "s3files_meta_size" => 1, "s3files_meta_public" => 0, "s3files_shareKey" => null, "s3files_meta_type" => 3,
        "s3files_meta_subType" => $t['assetTypeId'], "users_userid" => $manager, "s3files_meta_deleteOn" => null, "s3files_meta_physicallyStored" => 1,
    ], $remembered['fileId'] ?? null);
    $t['projectTypeId'] = upsert("projectsTypes", "projectsTypes_id", ["instances_id" => $instance, "projectsTypes_name" => "$marker project type"], ["projectsTypes_deleted" => 0], $remembered['projectTypeId'] ?? null);
    foreach (["first" => 0, "second" => 1] as $name => $rank) {
        $t['projectStatusIds'][$name] = upsert("projectsStatuses", "projectsStatuses_id", ["instances_id" => $instance, "projectsStatuses_name" => "$marker status $name"], [
            "projectsStatuses_description" => "$marker", "projectsStatuses_foregroundColour" => "#000000", "projectsStatuses_backgroundColour" => "#ffffff",
            "projectsStatuses_rank" => $rank, "projectsStatuses_assetsReleased" => 0, "projectsStatuses_deleted" => 0,
        ], $remembered['projectStatusIds'][$name] ?? null);
    }
    $t['assignmentStatusId'] = upsert("assetsAssignmentsStatus", "assetsAssignmentsStatus_id", ["instances_id" => $instance, "assetsAssignmentsStatus_name" => "$marker picked"], [
        "assetsAssignmentsStatus_order" => 0, "assetsAssignmentsStatus_deleted" => 0,
    ], $remembered['assignmentStatusId'] ?? null);
    $project = [
        "projects_manager" => $manager, "projects_deleted" => 0, "projects_archived" => 0, "clients_id" => $t['clientId'], "locations_id" => $t['locationId'],
        "projectsStatuses_id" => $t['projectStatusIds']['first'], "projectsTypes_id" => $t['projectTypeId'], "projects_status_follow_parent" => 0,
        "projects_invoiceNotes" => "$marker invoice notes", "projects_deliveryNotes" => "$marker delivery notes",
        "projects_dates_use_start" => "2030-01-02 09:00:00", "projects_dates_use_end" => "2030-01-04 18:00:00",
        "projects_dates_deliver_start" => "2030-01-01 09:00:00", "projects_dates_deliver_end" => "2030-01-05 18:00:00",
        "projects_dates_finances_days" => null, "projects_dates_finances_weeks" => null, "projects_defaultDiscount" => 0,
    ];
    $t['projectId'] = upsert("projects", "projects_id", ["instances_id" => $instance, "projects_description" => "$marker project description"],
        ["projects_name" => "$marker project", "projects_created" => "2024-01-01 00:00:00", "projects_parent_project_id" => null] + $project, $remembered['projectId'] ?? null);
    $t['subProjectId'] = upsert("projects", "projects_id", ["instances_id" => $instance, "projects_description" => "$marker sub-project description"],
        ["projects_name" => "$marker sub-project", "projects_created" => "2024-01-01 00:00:00", "projects_parent_project_id" => $t['projectId']] + $project, $remembered['subProjectId'] ?? null);

    // The finance cache the project page and the payment/asset endpoints read and adjust
    foreach (["projectId", "subProjectId"] as $project) {
        upsert("projectsFinanceCache", "projectsFinanceCache_id", ["projects_id" => $t[$project]], [
            "projectsFinanceCache_timestamp" => "2024-01-01 00:00:00", "projectsFinanceCache_timestampUpdated" => null,
            "projectsFinanceCache_equipmentSubTotal" => 0, "projectsFinanceCache_equiptmentDiscounts" => 0, "projectsFinanceCache_equiptmentTotal" => 0,
            "projectsFinanceCache_salesTotal" => 0, "projectsFinanceCache_staffTotal" => 0, "projectsFinanceCache_externalHiresTotal" => 0,
            "projectsFinanceCache_paymentsReceived" => 0, "projectsFinanceCache_grandTotal" => 0, "projectsFinanceCache_value" => 0, "projectsFinanceCache_mass" => 0,
        ]);
    }
    $t['assignmentId'] = upsert("assetsAssignments", "assetsAssignments_id", ["assets_id" => $t['assetId'], "projects_id" => $t['projectId']], [
        "assetsAssignments_comment" => "$marker assignment comment", "assetsAssignments_customPrice" => 0, "assetsAssignments_discount" => 0,
        "assetsAssignments_deleted" => 0, "assetsAssignmentsStatus_id" => null, "assetsAssignments_linkedTo" => null,
    ], $remembered['assignmentId'] ?? null);
    // Tests assign the spare asset (assign.php); it starts off free
    $db->prepare("UPDATE assetsAssignments SET assetsAssignments_deleted = 1 WHERE assets_id = ? AND assetsAssignments_id != ?")->execute([$t['spareAssetId'], $t['assignmentId']]);
    // Quick comments are stored in the audit log
    upsert("auditLog", "auditLog_id", ["projects_id" => $t['projectId'], "auditLog_actionType" => "QUICKCOMMENT", "auditLog_actionData" => "$marker quick comment"], [
        "auditLog_actionTable" => "projects", "users_userid" => $manager, "auditLog_deleted" => 0, "auditLog_timestamp" => "2024-01-01 00:00:00",
    ]);
    $t['noteId'] = upsert("projectsNotes", "projectsNotes_id", ["projects_id" => $t['projectId'], "projectsNotes_title" => "$marker note"], [
        "projectsNotes_text" => "$marker note text", "projectsNotes_userid" => $manager, "projectsNotes_deleted" => 0,
    ], $remembered['noteId'] ?? null);
    $t['paymentId'] = upsert("payments", "payments_id", ["projects_id" => $t['projectId'], "payments_reference" => "$marker payment"], [
        "payments_amount" => 1234, "payments_quantity" => 1, "payments_type" => 1, "payments_date" => "2024-01-01 00:00:00",
        "payments_supplier" => null, "payments_method" => null, "payments_comment" => "$marker payment comment", "payments_deleted" => 0,
    ], $remembered['paymentId'] ?? null);
    $t['crewAssignmentId'] = upsert("crewAssignments", "crewAssignments_id", ["projects_id" => $t['projectId'], "users_userid" => $manager], [
        "crewAssignments_role" => "$marker crew role", "crewAssignments_comment" => "$marker crew comment", "crewAssignments_deleted" => 0,
        "crewAssignments_personName" => null, "crewAssignments_rank" => 1,
    ], $remembered['crewAssignmentId'] ?? null);
    $t['vacantRoleId'] = upsert("projectsVacantRoles", "projectsVacantRoles_id", ["projects_id" => $t['projectId'], "projectsVacantRoles_description" => "$marker vacancy description"], [
        "projectsVacantRoles_name" => "$marker vacancy", "projectsVacantRoles_deleted" => 0, "projectsVacantRoles_open" => 1,
        "projectsVacantRoles_showPublic" => 0, "projectsVacantRoles_added" => "2024-01-01 00:00:00", "projectsVacantRoles_deadline" => null,
        "projectsVacantRoles_slots" => 5, "projectsVacantRoles_slotsFilled" => 0, "projectsVacantRoles_privateToPM" => 0,
        "projectsVacantRoles_visibleToGroups" => null, "projectsVacantRoles_applicationVisibleToUsers" => null, "projectsVacantRoles_questions" => null,
        "projectsVacantRoles_firstComeFirstServed" => 0, "projectsVacantRoles_fileUploads" => 0, "projectsVacantRoles_collectPhone" => 0,
    ], $remembered['vacantRoleId'] ?? null);
    $t['vacancyApplicationId'] = upsert("projectsVacantRolesApplications", "projectsVacantRolesApplications_id", ["projectsVacantRoles_id" => $t['vacantRoleId'], "users_userid" => $t['users']['limited']['id']], [
        "projectsVacantRolesApplications_applicantComment" => "$marker application", "projectsVacantRolesApplications_deleted" => 0,
        "projectsVacantRolesApplications_withdrawn" => 0, "projectsVacantRolesApplications_status" => 0, "projectsVacantRolesApplications_submitted" => "2024-01-01 00:00:00",
    ], $remembered['vacancyApplicationId'] ?? null);
    // Applications made by tests (apply.php refuses a second one from the same user)
    $db->prepare("UPDATE projectsVacantRolesApplications SET projectsVacantRolesApplications_deleted = 1 WHERE projectsVacantRoles_id = ? AND projectsVacantRolesApplications_id != ?")
        ->execute([$t['vacantRoleId'], $t['vacancyApplicationId']]);

    $t['maintenanceJobId'] = upsert("maintenanceJobs", "maintenanceJobs_id", ["instances_id" => $instance, "maintenanceJobs_faultDescription" => "$marker fault"], [
        "maintenanceJobs_title" => "$marker maintenance job", "maintenanceJobs_assets" => (string) $t['assetId'], "maintenanceJobs_user_creator" => $manager,
        "maintenanceJobs_user_assignedTo" => null, "maintenanceJobs_user_tagged" => null, "maintenanceJobs_timestamp_added" => "2024-01-01 00:00:00",
        "maintenanceJobs_timestamp_due" => null, "maintenanceJobs_priority" => 5, "maintenanceJobs_deleted" => 0, "maintenanceJobsStatuses_id" => 1,
        "maintenanceJobs_flagAssets" => 0, "maintenanceJobs_blockAssets" => 0,
    ], $remembered['maintenanceJobId'] ?? null);
    $t['maintenanceMessageId'] = upsert("maintenanceJobsMessages", "maintenanceJobsMessages_id", ["maintenanceJobs_id" => $t['maintenanceJobId'], "maintenanceJobsMessages_text" => "$marker message"], [
        "users_userid" => $manager, "maintenanceJobsMessages_deleted" => 0, "maintenanceJobsMessages_timestamp" => "2024-01-01 00:00:00",
    ], $remembered['maintenanceMessageId'] ?? null);

    $t['cmsPageId'] = upsert("cmsPages", "cmsPages_id", ["instances_id" => $instance, "cmsPages_description" => "$marker page description"], [
        "cmsPages_name" => "$marker page", "cmsPages_showNav" => 1, "cmsPages_visibleToGroups" => null, "cmsPages_navOrder" => 99,
        "cmsPages_archived" => 0, "cmsPages_deleted" => 0, "cmsPages_subOf" => null, "cmsPages_added" => "2024-01-01 00:00:00",
    ], $remembered['cmsPageId'] ?? null);
    $t['cmsPageDraftId'] = upsert("cmsPagesDrafts", "cmsPagesDrafts_id", ["cmsPages_id" => $t['cmsPageId'], "cmsPagesDrafts_revisionID" => 1], [
        "users_userid" => $manager, "cmsPagesDrafts_timestamp" => "2024-01-01 00:00:00", "cmsPagesDrafts_changelog" => "$marker changelog",
        "cmsPagesDrafts_data" => json_encode([["type" => "html", "content" => "<p>$marker page content</p>"]]),
    ]);
    // Drafts tests add would be shown instead of the seeded one
    $db->prepare("DELETE FROM cmsPagesDrafts WHERE cmsPages_id = ? AND cmsPagesDrafts_id != ?")->execute([$t['cmsPageId'], $t['cmsPageDraftId']]);
    upsert("cmsPagesViews", "cmsPagesViews_id", ["cmsPages_id" => $t['cmsPageId'], "users_userid" => $manager, "cmsPages_type" => 1], ["cmsPagesViews_timestamp" => "2024-01-01 00:00:00"]);
    $t['moduleId'] = upsert("modules", "modules_id", ["instances_id" => $instance, "modules_learningObjectives" => "$marker objectives"], [
        "users_userid" => $manager, "modules_name" => "$marker training module", "modules_description" => "$marker module description",
        "modules_visibleToGroups" => null, "modules_deleted" => 0, "modules_show" => 1, "modules_thumbnail" => null, "modules_type" => 1,
    ], $remembered['moduleId'] ?? null);
    $t['moduleStepId'] = upsert("modulesSteps", "modulesSteps_id", ["modules_id" => $t['moduleId'], "modulesSteps_name" => "$marker step"], [
        "modulesSteps_deleted" => 0, "modulesSteps_show" => 1, "modulesSteps_type" => 1, "modulesSteps_content" => "$marker step content",
        "modulesSteps_completionTime" => 0, "modulesSteps_internalNotes" => null, "modulesSteps_order" => 1, "modulesSteps_locked" => 0,
    ], $remembered['moduleStepId'] ?? null);
    $t['certificationId'] = upsert("userModulesCertifications", "userModulesCertifications_id", ["modules_id" => $t['moduleId'], "users_userid" => $t['users']['limited']['id']], [
        "userModulesCertifications_revoked" => 0, "userModulesCertifications_approvedBy" => $manager,
        "userModulesCertifications_approvedComment" => "$marker certification", "userModulesCertifications_timestamp" => "2024-01-01 00:00:00",
    ], $remembered['certificationId'] ?? null);
    $t['signupCodeId'] = upsert("signupCodes", "signupCodes_id", ["signupCodes_name" => "e2e-tenant-$lower-signup"], [
        "instances_id" => $instance, "signupCodes_deleted" => 0, "signupCodes_valid" => 1, "signupCodes_notes" => "$marker signup code",
        "signupCodes_role" => "$marker role", "instancePositions_id" => $limitedPosition,
    ], $remembered['signupCodeId'] ?? null);
    return $t;
}

$tenants = ["password" => "password!", "a" => tenant("A"), "b" => tenant("B")];

// A user with full access to both businesses, for what happens between them, e.g. that an asset can't be
// booked in A and B at once. Its name and labels have no marker, so it doesn't look like a leak when A sees it.
$sharedEmail = "e2e_tenants_shared@example.com";
$sharedId = user($sharedEmail, "Shared", "E2E user");
$sharedPositions = [$tenants['a']['positions']['full'], $tenants['b']['positions']['full']];
$db->prepare("UPDATE userInstances SET userInstances_deleted = 1 WHERE users_userid = ? AND instancePositions_id NOT IN (?, ?)")
    ->execute([$sharedId, ...$sharedPositions]);
foreach ($sharedPositions as $position) {
    upsert("userInstances", "userInstances_id", ["users_userid" => $sharedId, "instancePositions_id" => $position], [
        "userInstances_deleted" => 0, "userInstances_archived" => null, "userInstances_extraPermissions" => null, "userInstances_label" => "E2E shared user",
    ]);
}
$tenants['sharedUser'] = ["id" => $sharedId, "email" => $sharedEmail];
echo json_encode($tenants, JSON_PRETTY_PRINT) . "\n";

<?php
require_once __DIR__ . '/common/headSecure.php';

// Assets can be from another instance
if (isset($_GET['instance']) and in_array($_GET['instance'], $AUTH->data['instance_ids']) and $_GET['instance'] != $AUTH->data['instance']['instances_id']) {
    $DBLIB->where("instances_id", $_GET['instance']);
    $DBLIB->where("instances_deleted", 0);
    $PAGEDATA['ASSET_INSTANCE'] = $DBLIB->getone("instances", ["instances_id", "instances_name"]);
} else $PAGEDATA['ASSET_INSTANCE'] = $AUTH->data['instance'];

$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("assetTypes.assetTypes_id", $_GET['id']);
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['asset'] = $DBLIB->getone('assetTypes', ["*", "assetTypes.instances_id as assetInstances_id"]); //have to double download it as otherwise manufacturer instance id is returned instead
if (!$PAGEDATA['asset']) die($TWIG->render('404.twig', $PAGEDATA));
$PAGEDATA['asset']['thumbnail'] = $bCMS->s3List(2, $PAGEDATA['asset']['assetTypes_id']);
$PAGEDATA['asset']['files'] = $bCMS->s3List(3, $PAGEDATA['asset']['assetTypes_id']);
$PAGEDATA['asset']['fields'] = explode(",", $PAGEDATA['asset']['assetTypes_definableFields']);

$DBLIB->where("assets.instances_id", $PAGEDATA['ASSET_INSTANCE']['instances_id']);
$DBLIB->where("assets.assetTypes_id", $PAGEDATA['asset']['assetTypes_id']);
if (isset($_GET['asset'])) {
    $PAGEDATA['asset']['oneasset'] = true;
    $DBLIB->where("assets.assets_id", $_GET['asset']);
} else {
    $PAGEDATA['asset']['oneasset'] = false;
    if (!isset($_GET['showArchived'])) $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");  //show only active assets by default
}
$DBLIB->orderBy("assets.asset_definableFields_1", "ASC");
$DBLIB->orderby("assets.asset_definableFields_2", "ASC");
$DBLIB->orderby("assets.asset_definableFields_3", "ASC");
$DBLIB->orderby("assets.asset_definableFields_4", "ASC");
$DBLIB->orderby("assets.asset_definableFields_5", "ASC");
$DBLIB->orderby("assets.asset_definableFields_6", "ASC");
$DBLIB->orderby("assets.asset_definableFields_7", "ASC");
$DBLIB->orderby("assets.asset_definableFields_8", "ASC");
$DBLIB->orderby("assets.asset_definableFields_9", "ASC");
$DBLIB->orderby("assets.asset_definableFields_10", "ASC");
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->orderBy("assets.assets_tag", "ASC");
$assets = $DBLIB->get("assets");
if (!$assets) die($TWIG->render('404.twig', $PAGEDATA));
$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {
    //Flags&Blocks
    $asset['flagsblocks'] = assetFlagsAndBlocks($asset['assets_id']);

    //Calendar
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
    $DBLIB->where("projects.projects_deleted", 0);
    $asset['assignments'] = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id", "projects.projects_name", "projects_dates_deliver_start", "projects_dates_deliver_end", "assetsAssignments.assetsAssignmentsStatus_id", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour", "projectsStatuses.projectsStatuses_backgroundColour"]);

    $asset['files'] = $bCMS->s3List(4, $asset['assets_id']);

    $PAGEDATA['assets'][] = $asset;
}
$PAGEDATA['pageConfig'] = ["TITLE" => $PAGEDATA['asset']['assetTypes_name'], "BREADCRUMB" => false];

// For asset type editing
$DBLIB->where("(manufacturers.instances_id IS NULL OR manufacturers.instances_id = '" . $PAGEDATA['ASSET_INSTANCE']['instances_id'] . "')");
$DBLIB->orderBy("manufacturers_name", "ASC");
$PAGEDATA['manufacturers'] = $DBLIB->get('manufacturers', null, ["manufacturers.manufacturers_id", "manufacturers.manufacturers_name"]);

$DBLIB->orderBy("assetCategories_rank", "ASC");
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $PAGEDATA['ASSET_INSTANCE']["instances_id"] . "')");
$DBLIB->where("assetCategories_deleted", 0);
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$PAGEDATA['categories'] = $DBLIB->get('assetCategories');


if (count($PAGEDATA['assets']) == 1) {
    //Jobs
    $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
    $DBLIB->where("(FIND_IN_SET(" . $PAGEDATA['assets'][0]['assets_id'] . ", maintenanceJobs.maintenanceJobs_assets) > 0)");
    $DBLIB->join("maintenanceJobsStatuses", "maintenanceJobs.maintenanceJobsStatuses_id=maintenanceJobsStatuses.maintenanceJobsStatuses_id", "LEFT");
    $DBLIB->join("users AS userCreator", "userCreator.users_userid=maintenanceJobs.maintenanceJobs_user_creator", "LEFT");
    $DBLIB->join("users AS userAssigned", "userAssigned.users_userid=maintenanceJobs.maintenanceJobs_user_assignedTo", "LEFT");
    $DBLIB->orderBy("maintenanceJobsStatuses.maintenanceJobsStatuses_order", "ASC");
    $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_due", "ASC");
    $DBLIB->orderBy("maintenanceJobs.maintenanceJobs_timestamp_added", "ASC");
    $PAGEDATA['assets'][0]['jobs'] = $DBLIB->get('maintenanceJobs', null, ["maintenanceJobs.*", "maintenanceJobsStatuses.maintenanceJobsStatuses_name", "userCreator.users_userid AS userCreatorUserID", "userCreator.users_name1 AS userCreatorUserName1", "userCreator.users_name2 AS userCreatorUserName2", "userCreator.users_email AS userCreatorUserEMail", "userCreator.users_thumbnail AS userCreatorUserThumb", "userAssigned.users_name1 AS userAssignedUserName1", "userAssigned.users_userid AS userAssignedUserID", "userAssigned.users_name2 AS userAssignedUserName2", "userAssigned.users_email AS userAssignedUserEMail", "userAssigned.users_thumbnail AS userAssignedUserThumb"]);

    //Links
    if ($PAGEDATA['assets'][0]['assets_linkedTo']) {
        $DBLIB->where("assets_id", $PAGEDATA['assets'][0]['assets_linkedTo']);
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $PAGEDATA['assets'][0]['link'] = $DBLIB->getOne("assets", ["assets_tag", "assets_id", "assetTypes_name", "assets.assetTypes_id"]);
    } else $PAGEDATA['assets'][0]['link'] = false;

    $PAGEDATA['assets'][0]['linkedToThis'] = [];
    $assetsLinked = [];
    function linkedAssets($assetId, $tier)
    {
        global $DBLIB, $PAGEDATA, $assetsLinked;
        array_push($assetsLinked, $assetId);
        $DBLIB->where("assets.assets_linkedTo", $assetId);
        $DBLIB->where("assets.assets_id", $assetsLinked, "NOT IN"); // Make sure an asset is not double counted causing an infinite loop
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $assets = $DBLIB->get("assets", null, ["assets_tag", "assets_id", "assetTypes_name", "assets.assetTypes_id"]);
        $tier += 1;
        foreach ($assets as $asset) {
            $asset['tier'] = $tier;
            $PAGEDATA['assets'][0]['linkedToThis'][] = $asset;
            linkedAssets($asset['assets_id'], $tier);
        }
    }
    linkedAssets($PAGEDATA['assets'][0]['assets_id'], 0);
    unset($assetsLinked);

    //Groups
    if ($PAGEDATA['assets'][0]['assets_assetGroups']) {
        $DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
        $DBLIB->where("instances_id", $PAGEDATA['ASSET_INSTANCE']["instances_id"]);
        $DBLIB->where("assetGroups_deleted", 0);
        $DBLIB->where("assetGroups_id IN (" . $PAGEDATA['assets'][0]['assets_assetGroups'] . ")");
        $PAGEDATA['assets'][0]['groups'] = $DBLIB->get("assetGroups", null, ["assetGroups_id", "assetGroups_name"]);
    } else $PAGEDATA['assets'][0]['groups'] = [];

    // Barcodes & locations
    $DBLIB->where("assets_id", $PAGEDATA['assets'][0]['assets_id']);
    $DBLIB->where("assetsBarcodes_deleted", 0);
    $DBLIB->orderBy("assetsBarcodes_added", "ASC");
    $DBLIB->join("users", "assetsBarcodes.users_userid=users.users_userid", "LEFT");
    $barcodes = $DBLIB->get("assetsBarcodes", null, ["assetsBarcodes.*", "users.users_name1", "users.users_name2"]);
    $barcodeIDsForLocation = [];
    $PAGEDATA['assets'][0]['barcodes'] = [];
    foreach ($barcodes as $barcode) {
        $DBLIB->orderBy("assetsBarcodesScans.assetsBarcodesScans_timestamp", "DESC");
        $DBLIB->where("assetsBarcodesScans.assetsBarcodes_id", $barcode["assetsBarcodes_id"]);
        $DBLIB->where("assetsBarcodesScans.assetsBarcodesScans_barcodeWasScanned", 1);
        $DBLIB->join("locationsBarcodes", "locationsBarcodes.locationsBarcodes_id=assetsBarcodesScans.locationsBarcodes_id", "LEFT");
        $DBLIB->join("assets", "assets.assets_id=assetsBarcodesScans.location_assets_id", "LEFT");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("locations", "locations.locations_id=locationsBarcodes.locations_id", "LEFT");
        $DBLIB->join("users", "users.users_userid=assetsBarcodesScans.users_userid");
        $barcode['latestScan'] = $DBLIB->getone("assetsBarcodesScans", ["assetsBarcodesScans.*", "users.users_name1", "users.users_name2", "locations.locations_name", "locations.locations_id", "assets.assetTypes_id", "assetTypes.assetTypes_name", "assets.assets_tag"]);
        $PAGEDATA['assets'][0]['barcodes'][] = $barcode;
        $barcodeIDsForLocation[] = $barcode["assetsBarcodes_id"];
    }
    // 50 latest scans 
    if (count($barcodeIDsForLocation) > 0) {
        $DBLIB->orderBy("assetsBarcodesScans.assetsBarcodesScans_timestamp", "DESC");
        $DBLIB->where("assetsBarcodesScans.assetsBarcodes_id", $barcodeIDsForLocation, "IN");
        $DBLIB->join("locationsBarcodes", "locationsBarcodes.locationsBarcodes_id=assetsBarcodesScans.locationsBarcodes_id", "LEFT");
        $DBLIB->join("assets", "assets.assets_id=assetsBarcodesScans.location_assets_id", "LEFT");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->join("locations", "locations.locations_id=locationsBarcodes.locations_id", "LEFT");
        $DBLIB->join("users", "users.users_userid=assetsBarcodesScans.users_userid");
        $PAGEDATA['assets'][0]['locationScans'] = $DBLIB->get("assetsBarcodesScans", 50, ["assetsBarcodesScans.*", "users.users_name1", "users.users_name2", "locations.locations_name", "locations.locations_id", "assets.assetTypes_id", "assetTypes.assetTypes_name", "assets.assets_tag"]);
    } else $PAGEDATA['assets'][0]['locationScans'] = [];
    //Asset Location
    //Current asset location
    $latestScan = assetLatestScan($asset['assets_id']);

    if (isset($latestScan['locations_id'])) {
        $PAGEDATA['assets'][0]['assets_latestScanLocationName'] = $latestScan['locations_name'];
    } elseif (isset($latestScan['location_assets_id'])) {
        $PAGEDATA['assets'][0]['assets_latestScanLocationName'] = 'Inside asset ' . $latestScan['assetTypes_name'] . ' (' . $latestScan['assets_tag'] . ')';
    } elseif (isset($latestScan['assetsBarcodes_customLocation'])) {
        $PAGEDATA['assets'][0]['assets_latestScanLocationName'] = $latestScan['assetsBarcodes_customLocation'];
    }

    //All Locations - to be used for setting an asset location manually
    $DBLIB->where("locations_deleted", 0);
    $DBLIB->where("locations_archived", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations_subOf", NULL, 'IS');
    $DBLIB->orderBy("locations_subOf", "ASC");
    $DBLIB->orderBy("locations.locations_name", "ASC");
    $DBLIB->join('locationsBarcodes', 'locationsBarcodes.locations_id=locations.locations_id');
    $locations = $DBLIB->get("locations", null, ['locationsBarcodes.locationsBarcodes_id', 'locations.locations_name']);

    function linkedLocations($locationId, $tier, $locationKey)
    {
        global $DBLIB, $PAGEDATA, $AUTH;
        $DBLIB->where("locations_subOf", $locationId);
        $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->orderBy("locations.locations_name", "ASC");
        $DBLIB->where("locations.locations_deleted", 0);
        $DBLIB->where("locations.locations_archived", 0);
        $DBLIB->join('locationsBarcodes', 'locationsBarcodes.locations_id=locations.locations_id');
        $locations = $DBLIB->get("locations", null, ['locationsBarcodes.locationsBarcodes_id', 'locations.locations_name']);
        $tier += 1;
        foreach ($locations as $location) {
            $location['tier'] = $tier;
            $PAGEDATA['locations'][$locationKey]['linkedToThis'][] = $location;
            linkedLocations($location['locations_id'], $tier, $locationKey);
        }
    }
    foreach ($locations as $index => $location) {
        $PAGEDATA['locations'][] = $location;
        $PAGEDATA['locations'][$index]['linkedToThis'] = [];
        linkedLocations($location['locations_id'], 0, $index);
    }
}

$DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("assetsAssignmentsStatus.instances_id", $PAGEDATA['ASSET_INSTANCE']['instances_id']);
$PAGEDATA['assetsAssignmentsStatus'] = $DBLIB->get("assetsAssignmentsStatus");

echo $TWIG->render('asset.twig', $PAGEDATA);

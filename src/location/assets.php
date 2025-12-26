<?php
require_once __DIR__ . '/../common/headSecure.php';

if (!$AUTH->instancePermissionCheck("LOCATIONS:VIEW"))
    die($TWIG->render('404.twig', $PAGEDATA));

// Validate location access
$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations.locations_id", $_GET['location']);
$DBLIB->join("clients", "locations.clients_id=clients.clients_id", "LEFT");
$PAGEDATA['location'] = $DBLIB->getone('locations', [
    "locations.*",
    "clients.clients_name"
]);

if (!$PAGEDATA['location']) die($TWIG->render('404.twig', $PAGEDATA));

$PAGEDATA['pageConfig'] = [
    "TITLE" => "Assets at " . $PAGEDATA['location']['locations_name'],
    "BREADCRUMB" => false
];

// Get all assets at this location
$DBLIB->where("assets.assets_storageLocation", $_GET['location']);
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$DBLIB->orderBy("assets.assets_tag", "ASC");

$assets = $DBLIB->get("assets", null, [
    "assets.*",
    "assetTypes.assetTypes_name",
    "assetTypes.assetTypes_id",
    "assetCategories.assetCategories_name",
    "assetCategoriesGroups.assetCategoriesGroups_name",
    "manufacturers.manufacturers_name"
]);

// Enrich asset data with project assignments and status
foreach ($assets as &$asset) {
    // Check current project assignment
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->join("assetsAssignmentsStatus", "assetsAssignments.assetsAssignmentsStatus_id=assetsAssignmentsStatus.assetsAssignmentsStatus_id", "LEFT");
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);

    $assignment = $DBLIB->getone("assetsAssignments", [
        "projects.projects_id",
        "projects.projects_name",
        "assetsAssignmentsStatus.assetsAssignmentsStatus_name"
    ]);

    $asset['currentAssignment'] = $assignment ?? null;

    // Get latest scan information
    $asset['latestScan'] = assetLatestScan($asset['assets_id']);
}

$PAGEDATA['assets'] = $assets;
$PAGEDATA['assetCount'] = count($assets);

echo $TWIG->render('location/location_assets.twig', $PAGEDATA);
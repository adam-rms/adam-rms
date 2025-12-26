<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (
    !$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS")
    || !isset($_POST['projects_id'])
    || !isset($_POST['scans'])
    || !is_array($_POST['scans'])
) {
    finish(false, ["message" => "Missing required fields", "code" => "MISSINGFIELDS"]);
}

$projectId = $_POST['projects_id'];
$scans = $_POST['scans']; // Array of {barcode, location, locationType}

// Validate project access
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $projectId);
$project = $DBLIB->getone("projects", ["projects_id", "projects_name"]);
if (!$project) finish(false, ["message" => "Project not found", "code" => "PROJECTNOTFOUND"]);

$results = [];

foreach ($scans as $scan) {
    $barcodeText = $scan['barcode'];
    $result = [
        "barcode" => $barcodeText,
        "valid" => false,
        "warnings" => [],
        "errors" => [],
        "asset" => null,
        "assignment" => null,
        "needsAssignment" => false
    ];

    // Step 1: Find asset by barcode or tag
    $DBLIB->where("assetsBarcodes_value", $barcodeText);
    $DBLIB->where("assetsBarcodes_deleted", 0);
    $DBLIB->join("assets", "assets.assets_id=assetsBarcodes.assets_id", "LEFT");
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_deleted", 0);
    $barcode = $DBLIB->getone("assetsBarcodes", ["assets.assets_id"]);

    if (!$barcode) {
        // Try by tag directly
        $DBLIB->where("assets.assets_tag", $barcodeText);
        $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assets.assets_deleted", 0);
        $asset = $DBLIB->getone("assets", ["assets_id"]);
        if ($asset) $barcode = $asset;
    }

    if (!$barcode) {
        $result['errors'][] = "Barcode/tag not found in system";
        $results[] = $result;
        continue;
    }

    // Step 2: Get full asset details
    $DBLIB->where("assets.assets_id", $barcode['assets_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $asset = $DBLIB->getone("assets", [
        "assets.assets_id",
        "assets.assets_tag",
        "assetTypes.assetTypes_name",
        "assetTypes.assetTypes_id",
        "assetCategories.assetCategories_name",
        "manufacturers.manufacturers_name"
    ]);

    $result['asset'] = $asset;

    // Step 3: Check if assigned to project
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("projects_id", $projectId);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $assignment = $DBLIB->getone("assetsAssignments", [
        "assetsAssignments_id",
        "assetsAssignmentsStatus_id"
    ]);

    if (!$assignment) {
        $result['needsAssignment'] = true;
        $result['warnings'][] = "Asset not assigned to this project";
        $results[] = $result;
        continue;
    }

    $result['assignment'] = $assignment;

    // Step 4: Check maintenance blocks - ERRORS for overdue scheduled maintenance, warnings for others
    $flagsBlocks = assetFlagsAndBlocks($asset['assets_id']);
    if ($flagsBlocks['COUNT']['BLOCK'] > 0) {
        foreach ($flagsBlocks['BLOCK'] as $block) {
            // Check if this is overdue scheduled maintenance
            if (isset($block['isScheduledMaintenance']) && $block['isScheduledMaintenance']) {
                // OVERDUE SCHEDULED MAINTENANCE = ERROR (blocking)
                $result['errors'][] = [
                    'type' => 'scheduled_maintenance_overdue',
                    'message' => $block['maintenanceJobs_title'],
                    'schedule_id' => $block['assetMaintenanceSchedules_id'],
                    'description' => $block['maintenanceJobs_faultDescription'],
                ];
            } else {
                // Regular maintenance block = warning
                $result['warnings'][] = "Maintenance block: " . $block['maintenanceJobs_title'];
            }
        }
    }
    if ($flagsBlocks['COUNT']['FLAG'] > 0) {
        foreach ($flagsBlocks['FLAG'] as $flag) {
            $result['warnings'][] = "Maintenance flag: " . $flag['maintenanceJobs_title'];
        }
    }

    // Step 5: Get current location if available
    $latestScan = assetLatestScan($asset['assets_id']);
    if ($latestScan) {
        $result['asset']['currentLocation'] = $latestScan['locations_name']
            ?? $latestScan['assets_tag']
            ?? $latestScan['assetsBarcodes_customLocation']
            ?? 'Unknown';
    }

    // Mark as valid if no errors
    if (empty($result['errors'])) {
        $result['valid'] = true;
    }

    $results[] = $result;
}

finish(true, null, ["scans" => $results]);
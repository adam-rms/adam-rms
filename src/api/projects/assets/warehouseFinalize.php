<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (
    !$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS")
    || !isset($_POST['projects_id'])
    || !isset($_POST['assets'])
    || !isset($_POST['targetStatus'])
) {
    finish(false, ["message" => "Missing required fields", "code" => "MISSINGFIELDS"]);
}

$projectId = $_POST['projects_id'];
$targetStatusId = $_POST['targetStatus'];
$assets = $_POST['assets']; // Array of {assets_id, location, locationType}

// Validate project
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $projectId);
$project = $DBLIB->getone("projects", ["projects_id"]);
if (!$project) finish(false, ["message" => "Project not found"]);

// Validate status
$DBLIB->where("assetsAssignmentsStatus_id", $targetStatusId);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$status = $DBLIB->getone("assetsAssignmentsStatus");
if (!$status) finish(false, ["message" => "Status not found"]);

$results = [
    "success" => [],
    "failed" => []
];

foreach ($assets as $assetData) {
    $assetId = $assetData['assets_id'];
    $location = $assetData['location'] ?? null;
    $locationType = $assetData['locationType'] ?? null;
    $scannedAt = $assetData['scannedAt'] ?? null;

    // Find assignment
    $DBLIB->where("assets_id", $assetId);
    $DBLIB->where("projects_id", $projectId);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $assignment = $DBLIB->getone("assetsAssignments", ["assetsAssignments_id", "assetsAssignmentsStatus_id"]);

    if (!$assignment) {
        $results['failed'][] = [
            "assets_id" => $assetId,
            "reason" => "Assignment not found"
        ];
        continue;
    }

    // Update status
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    $updated = $DBLIB->update("assetsAssignments", [
        "assetsAssignmentsStatus_id" => $targetStatusId
    ], 1);

    if ($updated) {
        // Record scan with timestamp only if location was provided
        // Warehouse mode is primarily for status updates - only record location scans when location is specified
        if ($location) {
            // Find barcode for this asset
            $DBLIB->where("assets_id", $assetId);
            $DBLIB->where("assetsBarcodes_deleted", 0);
            $barcode = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes_id"]);

            if ($barcode) {
                // Convert ISO timestamp to MySQL datetime format
                $scanTimestamp = $scannedAt ? date('Y-m-d H:i:s', strtotime($scannedAt)) : date('Y-m-d H:i:s');

                $scanData = [
                    "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
                    "users_userid" => $AUTH->data['users_userid'],
                    "assetsBarcodesScans_timestamp" => $scanTimestamp,
                    "assetsBarcodesScans_validation" => "Warehouse Mode",
                    "assetsBarcodesScans_barcodeWasScanned" => 1
                ];

                // Add location based on type
                if ($locationType == "barcode") {
                    $scanData["locationsBarcodes_id"] = $location;
                } elseif ($locationType == "asset") {
                    $scanData["location_assets_id"] = $location;
                } else {
                    $scanData["assetsBarcodes_customLocation"] = $location;
                }

                $DBLIB->insert("assetsBarcodesScans", $scanData);
            }
        }

        // Audit log
        $bCMS->auditLog(
            "WAREHOUSE-SCAN",
            "assetsAssignments",
            "Status set from " . $assignment['assetsAssignmentsStatus_id'] . " to " . $targetStatusId . " via warehouse mode",
            $AUTH->data['users_userid'],
            null,
            $projectId
        );

        $results['success'][] = ["assets_id" => $assetId];
    } else {
        $results['failed'][] = [
            "assets_id" => $assetId,
            "reason" => "Failed to update status"
        ];
    }
}

finish(true, null, $results);
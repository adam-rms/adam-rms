<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Make type optional for auto-detection
if (!isset($_POST['text']) or strlen($_POST['text']) < 1) finish(false);

$barcodeType = $_POST['type'] ?? null;  // Optional - null means auto-detect

// See if the barcode is a location
$DBLIB->where("locationsBarcodes_value", $_POST['text']);
if ($barcodeType !== null) {
    $DBLIB->where("locationsBarcodes_type", $barcodeType);
}
$DBLIB->where("locationsBarcodes_deleted", 0);
$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->join("locations", "locations.locations_id=locationsBarcodes.locations_id", "LEFT");
$location = $DBLIB->getone("locationsBarcodes", ["locationsBarcodes.locations_id", "locationsBarcodes.locationsBarcodes_id", "locations.locations_name"]);
if ($location) {
    $location['barcode'] = $location['locationsBarcodes_id'];
    //Location has been found
} else $location = false;

//See if Barcode is in database
$DBLIB->where("assetsBarcodes_value", $_POST['text']);
if ($barcodeType !== null) {
    $DBLIB->where("assetsBarcodes_type", $barcodeType);
}
// Support global search for uniqueness validation (if globalSearch parameter is set)
if (!isset($_POST['globalSearch']) || $_POST['globalSearch'] !== 'true') {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']); // Restrict only to the current instance
}
$DBLIB->join("assets", "assets.assets_id=assetsBarcodes.assets_id", "LEFT");
$DBLIB->join("instances", "instances.instances_id=assets.instances_id", "LEFT");
$DBLIB->where("assetsBarcodes_deleted", 0);
$barcode = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes.assets_id", "assetsBarcodes.assetsBarcodes_id", "assets.assets_tag", "instances.instances_name"]);

// Initialize status and location tracking variables
$storageLocationUpdated = false;
$newStorageLocationId = null;
$statusUpdated = false;
$oldStatus = null;
$newStatus = null;
$assignment = null;

if ($barcode) {
    $scan = [
        "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "assetsBarcodesScans_timestamp" => date('Y-m-d H:i:s'),
        "locationsBarcodes_id" => ($_POST['locationType'] == "barcode" ? $_POST['location'] : null),
        "location_assets_id" => ($_POST['locationType'] == "asset" ? $_POST['location'] : null),
        "assetsBarcodes_customLocation" => ($_POST['locationType'] == "Custom" ? $_POST['location'] : null),
        "assetsBarcodesScans_barcodeWasScanned" => ($_POST['scanned'] == "true" ? 1 : 0),
        "assetsBarcodesScans_validation" => isset($_POST['validation']) ? $_POST['validation'] : null,
    ];
    $scanId = $DBLIB->insert("assetsBarcodesScans", $scan);

    // STEP 1: Always update storage location
    if (isset($_POST['location']) && $_POST['locationType'] == "barcode") {
        // Get actual locations_id from locationsBarcodes_id
        $DBLIB->where("locationsBarcodes_id", $_POST['location']);
        $locationData = $DBLIB->getone("locationsBarcodes", ["locations_id"]);

        if ($locationData && $AUTH->instancePermissionCheck("ASSETS:EDIT")) {
            $DBLIB->where("assets_id", $barcode['assets_id']);
            $DBLIB->update("assets", ["assets_storageLocation" => $locationData['locations_id']]);
            $storageLocationUpdated = true;
            $newStorageLocationId = $locationData['locations_id'];

            // Audit log for storage location update
            $bCMS->auditLog(
                "BARCODE-SCAN-LOCATION-UPDATE",
                "assets",
                "Storage location updated to " . $locationData['locations_id'],
                $AUTH->data['users_userid'],
                null,
                null,
                $barcode['assets_id']
            );
        }
    }

    // STEP 2: Check if asset is assigned to a project
    $DBLIB->where("assets_id", $barcode['assets_id']);
    $DBLIB->where("assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $assignment = $DBLIB->getone("assetsAssignments", [
        "assetsAssignments_id",
        "assetsAssignmentsStatus_id",
        "projects.projects_id",
        "projects.projects_name"
    ]);

    if ($assignment && $AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS")) {
        // Asset IS on a project - apply status logic

        // Get all statuses for this instance ordered by order value
        $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assetsAssignmentsStatus_deleted", 0);
        $DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
        $statuses = $DBLIB->get("assetsAssignmentsStatus");

        if (count($statuses) > 0) {
            $firstStatus = $statuses[0];
            $lastStatus = $statuses[count($statuses) - 1];

            // Get current status
            $currentStatusOrder = null;
            if ($assignment['assetsAssignmentsStatus_id']) {
                foreach ($statuses as $status) {
                    if ($status['assetsAssignmentsStatus_id'] == $assignment['assetsAssignmentsStatus_id']) {
                        $currentStatusOrder = $status['assetsAssignmentsStatus_order'];
                        $oldStatus = $status;
                        break;
                    }
                }
            } else {
                // NULL status treated as first status
                $currentStatusOrder = $firstStatus['assetsAssignmentsStatus_order'];
            }

            // Apply status update logic
            $isFirstStatus = ($currentStatusOrder == $firstStatus['assetsAssignmentsStatus_order']);
            $isLastStatus = ($currentStatusOrder == $lastStatus['assetsAssignmentsStatus_order']);

            if (!$isFirstStatus && !$isLastStatus) {
                // Update to last status
                $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
                $DBLIB->update("assetsAssignments", [
                    "assetsAssignmentsStatus_id" => $lastStatus['assetsAssignmentsStatus_id']
                ]);
                $statusUpdated = true;
                $newStatus = $lastStatus;

                // Audit log for status change
                $bCMS->auditLog(
                    "BARCODE-SCAN-STATUS-UPDATE",
                    "assetsAssignments",
                    "Status updated from " . ($oldStatus ? $oldStatus['assetsAssignmentsStatus_name'] : 'NULL') . " to " . $newStatus['assetsAssignmentsStatus_name'],
                    $AUTH->data['users_userid'],
                    null,
                    $assignment['projects_id'],
                    $assignment['assetsAssignments_id']
                );
            }
        }
    }

    // STEP 3: Update scan validation message with context
    // Only update if a custom validation message wasn't already provided
    if (!isset($_POST['validation']) || empty($_POST['validation'])) {
        $validationMessage = null;

        if ($statusUpdated && $assignment) {
            // Status was updated - asset returned from project
            $validationMessage = "Returned from " . $assignment['projects_name'];
        } elseif ($storageLocationUpdated && $assignment) {
            // Only location updated, but asset is still on a project
            $validationMessage = "Location updated (on project: " . $assignment['projects_name'] . ")";
        } elseif ($storageLocationUpdated) {
            // Only location updated, asset not on project
            $validationMessage = "Location set via barcode scan";
        }

        if ($validationMessage) {
            $DBLIB->where("assetsBarcodesScans_id", $scanId);
            $DBLIB->update("assetsBarcodesScans", [
                "assetsBarcodesScans_validation" => $validationMessage
            ]);
        }
    }
} else $barcode = false;

//If it's in the database and has an asset, return that asset
if ($barcode) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_id", $barcode['assets_id']);
    $asset = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
} else $asset = false;

if (!$asset) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_tag", $_POST['text']);
    $assetSuggest = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
    if (!$assetSuggest) $assetSuggest = false;
} else $assetSuggest = false;

finish(true, null, [
    "asset" => $asset,
    "assetSuggest" => $assetSuggest,
    "barcode" => ($barcode ? $barcode['assetsBarcodes_id'] : false),
    "location" => $location,
    "storageLocationUpdated" => $storageLocationUpdated,
    "statusUpdated" => $statusUpdated,
    "assignment" => $assignment,
    "oldStatus" => $oldStatus,
    "newStatus" => $newStatus
]);

/** @OA\Post(
 *     path="/assets/barcodes/search.php", 
 *     summary="Barcode Asset Search", 
 *     description="Search for an Asset using a barcode
", 
 *     operationId="barcodeSearch", 
 *     tags={"barcodes"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="text",
 *         in="query",
 *         description="The barcode value",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="The barcode type",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="locationType",
 *         in="query",
 *         description="What the location is - should be 'barcode', 'asset' or 'Custom' ",
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *    @OA\Parameter(
 *         name="location",
 *         in="query",
 *         description="a locationBarcodeId, assetBarcodeId or custom string",
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
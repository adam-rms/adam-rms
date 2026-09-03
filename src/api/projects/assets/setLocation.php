<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (
    !$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS")
    || !isset($_POST['projects_id'])
    || !isset($_POST['strategy'])
    || !isset($_POST['assetsAssignments_id'])
    || !is_array($_POST['assetsAssignments_id'])
    || count($_POST['assetsAssignments_id']) === 0
) finish(false, ["code" => "MISSINGFIELDS", "message" => "Missing required fields"]);

$strategy = $_POST['strategy'];
if (!in_array($strategy, ['project', 'storage', 'other'], true)) {
    finish(false, ["code" => "INVALIDSTRATEGY", "message" => "Invalid strategy"]);
}

if ($strategy === 'other' && (!isset($_POST['locations_id']) || !is_numeric($_POST['locations_id']))) {
    finish(false, ["code" => "MISSINGFIELDS", "message" => "locations_id required for 'other' strategy"]);
}

$projectsId = (int)$_POST['projects_id'];

// Fetch project name for scan validation label (and venue for 'project' strategy)
$DBLIB->where("projects_id", $projectsId);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_deleted", 0);
$project = $DBLIB->getone("projects", ["projects_name", "locations_id"]);
if (!$project) finish(false, ["code" => "NOTFOUND", "message" => "Project not found"]);
$scanValidation = "Dispatched from Project " . $project['projects_name'];

// Resolve project venue if needed
$projectLocationId = null;
if ($strategy === 'project') {
    if (!$project['locations_id']) {
        finish(false, ["code" => "NOVENUEASSIGNED", "message" => "This project has no venue assigned"]);
    }
    $projectLocationId = (int)$project['locations_id'];
}

// Validate 'other' location exists, is active, and belongs to an instance actually
// represented here (the project's instance, or a sub-business whose asset is selected).
// This prevents a caller injecting a locations_id from an unrelated tenant.
if ($strategy === 'other') {
    $allowedInstanceIds = [(int)$AUTH->data['instance']['instances_id']];
    $DBLIB->where("assetsAssignments.assetsAssignments_id", array_map('intval', $_POST['assetsAssignments_id']), "IN");
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->where("projects.projects_id", $projectsId);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $assignmentInstances = $DBLIB->get("assetsAssignments", null, ["assets.instances_id"]);
    foreach ($assignmentInstances as $ai) {
        if ($ai['instances_id'] !== null) $allowedInstanceIds[] = (int)$ai['instances_id'];
    }
    $allowedInstanceIds = array_values(array_unique($allowedInstanceIds));

    $DBLIB->where("locations_id", (int)$_POST['locations_id']);
    $DBLIB->where("instances_id", $allowedInstanceIds, "IN");
    $DBLIB->where("locations_deleted", 0);
    $DBLIB->where("locations_archived", 0);
    $loc = $DBLIB->getone("locations", ["locations_id"]);
    if (!$loc) finish(false, ["code" => "INVALIDLOCATION", "message" => "Location not found"]);
}

// Cache location barcodes to avoid repeated queries for the same location
$locationBarcodeCache = [];
function getLocationBarcodeId($locationsId) {
    global $DBLIB, $locationBarcodeCache;
    if (isset($locationBarcodeCache[$locationsId])) return $locationBarcodeCache[$locationsId];
    $DBLIB->where("locations_id", $locationsId);
    $DBLIB->where("locationsBarcodes_deleted", 0);
    $DBLIB->orderBy("locationsBarcodes_id", "ASC");
    $lb = $DBLIB->getone("locationsBarcodes", ["locationsBarcodes_id"]);
    $id = ($lb ? (int)$lb['locationsBarcodes_id'] : null);
    $locationBarcodeCache[$locationsId] = $id;
    return $id;
}

$succeeded = [];
$skipped = [];

foreach ($_POST['assetsAssignments_id'] as $rawId) {
    $assignmentId = (int)$rawId;

    // Fetch assignment + asset, scoped to this project and instance
    $DBLIB->where("assetsAssignments.assetsAssignments_id", $assignmentId);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->where("projects.projects_id", $projectsId);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("assets.assets_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $assignment = $DBLIB->getone("assetsAssignments", [
        "assetsAssignments.assetsAssignments_id",
        "assetsAssignments.assets_id",
        "assets.assets_tag",
        "assets.assets_storageLocation",
    ]);

    if (!$assignment || !$assignment['assets_id']) {
        $skipped[] = ["assetsAssignments_id" => $assignmentId, "assets_tag" => null, "reason" => "Asset not found"];
        continue;
    }

    // Determine target location
    $targetLocationId = null;
    if ($strategy === 'project') {
        $targetLocationId = $projectLocationId;
    } elseif ($strategy === 'storage') {
        if (!$assignment['assets_storageLocation']) {
            $skipped[] = [
                "assetsAssignments_id" => $assignmentId,
                "assets_tag" => $assignment['assets_tag'],
                "reason" => "No storage location set",
            ];
            continue;
        }
        $targetLocationId = (int)$assignment['assets_storageLocation'];
    } elseif ($strategy === 'other') {
        $targetLocationId = (int)$_POST['locations_id'];
    }

    // Find asset's primary barcode
    $DBLIB->where("assets_id", (int)$assignment['assets_id']);
    $DBLIB->where("assetsBarcodes_deleted", 0);
    $DBLIB->orderBy("assetsBarcodes_id", "ASC");
    $assetBarcode = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes_id"]);

    if (!$assetBarcode) {
        $skipped[] = [
            "assetsAssignments_id" => $assignmentId,
            "assets_tag" => $assignment['assets_tag'],
            "reason" => "No barcode registered for asset",
        ];
        continue;
    }

    // Find location barcode for the target location
    $locationBarcodeId = getLocationBarcodeId($targetLocationId);
    if (!$locationBarcodeId) {
        $skipped[] = [
            "assetsAssignments_id" => $assignmentId,
            "assets_tag" => $assignment['assets_tag'],
            "reason" => "Target location has no barcode",
        ];
        continue;
    }

    $DBLIB->insert("assetsBarcodesScans", [
        "assetsBarcodes_id"                     => (int)$assetBarcode['assetsBarcodes_id'],
        "users_userid"                          => $AUTH->data['users_userid'],
        "assetsBarcodesScans_timestamp"         => date('Y-m-d H:i:s'),
        "locationsBarcodes_id"                  => $locationBarcodeId,
        //Not a physical barcode scan, so recorded the same way a manually set location is.
        //assetsBarcodesScans_validation carries the provenance ("Dispatched from Project X").
        "assetsBarcodesScans_barcodeWasScanned" => 0,
        "assetsBarcodesScans_validation"        => $scanValidation,
    ]);

    $succeeded[] = ["assetsAssignments_id" => $assignmentId, "assets_tag" => $assignment['assets_tag']];
}

$bCMS->auditLog(
    "LOCATION-DISPATCH",
    "assetsAssignments",
    "Strategy: " . $strategy . " | " . count($succeeded) . " succeeded, " . count($skipped) . " skipped",
    $AUTH->data['users_userid'],
    null,
    $projectsId
);

finish(true, null, ["succeeded" => $succeeded, "skipped" => $skipped]);

/** @OA\Post(
 *     path="/projects/assets/setLocation.php",
 *     summary="Location Dispatch: assign location to project assets",
 *     description="Bulk-assigns a location to selected project asset assignments by creating barcode scan entries.
Requires Instance Permission PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS
Strategies:
- project: assigns the project's venue to all selected assets
- storage: assigns each asset's own pre-defined storage location (assets without one are skipped)
- other: assigns a caller-supplied locations_id to all selected assets
Assets without a registered barcode are always skipped. Partial success is returned in the 'skipped' array.
",
 *     operationId="setAssetAssignmentLocation",
 *     tags={"project_assets"},
 *     @OA\Response(
 *         response="200",
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="result", type="boolean"),
 *                 @OA\Property(
 *                     property="response",
 *                     type="object",
 *                     @OA\Property(property="succeeded", type="array", @OA\Items(type="object")),
 *                     @OA\Property(property="skipped",   type="array", @OA\Items(type="object")),
 *                 ),
 *             ),
 *         ),
 *     ),
 *     @OA\Parameter(name="projects_id",           in="query", required=true,  @OA\Schema(type="number")),
 *     @OA\Parameter(name="strategy",              in="query", required=true,  @OA\Schema(type="string", enum={"project","storage","other"})),
 *     @OA\Parameter(name="assetsAssignments_id[]", in="query", required=true,  @OA\Schema(type="array", @OA\Items(type="number"))),
 *     @OA\Parameter(name="locations_id",           in="query", required=false, @OA\Schema(type="number")),
 * )
 */

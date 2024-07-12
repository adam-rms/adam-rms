<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS") or !isset($_POST['projects_id']) or !isset($_POST['assetsAssignments_status']) or !isset($_POST['text']) or !isset($_POST['type']) or strlen($_POST['text']) < 1 or strlen($_POST['type']) < 1) finish(false);

//See if Barcode is in database
$DBLIB->where("assetsBarcodes_value", $_POST['text']);
$DBLIB->where("assetsBarcodes_type", $_POST['type']);
$DBLIB->where("assetsBarcodes_deleted", 0);
$barcode = $DBLIB->getone("assetsBarcodes", ["assets_id", "assetsBarcodes_id"]);
if ($barcode and $barcode['assets_id'] != null) {
    $scan = [
        "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "assetsBarcodesScans_timestamp" => date('Y-m-d H:i:s'),
        "locationsBarcodes_id" => ($_POST['locationType'] == "barcode" ? $_POST['location'] : null),
        "location_assets_id" => ($_POST['locationType'] == "asset" ? $_POST['location'] : null),
        "assetsBarcodes_customLocation" => ($_POST['locationType'] == "Custom" ? $_POST['location'] : null)
    ];
    $DBLIB->insert("assetsBarcodesScans", $scan);

    $DBLIB->where("assets_id", $barcode['assets_id']);
    $DBLIB->where("projects_id", $_POST['projects_id']);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $assignment = $DBLIB->update("assetsAssignments", ["assetsAssignments_status" => $_POST['assetsAssignments_status']], 1);
    if (!$assignment or $DBLIB->count != 1) {
        finish(false, ["message" => "Asset not assigned to project", "code" => "NOTASSIGNED"]);
    } else {
        $bCMS->auditLog("EDIT-STATUS", "assetsAssignments", $_POST['assetsAssignments_status'], $AUTH->data['users_userid'], null, $assignment['projects_id']);
        finish(true);
    }
} else finish(false);

/** @OA\Post(
 *     path="/projects/assets/setStatusBarcode.php", 
 *     summary="Set Asset Assignment Status using Barcode", 
 *     description="Set the status for an asset assignment using a barcode  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS
", 
 *     operationId="setAssetAssignmentStatusBarcode", 
 *     tags={"project_assets"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="text",
 *         in="query",
 *         description="barcode value",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="barcode type",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="locationType",
 *         in="query",
 *         description="location type",
 *         required="true", 
 *         @OA\Schema(
 *             type="enum"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetsAssignments_status",
 *         in="query",
 *         description="Status ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */

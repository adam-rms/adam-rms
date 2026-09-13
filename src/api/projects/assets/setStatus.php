<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS") || !isset($_POST['assetsAssignments_status']) || !(isset($_POST['assetsAssignments_id']) || isset($_POST['projects_id']))) finish(false);

if (isset($_POST['assetsAssignments_id'])){
    $DBLIB->where("assetsAssignments_id", $_POST['assetsAssignments_id'], (is_array($_POST['assetsAssignments_id'])? 'IN' : '='));
} elseif (isset($_POST['projects_id'])) {
    $DBLIB->where("assetsAssignments.projects_id", $_POST['projects_id']);
} else {
    finish(false);
}

$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
$matchedAssignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assets.instances_id"]);
if (!$matchedAssignments) finish(false);

// Validate that the requested status is not deleted and belongs to every instance that owns one
// of the matched assets - an asset can belong to a different instance to the project (e.g. a
// sub-project asset, or "Supermarket Sweep"), so this isn't necessarily the current instance.
$assetInstanceIds = array_unique(array_column($matchedAssignments, 'instances_id'));
$DBLIB->where("assetsAssignmentsStatus_id", $_POST['assetsAssignments_status']);
$DBLIB->where("instances_id", $assetInstanceIds, "IN");
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$validInstances = $DBLIB->get("assetsAssignmentsStatus", null, ["instances_id"]);
if (count($validInstances) !== count($assetInstanceIds)) finish(false, ["message" => "Status not found", "code" => "STATUSNOTFOUND"]);

$DBLIB->where("assetsAssignments_id", array_column($matchedAssignments, 'assetsAssignments_id'), "IN");
$assignment = $DBLIB->update("assetsAssignments", ["assetsAssignmentsStatus_id" => $_POST['assetsAssignments_status']]);

if (!$assignment) finish(false);
else finish(true);

/** @OA\Post(
 *     path="/projects/assets/setStatus.php", 
 *     summary="Set Asset Assignment Status", 
 *     description="Set the status for an asset assignment  
Requires Instance Permission PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS
", 
 *     operationId="setAssetAssignmentStatus", 
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
 *         name="assetsAssignments_status",
 *         in="query",
 *         description="Status",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetsAssignments_id",
 *         in="query",
 *         description="Asset Assignment ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
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
 *         name="status_is_order",
 *         in="query",
 *         description="Whether the status is an ordering rather than a status",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 * )
 */
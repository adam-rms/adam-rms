<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS") or !isset($_POST['projects_id']) or !isset($_POST['assetsAssignments_status']) or !isset($_POST['text']) or strlen($_POST['text']) < 1) finish(false, ["message" => "Missing required fields","code"=>"MISSINGFIELDS"]);

$DBLIB->where("assets.assets_deleted",0);
$DBLIB->where("assets.assets_tag", $_POST["text"]);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
$assignment = $DBLIB->getone("assetsAssignments",["assets.assets_id", "assetsAssignments.assetsAssignments_id", "assetsAssignments.assetsAssignmentsStatus_id", "assets.instances_id"]);
if (!$assignment or $assignment['assets_id'] == null) finish(false, ["message" => "Asset not found","code"=>"NOTFOUND"]);
if ($assignment['assetsAssignmentsStatus_id'] == $_POST['assetsAssignments_status']) finish(true, null, ["assets_id" => $assignment['assets_id']]); // No change

$DBLIB->where("assetsAssignmentsStatus_id", $_POST['assetsAssignments_status']);
$DBLIB->where("instances_id", $assignment['instances_id']); // Use the instance of the asset
$status = $DBLIB->getone("assetsAssignmentsStatus",["assetsAssignmentsStatus_id"]);
if (!$status or $status['assetsAssignmentsStatus_id'] == null) finish(false, ["message" => "Status not found","code"=>"STATUSNOTFOUND"]);

$DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
$update = $DBLIB->update("assetsAssignments", ["assetsAssignmentsStatus_id" => $status['assetsAssignmentsStatus_id']], 1);
if (!$update) finish(false, ["message" => "Asset not assigned to project","code"=>"NOTASSIGNED"]);
else {
    $bCMS->auditLog("EDIT-STATUS", "assetsAssignments", $assignment['assetsAssignments_id'] . " set from " . $assignment['assetsAssignmentsStatus_id'] . " to " . $status['assetsAssignmentsStatus_id'] . " by direct tag entry", $AUTH->data['users_userid'],null, $_POST['projects_id']);
    finish(true, null, ["assets_id" => $assignment['assets_id']]);
}

/**
 *  @OA\Post(
 *      path="/projects/assets/setStatusByTag.php",
 *      summary="Set Asset Status by Tag",
 *      description="Set asset status for a project by the asset's tag",
 *      operationId="setStatusByTag",
 *      tags={"project_assets"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="text",
 *          in="query",
 *          description="Value of the asset tag",
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="assetsAssignments_status",
 *          in="query",
 *          description="Status Id to set asset to",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */
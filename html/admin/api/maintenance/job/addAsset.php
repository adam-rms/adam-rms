<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:ADD_ASSETS") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_assets", "maintenanceJobs_id"]);
if (!$job) die("404");
if ($job["maintenanceJobs_assets"] != "") $job["maintenanceJobs_assets"] = explode(",", $job["maintenanceJobs_assets"]);
else $job["maintenanceJobs_assets"] = [];
foreach ($_POST['maintenanceJobs_assets'] as $asset) {
    array_push($job["maintenanceJobs_assets"], $asset);
}
$job["maintenanceJobs_assets"] = implode(",", $job["maintenanceJobs_assets"]);

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_assets" => $job["maintenanceJobs_assets"]]);
if (!$update) finish(false);

$bCMS->auditLog("ADD-ASSETS", "maintenanceJobs", $job["maintenanceJobs_assets"], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);

finish(true);

/** @OA\Post(
 *     path="/maintenance/job/addAsset.php", 
 *     summary="Add Asset", 
 *     description="Add an asset to a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:ADD_ASSETS
", 
 *     operationId="addAsset", 
 *     tags={"maintenanceJobs"}, 
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
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="maintenanceJobs_id",
 *         in="query",
 *         description="Maintenance Job ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="maintenanceJobs_assets",
 *         in="query",
 *         description="Maintenance Job Assets",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */
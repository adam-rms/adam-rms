<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:ASSET_BLOCKS") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_title",  "maintenanceJobs_id"]);
if (!$job) die("404");

if ($_POST['maintenanceJobs_blockAssets'] != 1 && $_POST['maintenanceJobs_blockAssets'] != 0) finish(false);

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_blockAssets" => $_POST['maintenanceJobs_blockAssets']]);
if (!$update) finish(false);

if ($_POST['maintenanceJobs_blockAssets'] == 1) {
    $bCMS->auditLog("BLOCK-ASSETS", "maintenanceJobs", "Set the project to block assets", $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);
} else $bCMS->auditLog("UNBLOCK-ASSETS", "maintenanceJobs", "Remove the asset block from the job", $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);


finish(true);

/** @OA\Post(
 *     path="/maintenance/job/changeBlock.php", 
 *     summary="Change Block", 
 *     description="Change the block status of a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:ASSET_BLOCKS
", 
 *     operationId="changeBlock", 
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
 *         name="maintenanceJobs_blockAssets",
 *         in="query",
 *         description="Maintenance Job Block",
 *         required="true", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 * )
 */
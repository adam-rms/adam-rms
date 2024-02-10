<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:NAME") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_title" => $_POST['maintenanceJobs_title']]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-TITLE", "maintenanceJobs", "Set the title to ". $_POST['maintenanceJobs_title'], $AUTH->data['users_userid'],null, null,$_POST['maintenanceJobs_id']);
finish(true);

/** @OA\Post(
 *     path="/maintenance/job/changeName.php", 
 *     summary="Change Name", 
 *     description="Change the name of a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:NAME
", 
 *     operationId="changeName", 
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
 *         name="maintenanceJobs_title",
 *         in="query",
 *         description="Maintenance Job Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
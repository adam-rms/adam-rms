<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:JOB_DUE_DATE") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_timestamp_due" => date ("Y-m-d H:i:s", strtotime($_POST['maintenanceJobs_timestamp_due']))]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-DUE-DATE", "maintenanceJobs", "Set the due date to ". date ("D jS M Y h:i:sa", strtotime($_POST['maintenanceJobs_timestamp_due'])), $AUTH->data['users_userid'],null, null, $_POST['maintenanceJobs_id']);
finish(true);

/** @OA\Post(
 *     path="/maintenance/job/changeDueDate.php", 
 *     summary="Change Due Date", 
 *     description="Change the due date of a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:JOB_DUE_DATE
", 
 *     operationId="changeDueDate", 
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
 *         name="maintenanceJobs_timestamp_due",
 *         in="query",
 *         description="Maintenance Job Due Date",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
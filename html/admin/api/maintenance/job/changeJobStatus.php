<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:STATUS") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobsStatuses_deleted", 0);
$DBLIB->where("(instances_id IS NULL OR instances_id = '" . $AUTH->data['instance']["instances_id"] . "')");
$DBLIB->where("maintenanceJobsStatuses_id",$_POST['maintenanceJobsStatuses_id']);
$status = $DBLIB->getone("maintenanceJobsStatuses", ["maintenanceJobsStatuses.maintenanceJobsStatuses_id", "maintenanceJobsStatuses.maintenanceJobsStatuses_name"]);
if (!$status) finish(false);

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_title",  "maintenanceJobs_id"]);
if (!$job) die("404");

$updateData = ["maintenanceJobsStatuses_id" => $status['maintenanceJobsStatuses_id']];
if ($status['maintenanceJobsStatuses_id'] == "2") {
    //Job being closed so remove flags and blocks
    $updateData['maintenanceJobs_flagAssets'] = 0;
    $updateData['maintenanceJobs_blockAssets'] = 0;
}
$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", $updateData);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-STATUS", "maintenanceJobs", "Change status to " . $status['maintenanceJobsStatuses_name'], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);

$job['tagged'] = [];
if ($job['maintenanceJobs_user_tagged'] != "") {
    $DBLIB->where("(users.users_userid IN (" . $job['maintenanceJobs_user_tagged'] . "))");
    $DBLIB->orderBy("users.users_name1", "ASC");
    $DBLIB->orderBy("users.users_name2", "ASC");
    $DBLIB->orderBy("users.users_created", "ASC");
    $DBLIB->where("users_deleted", 0);
    $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
    $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
    $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
    $DBLIB->where("userInstances.userInstances_deleted",  0);
    $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
    $job['tagged'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
}
if (count($job['tagged']) > 0) {
    foreach ($job['tagged'] as $user) {
        if ($user['users_userid'] == $AUTH->data['users_userid']) continue;
            notify(14,$user['users_userid'], $AUTH->data['instance']['instances_id'], "Status of job: " . $job['maintenanceJobs_title'] . " has been set to " . $status['maintenanceJobsStatuses_name'], false, "api/maintenance/job/changeJobStatus-EmailTemplate.twig", ["users_name1" => $AUTH->data['users_name1'], "users_name2"=> $AUTH->data['users_name2'], "job" => $job]);
    }
}


finish(true);

/** @OA\Post(
 *     path="/maintenance/job/changeJobStatus.php", 
 *     summary="Change Job Status", 
 *     description="Change the status of a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:STATUS
", 
 *     operationId="changeJobStatus", 
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
 *         name="maintenanceJobsStatuses_id",
 *         in="query",
 *         description="Maintenance Job Status id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
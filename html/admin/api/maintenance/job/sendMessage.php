<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_title",  "maintenanceJobs_id"]);
if (!$job) die("404");

$data = [
    "maintenanceJobsMessages_timestamp" => date('Y-m-d H:i:s'),
    "maintenanceJobs_id" => $job["maintenanceJobs_id"],
    "users_userid" => $AUTH->data['users_userid']
];
if (isset($_POST['maintenanceJobsMessages_text'])) $data["maintenanceJobsMessages_text"] = $_POST['maintenanceJobsMessages_text'];
else $data["maintenanceJobsMessages_file"] = $_POST['maintenanceJobsMessages_file'];

$message = $DBLIB->insert("maintenanceJobsMessages", $data);
if (!$message) finish(false);


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
if (count($job['tagged']) > 0 and isset($data["maintenanceJobsMessages_text"])) {
    foreach ($job['tagged'] as $user) {
        if ($user['users_userid'] == $AUTH->data['users_userid']) continue;
        notify(13,$user['users_userid'], $AUTH->data['instance']['instances_id'], "New message to job " . $job['maintenanceJobs_title'], false, "api/maintenance/job/messageJob-EmailTemplate.twig", ["users_name1" => $AUTH->data['users_name1'], "users_name2"=> $AUTH->data['users_name2'], "job" => $job, "message" => $data]);
    }
}

finish(true);

/** @OA\Post(
 *     path="/maintenance/job/sendMessage.php", 
 *     summary="Send Message", 
 *     description="Send a message to a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:ADD_MESSAGE_TO_JOB
", 
 *     operationId="sendMessage", 
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
 *         name="maintenanceJobsMessages_text",
 *         in="query",
 *         description="Message",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
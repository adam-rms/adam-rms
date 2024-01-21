<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:USERS_TAGGED_IN_JOB") or !isset($_POST['maintenanceJobs_id'])) die("404");


$DBLIB->where("users_userid", $_POST['users_userid']);
$user = $DBLIB->getone("users",["users_userid", "users_name1", "users_name2"]);
if (!$user) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_id"]);
if (!$job) die("404");

if ($job["maintenanceJobs_user_tagged"] != "") {
    $job["maintenanceJobs_user_tagged"] = explode(",", $job["maintenanceJobs_user_tagged"]);
    array_push($job["maintenanceJobs_user_tagged"], $user['users_userid']);
    $job["maintenanceJobs_user_tagged"] = implode(",", $job["maintenanceJobs_user_tagged"]);
} else $job["maintenanceJobs_user_tagged"] = $user['users_userid'];

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_user_tagged" => $job["maintenanceJobs_user_tagged"]]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-TAGGED", "maintenanceJobs", "Tag ". $user['users_name1'] . " " . $user['users_name2'], $AUTH->data['users_userid'],$user['users_userid'],null, $_POST['maintenanceJobs_id']);
finish(true);

/** @OA\Post(
 *     path="/maintenance/job/tagUser.php", 
 *     summary="Tag User", 
 *     description="Tag a user to a maintenance job  
Requires Instance Permission MAINTENANCE_JOBS:EDIT:USERS_TAGGED_IN_JOB
", 
 *     operationId="tagUser", 
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
 *         name="users_userid",
 *         in="query",
 *         description="User ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
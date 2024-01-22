<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    if (!isset($item['value'])) $item['value'] = null; //bug fix for nobody being tagged
    $array[$item['name']] = $item['value'];
}
foreach ($_POST as $name=>$item) {
    $array[$name] = $item; //Use POST too (for the app)
}
$array['instances_id'] = $AUTH->data['instance']["instances_id"];
$array['maintenanceJobs_timestamp_added'] = date('Y-m-d H:i:s');

if ($array['maintenanceJobs_assets'] == "") finish(false, ["code" => "NO-ASSETS", "message"=> "No assets"]);

if ($array["maintenanceJobs_user_tagged"] == "" or !isset($array["maintenanceJobs_user_tagged"])) $array["maintenanceJobs_user_tagged"] = [];
$array["maintenanceJobs_user_taggedFINAL"] = [];
foreach ($array["maintenanceJobs_user_tagged"] as $user) {
    array_push($array["maintenanceJobs_user_taggedFINAL"], $user);
}
$array['maintenanceJobs_user_tagged'] = implode(",", $array['maintenanceJobs_user_taggedFINAL']);

if (!$array['maintenanceJobs_user_creator']) $array['maintenanceJobs_user_creator'] =  $AUTH->data['users_userid'];

//TODO verify these users are in the instance
$result = $DBLIB->insert("maintenanceJobs", array_intersect_key( $array, array_flip( ['maintenanceJobs_assets','maintenanceJobs_title','maintenanceJobs_timestamp_added','maintenanceJobs_user_creator','maintenanceJobs_user_assignedTo','maintenanceJobs_faultDescription','maintenanceJobs_priority',"instances_id","maintenanceJobs_user_tagged"] ) ));
if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert job" . $DBLIB->getlasterror()]);
else {
    $bCMS->auditLog("INSERT", "maintenanceJobs", null, $AUTH->data['users_userid'],null, null,$result);
    $bCMS->auditLog("CHANGE-TITLE", "maintenanceJobs", "Set the title to ". $array['maintenanceJobs_title'], $AUTH->data['users_userid'],null, null,$result);

    $array['maintenanceJobs_id'] = $result;
    $array['tagged'] = [];
    if ($array['maintenanceJobs_user_tagged'] != "") {
        $DBLIB->where("(users.users_userid IN (" . $array['maintenanceJobs_user_tagged'] . "))");
        $DBLIB->orderBy("users.users_name1", "ASC");
        $DBLIB->orderBy("users.users_name2", "ASC");
        $DBLIB->orderBy("users.users_created", "ASC");
        $DBLIB->where("users_deleted", 0);
        $DBLIB->join("userInstances", "users.users_userid=userInstances.users_userid","LEFT");
        $DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id","LEFT");
        $DBLIB->where("instances_id",  $AUTH->data['instance']['instances_id']);
        $DBLIB->where("userInstances.userInstances_deleted",  0);
        $DBLIB->where("(userInstances.userInstances_archived IS NULL OR userInstances.userInstances_archived >= '" . date('Y-m-d H:i:s') . "')");
        $array['tagged'] = $DBLIB->get('users', null, ["users.users_name1", "users.users_name2", "users.users_userid"]);
    }
    if (count($array['tagged']) > 0) {
        foreach ($array['tagged'] as $user) {
            if ($user['users_userid'] == $AUTH->data['users_userid']) continue;
            notify(12,$user['users_userid'], $AUTH->data['instance']['instances_id'], "Tagged you in a job " . $array['maintenanceJobs_title'], false, "api/maintenance/createJob-EmailTemplate.twig", ["users_name1" => $AUTH->data['users_name1'], "users_name2"=> $AUTH->data['users_name2'], "job" => $array]);
        }
    }
    
    finish(true, null, ["maintenanceJobs_id" => $result]);
}

/** @OA\Post(
 *     path="/maintenance/newJob.php", 
 *     summary="New Job", 
 *     description="Create a new maintenance job  
Requires Instance Permission ASSETS:ASSET_TYPES:CREATE
", 
 *     operationId="newJob", 
 *     tags={"maintenance"}, 
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
 *         name="formData",
 *         in="query",
 *         description="Form Data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="maintenanceJobs_title", 
 *                 type="string", 
 *                 description="Title",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_faultDescription", 
 *                 type="string", 
 *                 description="Description",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_priority", 
 *                 type="number", 
 *                 description="Priority",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_status", 
 *                 type="number", 
 *                 description="Status",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_assets", 
 *                 type="json", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_timestamp_due", 
 *                 type="timestamp", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_user_tagged", 
 *                 type="number", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_user_creator", 
 *                 type="number", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_flagAssets", 
 *                 type="boolean", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="maintenanceJobs_blockAssets", 
 *                 type="boolean", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */
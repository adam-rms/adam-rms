<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(18)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    if (!isset($item['value'])) $item['value'] = null; //bug fix for nobody being tagged
    $array[$item['name']] = $item['value'];
}
$array['instances_id'] = $AUTH->data['instance']["instances_id"];
$array['maintenanceJobs_timestamp_added'] = date('Y-m-d H:i:s');
if (count($array['maintenanceJobs_assets']) < 1) finish(false, ["code" => "NO-ASSETS", "message"=> "No assets"]);
$array['maintenanceJobs_assets'] = implode(",", $array['maintenanceJobs_assets']);

if ($array["maintenanceJobs_user_tagged"] == "" or !isset($array["maintenanceJobs_user_tagged"])) $array["maintenanceJobs_user_tagged"] = [];
$array["maintenanceJobs_user_taggedFINAL"] = [];
foreach ($array["maintenanceJobs_user_tagged"] as $user) {
    array_push($array["maintenanceJobs_user_taggedFINAL"], $user);
}
$array['maintenanceJobs_user_tagged'] = implode(",", $array['maintenanceJobs_user_taggedFINAL']);

//TODO verify these users are in the instance
$result = $DBLIB->insert("maintenanceJobs", array_intersect_key( $array, array_flip( ['maintenanceJobs_assets','maintenanceJobs_title','maintenanceJobs_timestamp_added','maintenanceJobs_user_creator','maintenanceJobs_user_assignedTo','maintenanceJobs_faultDescription','maintenanceJobs_priority',"instances_id","maintenanceJobs_user_tagged"] ) ));
if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert job"]);
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
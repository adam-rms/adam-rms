<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(69) or !isset($_POST['maintenanceJobs_id'])) die("404");

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
    $job["maintenanceJobs_user_tagged"] = array_diff($job["maintenanceJobs_user_tagged"], [$user['users_userid']]);
    $job["maintenanceJobs_user_tagged"] = implode(",", $job["maintenanceJobs_user_tagged"]);
} else finish(true); //The array was already empty so might as well give up

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_user_tagged" => $job["maintenanceJobs_user_tagged"]]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-TAGGED", "maintenanceJobs", "Remove tag for ". $user['users_name1'] . " " . $user['users_name2'], $AUTH->data['users_userid'],$user['users_userid'],null, $_POST['maintenanceJobs_id']);
finish(true);
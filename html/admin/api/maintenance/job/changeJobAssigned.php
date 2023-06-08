<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:USER_ASSIGNED_TO_JOB") or !isset($_POST['maintenanceJobs_id'])) die("404");

if ($_POST['users_userid'] != 0) {
    $DBLIB->where("users_userid", $_POST['users_userid']);
    $user = $DBLIB->getone("users",["users_userid", "users_name1", "users_name2"]);
    if (!$user) die("404");
} else $user['users_userid'] = null; //For unassigning the job
$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_user_assignedTo" => $user['users_userid']]);
if (!$update) finish(false);

if ($user['users_userid'] == null) {
    $bCMS->auditLog("CHANGE-ASSIGNED", "maintenanceJobs", "Unassign the job", $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);
} else {
    $bCMS->auditLog("CHANGE-ASSIGNED", "maintenanceJobs", "Assigned the job to ". $user['users_name1'] . " " . $user['users_name2'], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);
    if ($user['users_userid'] != $AUTH->data['users_userid']) notify(15, $user['users_userid'], $AUTH->data['instance']['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " assigned you to a Maintenance Job", false, "api/maintenance/job/changeJobAssigned-EmailTemplate.twig", ["users_name1" => $AUTH->data['users_name1'], "users_name2"=> $AUTH->data['users_name2'], "maintenanceJobs_id" => $bCMS->sanitizeString($_POST['maintenanceJobs_id'])]);
}
finish(true);
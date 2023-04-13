<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:JOB_PRIORITY") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_title",  "maintenanceJobs_id"]);
if (!$job) die("404");

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_priority" => $_POST['maintenanceJobs_priority']]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-PRIORITY", "maintenanceJobs", "Change priority to " . $_POST['maintenanceJobs_priority'], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);


finish(true);
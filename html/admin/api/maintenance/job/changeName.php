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
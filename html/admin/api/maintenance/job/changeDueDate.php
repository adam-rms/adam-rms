<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(67) or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_timestamp_due" => date ("Y-m-d H:i:s", strtotime($_POST['maintenanceJobs_timestamp_due']))]);
if (!$update) finish(false);

$bCMS->auditLog("CHANGE-DUE-DATE", "maintenanceJobs", "Set the due date to ". date ("D jS M Y h:i:sa", strtotime($_POST['maintenanceJobs_timestamp_due'])), $AUTH->data['users_userid'],null, null, $_POST['maintenanceJobs_id']);
finish(true);
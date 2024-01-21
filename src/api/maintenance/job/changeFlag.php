<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("MAINTENANCE_JOBS:EDIT:ASSET_FLAGS") or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_user_tagged", "maintenanceJobs_title",  "maintenanceJobs_id"]);
if (!$job) die("404");

if ($_POST['maintenanceJobs_flagAssets'] != 1 && $_POST['maintenanceJobs_flagAssets'] != 0) finish(false);

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_flagAssets" => $_POST['maintenanceJobs_flagAssets']]);
if (!$update) finish(false);

if ($_POST['maintenanceJobs_flagAssets'] == 1) {
    $bCMS->auditLog("BLOCK-ASSETS", "maintenanceJobs", "Set the project to flag assets", $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);
} else $bCMS->auditLog("UNBLOCK-ASSETS", "maintenanceJobs", "Remove the asset flag from the job", $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);


finish(true);
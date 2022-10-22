<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(75) or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_assets", "maintenanceJobs_id"]);
if (!$job) die("404");

if ($job["maintenanceJobs_assets"] != "") {
    $job["maintenanceJobs_assets"] = explode(",", $job["maintenanceJobs_assets"]);
    $job["maintenanceJobs_assets"] = array_diff($job["maintenanceJobs_assets"], [$_POST['assets_id']]);
    $job["maintenanceJobs_assets"] = implode(",", $job["maintenanceJobs_assets"]);
} else finish(true); //The array was already empty so might as well give up

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_assets" => $job["maintenanceJobs_assets"]]);
if (!$update) finish(false);

$bCMS->auditLog("REMOVE-ASSET", "maintenanceJobs", $_POST['assets_id'], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);
finish(true);
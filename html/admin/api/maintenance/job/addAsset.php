<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(74) or !isset($_POST['maintenanceJobs_id'])) die("404");

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_assets", "maintenanceJobs_id"]);
if (!$job) die("404");
if ($job["maintenanceJobs_assets"] != "") $job["maintenanceJobs_assets"] = explode(",", $job["maintenanceJobs_assets"]);
else $job["maintenanceJobs_assets"] = [];
foreach ($_POST['maintenanceJobs_assets'] as $asset) {
    array_push($job["maintenanceJobs_assets"], $asset);
}
$job["maintenanceJobs_assets"] = implode(",", $job["maintenanceJobs_assets"]);

$DBLIB->where("maintenanceJobs_id", $job['maintenanceJobs_id']);
$update = $DBLIB->update("maintenanceJobs", ["maintenanceJobs_assets" => $job["maintenanceJobs_assets"]]);
if (!$update) finish(false);

$bCMS->auditLog("ADD-ASSETS", "maintenanceJobs", $job["maintenanceJobs_assets"], $AUTH->data['users_userid'],null,null, $_POST['maintenanceJobs_id']);

finish(true);
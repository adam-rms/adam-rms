<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
$DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
$job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_assets",  "maintenanceJobs_id"]);
if (!$job) die("404");

$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
if ($job['maintenanceJobs_assets'] != "") $DBLIB->where("(assets_id NOT IN (" . $job['maintenanceJobs_assets'] . "))");
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
if (strlen($_POST['term']) > 0) {
    $DBLIB->where("(
		assets.assets_tag LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
		OR assetTypes.assetTypes_name LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
    )");
}
$assets = $DBLIB->get("assets", 15, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
if (!$assets) finish(false, ["code" => "LIST-ASSETS-FAIL", "message"=> "Could not search for assets"]);
else {
    finish(true, null, $assets);
}

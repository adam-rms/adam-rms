<?php
require_once __DIR__ . '/../apiHeadSecure.php';


if (!$AUTH->instancePermissionCheck(29) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id", "projects_status","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false);

$thisStatus = $GLOBALS['STATUSES'][$project['projects_status']];
$newStatus = $GLOBALS['STATUSES'][$_POST['projects_status']];
if (!$newStatus) finish(false);

if ($thisStatus["assetsAvailable"] && $newStatus["assetsAvailable"] != true) {
    //We're taking the project from a state where its assets had been released to a state where its assets are now locked down
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
    $assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id", "assetsAssignments.assetsAssignments_id"]);
    if ($assets) {
        $unavailableAssets = [];
        foreach ($assets as $asset) {
            $DBLIB->where("assetsAssignments.assets_id", $asset['assets_id']);
            $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
            $DBLIB->where("projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . ")");
            $DBLIB->where("projects.projects_deleted", 0);
            $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
            $DBLIB->join("assets","assetsAssignments.assets_id=assets.assets_id", "LEFT");
            $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
            $DBLIB->where("((projects_dates_deliver_start >= '" . $project["projects_dates_deliver_start"]  . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_start"] . "'))");
            $assignment = $DBLIB->getone("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assetsAssignments.assets_id","assetsAssignments.projects_id", "assetTypes.assetTypes_name", "projects.projects_name", "assets.assets_tag"]);
            if ($assignment) {
                $assignment['old_assetsAssignments_id'] = $asset['assetsAssignments_id'];
                $unavailableAssets[] = $assignment;
            }
        }
        if (count($unavailableAssets) > 0) {
            finish(true, null, ["changed" => false, "assets" => $unavailableAssets]);
        }
    }

}

$DBLIB->where("projects.projects_id", $project['projects_id']);
$projectupdate =  $DBLIB->update("projects", ["projects.projects_status" => $_POST['projects_status']]);
if (!$projectupdate) finish(false);
$bCMS->auditLog("UPDATE-STATUS", "projects", "Set the status to ". $GLOBALS['STATUSES'][$_POST['projects_status']]['name'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true, null, ["changed" => true]);
<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(27) or !isset($_POST['projects_id'])) die("404");
$newDates = ["projects_dates_deliver_start" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_deliver_start'])), "projects_dates_deliver_end" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_deliver_end']))];

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id", "projects_status","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false);

//We're changing dates so we need to find clashes in the new dates
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
$assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id", "assetsAssignments.assetsAssignments_id"]);
if ($assets) {
    $unavailableAssets = [];
    foreach ($assets as $asset) {
        $DBLIB->where("assetsAssignments.assets_id", $asset['assets_id']);
        $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
        $DBLIB->where("(projects.projects_id != " .  $project['projects_id'] . ")"); //It might be there's a slight overlap with this project so avoid finding that
        $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
        $DBLIB->join("assets","assetsAssignments.assets_id=assets.assets_id", "LEFT");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
        $DBLIB->where("((projects_dates_deliver_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
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


$DBLIB->where("projects.projects_id", $project['projects_id']);
$projectUpdate = $DBLIB->update("projects", $newDates);
if (!$projectUpdate) finish(false);
$bCMS->auditLog("CHANGE-DATE", "projects", "Set the deliver start date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_deliver_start'])) . "\nSet the deliver end date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_deliver_end'])), $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true, null, ["changed" => true]);
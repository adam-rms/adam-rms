<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31)) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
if (isset($_POST['projects_id'])) $DBLIB->where("projects.projects_id", $_POST['projects_id']);
else $DBLIB->where("projects.projects_id", $AUTH->data['users_selectedProjectID']);
$project = $DBLIB->getone("projects", ["projects_id","projects_dates_deliver_start","projects_dates_deliver_end"]);
if (!$project) finish(false);

if (!isset($_POST['assets_id'])) {
    if (!$AUTH->instancePermissionCheck(32)) die("404"); //Can't do an add all
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets_deleted", 0);
    $assetIDs = $DBLIB->get("assets", null, ["assets_id"]);
} else $assetIDs = [0 => ["assets_id" => $_POST['assets_id']]];

foreach ($assetIDs as $asset) {
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("(projects.projects_id = '" . $project['projects_id'] . "' OR projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
    $DBLIB->where("((projects_dates_deliver_start >= '" . $project["projects_dates_deliver_start"] . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $project["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $project["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $project["projects_dates_deliver_start"] . "'))");
    $assignment = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id"]);
    if ($assignment) {
        if (isset($_POST['assets_id'])) finish(false); //Can't add that one
        else continue; //Skip - it's not available
    }
    $insert = $DBLIB->insert("assetsAssignments", ["projects_id" => $project['projects_id'], "assets_id" => $asset['assets_id'], "assetsAssignments_timestamp" => date('Y-m-d H:i:s')]);
    if ($insert) $bCMS->auditLog("ASSIGN-ASSET", "assetsAssignments", $insert, $AUTH->data['users_userid'], null, $project['projects_id']);
    elseif (isset($_POST['assets_id'])) finish(false); //Can't add that one
}
finish(true);
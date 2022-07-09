<?php
/**
 * API
 * \assets\swap.php
 * updates the asset associated with a given asset Assignment
 *
 * Arguments:
 *  - assetsAssignments_id: an Asset Assignment ID
 *  - assets_id: the asset to replace in the assignment
 */

require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31)) die("404");
if (!(isset($_POST['assetsAssignments_id'])) || !(isset($_POST['assets_id']))) finish(false);

$DBLIB->where("assetsAssignments.assetsAssignments_id", $_POST['assetsAssignments_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_dates_deliver_start",NULL,"IS NOT");
$DBLIB->where("projects_dates_deliver_end",NULL,"IS NOT");
$DBLIB->join("assetsAssignments", "assetsAssignments.assets_id = assets.assets_id");
$DBLIB->join("projects", "assetsAssignments.projects_id = projects.projects_id");
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->where('assets_deleted', 0);
$currentAsset = $DBLIB->getone("assets");
if (!$currentAsset) finish(false);

// Check Asset is free and available
$DBLIB->where("assets.assets_id", $_POST['assets_id']);
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assetTypes_id", $currentAsset["assetTypes_id"]);
$DBLIB->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");
$DBLIB->where('assets.assets_deleted', 0);
$assetToSwap = $DBLIB->getone("assets", "assets_id");
if (!$assetToSwap) finish(false);

$DBLIB->where("assetsAssignments.assets_id", $assetToSwap['assets_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_status NOT IN (" . implode(",", $GLOBALS['STATUSES-AVAILABLE']) . "))");
$DBLIB->where("((projects_dates_deliver_start >= '" . $currentAsset["projects_dates_deliver_start"] . "' AND projects_dates_deliver_start <= '" . $currentAsset["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $currentAsset["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $currentAsset["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $currentAsset["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $currentAsset["projects_dates_deliver_start"] . "'))");
$assignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id"]);

$flagsBlocks = assetFlagsAndBlocks($_POST['assets_id']);

if (count($assignments) < 1 and $flagsBlocks['COUNT']['BLOCK'] < 1) {
    $DBLIB->where('assetsAssignments_id', $currentAsset['assetsAssignments_id']);
    $assignment = $DBLIB->update("assetsAssignments", ["assets_id" => $_POST['assets_id']],1);
    finish(true);
} else finish(false);
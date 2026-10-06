<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN")) {
    finish(false, ["code" => "AUTH-ERROR", "message" => "You do not have permission to clone project assets"]);
}
if (!isset($_POST['source_projects_id'], $_POST['target_projects_id']) || !is_numeric($_POST['source_projects_id']) || !is_numeric($_POST['target_projects_id'])) {
    finish(false, ["code" => "PARAM-ERROR", "message" => "Source and target projects are required"]);
}
if ((int)$_POST['source_projects_id'] === (int)$_POST['target_projects_id']) {
    finish(false, ["code" => "PARAM-ERROR", "message" => "Choose a different source project"]);
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['target_projects_id']);
$targetProject = $DBLIB->getOne("projects", ["projects_id"]);
if (!$targetProject) {
    finish(false, ["code" => "PROJECT-NOT-FOUND", "message" => "Target project not found"]);
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['source_projects_id']);
$sourceProject = $DBLIB->getOne("projects", ["projects_id"]);
if (!$sourceProject) {
    finish(false, ["code" => "PROJECT-NOT-FOUND", "message" => "Source project not found"]);
}

$DBLIB->where("assetsAssignments.projects_id", $sourceProject['projects_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.assetsAssignments_linkedTo", null, "IS");
$DBLIB->where("assetsAssignments.assets_id", null, "IS NOT");
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("assets.instances_id", $AUTH->data['instance_ids'], "IN");
$DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
$sourceAssignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id"]);
$sourceAssetIds = array_values(array_unique(array_column($sourceAssignments, "assets_id")));

if (count($sourceAssetIds) === 0) {
    finish(true, null, ["assets" => []]);
}

$DBLIB->where("assetsAssignments.projects_id", $targetProject['projects_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.assets_id", $sourceAssetIds, "IN");
$targetAssignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id"]);
$alreadyAssignedIds = array_column($targetAssignments, "assets_id");

finish(true, null, ["assets" => array_values(array_diff($sourceAssetIds, $alreadyAssignedIds))]);

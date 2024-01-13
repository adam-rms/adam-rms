<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS") or !isset($_POST['projects_id']) or !isset($_POST['assetsAssignments_status']) or !isset($_POST['text']) or strlen($_POST['text']) < 1) finish(false, ["message" => "Missing required fields","code"=>"MISSINGFIELDS"]);

$DBLIB->where("assets_deleted",0);
$DBLIB->where("assets_tag", $_POST["text"]);
$asset = $DBLIB->getone("assets",["assets.assets_id","assetsBarcodes_id"]);
if ($asset and $asset['assets_id'] != null) {
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("projects.projects_id", $_POST['projects_id']);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->update("assetsAssignments", ["assetsAssignmentsStatus_id" => $_POST['assetsAssignments_status']], 1);

    if ($DBLIB->count != 1) {
        finish(false, ["message" => "Asset not assigned to project","code"=>"NOTASSIGNED"]);
    } else {
        $bCMS->auditLog("EDIT-STATUS", "assetsAssignments", $_POST['assetsAssignments_status'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
        finish(true, null, ["assets_id" => $asset['assets_id']]);
    }
} else finish(false, ["message" => "Asset not found","code"=>"NOTFOUND"]);

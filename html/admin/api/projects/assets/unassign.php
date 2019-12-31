<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31) or !isset($_POST['assets_id'])) die("404");

$DBLIB->where("assets_id", $_POST['assets_id']);
$DBLIB->where("projects_id", $AUTH->data['users_selectedProjectID']);
$DBLIB->where("assetsAssignments_deleted", 0);
$assignment = $DBLIB->getone("assetsAssignments", ["assetsAssignments_id", "projects_id"]);
if (!$assignment) finish(false);
else {
    $bCMS->auditLog("UNASSIGN-ASSET", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
    if ($DBLIB->update("assetsAssignments", ["assetsAssignments_deleted" => 1])) finish(true);
    else finish(false);
}
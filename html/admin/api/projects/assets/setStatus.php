<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(53) or !isset($_POST['assetsAssignments_id'])) die("404");

$DBLIB->where("assetsAssignments_id", $_POST['assetsAssignments_id']);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$assignment = $DBLIB->update("assetsAssignments", ["assetsAssignments_status" => $_POST['assetsAssignments_status']]);
if (!$assignment) finish(false);
else {
    $bCMS->auditLog("EDIT-STATUS", "assetsAssignments", $_POST['assetsAssignments_status'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    finish(true);
}

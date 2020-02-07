<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31) or !isset($_POST['assetsAssignments'])) die("404");
foreach ($_POST['assetsAssignments'] as $assignment) {
    $DBLIB->where("assetsAssignments_id", $assignment);
    $DBLIB->where("projects.instances_id IN (" . implode(",", $AUTH->data['instance_ids']) . ")");
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $assignment = $DBLIB->getone("assetsAssignments", ["assetsAssignments_id", "assetsAssignments.projects_id"]);
    if (!$assignment) finish(false);
    else {
        $bCMS->auditLog("UNASSIGN-ASSET", "assetsAssignments", $assignment['assetsAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
        $DBLIB->where("assetsAssignments_id", $assignment['assetsAssignments_id']);
        if (!$DBLIB->update("assetsAssignments", ["assetsAssignments_deleted" => 1])) finish(false);
    }
}
finish(true);
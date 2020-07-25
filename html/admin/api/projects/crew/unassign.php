<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(49) or !isset($_POST['crewAssignments_id'])) die("404");

$DBLIB->where("crewAssignments_id", $_POST['crewAssignments_id']);
$DBLIB->where("crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$assignment = $DBLIB->getone("crewAssignments", ["crewAssignments.crewAssignments_id", "crewAssignments.users_userid", "projects.projects_id", "projects.projects_name"]);
if (!$assignment) finish(false);
else {
    $bCMS->auditLog("UNASSIGN-CREW", "crewAssignments", $assignment['crewAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    if ($assignment["users_userid"]) {
        notify(10, $assignment["users_userid"], $AUTH->data['instance']['instances_id'], $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " removed you as crew from the event " . $assignment['projects_name']);
    }
    $DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
    if ($DBLIB->update("crewAssignments", ["crewAssignments_deleted" => 1])) finish(true);
    else finish(false);
}
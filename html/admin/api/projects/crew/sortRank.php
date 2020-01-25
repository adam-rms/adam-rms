<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(51)) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_name", "projects_dates_use_start","projects_dates_use_end"]);
if (!$project) finish(false);

foreach ($_POST['order'] as $count=>$item) {
    $DBLIB->where("crewAssignments.projects_id",$project['projects_id']);
    $DBLIB->where("crewAssignments.crewAssignments_id",$item);
    if (!$DBLIB->update("crewAssignments", ["crewAssignments_rank" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-CREW", "crewAssignments", null, $AUTH->data['users_userid'],null, $project['projects_id']);
finish(true);

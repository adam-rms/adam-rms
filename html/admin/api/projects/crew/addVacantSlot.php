<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(48)) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id","projects_name", "projects_dates_use_start","projects_dates_use_end"]);
if (!$project) finish(false);

$data = [
    "projects_id" => $project["projects_id"],
    "crewAssignments_role" => $_POST["crewAssignments_role"]
];
$insert = $DBLIB->insert("crewAssignments", $data);
if (!$insert) finish(false);
else {
    $bCMS->auditLog("ASSIGN-CREW-VACANT", "crewAssignments", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);
    finish(true);
}

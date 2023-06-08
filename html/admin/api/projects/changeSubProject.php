<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:CREATE") or !isset($_POST['projects_id'])) die("404");

if($_POST['projects_parent_project_id'] == -1) {
    $parent_id = null;
} else {
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_id", $_POST['projects_parent_project_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $parent = $DBLIB->getOne("projects", ["projects.projects_id"]);
    if (!$parent) finish(false, ["code" => null, "message"=> "Error changing parent project"]);
    else $parent_id = $parent['projects_id'];
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_parent_project_id" => $parent_id]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-SUBPROJECT", "projects", $parent_id, $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
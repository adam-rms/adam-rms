<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:CREATE") or !isset($_POST['projects_name'])) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projectsStatuses_deleted", 0);
$DBLIB->orderBy("projectsStatuses_rank", "ASC");
$projectsStatus = $DBLIB->getValue("projectsStatuses","projectsStatuses_id",1);

$project = $DBLIB->insert("projects", [
    "projects_name" => $_POST['projects_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "projects_created" => date('Y-m-d H:i:s'),
    "projects_manager" => $_POST['projects_manager'],
    "projectsTypes_id" => $_POST['projectsType_id'],
    "projectsStatuses_id" => $projectsStatus,
    "projects_parent_project_id" => ($_POST['projects_parent_project_id'] ?? null),
]);
if (!$project) finish(false, ["code" => "CREATE-PROJECT-FAIL", "message"=> "Could not create new project"]);

$bCMS->auditLog("INSERT", "projects",null, $AUTH->data['users_userid'],null, $project);
$bCMS->auditLog("UPDATE-NAME", "projects", "Set the name to ". $_POST['projects_name'], $AUTH->data['users_userid'],null, $project);
finish(true, null, ["projects_id" => $project]);
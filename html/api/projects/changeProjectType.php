<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(104) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projectsTypes_id",$_POST['projectsTypes_id']);
$DBLIB->where("projectsTypes_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$type = $DBLIB->getOne("projectsTypes",["projectsTypes_id","projectsTypes_name"]);
if (!$type) finish(false);


$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projectsTypes_id" => $type['projectsTypes_id']]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-TYPE", "projects", "Set the project type to ". $type['projectsTypes_name'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DESCRIPTION_AND_SUB_PROJECTS") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_description" => $_POST['projects_description']]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-DESCRIPTION", "projects", "Set the description to ". $_POST['projects_description'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
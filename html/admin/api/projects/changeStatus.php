<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(29) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_status" => $_POST['projects_status']]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-STATUS", "projects", "Set the status to ". $GLOBALS['STATUSES'][$_POST['projects_status']]['name'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
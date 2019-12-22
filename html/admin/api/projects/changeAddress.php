<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(30) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_address" => $_POST['projects_address']]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-ADDRESS", "projects", "Set the address to ". $_POST['projects_address'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
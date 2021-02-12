<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(23) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("users_userid", $_POST['users_userid']);
$user = $DBLIB->getone("users",["users_userid", "users_name1", "users_name2"]);
if (!$user) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_manager" => $user['users_userid']]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-MANAGER", "projects", "Set the project manager to ". $user['users_name1'] . " " . $user['users_name2'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
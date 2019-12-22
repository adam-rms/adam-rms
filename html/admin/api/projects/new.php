<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(21) or !isset($_POST['projects_name'])) die("404");

$project = $DBLIB->insert("projects", [
    "projects_name" => $_POST['projects_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "projects_created" => date('Y-m-d H:i:s'),
    "projects_manager" => $AUTH->data['users_userid']
]);
if (!$project) finish(false, ["code" => "CREATE-PROJECT-FAIL", "message"=> "Could not create new project"]);

$bCMS->auditLog("INSERT", "projects",null, $AUTH->data['users_userid'],null, $project);
$bCMS->auditLog("UPDATE-NAME", "projects", "Set the name to ". $_POST['projects_name'], $AUTH->data['users_userid'],null, $project);
finish(true, null, ["projects_id" => $project]);
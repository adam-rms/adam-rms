<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:CLIENT") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("clients_id", $_POST['clients_id']);
$client = $DBLIB->getone("clients",["clients_id", "clients_name"]);
if (!$client) die("404");


$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.clients_id" => $client['clients_id']]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-CLIENT", "projects", "Set the client to ". $client['clients_name'], $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
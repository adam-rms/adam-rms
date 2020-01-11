<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(45)) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);
if (!$project) finish(false);

$insert = $DBLIB->insert("projectsNotes", ["projects_id" => $project['projects_id'], "projectsNotes_userid" => $AUTH->data['users_userid'], "projectsNotes_title" => $_POST['projectsNotes_title']]);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "projectsNotes", $insert, $AUTH->data['users_userid'],null, $project['projects_id']);
finish(true);
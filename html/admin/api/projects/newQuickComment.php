<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(20) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

$bCMS->auditLog("QUICKCOMMENT", "projects", $bCMS->cleanString($_POST['text']), $AUTH->data['users_userid'],null, $_POST['projects_id']);

finish(true, null, ["projects_id" => $project]);
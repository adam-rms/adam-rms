<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(26) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_deleted" => 1]);
if (!$project) finish(false);

if ($AUTH->data['users_selectedProjectID'] == $_POST['projects_id']) {
    $DBLIB->where("users_userid", $AUTH->data['users_userid']);
    $DBLIB->update("users", ["users_selectedProjectID" => ""]);
}

$bCMS->auditLog("DELETE", "projects", "Deleted the project", $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
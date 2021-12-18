<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(25) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_id = ? OR projects.projects_parent_project_id = ?)", [$_POST['projects_id'],$_POST['projects_id']]);
$project = $DBLIB->update("projects", ["projects.projects_archived" => 1]);
if (!$project) finish(false);

$bCMS->auditLog("ARCHIVE", "projects", "Moved the project and its subprojects to archive", $AUTH->data['users_userid'],null, $_POST['projects_id']);

finish(true);
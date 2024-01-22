<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.projects_dates_use_start" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_start'])), "projects.projects_dates_use_end" => date ("Y-m-d H:i:s", strtotime($_POST['projects_dates_use_end']))]);
if (!$project) finish(false);

$bCMS->auditLog("CHANGE-DATE", "projects", "Set the start date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_use_start'])) . "\nSet the end date to ". date ("D jS M Y h:i:sa", strtotime($_POST['projects_dates_use_end'])), $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
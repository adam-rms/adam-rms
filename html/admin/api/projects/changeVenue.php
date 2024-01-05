<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:ADDRESS") or !isset($_POST['projects_id'])) die("404");

if ($_POST['locations_id'] != "") {
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations.locations_id", $_POST['locations_id']);
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("locations.locations_archived", 0);
    $location = $DBLIB->getone("locations",["locations.locations_id"]);
    if (!$location) finish(false);
    else $locationid = $location['locations_id'];
} else $locationid = null;

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", ["projects.locations_id" => $locationid]);
if (!$project) finish(false);

$bCMS->auditLog("UPDATE-LOCATION", "projects", $locationid, $AUTH->data['users_userid'],null, $_POST['projects_id']);
finish(true);
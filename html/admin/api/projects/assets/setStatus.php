<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(53) or !isset($_POST['assetsAssignments_id']) or !isset($_POST['assetsAssignments_status'])) finish(false);

$DBLIB->where("assetsAssignments_id", $_POST['assetsAssignments_id']);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$assignment = $DBLIB->update("assetsAssignments", ["assetsAssignmentsStatus_id" => $_POST['assetsAssignments_status']]);
if (!$assignment) finish(false);
else finish(true);
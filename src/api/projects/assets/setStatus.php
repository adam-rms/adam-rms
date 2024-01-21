<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:EDIT:ASSIGNMENT_STATUS") || !isset($_POST['assetsAssignments_status']) || !(isset($_POST['assetsAssignments_id']) || isset($_POST['projects_id']))) finish(false);

if (isset($_POST['assetsAssignments_id'])){
    $DBLIB->where("assetsAssignments_id", $_POST['assetsAssignments_id'], (is_array($_POST['assetsAssignments_id'])? 'IN' : '='));
} elseif (isset($_POST['projects_id'])) {
    $DBLIB->where("assetsAssignments.projects_id", $_POST['projects_id']);
} else {
    finish(false);
}

$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$assignment = $DBLIB->update("assetsAssignments", ["assetsAssignmentsStatus_id" => $_POST['assetsAssignments_status']]);

if (!$assignment) finish(false);
else finish(true);
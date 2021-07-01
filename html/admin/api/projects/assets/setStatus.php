<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(53) or !isset($_POST['assetsAssignments_id']) or !isset($_POST['assetsAssignments_status'])) finish(false);

$DBLIB->where("assetsAssignmentsStatus_order", $_POST['assetsAssignments_status']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$status_id = $DBLIB->getOne("assetsassignmentsstatus", "assetsAssignmentsStatus_id");

$DBLIB->where("assetsAssignments_id", $_POST['assetsAssignments_id']);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$assignment = $DBLIB->update("assetsAssignments", $status_id);
if (!$assignment) finish(false);
else finish(true);
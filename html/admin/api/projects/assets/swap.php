<?php
/**
 * API
 * \assets\swap.php
 * updates the asset associated with a given asset Assignment
 *
 * Arguments:
 *  - assetsAssignments_id: an Asset Assignment ID
 *  - assets_id: the asset to replace in the assignment
 */

require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(31)) die("404");
if (!(isset($_POST['assetsAssignments_id'])) || !(isset($_POST['assets_id']))) finish(false);

$DBLIB->where('assetsAssignments_id', $_POST['assetsAssignments_id']);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
$assignment = $DBLIB->update("assetsAssignments", ["assets_id" => $_POST['assets_id']]);

if (!$assignment) finish(false);
else finish(true);
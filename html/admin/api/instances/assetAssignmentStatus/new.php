<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT") or !isset($_POST['statusName']) or !isset($_POST['statusOrder'])) finish(false);

$assignmentsStatus = $DBLIB->insert("assetsAssignmentsStatus", [
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "assetsAssignmentsStatus_name" => $_POST['statusName'],
    "assetsAssignmentsStatus_order" => $_POST['statusOrder'],
]);

if (!$assignmentsStatus) finish(false, ["code" => "ADD-STATUS-FAIL", "message"=> "Could not create new assignment status"]);
finish(true);
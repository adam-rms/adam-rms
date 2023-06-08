<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT") or !isset($_POST['statusName']) or !isset($_POST['statusId'])) finish(false);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("assetsAssignmentsStatus_id", $_POST['statusId']);
$updateQuery = $DBLIB->update("assetsAssignmentsStatus", ["assetsAssignmentsStatus_name" => $_POST['statusName']]);

if (!$updateQuery) finish(false, ["code" => "UPDATE-STATUS-FAIL", "message"=> "Could not Update asset status"]);
finish(true);
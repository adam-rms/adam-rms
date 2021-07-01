<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(53) or !isset($_POST['statusId'])) finish(false,["message"=>"invalid status id or permission"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("assetsAssignmentsStatus_id", $_POST['statusId']);
$updateQuery = $DBLIB->update("assetsassignmentsstatus", ["assetsAssignmentsStatus_deleted" => 1]);
if (!$updateQuery) finish(false, ["code" => "REMOVE-STATUS-FAIL", "message"=> "Could not remove asset status from Business"]);
else finish(true);
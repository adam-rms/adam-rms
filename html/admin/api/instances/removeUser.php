<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(5) or !isset($_POST['userid']) and count($_POST['userid']) > 0) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("userInstances.users_userid", $_POST['userid']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instancePositions.instances_id", $AUTH->data['instance']['instances_id']);
$updateQuery = $DBLIB->update("userInstances", ["userInstances_deleted" => 1]);
if (!$updateQuery) finish(false, ["code" => "REMOVE-USER-TO-INSTANCE-FAIL", "message"=> "Could not remove user from Business"]);
else finish(true);
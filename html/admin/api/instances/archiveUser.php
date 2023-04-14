<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:EDIT:ARCHIVE") or !isset($_POST['userid']) and count($_POST['userid']) > 0) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("userInstances.users_userid", $_POST['userid']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instancePositions.instances_id", $AUTH->data['instance']['instances_id']);
$userInstance = $DBLIB->getone("userInstances",["userInstances_id","userInstances_archived"]);
if (!$userInstance) finish(false, ["code" => "REMOVE-USER-TO-INSTANCE-FAIL", "message"=> "Could not remove user from Business"]);
if ($userInstance["userInstances_archived"] == null) $array = ["userInstances_archived" => date('Y-m-d H:i:s')];
else $array = ["userInstances_archived" => null];
$DBLIB->where("userInstances_id",$userInstance['userInstances_id']);
$updateQuery = $DBLIB->update("userInstances", $array);

if (!$updateQuery) finish(false, ["code" => "ARCHIVE-USER-TO-INSTANCE-FAIL", "message"=> "Could not archive user in Business"]);
else {
    $bCMS->auditLog("ARCHIVE-USER", "users", json_encode($array), $AUTH->data['users_userid'],$_POST['userid'], $AUTH->data['instance']["instances_id"]);
    finish(true);
}
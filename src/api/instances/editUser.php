<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:EDIT:CHANGE_ROLE") or !isset($_POST['userinstanceid']) and count($_POST['userinstanceid']) > 0) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("userInstances.userInstances_id", $_POST['userinstanceid']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$updateQuery = $DBLIB->update("userInstances", ["instancePositions_id" => $_POST['position'], "userInstances_label" => $_POST['label']]);
if (!$updateQuery) finish(false, ["code" => "EDIT-USER-TO-INSTANCE-FAIL", "message"=> "Could not edit user in Business"]);
else finish(true);
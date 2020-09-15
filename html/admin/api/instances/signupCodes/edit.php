<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (!is_numeric($array['signupCodes_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
elseif (!$AUTH->instancePermissionCheck(111)) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("signupCodes_deleted", 0);
$DBLIB->where("signupCodes_id", $array['signupCodes_id']);
$category = $DBLIB->update("signupCodes", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "signupCodes", json_encode($array), $AUTH->data['users_userid']);
finish(true);
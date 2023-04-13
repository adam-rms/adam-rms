<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['signupCodes_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];

$DBLIB->where("signupCodes_name", $array['signupCodes_name']);
if ($DBLIB->getOne("signupCodes",["signupCodes_id"])) finish(false, ["message"=>"Sorry this code is in use"]);

$insert = $DBLIB->insert("signupCodes", $array);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "signupCodes", json_encode($array), $AUTH->data['users_userid']);
finish(true);
<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(115)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modules_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$array['modules_id'] = null;
$array['users_userid'] = $AUTH->data['users_userid'];
$insert = $DBLIB->insert("modules", $array);
if (!$insert) finish(false, ["message" => $DBLIB->getLastError()]);

$bCMS->auditLog("INSERT", "modules", json_encode($array), $AUTH->data['users_userid']);
finish(true);
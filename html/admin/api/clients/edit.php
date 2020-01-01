<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(39)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['clients_id']) <0) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("clients_deleted", 0);
$DBLIB->where("clients_id", $array['clients_id']);
$project = $DBLIB->update("clients", $array);
if (!$project) finish(false);

$bCMS->auditLog("EDIT", "clients", json_encode($array), $AUTH->data['users_userid']);
finish(true);
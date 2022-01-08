<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(116)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modules_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['modules_show']) $array['modules_show'] = 1;
else $array['modules_show'] = 0;

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("modules.modules_id",$array['modules_id']);
$update = $DBLIB->update("modules", $array,1);
if (!$update) finish(false);

$bCMS->auditLog("UPDATE", "modules", json_encode($array), $AUTH->data['users_userid']);
finish(true);
<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(99)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['locations_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations_id",$array['locations_id']);
$group = $DBLIB->update("locations", $array,1);
if (!$group) finish(false);

$bCMS->auditLog("UPDATE", "locations", json_encode($array), $AUTH->data['users_userid']);
finish(true);
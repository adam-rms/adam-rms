<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(94)) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetGroups_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['personal'] == "on") $array['users_userid'] = $AUTH->data['users_userid'];
else $array['users_userid'] = null;
unset($array['personal']);

$DBLIB->where("assetGroups_id",$array['assetGroups_id']);
$DBLIB->where("(users_userid IS NULL OR users_userid = '" . $AUTH->data['users_userid'] . "')");
$DBLIB->where('instances_id',$AUTH->data['instance']['instances_id']);
$DBLIB->where("assetGroups_deleted",0);
$group = $DBLIB->update("assetGroups", $array,1);
if (!$group) finish(false);

$bCMS->auditLog("UPDATE", "assetGroups", json_encode($array), $AUTH->data['users_userid']);
finish(true);
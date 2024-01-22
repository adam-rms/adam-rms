<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_GROUPS:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetGroups_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];

if ($array['personal'] == "on") $array['users_userid'] = $AUTH->data['users_userid'];
unset($array['personal']);

$group = $DBLIB->insert("assetGroups", $array);
if (!$group) finish(false);

$bCMS->auditLog("INSERT", "assetGroups", json_encode($array), $AUTH->data['users_userid']);
finish(true);
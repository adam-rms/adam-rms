<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(59)) die("Sorry - you can't access this page");
$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assets_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets_id", $array['assets_id']);
$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);

$result = $DBLIB->update("assets", array_intersect_key( $array, array_flip( ['assets_linkedTo','assetTypes_id','assets_notes','asset_definableFields_1','asset_definableFields_2','asset_definableFields_3','asset_definableFields_4','asset_definableFields_5','asset_definableFields_6','asset_definableFields_7','asset_definableFields_8','asset_definableFields_9','asset_definableFields_10','assets_value','assets_dayRate','assets_weekRate','assets_mass'] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset"]);
else {
    $bCMS->auditLog("EDIT-ASSET", "assetTypes", json_encode($array), $AUTH->data['users_userid'],null, $array['assets_id']);
    finish(true);
}
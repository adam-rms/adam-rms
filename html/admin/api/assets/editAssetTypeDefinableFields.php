<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(58)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

if (!$AUTH->serverPermissionCheck("ASSETS:EDIT:ANY_ASSET_TYPE")) {
    $DBLIB->where("(instances_id IS NOT NULL)");
    $DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
}
$DBLIB->where("assetTypes_id",$array['assetTypes_id']);
$array['assetTypes_definableFields'] = array();
$array['assetTypes_definableFields'][0] = $array['asset_definableFields_1'];
$array['assetTypes_definableFields'][1] = $array['asset_definableFields_2'];
$array['assetTypes_definableFields'][2] = $array['asset_definableFields_3'];
$array['assetTypes_definableFields'][3] = $array['asset_definableFields_4'];
$array['assetTypes_definableFields'][4] = $array['asset_definableFields_5'];
$array['assetTypes_definableFields'][5] = $array['asset_definableFields_6'];
$array['assetTypes_definableFields'][6] = $array['asset_definableFields_7'];
$array['assetTypes_definableFields'][7] = $array['asset_definableFields_8'];
$array['assetTypes_definableFields'][8] = $array['asset_definableFields_9'];
$array['assetTypes_definableFields'][9] = $array['asset_definableFields_10'];
$array['assetTypes_definableFields'] = implode(",", $array['assetTypes_definableFields']);
$result = $DBLIB->update("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_definableFields'] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset type"]);
else {
    $bCMS->auditLog("EDIT-ASSET-TYPE-DEFINABLEFIELDS", "assetTypes", json_encode($array), $AUTH->data['users_userid'],null, $array['assetTypes_id']);
    finish(true);
}
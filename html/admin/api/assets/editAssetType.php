<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(58)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if (!$AUTH->permissionCheck(19)) {
    $DBLIB->where("(instances_id IS NOT NULL)");
    $DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
}
$DBLIB->where("assetTypes_id",$array['assetTypes_id']);
if ($array['assetTypes_mass'] == "") $array['assetTypes_mass'] = null; //This is odd but seems to fix an error
if ($array['assetTypes_dayRate'] == "") $array['assetTypes_dayRate'] = null; //This is odd but seems to fix an error
if ($array['assetTypes_weekRate'] == "") $array['assetTypes_weekRate'] = null; //This is odd but seems to fix an error

$result = $DBLIB->update("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_name','assetCategories_id','assetTypes_productLink','manufacturers_id','assetTypes_description','assetTypes_definableFields','assetTypes_mass','assetTypes_inserted',"assetTypes_dayRate","assetTypes_weekRate","assetTypes_value"] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset type"]);
else {
    $bCMS->auditLog("EDIT-ASSET-TYPE", "assetTypes", json_encode($array), $AUTH->data['users_userid'],null, $array['assetTypes_id']);
    finish(true);
}
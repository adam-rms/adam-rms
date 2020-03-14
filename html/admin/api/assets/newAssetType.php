<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck(18)) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['instances_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$array['instances_id'] = $AUTH->data['instance']["instances_id"];
$array['assetTypes_inserted'] = date('Y-m-d H:i:s');

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

if ($array['assetTypes_mass'] == "") $array['assetTypes_mass'] = null; //This is odd but seems to fix an error
if ($array['assetTypes_dayRate'] == "") $array['assetTypes_dayRate'] = null; //This is odd but seems to fix an error
if ($array['assetTypes_weekRate'] == "") $array['assetTypes_weekRate'] = null; //This is odd but seems to fix an error


$result = $DBLIB->insert("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_name','assetCategories_id','manufacturers_id','assetTypes_description','assetTypes_definableFields','assetTypes_mass','assetTypes_inserted',"instances_id","assetTypes_dayRate","assetTypes_weekRate","assetTypes_value"] ) ));
if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert asset type"]);
else finish(true, null, ["assetTypes_id" => $result]);
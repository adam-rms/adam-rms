<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:CREATE")) die("Sorry - you can't access this page");
$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$array['assets_inserted'] = date('Y-m-d H:i:s');

$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->where("assetTypes_id", $array['assetTypes_id']);
$asset = $DBLIB->getone("assetTypes");
if (!$asset) finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message"=> "Could not find asset type"]);

if (isset($array['assets_tag']) and $array['assets_tag'] != null) {
    $DBLIB->where("assets.instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_tag", $array['assets_tag']);
    $DBLIB->where("assets.assets_deleted", 0); //Deleted assets can't be restored, so can be used
    $duplicateAssetTag = $DBLIB->getValue ("assets", "count(*)");
    if ($duplicateAssetTag > 0) finish(false, ["code" => "INSERT-FAIL", "message"=> "Sorry that tag you chose was a duplicate - please choose another one"]);
} else $array['assets_tag'] = generateNewTag();

$result = $DBLIB->insert("assets", array_intersect_key( $array, array_flip( ['assets_tag','assetTypes_id','assets_notes','instances_id','asset_definableFields_1','asset_definableFields_2','asset_definableFields_3','asset_definableFields_4','asset_definableFields_5','asset_definableFields_6','asset_definableFields_7','asset_definableFields_8','asset_definableFields_9','asset_definableFields_10','assets_assetGroups'] ) ));

if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert asset"]);
else finish(true, null, ["assets_id" => $result, "assets_tag" => $array['assets_tag'], "assetTypes_id" => $array['assetTypes_id']]);
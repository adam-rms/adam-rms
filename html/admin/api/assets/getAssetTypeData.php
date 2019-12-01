<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);


$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->where("assetTypes_id", $_POST['term']);
$assets = $DBLIB->getone("assetTypes");

if ($assets['assetTypes_definableFields']) $assets['assetCategories_fields'] = explode(",", $assets['assetTypes_definableFields']);
else $assets['assetCategories_fields'] = [];

if (!$assets) finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message"=> "Could not find asset"]);
else finish(true, null, $assets);

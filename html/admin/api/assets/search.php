<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);


if (isset($_POST['manufacturer'])) $DBLIB->where("manufacturers_id", $_POST['manufacturer']);
$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->where("(
    assetTypes_description LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%' OR
    assetTypes_name LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%' 
)");
$assets = $DBLIB->get("assetTypes", 10, ["assetTypes_name", "assetTypes_id", "assetCategories_name"]);
if (!$assets) finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message"=> "Could not search"]);
else finish(true, null, $assets);

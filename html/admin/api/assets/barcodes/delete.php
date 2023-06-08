<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_BARCODES:DELETE")) die("Sorry - you can't access this page");

if (!isset($_POST['barcodes_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("assets","assetsBarcodes.assets_id=assets.assets_id","LEFT");
$DBLIB->where("assetsBarcodes.assetsBarcodes_id", $_POST['barcodes_id']);
$DBLIB->where("assetsBarcodes.assetsBarcodes_deleted", 0);
$result = $DBLIB->update("assetsBarcodes", ["assetsBarcodes_deleted" => 1]);
if (!$result) finish(false, ["code" => "DELETE-FAIL", "message"=> "Could not delete barcode"]);
else finish(true);
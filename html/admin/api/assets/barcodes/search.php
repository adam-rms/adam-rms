<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

$DBLIB->where("locationsBarcodes_value", $_POST['text']);
$DBLIB->where("locationsBarcodes_type", $_POST['type']);
$DBLIB->where("locationsBarcodes_deleted",0);
$locationBarcode = $DBLIB->getone("locationsBarcodes",["locations_id","locationsBarcodes_id"]);
if ($locationBarcode) {
    $DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("locations.locations_deleted", 0);
    $DBLIB->where("locations.locations_id",$locationBarcode['locations_id']);
    $location = $DBLIB->getOne("locations", ["locations_id","locations_name"]);
    if ($location) {
        $location['barcode'] = $locationBarcode;
        //Location has been found
    } else $location = false;
} else $location = false;


$DBLIB->where("assetsBarcodes_value", $_POST['text']);
$DBLIB->where("assetsBarcodes_type", $_POST['type']);
$DBLIB->where("assetsBarcodes_deleted",0);
$barcode = $DBLIB->getone("assetsBarcodes",["assets_id","assetsBarcodes_id"]);
if (!$barcode) finish(true, null, ["asset" => false, "barcode" => false,"location" => ($location ? $location : false)]);

$scan = [
    "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
    "users_userid" => $AUTH->data['users_userid'],
    "assetsBarcodesScans_timestamp" => date('Y-m-d H:i:s'),
    "locationsBarcodes_id" => ($_POST['locationType'] == "barcode" ? $_POST['location'] : null),
    "location_assets_id" => ($_POST['locationType'] == "asset" ? $_POST['location'] : null),
    "assetsBarcodes_customLocation" => ($_POST['locationType'] == "Custom" ? $_POST['location'] : null)
];
$DBLIB->insert("assetsBarcodesScans",$scan);

if ($barcode['assets_id'] == null) finish(true, null, ["asset" => false, "barcode" => $barcode['assetsBarcodes_id'],"location" => ($location ? $location : false)]);

$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id IN (" . implode(",",$AUTH->data['instance_ids']) . "))");
$DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$DBLIB->where("assets.assets_id",$barcode['assets_id']);
$asset = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
if (!$asset) finish(true, null, ["asset" => false, "barcode" => $barcode['assetsBarcodes_id'],"location" => ($location ? $location : false)]);

//Format asset tag
if ($asset['assets_tag'] == null) $asset['tag']= '';
if ($asset['assets_tag'] <= 9999) $asset['tag'] = "A-" . sprintf('%04d', $asset['assets_tag']);
else $asset['tag'] = "A-" . $asset['assets_tag'];

finish(true, null, ["asset" => $asset, "barcode" => $barcode['assetsBarcodes_id'],"location" => ($location ? $location : false)]);

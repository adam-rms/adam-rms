<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!isset($_POST['text']) or !isset($_POST['type']) or strlen($_POST['text']) < 1 or strlen($_POST['type']) < 1) finish(false);

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

//See if Barcode is in database
$DBLIB->where("assetsBarcodes_value", $_POST['text']);
$DBLIB->where("assetsBarcodes_type", $_POST['type']);
$DBLIB->where("assetsBarcodes_deleted",0);
$barcode = $DBLIB->getone("assetsBarcodes",["assets_id","assetsBarcodes_id"]);
if ($barcode) {
    $scan = [
        "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "assetsBarcodesScans_timestamp" => date('Y-m-d H:i:s'),
        "locationsBarcodes_id" => ($_POST['locationType'] == "barcode" ? $_POST['location'] : null),
        "location_assets_id" => ($_POST['locationType'] == "asset" ? $_POST['location'] : null),
        "assetsBarcodes_customLocation" => ($_POST['locationType'] == "Custom" ? $_POST['location'] : null)
    ];
    $DBLIB->insert("assetsBarcodesScans",$scan);
} else $barcode = false;

//If it's in the database and has an asset, return that asset
if ($barcode and $barcode['assets_id'] != null) {
    $DBLIB->where("assets.instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes","assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_id",$barcode['assets_id']);
    $asset = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
} else $asset = false;

if (!$asset) {
    $DBLIB->where("assets.instances_id",$AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_tag", $_POST['text']);
    $assetSuggest = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
    if (!$assetSuggest) $assetSuggest = false; //Not sure why this is needed or happens
} else $assetSuggest = false;

finish(true, null, ["asset" => $asset, "assetSuggest" => $assetSuggest, "barcode" => ($barcode ? $barcode['assetsBarcodes_id'] : false),"location" => $location]);

/** @OA\Post(
 *     path="/assets/barcodes/search.php", 
 *     summary="Barcode Asset Search", 
 *     description="Search for an Asset using a barcode
", 
 *     operationId="barcodeSearch", 
 *     tags={"barcodes"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="text",
 *         in="query",
 *         description="The barcode value",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="The barcode type",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="locationType",
 *         in="query",
 *         description="What the location is",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!isset($_POST['text']) or !isset($_POST['type']) or strlen($_POST['text']) < 1 or strlen($_POST['type']) < 1) finish(false);

// See if the barcode is a location
$DBLIB->where("locationsBarcodes_value", $_POST['text']);
$DBLIB->where("locationsBarcodes_type", $_POST['type']);
$DBLIB->where("locationsBarcodes_deleted", 0);
$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->join("locations", "locations.locations_id=locationsBarcodes.locations_id", "LEFT");
$location = $DBLIB->getone("locationsBarcodes", ["locationsBarcodes.locations_id", "locationsBarcodes.locationsBarcodes_id", "locations.locations_name"]);
if ($location) {
    $location['barcode'] = $location['locationsBarcodes_id'];
    //Location has been found
} else $location = false;

//See if Barcode is in database
$DBLIB->where("assetsBarcodes_value", $_POST['text']);
$DBLIB->where("assetsBarcodes_type", $_POST['type']);
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']); // Restrict only to the current instance. This may need revisiting with asset dispatch (might be worth doing some seperate logic for that page)
$DBLIB->join("assets", "assets.assets_id=assetsBarcodes.assets_id", "LEFT");
$DBLIB->where("assetsBarcodes_deleted", 0);
$barcode = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes.assets_id", "assetsBarcodes.assetsBarcodes_id"]);
if ($barcode) {
    $scan = [
        "assetsBarcodes_id" => $barcode['assetsBarcodes_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "assetsBarcodesScans_timestamp" => date('Y-m-d H:i:s'),
        "locationsBarcodes_id" => ($_POST['locationType'] == "barcode" ? $_POST['location'] : null),
        "location_assets_id" => ($_POST['locationType'] == "asset" ? $_POST['location'] : null),
        "assetsBarcodes_customLocation" => ($_POST['locationType'] == "Custom" ? $_POST['location'] : null),
        "assetsBarcodesScans_barcodeWasScanned" => ($_POST['scanned'] == "true" ? 1 : 0),
        "assetsBarcodesScans_validation" => isset($_POST['validation']) ? $_POST['validation'] : null,
    ];
    $DBLIB->insert("assetsBarcodesScans", $scan);
} else $barcode = false;

//If it's in the database and has an asset, return that asset
if ($barcode) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_id", $barcode['assets_id']);
    $asset = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
} else $asset = false;

if (!$asset) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->where("assets.assets_tag", $_POST['text']);
    $assetSuggest = $DBLIB->getone("assets", ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
    if (!$assetSuggest) $assetSuggest = false;
} else $assetSuggest = false;

finish(true, null, ["asset" => $asset, "assetSuggest" => $assetSuggest, "barcode" => ($barcode ? $barcode['assetsBarcodes_id'] : false), "location" => $location]);

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
 *         description="What the location is - should be 'barcode', 'asset' or 'Custom' ",
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *    @OA\Parameter(
 *         name="location",
 *         in="query",
 *         description="a locationBarcodeId, assetBarcodeId or custom string",
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */

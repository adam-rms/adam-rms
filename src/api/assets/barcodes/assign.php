<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_BARCODES:EDIT:ASSOCIATE_UNNASOCIATED_BARCODES_WITH_ASSETS")) die("Sorry - you can't access this page");

if (!isset($_POST['id'])) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assets.assets_id", $_POST['id']);
$asset = $DBLIB->getone("assets", ["assets_id", "assetTypes_id"]);
if (!$asset) finish(false, ["message" => "Asset not found"]);

$barcode = $DBLIB->insert("assetsBarcodes", [
    "assetsBarcodes_value" => $_POST['text'],
    "assetsBarcodes_type" => $_POST['type'],
    "users_userid" => $AUTH->data['users_userid'],
    "assetsBarcodes_added" => date("Y-m-d H:i:s"),
    "assets_id" => $asset['assets_id']
]);
if (!$barcode) finish(false, ["message" => "Barcode insert error"]);
else $barcodeId = $barcode;
$asset['barcodeId'] = $barcode; // Grab the barcode ID now it has been associated

$bCMS->auditLog("ASSOCIATE", "assetsBarcodes", $barcodeId . " set to " . $asset['assets_id'], $AUTH->data['users_userid'], null);
finish(true, null, $asset);


/** @OA\Post(
 *     path="/assets/barcodes/assign.php", 
 *     summary="Assign Barcode", 
 *     description="Assign a barcode to an asset  
Requires Instance Permission ASSETS:ASSET_BARCODES:EDIT:ASSOCIATE_UNNASOCIATED_BARCODES_WITH_ASSETS
", 
 *     operationId="assignBarcode", 
 *     tags={"barcodes"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *     ),  
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="The id of the Asset to assign a barcode to",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="barcodeid",
 *         in="query",
 *         description="An ID of an existing barcode or false",
 *         required="true", 
 *         @OA\Schema(
 *             type="undefined"), 
 *         ), 
 *     @OA\Parameter(
 *         name="text",
 *         in="query",
 *         description="the value of a new barcode",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         description="The Barcode type",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */

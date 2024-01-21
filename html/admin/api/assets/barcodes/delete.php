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

/** @OA\Post(
 *     path="/assets/barcodes/delete.php", 
 *     summary="Delete Barcode", 
 *     description="Delete a barcode  
Requires Instance Permission ASSETS:ASSET_BARCODES:DELETE
", 
 *     operationId="deleteBarcode", 
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
 *         name="barcodes_id",
 *         in="query",
 *         description="the ID to remove",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
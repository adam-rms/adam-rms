<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:CREATE")) die("Sorry - you can't access this page");
$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$array['assets_inserted'] = date('Y-m-d H:i:s');

$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->where("assetTypes_id", $array['assetTypes_id']);
$asset = $DBLIB->getone("assetTypes");
if (!$asset) finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message" => "Could not find asset type"]);

if (isset($array['assets_tag']) and $array['assets_tag'] != null) {
    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assets_tag", $array['assets_tag']);
    $DBLIB->where("assets.assets_deleted", 0); //Deleted assets can't be restored, so can be used
    $duplicateAssetTag = $DBLIB->getValue("assets", "count(*)");
    if ($duplicateAssetTag > 0) finish(false, ["code" => "INSERT-FAIL", "message" => "Sorry that tag you chose was a duplicate - please choose another one"]);
} else $array['assets_tag'] = generateNewTag();

$result = $DBLIB->insert("assets", array_intersect_key($array, array_flip(['assets_tag', 'assetTypes_id', 'assets_notes', 'instances_id', 'asset_definableFields_1', 'asset_definableFields_2', 'asset_definableFields_3', 'asset_definableFields_4', 'asset_definableFields_5', 'asset_definableFields_6', 'asset_definableFields_7', 'asset_definableFields_8', 'asset_definableFields_9', 'asset_definableFields_10', 'assets_assetGroups'])));

if (!$result) finish(false, ["code" => "INSERT-FAIL", "message" => "Could not insert asset"]);

function checkDuplicate($value, $type)
{
    global $DBLIB;
    $DBLIB->where("assetsBarcodes_value", $value);
    $DBLIB->where("assetsBarcodes_type", $type);
    $result = $DBLIB->getone("assetsBarcodes", ["assetsBarcodes_id"]);
    if ($result) return true;
    else return false;
}

//Generate asset barcode

$assetBarcodeData = [
    "assetsBarcodes_value" => $array['assets_tag'],
    "assetsBarcodes_type" => "QR_CODE",
    "assets_id" => $result,
    "users_userid" => $AUTH->data['users_userid'],
    "assetsBarcodes_added" => date("Y-m-d H:i:s")
];
while (checkDuplicate($assetBarcodeData["assetsBarcodes_value"], $assetBarcodeData["assetsBarcodes_type"])) {
    $assetBarcodeData["assetsBarcodes_value"] = mt_rand(1000, 999999); //Duplicate, so generate a hopefully random number as a replacement
}
$insert = $DBLIB->insert("assetsBarcodes", $assetBarcodeData);
//We don't really mind if the insert fails, we can always generate another one later...

finish(true, null, ["assets_id" => $result, "assets_tag" => $array['assets_tag'], "assetTypes_id" => $array['assetTypes_id']]);

/** @OA\Post(
 *     path="/assets/newAssetFromType.php", 
 *     summary="Create Asset From Type", 
 *     description="Creates an asset from an asset type
Requires Instance Permission 17 ASSETS:CREATE
", 
 *     operationId="createAssetFromType", 
 *     tags={"assets"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
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
 *                     property="assets_id", 
 *                     type="integer", 
 *                     description="The ID of the asset",
 *                 ),
 *                 @OA\Property(
 *                     property="assets_tag", 
 *                     type="string", 
 *                     description="The tag of the asset",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_id", 
 *                     type="integer", 
 *                     description="The ID of the asset type",
 *                 ),
 *         ),
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
 *                     property="code", 
 *                     type="string", 
 *                     description="The error code",
 *                 ),
 *                 @OA\Property(
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *         ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The data to create the asset from",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assetTypes_id", 
 *                 type="integer", 
 *                 description="The ID of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assets_tag", 
 *                 type="string", 
 *                 description="The tag of the asset",
 *             ),
 *             @OA\Property(
 *                 property="assets_notes", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assets_assetGroups", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_1", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_2", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_3", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_4", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_5", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_6", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_7", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_8", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_9", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="asset_definableFields_10", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */

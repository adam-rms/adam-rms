<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_TYPES:EDIT")) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetTypes_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

if (!$AUTH->serverPermissionCheck("ASSETS:EDIT:ANY_ASSET_TYPE")) {
    $DBLIB->where("(instances_id IS NOT NULL)");
    $DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
}
$DBLIB->where("assetTypes_id",$array['assetTypes_id']);
$array['assetTypes_definableFields'] = array();
$array['assetTypes_definableFields'][0] = $array['asset_definableFields_1'];
$array['assetTypes_definableFields'][1] = $array['asset_definableFields_2'];
$array['assetTypes_definableFields'][2] = $array['asset_definableFields_3'];
$array['assetTypes_definableFields'][3] = $array['asset_definableFields_4'];
$array['assetTypes_definableFields'][4] = $array['asset_definableFields_5'];
$array['assetTypes_definableFields'][5] = $array['asset_definableFields_6'];
$array['assetTypes_definableFields'][6] = $array['asset_definableFields_7'];
$array['assetTypes_definableFields'][7] = $array['asset_definableFields_8'];
$array['assetTypes_definableFields'][8] = $array['asset_definableFields_9'];
$array['assetTypes_definableFields'][9] = $array['asset_definableFields_10'];
$array['assetTypes_definableFields'] = implode(",", $array['assetTypes_definableFields']);
$result = $DBLIB->update("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_definableFields'] ) ));
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update asset type"]);
else {
    $bCMS->auditLog("EDIT-ASSET-TYPE-DEFINABLEFIELDS", "assetTypes", json_encode($array), $AUTH->data['users_userid'],null, $array['assetTypes_id']);
    finish(true);
}

/** @OA\Post(
 *     path="/assets/editAssetTypeDefinableFields.php", 
 *     summary="Edit an Asset Type's Definable Fields", 
 *     description="Edits an asset type's definable fields  
Requires Instance Permission ASSETS:ASSET_TYPES:EDIT
", 
 *     operationId="editAssetTypeDefinableFields", 
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
 *                     property="message", 
 *                     type="null", 
 *                     description="an empty array",
 *                 ),
 *             ),
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
 *         name="formData",
 *         in="query",
 *         description="The data to update the asset type's definable fields with",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assetTypes_id", 
 *                 type="integer", 
 *                 description="The ID of the asset type to update",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_1", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_2", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_3", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_4", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_5", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_6", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_7", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_8", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_9", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_10", 
 *                 type="string", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */
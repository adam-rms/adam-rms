<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_TYPES:CREATE")) die("Sorry - you can't access this page");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['manufacturers_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if (strlen($array['assetTypes_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No asset type name provided"]);

$array['instances_id'] = $AUTH->data['instance']["instances_id"];
$array['assetTypes_inserted'] = date('Y-m-d H:i:s');

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

use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['assetTypes_value'] = $moneyParser->parse(($array['assetTypes_value'] ?? '0.00'), $AUTH->data['instance']['instances_config_currency'])->getAmount();
$array['assetTypes_dayRate'] = $moneyParser->parse(($array['assetTypes_dayRate'] ?? '0.00'), $AUTH->data['instance']['instances_config_currency'])->getAmount();
$array['assetTypes_weekRate'] = $moneyParser->parse(($array['assetTypes_weekRate'] ?? '0.00'), $AUTH->data['instance']['instances_config_currency'])->getAmount();

$result = $DBLIB->insert("assetTypes", array_intersect_key( $array, array_flip( ['assetTypes_name','assetTypes_productLink','assetCategories_id','manufacturers_id','assetTypes_description','assetTypes_definableFields','assetTypes_mass','assetTypes_inserted',"instances_id","assetTypes_dayRate","assetTypes_weekRate","assetTypes_value"] ) ));
if (!$result) finish(false, ["code" => "INSERT-FAIL", "message"=> "Could not insert asset type"]);
else finish(true, null, ["assetTypes_id" => $result]);

/** @OA\Post(
 *     path="/assets/newAssetType.php", 
 *     summary="Create Asset Type", 
 *     description="Creates an asset type  
Requires Instance Permission ASSETS:ASSET_TYPES:CREATE
", 
 *     operationId="createAssetType", 
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
 *         description="The data to create the asset type from",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="assetTypes_name", 
 *                 type="string", 
 *                 description="The name of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_description", 
 *                 type="string", 
 *                 description="The description of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetCategories_id", 
 *                 type="integer", 
 *                 description="The ID of the asset category",
 *             ),
 *             @OA\Property(
 *                 property="manufacturers_id", 
 *                 type="integer", 
 *                 description="The ID of the manufacturer",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_productLink", 
 *                 type="string", 
 *                 description="Link to the website of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_1", 
 *                 type="string", 
 *                 description="The first definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_2", 
 *                 type="string", 
 *                 description="The second definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_3", 
 *                 type="string", 
 *                 description="The third definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_4", 
 *                 type="string", 
 *                 description="The fourth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_5", 
 *                 type="string", 
 *                 description="The fifth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_6", 
 *                 type="string", 
 *                 description="The sixth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_7", 
 *                 type="string", 
 *                 description="The seventh definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_8", 
 *                 type="string", 
 *                 description="The eighth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_9", 
 *                 type="string", 
 *                 description="The ninth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_definableFields_10", 
 *                 type="string", 
 *                 description="The tenth definable field",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_mass", 
 *                 type="number", 
 *                 description="The weight of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_inserted", 
 *                 type="string", 
 *                 description="The date the asset type was inserted",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_dayRate", 
 *                 type="number", 
 *                 description="The day rate of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_weekRate", 
 *                 type="number", 
 *                 description="The week rate of the asset type",
 *             ),
 *             @OA\Property(
 *                 property="assetTypes_value", 
 *                 type="number", 
 *                 description="The value of the asset type",
 *             ),
 *         ),
 *     ), 
 * )
 */
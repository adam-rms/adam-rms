<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['term'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);


$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("(assetTypes.instances_id IS NULL OR assetTypes.instances_id = '" . $AUTH->data['instance']['instances_id'] . "')");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$DBLIB->where("assetTypes_id", $_POST['term']);
$assets = $DBLIB->getone("assetTypes",['assetTypes_definableFields','assetTypes_id']);

if ($assets['assetTypes_definableFields']) $assets['assetCategories_fields'] = explode(",", $assets['assetTypes_definableFields']);
else $assets['assetCategories_fields'] = [];

if (!$assets) finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message"=> "Could not find asset"]);
else finish(true, null, $assets);

/** @OA\Post(
 *     path="/assets/getAssetTypeData.php", 
 *     summary="Get Asset Type Data", 
 *     description="Gets data for an asset type
", 
 *     operationId="getAssetTypeData", 
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
 *                 @OA\Property(
 *                     property="assetTypes_name", 
 *                     type="string", 
 *                     description="The name of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="assetCategories_id", 
 *                     type="integer", 
 *                     description="The ID of the asset category",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_productLink", 
 *                     type="string", 
 *                     description="Link to the website of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="manufacturers_id", 
 *                     type="integer", 
 *                     description="The ID of the manufacturer",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_description", 
 *                     type="string", 
 *                     description="The description of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_definableFields", 
 *                     type="string", 
 *                     description="A comma-separated list of 10 definable field names",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_mass", 
 *                     type="number", 
 *                     description="The weight of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_inserted", 
 *                     type="string", 
 *                     description="The date the asset type was inserted",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_value", 
 *                     type="string", 
 *                     description="The value of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_dayRate", 
 *                     type="number", 
 *                     description="The day rate of the asset type",
 *                 ),
 *                 @OA\Property(
 *                     property="assetTypes_weekRate", 
 *                     type="number", 
 *                     description="The week rate of the asset type",
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
 *                     property="error", 
 *                     type="array", 
 *                     description="An Array containing an error code and a message",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="term",
 *         in="query",
 *         description="The ID of the asset type to get data for",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
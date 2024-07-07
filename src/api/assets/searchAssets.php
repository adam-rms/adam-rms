<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
if (isset($_POST['term'])) {
    $DBLIB->where("(
        manufacturers_name LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%' OR
		assetTypes_description LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%' OR
		assets_notes LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%' OR
		assets_tag LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "' OR
        assetTypes_name LIKE '%" . $bCMS->sanitizeStringMYSQL($_POST['term']) . "%'
    )");
} else $DBLIB->orderBy("assetTypes_name", "ASC");
$assets = $DBLIB->get("assets", 15, ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id", "assetCategories.assetCategories_name", "assetCategoriesGroups.assetCategoriesGroups_name", "manufacturers.manufacturers_name"]);
if (!$assets) finish(false, ["code" => "LIST-ASSETS-FAIL", "message" => "Could not search"]);
$assetsReturn = [];
foreach ($assets as $asset) {
    $asset['tag'] = $asset['assets_tag'];

    if (isset($_POST['barcodes'])) {
        $DBLIB->where('assets_id', $asset['assets_id']);
        $DBLIB->where('assetsBarcodes_deleted', 0);
        $barcodes = $DBLIB->get('assetsBarcodes', null, ['assetsBarcodes_value', 'assetsBarcodes_type']);
        $asset['barcodes'] = $barcodes;
    }

    $assetsReturn[] = $asset;
}
finish(true, null, $assetsReturn);

/** @OA\Post(
 *     path="/assets/searchAssets.php", 
 *     summary="Simple Asset Search", 
 *     description="Searches for assets by tag or name",
 *     operationId="simpleAssetSearch", 
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
 *                     property="response", 
 *                     type="array", 
 *                     description="An array of assets",
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
 *         name="term",
 *         in="query",
 *         description="The term to search for",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="barcodes",
 *         in="query",
 *         description="whether to include asset barcodes",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ),
 *     
 * )
 */

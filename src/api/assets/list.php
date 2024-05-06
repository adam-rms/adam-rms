<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['term'])) $PAGEDATA['search'] = $bCMS->sanitizeString($_POST['term']);
else $PAGEDATA['search'] = null;

if (isset($_POST['page'])) $page = $bCMS->sanitizeString($_POST['page']);
else $page = 1;
$DBLIB->pageLimit = (isset($_POST['pageLimit']) ? $_POST['pageLimit'] : 20); //Users per page
if (isset($_POST['category'])) $DBLIB->where("assetTypes.assetCategories_id", $_POST['category']);
if (isset($_POST['manufacturer'])) $DBLIB->where("manufacturers.manufacturers_id", $_POST['manufacturer']);
if (isset($_POST['assetTypes_id'])) $DBLIB->where("assetTypes.assetTypes_id", $_POST['assetTypes_id']);
$DBLIB->orderBy("assetCategories.assetCategories_id", "ASC");
$DBLIB->orderBy("assetTypes.assetTypes_name", "ASC");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("((SELECT COUNT(*) FROM assets WHERE assetTypes.assetTypes_id=assets.assetTypes_id AND assets.instances_id = '" . $AUTH->data['instance']['instances_id'] . "' AND (assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP()) AND assets_deleted = 0" . (!isset($_POST['all']) ? ' AND assets.assets_linkedTo IS NULL' : '') .") > 0)");
$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
if (strlen($PAGEDATA['search']) > 0) {
    //Search
    $DBLIB->where("(
		manufacturers_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		assetTypes_description LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' OR
		assetTypes_name LIKE '%" . $bCMS->sanitizeStringMYSQL($PAGEDATA['search']) . "%' 
    )");
}
$assets = $DBLIB->arraybuilder()->paginate('assetTypes', $page, ["assetTypes.*", "manufacturers.*", "assetCategories.*", "assetCategoriesGroups_name"]);
$PAGEDATA['pagination'] = ["page" => $page, "total" => $DBLIB->totalPages];

$PAGEDATA['assets'] = [];
foreach ($assets as $asset) {
    $asset['thumbnails'] = [];
    foreach ($bCMS->s3List(2, $asset['assetTypes_id']) as $thumbnail) {
        $thumbnail['url'] = $bCMS->s3URL($thumbnail['s3files_id'], false, '+1 hour');
        $asset['thumbnails'][] = $thumbnail;
    }

    //Format finances
    $asset['assetTypes_mass_format'] = apiMass($asset['assetTypes_mass']);
    $asset['assetTypes_value_format'] = apiMoney($asset['assetTypes_value']);
    $asset['assetTypes_dayRate_format'] = apiMoney($asset['assetTypes_dayRate']);
    $asset['assetTypes_weekRate_format'] = apiMoney($asset['assetTypes_weekRate']);





    $DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("assets.assetTypes_id", $asset['assetTypes_id']);
    $DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
    $DBLIB->where("assets_deleted", 0);
    if (!isset($_POST['all'])) $DBLIB->where("(assets.assets_linkedTo IS NULL)");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $assetTags = $DBLIB->get("assets", null, ["assets_id", "assets_notes","assets_tag","asset_definableFields_1","asset_definableFields_2","asset_definableFields_3","asset_definableFields_4","asset_definableFields_5","asset_definableFields_6","asset_definableFields_7","asset_definableFields_8","asset_definableFields_9","asset_definableFields_10","assets_dayRate","assets_weekRate","assets_value","assets_mass"]);
    $asset['count'] = count($assetTags);
    $asset['fields'] = explode(",", $asset['assetTypes_definableFields']);
    $asset['tags'] = [];
    foreach ($assetTags as $tag) {
        $tag['assets_tag_format'] = $bCMS->aTag($tag['assets_tag']);
        $tag['assets_mass_format'] = apiMass($tag['assets_mass']);
        $tag['assets_value_format'] = apiMoney($tag['assets_value']);
        $tag['assets_dayRate_format'] = apiMoney($tag['assets_dayRate']);
        $tag['assets_weekRate_format'] = apiMoney($tag['assets_weekRate']);

        if (!isset($_POST['abridgedList']) or $_POST['abridgedList'] == false) {
            $tag['flagsblocks'] = assetFlagsAndBlocks($tag['assets_id']);
            $tag['files'] = $bCMS->s3List(4, $tag['assets_id']);
        }
        $asset['tags'][] = $tag;

    }
    if (!isset($_POST['abridgedList']) or $_POST['abridgedList'] == false)  $asset['files'] = $bCMS->s3List(3, $asset['assetTypes_id']);

    $PAGEDATA['assets'][] = $asset;
}
finish(true, null, ["assets" => $PAGEDATA['assets'], "pagination" => $PAGEDATA['pagination']]);

/** @OA\Post(
 *     path="/assets/list.php", 
 *     summary="List Assets", 
 *     description="Lists assets", 
 *     operationId="listAssets", 
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
 *                     property="assets", 
 *                     type="array", 
 *                     description="An array of asset objects",
 *                 ),
 *                 @OA\Property(
 *                     property="pagination", 
 *                     type="number", 
 *                     description="An object containing pagination data",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="term",
 *         in="query",
 *         description="A search term to filter the list by",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="The page number to get",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="pageLimit",
 *         in="query",
 *         description="The number of items to get per page",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="category",
 *         in="query",
 *         description="The ID of the asset category to filter by",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="manufacturer",
 *         in="query",
 *         description="The ID of the manufacturer to filter by",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="assetTypes_id",
 *         in="query",
 *         description="The ID of the asset type to filter by",
 *         required="false", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 *     @OA\Parameter(
 *         name="all",
 *         in="query",
 *         description="Whether to get all linked assets",
 *         required="false", 
 *         @OA\Schema(
 *             type="any"), 
 *         ), 
 *     @OA\Parameter(
 *         name="abridgedList",
 *         in="query",
 *         description="Whether to get file and flags & blocks",
 *         required="false", 
 *         @OA\Schema(
 *             type="boolean"), 
 *         ), 
 * )
 */
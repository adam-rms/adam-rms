<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (isset($_POST['maintenanceJobs_id'])) {
    $DBLIB->where("maintenanceJobs.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("maintenanceJobs.maintenanceJobs_deleted", 0);
    $DBLIB->where("maintenanceJobs_id", $_POST['maintenanceJobs_id']);
    $job = $DBLIB->getOne("maintenanceJobs", ["maintenanceJobs_assets",  "maintenanceJobs_id"]);
    if (!$job) finish(false);
} else $job = false;


$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
$DBLIB->where("assets.assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");
if ($job and $job['maintenanceJobs_assets'] != "") $DBLIB->where("(assets_id NOT IN (" . $job['maintenanceJobs_assets'] . "))");
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
if (isset($_POST['term']) and strlen($_POST['term']) > 0) {
    $DBLIB->where("(
		assets.assets_tag LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
		OR assetTypes.assetTypes_name LIKE '%" . $bCMS->sanitizeString($_POST['term']) . "%'
    )");
}
$assets = $DBLIB->get("assets", 15, ["assets.assets_id", "assetTypes.assetTypes_name", "assets.assets_tag", "manufacturers.manufacturers_name"]);
if (!$assets) finish(false, ["code" => "LIST-ASSETS-FAIL", "message"=> "Could not search for assets"]);
else {
    finish(true, null, $assets);
}

/** @OA\Post(
 *     path="/maintenance/searchAsset.php", 
 *     summary="Search Asset", 
 *     description="Search for an asset to add to a maintenance job", 
 *     operationId="searchAsset", 
 *     tags={"maintenance"}, 
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
 *                     description="Array of Assets",
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
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="maintenanceJobs_id",
 *         in="query",
 *         description="Maintenance Job ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="term",
 *         in="query",
 *         description="Search Term",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
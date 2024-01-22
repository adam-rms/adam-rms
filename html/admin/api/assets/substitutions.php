<?php
/**
 * API
 * \assets\substitutions.php
 * Gets possible alternatives for a file
 *
 * Arguments:
 *  - assetsAssignments_id: an Asset Assignment ID
 *
 * Returns:
 *      "result": true,
 *      "response": [] - array of assets
 *   -or-
 *      "result": true
 *      "error": ["code" => "NO-ASSETS", "message"=>"No Assets Available"] - no assets error message
 *  -or-
 *      "result": false
 *      "error": [] - error code
 */

require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->where("assetsAssignments.assetsAssignments_id", $_POST['assetsAssignments_id']);
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects_dates_deliver_start",NULL,"IS NOT");
$DBLIB->where("projects_dates_deliver_end",NULL,"IS NOT");
$DBLIB->join("assetsAssignments", "assetsAssignments.assets_id = assets.assets_id");
$DBLIB->join("projects", "assetsAssignments.projects_id = projects.projects_id");
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_archived", 0);
$DBLIB->where('assets_deleted', 0);
$currentAsset = $DBLIB->getOne("assets");
if (!$currentAsset) finish(true, ["code" => "", "message"=>"No Asset Found"]);

$ASSET_OPTIONS = [];

$DBLIB->where("assets.assetTypes_id", $currentAsset["assetTypes_id"]);
$DBLIB->where("assets.assets_id", $currentAsset["assets_id"], "!=");
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where ("(assets.assets_endDate IS NULL OR assets.assets_endDate >= '" . date ("Y-m-d H:i:s") . "')");
$DBLIB->where('assets.assets_deleted', 0);
$assets = $DBLIB->get("assets", null, ["assets_id","assets_tag","asset_definableFields_1"]);
foreach ($assets as $asset) {
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
    $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
    $DBLIB->where("((projects_dates_deliver_start >= '" . $currentAsset["projects_dates_deliver_start"] . "' AND projects_dates_deliver_start <= '" . $currentAsset["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $currentAsset["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $currentAsset["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $currentAsset["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $currentAsset["projects_dates_deliver_start"] . "'))");
    $assignments = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.projects_id"]);
    $flagsBlocks = assetFlagsAndBlocks($asset['assets_id']);
    if (count($assignments) < 1 and $flagsBlocks['COUNT']['BLOCK'] < 1) {
        $ASSET_OPTIONS[] = $asset;
    }
}

if ($ASSET_OPTIONS) finish(true, null, $ASSET_OPTIONS);
finish(true, ["code" => "NO-ASSETS", "message"=>"No Assets Available"]); //this is not an error, just information that there are no assets!

/** @OA\Post(
 *     path="/assets/substitutions.php", 
 *     summary="Get Swappable Assets", 
 *     description="Gets a list of assets that can be swapped with the given asset assignment
", 
 *     operationId="getSwappableAssets", 
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
 *         name="assetsAssignments_id",
 *         in="query",
 *         description="The ID of the asset assignment",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id'])) finish(false, ["code" => "AUTH-ERROR", "message" => "Provide a project"]);


$_POST['id'] = $_POST['projects_id'];
require_once __DIR__ . '/../data.php'; //Where most of the data comes from


// Keep this endpoint's response keyed by assetsAssignmentsStatus_order, as it was before, for compatibility
// with existing consumers. A deleted status keeps whatever order it had when it was deleted, so - unlike the
// live, always-contiguous active statuses - it can (rarely) still collide with another status's order; when
// that happens, key that entry by "order-id" instead of silently dropping it by overwriting the same key.
function reindexBoardByOrder($board)
{
    $reindexed = [];
    foreach ($board as $status) {
        $key = $status['assetsAssignmentsStatus_order'];
        if (array_key_exists($key, $reindexed)) $key = $key . '-' . $status['assetsAssignmentsStatus_id'];
        $reindexed[$key] = $status;
    }
    return $reindexed;
}

$sortedAssets = [];
$sortedAssets[$AUTH->data['instance']['instances_id']] = reindexBoardByOrder(buildAssetsAssignmentsBoard($AUTH->data['instance']['instances_id'], $PAGEDATA['FINANCIALS']['assetsAssigned']));
foreach ($PAGEDATA['FINANCIALS']['assetsAssignedSUB'] as $instance) { //Go through the sub projects
    $sortedAssets[$instance['instance']['instances_id']] = reindexBoardByOrder(buildAssetsAssignmentsBoard($instance['instance']['instances_id'], $instance['assets']));
}

finish(true, null, $sortedAssets);

/** @OA\Post(
 *     path="/projects/assets/statusList.php", 
 *     summary="Get Asset Assignment Status List", 
 *     description="Get the list of statuses for an asset assignment  
Requires Instance Permission PROJECTS:VIEW
", 
 *     operationId="getAssetAssignmentStatusList", 
 *     tags={"project_assets"}, 
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
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
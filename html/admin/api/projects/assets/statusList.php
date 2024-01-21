<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id'])) finish(false, ["code" => "AUTH-ERROR", "message" => "Provide a project"]);


$_POST['id'] = $_POST['projects_id'];
require_once __DIR__ . '/../data.php'; //Where most of the data comes from


$sortedAssets = [];
foreach ($PAGEDATA['assetsAssignmentsStatus'] as $status) {
    $tempAssets = [];
    foreach ($PAGEDATA['FINANCIALS']['assetsAssigned'] as $assetType){
        foreach ($assetType['assets'] as $asset){
            if ($asset['assetsAssignmentsStatus_order'] == null && $status['assetsAssignmentsStatus_order'] == 0) { //if asset status is null, add to the first column
                $tempAssets[] = $asset;
            } elseif ($asset['assetsAssignmentsStatus_order'] == $status['assetsAssignmentsStatus_order']) {
                $tempAssets[] = $asset;
            }
        }
    }
    $sortedAssets[$AUTH->data['instance']['instances_id']][$status['assetsAssignmentsStatus_order']] = $status;
    $sortedAssets[$AUTH->data['instance']['instances_id']][$status['assetsAssignmentsStatus_order']]["assets"] = $tempAssets;
}
foreach ($PAGEDATA['FINANCIALS']['assetsAssignedSUB'] as $instance) { //Go through the sub projects
    $DBLIB->orderBy("assetsAssignmentsStatus_order","ASC");
    $DBLIB->where("assetsAssignmentsStatus.instances_id", $instance['instance']['instances_id']);
    $DBLIB->where("assetsAssignmentsStatus.assetsAssignmentsStatus_deleted", 0);
    $sortedAssets[$instance['instance']['instances_id']] = $DBLIB->get("assetsAssignmentsStatus");
    foreach ($sortedAssets[$instance['instance']['instances_id']] as $status) {
        $tempAssets=[];
        foreach ($instance["assets"] as $assetType){
            foreach ($assetType['assets'] as $asset){
                if ($asset['assetsAssignmentsStatus_order'] == null && $status['assetsAssignmentsStatus_order'] == 0) { //if asset status is null, add to the first column
                    $tempAssets[] = $asset;
                } elseif ($asset['assetsAssignmentsStatus_order'] == $status['assetsAssignmentsStatus_order']) {
                    $tempAssets[] = $asset;
                }
            }
        }
        $sortedAssets[$instance['instance']['instances_id']][$status['assetsAssignmentsStatus_order']]["assets"] = $tempAssets;
    }
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
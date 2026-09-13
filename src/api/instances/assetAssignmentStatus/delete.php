<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT") or !isset($_POST['statusId'])) finish(false,["message"=>"invalid status id or permission"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("assetsAssignmentsStatus_id", $_POST['statusId']);
$updateQuery = $DBLIB->update("assetsAssignmentsStatus", ["assetsAssignmentsStatus_deleted" => 1]);
if (!$updateQuery) finish(false, ["code" => "REMOVE-STATUS-FAIL", "message"=> "Could not remove asset status from Business"]);

// Re-sequence the remaining statuses to be contiguous from 0, so there's always an order-0 status to act as
// the default for newly-assigned assets (which otherwise start with no status at all) as long as one remains
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->orderBy("assetsAssignmentsStatus_order", "ASC");
$remainingStatuses = $DBLIB->get("assetsAssignmentsStatus", null, ["assetsAssignmentsStatus_id", "assetsAssignmentsStatus_order"]);
foreach ($remainingStatuses as $index => $remainingStatus) {
    if ((int)$remainingStatus['assetsAssignmentsStatus_order'] !== $index) {
        $DBLIB->where("assetsAssignmentsStatus_id", $remainingStatus['assetsAssignmentsStatus_id']);
        $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
        $DBLIB->where("assetsAssignmentsStatus_deleted", 0);
        if (!$DBLIB->update("assetsAssignmentsStatus", ["assetsAssignmentsStatus_order" => $index], 1)) finish(false, ["code" => "REMOVE-STATUS-FAIL", "message"=> "Could not resequence remaining asset statuses"]);
    }
}

finish(true);

/** @OA\Post(
 *     path="/instances/assetAssignmentStatus/delete.php", 
 *     summary="Delete Asset Assignment Status", 
 *     description="Delete an asset assignment status  
Requires Instance Permission BUSINESS:BUSINESS_SETTINGS:EDIT
", 
 *     operationId="deleteAssetAssignmentStatus", 
 *     tags={"assetAssignmentStatus"}, 
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
 *                     description="A null Array",
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
 *         name="statusId",
 *         in="query",
 *         description="The status id",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
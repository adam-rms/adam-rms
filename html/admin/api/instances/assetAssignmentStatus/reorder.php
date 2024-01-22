<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) die("Sorry - you can't access this page");

foreach ($_POST['order'] as $count=>$item) {
    if ($item == "") continue;
    $DBLIB->where("assetsAssignmentsStatus_id", $item);
    $DBLIB->where("assetsAssignmentsStatus_deleted", 0);
    $DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
    if (!$DBLIB->update("assetsAssignmentsStatus", ["assetsAssignmentsStatus_order" => $count], 1)) finish(false);
}
$bCMS->auditLog("RANK-ASSETSTATUS", "assetStatuses", "Set the order of statuses", $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/assetAssignmentStatus/reorder.php", 
 *     summary="Reorder Asset Assignment Status", 
 *     description="Reorder asset assignment statuses  
Requires Instance Permission BUSINESS:BUSINESS_SETTINGS:EDIT
", 
 *     operationId="reorderAssetAssignmentStatus", 
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
 *         name="order",
 *         in="query",
 *         description="The order of the statuses",
 *         required="true", 
 *         @OA\Schema(
 *             type="array"), 
 *         ), 
 * )
 */
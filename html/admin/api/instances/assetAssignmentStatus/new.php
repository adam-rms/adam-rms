<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT") or !isset($_POST['statusName']) or !isset($_POST['statusOrder'])) finish(false);

$assignmentsStatus = $DBLIB->insert("assetsAssignmentsStatus", [
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "assetsAssignmentsStatus_name" => $_POST['statusName'],
    "assetsAssignmentsStatus_order" => $_POST['statusOrder'],
]);

if (!$assignmentsStatus) finish(false, ["code" => "ADD-STATUS-FAIL", "message"=> "Could not create new assignment status"]);
finish(true);

/** @OA\Post(
 *     path="/instances/assetAssignmentStatus/new.php", 
 *     summary="Create Asset Assignment Status", 
 *     description="Create an asset assignment status  
Requires Instance Permission BUSINESS:BUSINESS_SETTINGS:EDIT
", 
 *     operationId="createAssetAssignmentStatus", 
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
 *         name="statusName",
 *         in="query",
 *         description="The status name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="statusOrder",
 *         in="query",
 *         description="The status order",
 *         required="true", 
 *         @OA\Schema(
 *             type="integer"), 
 *         ), 
 * )
 */
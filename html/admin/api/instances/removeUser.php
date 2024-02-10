<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS") or !isset($_POST['userid']) and count($_POST['userid']) > 0) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("userInstances.users_userid", $_POST['userid']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$DBLIB->join("instancePositions", "userInstances.instancePositions_id=instancePositions.instancePositions_id", "LEFT");
$DBLIB->where("instancePositions.instances_id", $AUTH->data['instance']['instances_id']);
$updateQuery = $DBLIB->update("userInstances", ["userInstances_deleted" => 1]);
if (!$updateQuery) finish(false, ["code" => "REMOVE-USER-TO-INSTANCE-FAIL", "message"=> "Could not remove user from Business"]);
else finish(true);

/** @OA\Post(
 *     path="/instances/removeUser.php", 
 *     summary="Remove User", 
 *     description="Remove a user from an instance  
Requires Instance Permission BUSINESS:USERS:DELETE:REMOVE_FORM_BUSINESS
", 
 *     operationId="removeUser", 
 *     tags={"instances"}, 
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
 *         name="userid",
 *         in="query",
 *         description="The user id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
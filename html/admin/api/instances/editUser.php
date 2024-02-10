<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USERS:EDIT:CHANGE_ROLE") or !isset($_POST['userinstanceid']) and count($_POST['userinstanceid']) > 0) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("userInstances.userInstances_id", $_POST['userinstanceid']);
$DBLIB->where("userInstances.userInstances_deleted", 0);
$updateQuery = $DBLIB->update("userInstances", ["instancePositions_id" => $_POST['position'], "userInstances_label" => $_POST['label']]);
if (!$updateQuery) finish(false, ["code" => "EDIT-USER-TO-INSTANCE-FAIL", "message"=> "Could not edit user in Business"]);
else finish(true);
/** @OA\Post(
 *     path="/instances/editUser.php", 
 *     summary="Edit User", 
 *     description="Edit a user's role  
Requires Instance Permission BUSINESS:USERS:EDIT:CHANGE_ROLE
", 
 *     operationId="editUser", 
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
 *         name="userinstanceid",
 *         in="query",
 *         description="The userinstance id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="position",
 *         in="query",
 *         description="The user's position id",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="label",
 *         in="query",
 *         description="The user's role label",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
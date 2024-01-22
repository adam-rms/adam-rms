<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:ROLES_AND_PERMISSIONS:CREATE") or !isset($_POST['name'])) die("404");

$instance = $DBLIB->insert("instancePositions", [
    "instances_id" => $AUTH->data['instance']['instances_id'],
    "instancePositions_displayName" => $_POST['name'],
]);
if ($instance) finish(true);
else finish(false, ["code" => "CREATE-INSTANCE-POSITION-FAIL", "message"=> "Could not create new instance position"]);

/** @OA\Post(
 *     path="/permissions/newInstancePermission.php", 
 *     summary="New Instance Permission", 
 *     description="Create a new permission group  
Requires Instance Permission BUSINESS:ROLES_AND_PERMISSIONS:CREATE
", 
 *     operationId="newInstancePermission", 
 *     tags={"permissions"}, 
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
 *         name="name",
 *         in="query",
 *         description="Permission Group name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
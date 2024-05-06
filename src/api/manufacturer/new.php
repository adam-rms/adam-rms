<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:MANUFACTURERS:CREATE") or !isset($_POST['manufacturers_name'])) die("404");

$insert = $DBLIB->insert("manufacturers", [
    "manufacturers_name" => $_POST['manufacturers_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
]);
if (!$insert) finish(false, ["code" => "CREATE-CLIENT-FAIL", "message"=> "Could not create new manufacturers"]);

$bCMS->auditLog("INSERT", "manufacturers",null, $AUTH->data['users_userid'],null, $insert);
finish(true, null, ["manufacturers_id" => $insert]);

/** @OA\Post(
 *     path="/manufacturer/new.php", 
 *     summary="New Manufacturer", 
 *     description="Create a new manufacturer  
Requires Instance Permission ASSETS:MANUFACTURERS:CREATE
", 
 *     operationId="newManufacturer", 
 *     tags={"manufacturers"}, 
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
 *         name="manufacturers_name",
 *         in="query",
 *         description="Manufacturer Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
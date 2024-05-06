<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CLIENTS:CREATE") or !isset($_POST['clients_name'])) die("404");

$client = $DBLIB->insert("clients", [
    "clients_name" => $_POST['clients_name'],
    "instances_id" => $AUTH->data['instance']['instances_id'],
]);
if (!$client) finish(false, ["code" => "CREATE-CLIENT-FAIL", "message"=> "Could not create new client"]);

$bCMS->auditLog("INSERT", "clients",null, $AUTH->data['users_userid'],null, $client);
finish(true, null, ["clients_id" => $client]);

/** @OA\Post(
 *     path="/clients/new.php", 
 *     summary="Create Client", 
 *     description="Create a client  
Requires Instance Permission CLIENTS:CREATE", 
 *     operationId="createClient", 
 *     tags={"clients"}, 
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
 *         response="400", 
 *         description="Error",
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
 *         name="clients_name",
 *         in="query",
 *         description="The name of the client",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
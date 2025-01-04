<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CLIENTS:CREATE")) die(404);

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}

if (!isset($array['clients_name'])) finish(false, ["code" => "INVALID-CLIENT", "message" => "No Client name provided"]);

$array['instances_id'] = $AUTH->data['instance']['instances_id'];

$client = $DBLIB->insert("clients", $array);
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
 *         name="formData",
 *         in="query",
 *         description="The client data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="clients_name", 
 *                 type="string", 
 *                 description="The name of the client",
 *                 required="true",
 *             ),
 *             @OA\Property(
 *                 property="clients_address", 
 *                 type="string", 
 *                 description="The address of the client",
 *             ),
 *             @OA\Property(
 *                 property="clients_phone", 
 *                 type="string", 
 *                 description="The phone number of the client",
 *             ),
 *             @OA\Property(
 *                 property="clients_email", 
 *                 type="string", 
 *                 description="The email of the client",
 *             ),
 *             @OA\Property(
 *                 property="clients_website", 
 *                 type="string", 
 *                 description="The website of the client",
 *             ),
 *             @OA\Property(
 *                 property="clients_notes", 
 *                 type="string", 
 *                 description="The notes of the client",
 *             ),
 *         ),
 *     ), 
 * )
 */
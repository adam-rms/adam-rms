<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("CLIENTS:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['clients_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("clients_deleted", 0);
$DBLIB->where("clients_id", $array['clients_id']);
$project = $DBLIB->update("clients", $array);
if (!$project) finish(false);

$bCMS->auditLog("EDIT", "clients", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/clients/edit.php", 
 *     summary="Edit Client", 
 *     description="Edit a client  
Requires Instance Permission CLIENTS:EDIT", 
 *     operationId="editClient", 
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
 *                 property="clients_id", 
 *                 type="integer", 
 *                 description="The ID of the client",
 *             ),
 *             @OA\Property(
 *                 property="clients_name", 
 *                 type="string", 
 *                 description="The name of the client",
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
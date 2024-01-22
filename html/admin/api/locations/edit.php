<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("LOCATIONS:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['locations_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);

$DBLIB->where("locations.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("locations.locations_deleted", 0);
$DBLIB->where("locations_id",$array['locations_id']);
$group = $DBLIB->update("locations", $array,1);
if (!$group) finish(false);

$bCMS->auditLog("UPDATE", "locations", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/locations/edit.php", 
 *     summary="Edit Location", 
 *     description="Edit a location  
Requires Instance Permission LOCATIONS:EDIT
", 
 *     operationId="editLocation", 
 *     tags={"locations"}, 
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
 *         response="404", 
 *         description="Auth Fail",
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
 *         name="formData",
 *         in="query",
 *         description="The location data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="locations_id", 
 *                 type="number", 
 *                 description="The location id",
 *             ),
 *             @OA\Property(
 *                 property="locations_name", 
 *                 type="string", 
 *                 description="The location name",
 *             ),
 *             @OA\Property(
 *                 property="clients_id", 
 *                 type="number", 
 *                 description="The owning Client",
 *             ),
 *             @OA\Property(
 *                 property="locations_address", 
 *                 type="string", 
 *                 description="The location address",
 *             ),
 *             @OA\Property(
 *                 property="locations_subOf", 
 *                 type="number", 
 *                 description="The parent location",
 *             ),
 *             @OA\Property(
 *                 property="locations_notes", 
 *                 type="string", 
 *                 description="The location notes",
 *             ),
 *         ),
 *     ), 
 * )
 */
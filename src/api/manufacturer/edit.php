<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:MANUFACTURERS:EDIT"))
  die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
  $array[$item['name']] = $item['value'];
}
// Basic parameter validation
if (!isset($array['manufacturers_id']) || strlen($array['manufacturers_id']) < 1) {
  finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);
}
if (!isset($array['manufacturers_name']) || trim($array['manufacturers_name']) === '') {
  finish(false, ["code" => "PARAM-ERROR", "message" => "Manufacturer name is required"]);
}
if (isset($array['manufacturers_website']) && trim($array['manufacturers_website']) !== '') {
  if (filter_var($array['manufacturers_website'], FILTER_VALIDATE_URL) === false) {
    finish(false, ["code" => "PARAM-ERROR", "message" => "Manufacturer website must be a valid URL"]);
  }
}

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("manufacturers_id", $array['manufacturers_id']);
$result = $DBLIB->update("manufacturers", array_intersect_key($array, array_flip(['manufacturers_name', 'manufacturers_website', 'manufacturers_notes'])));
if (!$result)
  finish(false);

$bCMS->auditLog("EDIT", "manufacturers", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/manufacturer/edit.php", 
 *     summary="Edit Manufacturer", 
 *     description="Edit a Manufacturer  
Requires Instance Permission ASSETS:MANUFACTURERS:EDIT", 
 *     operationId="editManufacturer", 
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
 *     @OA\RequestBody(
 *         required=true,
 *         description="The manufacturer data",
 *         @OA\MediaType(
 *             mediaType="application/x-www-form-urlencoded",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="manufacturers_id",
 *                     type="integer",
 *                     description="The ID of the manufacturer",
 *                 ),
 *                 @OA\Property(
 *                     property="manufacturers_name",
 *                     type="string",
 *                     description="The name of the manufacturer",
 *                 ),
 *                 @OA\Property(
 *                     property="manufacturers_website",
 *                     type="string",
 *                     description="The website of the manufacturer",
 *                 ),
 *                 @OA\Property(
 *                     property="manufacturers_notes",
 *                     type="string",
 *                     description="The notes of the manufacturer",
 *                 ),
 *             ),
 *         ),
 *     ), 
 * )
 */

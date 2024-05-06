<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->serverPermissionCheck("INSTANCES:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['instances_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

$DBLIB->where("instances_deleted", 0);
$DBLIB->where("instances_id", $array['instances_id']);
$category = $DBLIB->update("instances", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "instances", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/editInstanceServerAdmin.php", 
 *     summary="Edit Instance Server Config", 
 *     description="Edit an instance as a server administrator. This is typically used for the instance plan, but you can pass any valid parameter from the database of the instance.", 
 *     operationId="editInstanceServerAdmin", 
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
 *         description="The instance data to manipulate",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="instances_id", 
 *                 type="integer", 
 *                 description="The ID of the instance",
 *             ),
 *             @OA\Property(
 *                 property="instances_planName", 
 *                 type="string", 
 *                 description="The instance plan name",
 *             ),
 *         ),
 *     ), 
 * )
 */

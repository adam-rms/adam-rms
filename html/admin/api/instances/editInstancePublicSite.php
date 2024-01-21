<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:BUSINESS_SETTINGS:EDIT")) die("Sorry - you can't access this page");
$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    elseif ($item['value'] == "on") $item['value'] = true;

    $array[$item['name']] = $item['value'];
}
$oldData = json_decode($AUTH->data['instance']['instances_publicConfig'],true);

$DBLIB->where("instances_id",$AUTH->data['instance']["instances_id"]);
$result = $DBLIB->update("instances", ["instances_publicConfig" => json_encode($array)]);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update instance"]);
else {
    $bCMS->auditLog("EDIT-INSTANCE", "instances", "Public Site - " . json_encode($array), $AUTH->data['users_userid'],null, $AUTH->data['instance']["instances_id"]);
    finish(true);
}

/** @OA\Post(
 *     path="/instances/editInstancePublicData.php", 
 *     summary="Edit Instance Public Data", 
 *     description="Edit an instance's public data  
Requires Instance Permission BUSINESS:BUSINESS_SETTINGS:EDIT
", 
 *     operationId="editInstancePublicData", 
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
 *         name="formData",
 *         in="query",
 *         description="The instance data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             ),
 *     ), 
 * )
 */
<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("ASSETS:ASSET_GROUPS:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['assetGroups_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];

if ($array['personal'] == "on") $array['users_userid'] = $AUTH->data['users_userid'];
unset($array['personal']);

$group = $DBLIB->insert("assetGroups", $array);
if (!$group) finish(false);

$bCMS->auditLog("INSERT", "assetGroups", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/groups/new.php", 
 *     summary="Create Group", 
 *     description="Create a group  
Requires Instance Permission ASSETS:ASSET_GROUPS:CREATE
", 
 *     operationId="createGroup", 
 *     tags={"groups"}, 
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
 *                     description="A null array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The group data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="personal", 
 *                 type="boolean", 
 *                 description="Whether the group is personal",
 *             ),
 *             @OA\Property(
 *                 property="assetGroups_name", 
 *                 type="string", 
 *                 description="The group name",
 *             ),
 *             @OA\Property(
 *                 property="assetGroups_description", 
 *                 type="string", 
 *                 description="The group description",
 *             ),
 *         ),
 *     ), 
 * )
 */
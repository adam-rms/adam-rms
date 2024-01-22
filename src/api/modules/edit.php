<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT")) die("404");

$array = [];
$array['modules_visibleToGroups'] = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;

    if ($item['name'] == 'modules_visibleToGroups') array_push($array['modules_visibleToGroups'],$item['value']);
    else $array[$item['name']] = $item['value'];
}
if (strlen($array['modules_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['modules_show']) $array['modules_show'] = 1;
else $array['modules_show'] = 0;

if ($array['modules_visibleToGroups'] == []) $array['modules_visibleToGroups'] = null;
else $array['modules_visibleToGroups'] = implode(",",$array['modules_visibleToGroups']);

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("modules.modules_id",$array['modules_id']);
$update = $DBLIB->update("modules", $array,1);
if (!$update) finish(false);

$bCMS->auditLog("UPDATE", "modules", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/modules/edit.php", 
 *     summary="Edit Module", 
 *     description="Edit a module  
Requires Instance Permission TRAINING:EDIT
", 
 *     operationId="editModule", 
 *     tags={"modules"}, 
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
 *         name="formData",
 *         in="query",
 *         description="undefined",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="modules_id", 
 *                 type="number", 
 *                 description="Module ID",
 *             ),
 *             @OA\Property(
 *                 property="modules_name", 
 *                 type="string", 
 *                 description="Module Name",
 *             ),
 *             @OA\Property(
 *                 property="modules_description", 
 *                 type="string", 
 *                 description="Module Description",
 *             ),
 *             @OA\Property(
 *                 property="modules_learningObjectives", 
 *                 type="string", 
 *                 description="LOs",
 *             ),
 *             @OA\Property(
 *                 property="modules_show", 
 *                 type="boolean", 
 *                 description="undefined",
 *             ),
 *             @OA\Property(
 *                 property="modules_thumbnail", 
 *                 type="number", 
 *                 description="Thumbnail ID",
 *             ),
 *             @OA\Property(
 *                 property="modules_type", 
 *                 type="number", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */
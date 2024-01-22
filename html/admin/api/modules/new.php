<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modules_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['modules_show']) $array['modules_show'] = 1;
else $array['modules_show'] = 0;
$array['instances_id'] = $AUTH->data['instance']['instances_id'];
$array['modules_id'] = null;
$array['users_userid'] = $AUTH->data['users_userid'];
$insert = $DBLIB->insert("modules", $array);
if (!$insert) finish(false, ["message" => $DBLIB->getLastError()]);

$insertStep = $DBLIB->insert("modulesSteps", [
    "modules_id" => $insert,
    "modulesSteps_show" => 1,
    "modulesSteps_name" => "Introduction",
    "modulesSteps_order" => 0,
    "modulesSteps_internalNotes" => "Use this step to introduce the learning objectives and the module, but don't use it to cover any content",
    "modulesSteps_content" => "Welcome to this module. The learning objectives are:<br/><br/>" . str_replace("\n","<br/>",$array['modules_learningObjectives']),
    "modulesSteps_type" => 1,
    "modulesSteps_locked" => 1
]);

$bCMS->auditLog("INSERT", "modules", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/modules/new.php", 
 *     summary="New Module", 
 *     description="Create a new module  
Requires Instance Permission TRAINING:CREATE
", 
 *     operationId="newModule", 
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
<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modulesSteps_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['modulesSteps_show'] = 0;

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.modules_id", $array['modules_id']);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$module = $DBLIB->getOne('modules', ["modules.modules_id"]);
if (!$module) finish(false, ["message" => "Can't find module"]);

$insert = $DBLIB->insert("modulesSteps", $array);
if (!$insert) finish(false, ["message" => "Can't insert module step"]);

$bCMS->auditLog("INSERT", "modulesSteps", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/modules/steps/new.php", 
 *     summary="New Step", 
 *     description="Create a new module step  
Requires Instance Permission TRAINING:EDIT
", 
 *     operationId="newStep", 
 *     tags={"module_steps"}, 
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
 *         description="Form Data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="modulesSteps_name", 
 *                 type="string", 
 *                 description="Step Name",
 *             ),
 *             @OA\Property(
 *                 property="modulesSteps_content", 
 *                 type="string", 
 *                 description="Step Content",
 *             ),
 *             @OA\Property(
 *                 property="modules_steps_order", 
 *                 type="number", 
 *                 description="Step Order",
 *             ),
 *             @OA\Property(
 *                 property="modules_id", 
 *                 type="number", 
 *                 description="Module ID",
 *             ),
 *             @OA\Property(
 *                 property="modulesSteps_internalNotes", 
 *                 type="string", 
 *                 description="Internal Notes",
 *             ),
 *             @OA\Property(
 *                 property="modulesSteps_show", 
 *                 type="boolean", 
 *                 description="undefined",
 *             ),
 *         ),
 *     ), 
 * )
 */
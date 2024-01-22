<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['modulesSteps_id']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
if ($array['modulesSteps_show']) $array['modulesSteps_show'] = 1;
else $array['modulesSteps_show'] = 0;

$array['modulesSteps_content'] = $bCMS->cleanString($array['modulesSteps_content']);

$DBLIB->where("modules.modules_deleted", 0);
$DBLIB->where("modules.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("modules","modules.modules_id=modulesSteps.modules_id","LEFT");
$DBLIB->where("modulesSteps.modulesSteps_id",$array['modulesSteps_id']);
$id = $DBLIB->getOne("modulesSteps",["modulesSteps_id"]);
if (!$id) finish(false);

$DBLIB->where("modulesSteps.modulesSteps_id",$id['modulesSteps_id']);
$update = $DBLIB->update("modulesSteps", $array,1);
if (!$update) finish(false);

$bCMS->auditLog("UPDATE", "modulesSteps", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/modules/steps/edit.php", 
 *     summary="Edit Step", 
 *     description="Edit a module step  
Requires Instance Permission TRAINING:EDIT
", 
 *     operationId="editStep", 
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
 *                 property="modulessteps_id", 
 *                 type="number", 
 *                 description="Step ID",
 *             ),
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
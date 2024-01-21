<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!isset($_POST['id'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("modulesSteps_deleted",0);
$DBLIB->where("modulesSteps_id",$_POST['id']);
$DBLIB->where("modulesSteps_show",1);
$step = $DBLIB->getone("modulesSteps",["modulesSteps_id","modules_id"]);
if (!$step) finish(false, ["message"=> "Can't find step"]);

$DBLIB->orderBy("userModules_updated","DESC");
$DBLIB->where("users_userid", $AUTH->data['users_userid']);
$DBLIB->where("modules_id",$step['modules_id']);
$progress = $DBLIB->getone("userModules",["userModules_stepsCompleted","userModules_id"]);
if (!$progress) {
    $insert = $DBLIB->insert("userModules",[
        "userModules_stepsCompleted" =>$step['modulesSteps_id'],
        "users_userid" => $AUTH->data['users_userid'],
        "modules_id" => $step['modules_id'],
        "userModules_started" => date('Y-m-d H:i:s'),
        "userModules_updated" => date('Y-m-d H:i:s')
    ]);
    if (!$insert) finish(false, ["message"=> "Could not add progress"]);
    else finish(true);
} else {
    $progress["userModules_stepsCompleted"] = explode(",",$progress["userModules_stepsCompleted"]);
    if (!in_array($step['modulesSteps_id'],$progress["userModules_stepsCompleted"])) array_push($progress["userModules_stepsCompleted"],$step['modulesSteps_id']);
    $progress["userModules_stepsCompleted"] = implode(",",$progress["userModules_stepsCompleted"]);
    $DBLIB->where("userModules_id",$progress["userModules_id"]);
    $update = $DBLIB->update("userModules",["userModules_stepsCompleted"=>$progress["userModules_stepsCompleted"],"userModules_updated" => date('Y-m-d H:i:s')]);
    if (!$update) finish(false, ["message"=> "Could not update progress"]);
    else finish(true);
}

/** @OA\Post(
 *     path="/training/completeStep.php", 
 *     summary="Complete Step", 
 *     description="Complete a training step
", 
 *     operationId="completeStep", 
 *     tags={"training"}, 
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
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Step ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */
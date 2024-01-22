<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT:CERTIFY_USER") or !isset($_POST['userid'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$insert = $DBLIB->insert("userModulesCertifications",[
    "users_userid" => $_POST['userid'],
    "userModulesCertifications_approvedBy" => $AUTH->data['users_userid'],
    "modules_id" => $_POST['modules_id'],
    "userModulesCertifications_approvedComment" => $_POST['comment'],
    "userModulesCertifications_timestamp" => date('Y-m-d H:i:s')
]);
if (!$insert) finish(false, ["message"=> "Could not add certification"]);
else finish(true);

/** @OA\Post(
 *     path="/training/certify.php", 
 *     summary="Certify", 
 *     description="Certify a user for a module  
Requires instance permission TRAINING:EDIT:CERTIFY_USER
", 
 *     operationId="certify", 
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
 *         name="userid",
 *         in="query",
 *         description="User ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="modules_id",
 *         in="query",
 *         description="Module ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="comment",
 *         in="query",
 *         description="Comment",
 *         required="false", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
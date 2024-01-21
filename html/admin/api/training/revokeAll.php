<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("TRAINING:EDIT:REVOKE_USER_CERTIFICATION") or !isset($_POST['userid']) or !isset($_POST['modules_id'])) finish(false, ["code" => "AUTH-ERROR", "message"=> "No auth for action"]);

$DBLIB->where("users_userid",$_POST['userid']);
$DBLIB->where("modules_id", $_POST['modules_id']);
$update = $DBLIB->update("userModulesCertifications",["userModulesCertifications_revoked" => 1]);
if (!$update) finish(false, ["message"=> "Could not edit certification"]);
else {
    $bCMS->auditLog("REVOKE-ALL-CERTS", "userModulesCertifications", $_POST['modules_id'], $AUTH->data['users_userid'],$_POST['userid']);
    finish(true);
}

/** @OA\Post(
 *     path="/training/revokeAll.php", 
 *     summary="Revoke All", 
 *     description="Revoke all certifications for a user  
Requires instance permission TRAINING:EDIT:REVOKE_USER_CERTIFICATION
", 
 *     operationId="revokeAll", 
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
 * )
 */
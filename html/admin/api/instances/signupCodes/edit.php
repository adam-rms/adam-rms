<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (!is_numeric($array['signupCodes_id'])) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
elseif (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:EDIT")) die("404");

$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("signupCodes_deleted", 0);
$DBLIB->where("signupCodes_id", $array['signupCodes_id']);
$category = $DBLIB->update("signupCodes", $array);
if (!$category) finish(false);

$bCMS->auditLog("EDIT", "signupCodes", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/signupCodes/edit.php", 
 *     summary="Edit Signup Code", 
 *     description="Edit a signup code  
Requires Instance Permission BUSINESS:USER_SIGNUP_CODES:EDIT
", 
 *     operationId="editSignupCode", 
 *     tags={"signupCodes"}, 
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
 *         description="The signup code data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="signupCodes_id", 
 *                 type="integer", 
 *                 description="The signup code id",
 *             ),
 *             @OA\Property(
 *                 property="signupCodes_name", 
 *                 type="string", 
 *                 description="The signup code",
 *             ),
 *             @OA\Property(
 *                 property="signupCodes_valid", 
 *                 type="boolean", 
 *                 description="Whether the signup code is valid",
 *             ),
 *             @OA\Property(
 *                 property="signupCodes_notes", 
 *                 type="string", 
 *                 description="The signup code notes",
 *             ),
 *             @OA\Property(
 *                 property="signupCodes_role", 
 *                 type="string", 
 *                 description="Associated role for the signup code",
 *             ),
 *             @OA\Property(
 *                 property="instancePositions_id", 
 *                 type="integer", 
 *                 description="Associated position for the signup code",
 *             ),
 *         ),
 *     ), 
 * )
 */
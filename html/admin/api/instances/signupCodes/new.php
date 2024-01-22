<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = $item['value'];
}
if (strlen($array['signupCodes_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];

$DBLIB->where("signupCodes_name", $array['signupCodes_name']);
if ($DBLIB->getOne("signupCodes",["signupCodes_id"])) finish(false, ["message"=>"Sorry this code is in use"]);

$insert = $DBLIB->insert("signupCodes", $array);
if (!$insert) finish(false);

$bCMS->auditLog("INSERT", "signupCodes", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/instances/signupCodes/new.php", 
 *     summary="Create Signup Code", 
 *     description="Create a signup code  
Requires Instance Permission BUSINESS:USER_SIGNUP_CODES:CREATE
", 
 *     operationId="createSignupCode", 
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
 *                 property="signupCodes_name", 
 *                 type="string", 
 *                 description="The signup code",
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
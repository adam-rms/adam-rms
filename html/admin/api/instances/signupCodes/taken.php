<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("BUSINESS:USER_SIGNUP_CODES:VIEW")) die("404");

$DBLIB->where("signupCodes_name", $_GET['signupCode']);
$code = $DBLIB->getOne("signupCodes",["signupCodes_id"]);
if (!$code) finish(true,null,["taken"=>false]);
else finish(true,null,["taken"=>true]);

/** @OA\Get(
 *     path="/instances/signupCodes/taken.php", 
 *     summary="Check if Signup Code is Taken", 
 *     description="Check if a signup code is taken  
Requires Instance Permission BUSINESS:USER_SIGNUP_CODES:VIEW
", 
 *     operationId="checkSignupCodeTaken", 
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
 *                     type="boolean", 
 *                     description="Whether the signup code is taken",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="signupCode",
 *         in="query",
 *         description="The signup code",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
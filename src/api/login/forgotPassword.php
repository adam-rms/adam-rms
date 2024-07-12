<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['formInput'])) {
    $input = trim(strtolower($GLOBALS['bCMS']->sanitizeString($_POST['formInput'])));
    if ($input == "") finish(false, ["code" => null, "message" => "No data specified"]);
    else {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) $DBLIB->where ("users_email", $input);
        else $DBLIB->where ("users_username", $input);
        $user = $DBLIB->getOne("users",["users.users_userid"]);
        if (!$user) finish(true, null, true);

        if ($AUTH->forgotPassword($user['users_userid'])) {
            $bCMS->auditLog("UPDATE", "users", "INIT PASSWORD RESET AT LOGIN PAGE", $user['users_userid'],$user['users_userid']);
            finish(true, null, true);
        } else finish(true, null, true);
    }
} else finish(false, ["code" => null, "message" => "Unknown error"]);

/** @OA\Post(
 *     path="/login/forgotPassword.php", 
 *     summary="Forgot Password", 
 *     description="Send a password reset email to the user", 
 *     operationId="forgotPassword", 
 *     tags={"authentication"}, 
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
 *         name="formInput",
 *         in="query",
 *         description="Username",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */
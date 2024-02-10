<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['userid'])) {
    if ($AUTH->verifyEmail($GLOBALS['bCMS']->sanitizeString($_POST['userid']))) {
        $bCMS->auditLog("UPDATE", "users", "RESEND VERIFICATION EMAIL", $GLOBALS['bCMS']->sanitizeString($_POST['userid']),$GLOBALS['bCMS']->sanitizeString($_POST['userid']));
        finish(true, null, true);
    }
    else finish(true, null, false);
} else die("Sorry - page not found");

/** @OA\Post(
 *     path="/login/reSendVerificationEmail.php", 
 *     summary="Resend Verification Email", 
 *     description="Resend the verification email to the user", 
 *     operationId="resendVerificationEmail", 
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
 *         name="userid",
 *         in="query",
 *         description="User ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */

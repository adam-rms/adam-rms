<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['email'])) {
    if ($AUTH->emailTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['email'])))) finish(true, null, true);
    else finish(true, null, false);
} elseif (isset($_POST['username'])) {
    if ($AUTH->usernameTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['username'])))) finish(true, null, true);
    else finish(true, null, false);
} else die('Sorry - I think you are in the wrong place!');

/** @OA\Get(
 *     path="/login/usernameOrEmailTaken.php", 
 *     summary="Username or Email Taken", 
 *     description="Check if a username or email is already taken", 
 *     operationId="usernameOrEmailTaken", 
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
 *         name="username",
 *         in="query",
 *         description="Username",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         description="Email",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */

<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['name1']) and isset($_POST['password']) and isset($_POST['username']) and isset($_POST['email']) and isset($_POST['name2'])) {
    if ($AUTH->usernameTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['username'])))) finish(false, ["code" => null, "message" => "Sorry that username is taken, please try another"]);
    if ($AUTH->emailTaken($GLOBALS['bCMS']->sanitizeString(strtolower($_POST['email'])))) finish(false, ["code" => null, "message" => "Sorry, you already have an account with that email address"]);
    $data = Array (
        'users_email' => strtolower($bCMS->sanitizeString($_POST['email'])),
        'users_username' => strtolower($bCMS->sanitizeString($_POST['username'])),
        'users_name1' => $bCMS->sanitizeString($_POST['name1']),
        'users_name2' => $bCMS->sanitizeString($_POST['name2']),
        "users_salty1" => $bCMS->randomString(8),
        "users_salty2" => $bCMS->randomString(8),
        "users_hash" => $CONFIG['AUTH_NEXTHASH'],
    );
    $data["users_password"] = hash($data['users_hash'], $data['users_salty1'] . $_POST['password'] . $data['users_salty2']);
    $newUser = $DBLIB->insert("users", $data);
    if (!$newUser) finish(false, ["code" => null, "message" => "Can't create user due to database error"]);
    else {
        $bCMS->auditLog("INSERT", "users", json_encode($data), null,$newUser);
        $AUTH->verifyEmail($newUser);
        finish(true);
    }
} else finish(false, ["code" => null, "message" => "Parameter error"]);

/** @OA\Post(
 *     path="/login/signup.php", 
 *     summary="Signup", 
 *     description="Create a new user account", 
 *     operationId="signup", 
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
 *         name="name1",
 *         in="query",
 *         description="First Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="name2",
 *         in="query",
 *         description="Last Name",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="password",
 *         in="query",
 *         description="Password",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
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

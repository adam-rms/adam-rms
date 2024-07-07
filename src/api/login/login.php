<?php
require_once 'loginAjaxHead.php';
use \Firebase\JWT\JWT;
if (isset($_POST['formInput']) and isset($_POST['password'])) {
	$input = trim(strtolower($GLOBALS['bCMS']->sanitizeString($_POST['formInput'])));
    $password = $GLOBALS['bCMS']->sanitizeString($_POST['password']);
	if ($input == "" || $password == "") finish(false, ["code" => null, "message" => "No data specified"]);
	else {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) $DBLIB->where ("users_email", $input);
        else $DBLIB->where ("users_username", $input);
        $DBLIB->where("users_password", NULL, "IS NOT"); //To cover oauth users
        $user = $DBLIB->getOne("users",["users.users_salty1", "users.users_suspended", "users.users_salty2", "users.users_password", "users.users_userid", "users.users_hash"]);
        if (!$user) finish(false, ["code" => null, "message" => "Username, email or password incorrect"]);

        if ($user['users_password'] != hash($user['users_hash'], $user['users_salty1'] . $password . $user['users_salty2'])) $successful = false;
        else $successful = true;

        $DBLIB->where ("loginAttempts_timestamp >= '" . date('Y-m-d G:i:s', strtotime('-5 minutes')) . "'");
        $DBLIB->where ("loginAttempts_successful",0);
        $DBLIB->where ("loginAttempts_textEntered", $input);
        $previousattempts = $DBLIB->getValue("loginAttempts", "count(*)");
        if ($previousattempts > 6) $bruteforceattempt = true; //Only log it as a brute force if they get it wrong
        else $bruteforceattempt = false;

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ipAddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
        elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) $ipAddress = array_shift(explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]));
        else $ipAddress = $_SERVER["REMOTE_ADDR"];

        //Record this login attempt
        $DBLIB->insert ('loginAttempts', [
            "loginAttempts_ip" => $ipAddress,
            "loginAttempts_textEntered" => $input,
            "loginAttempts_timestamp" => date('Y-m-d G:i:s'),
            "loginAttempts_blocked" => ($bruteforceattempt ? '1' : '0'),
            "loginAttempts_successful" => ($successful ? '1' : '0')
        ]);

        if ($bruteforceattempt && !$successful) finish(false, ["code" => null, "message" => "Sorry - you've tried too many times to login - please try again in 5 minutes"]);
        elseif (!$successful) finish(false, ["code" => null, "message" => "Username, email or password incorrect"]);
        elseif ($user['users_suspended'] != '0') finish(false, ["code" => null, "message" => "User account is suspended"]);
        else {
            if (!$_SESSION['return'] and isset($_SESSION['app-oauth'])) {
                $token = $GLOBALS['AUTH']->generateToken($user['users_userid'], false, "App OAuth", "app-v1");
                $jwt = $GLOBALS['AUTH']->issueJWT($token, $user['users_userid'], "app-v1");
                finish(true,null,["redirect" => $_SESSION['app-oauth'] . "oauth_callback?token=" . $jwt]);
            } else {
                $GLOBALS['AUTH']->generateToken($user['users_userid'], false, "Web", "web-session");
                finish(true,null,["redirect" => (isset($_SESSION['return']) ? $_SESSION['return'] : $CONFIG['ROOTURL'])]);
            }
        }
	}
} else finish(false, ["code" => null, "message" => "Unknown error"]);

/**
 *  @OA\Post(
 *      path="/login/login.php",
 *      summary="Login",
 *      description="User Login",
 *      operationId="login",
 *      tags={"authentication"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="formInput",
 *          in="query",
 *          description="Email Address of user",
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *      @OA\Parameter(
 *          name="password",
 *          in="query",
 *          description="Password of user",
 *          required="true",
 *          @OA\Schema(
 *              type="string",
 *          ),
 *      ),
 *  )
 */

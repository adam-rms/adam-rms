<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['formInput']) and isset($_POST['password'])) {
	$input = trim(strtolower($GLOBALS['bCMS']->sanitizeString($_POST['formInput'])));
    $password = $GLOBALS['bCMS']->sanitizeString($_POST['password']);
	if ($input == "") finish(false, ["code" => null, "message" => "No data specified"]);
	else {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) $DBLIB->where ("users_email", $input);
        else $DBLIB->where ("users_username", $input);
        $user = $DBLIB->getOne("users",["users.users_salty1", "users.users_suspended", "users.users_salty2", "users.users_password", "users.users_userid", "users.users_hash"]);
        if (!$user) finish(false, ["code" => null, "message" => "No user found with associated email address or username"]);

        if ($user['users_password'] != hash($user['users_hash'], $user['users_salty1'] . $password . $user['users_salty2'])) $successful = false;
        else $successful = true;

        $DBLIB->where ("loginAttempts_timestamp >= '" . date('Y-m-d G:i:s', strtotime('-5 minutes')) . "'");
        $DBLIB->where ("loginAttempts_successful",0);
        $DBLIB->where ("loginAttempts_textEntered", $input);
        $previousattempts = $DBLIB->getValue("loginAttempts", "count(*)");
        if ($previousattempts > 6) $bruteforceattempt = true; //Only log it as a brute force if they get it wrong
        else $bruteforceattempt = false;

        //Record this login attempt
        $DBLIB->insert ('loginAttempts', [
            "loginAttempts_ip" => (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"]),
            "loginAttempts_textEntered" => $input,
            "loginAttempts_timestamp" => date('Y-m-d G:i:s'),
            "loginAttempts_blocked" => ($bruteforceattempt ? '1' : '0'),
            "loginAttempts_successful" => ($successful ? '1' : '0')
        ]);

        if ($bruteforceattempt && !$successful) finish(false, ["code" => null, "message" => "Sorry - you've tried too many times to login - please try again in 5 minutes"]);
        elseif (!$successful) finish(false, ["code" => null, "message" => "Password incorrect"]);
        elseif ($user['users_suspended'] != '0') finish(false, ["code" => 5, "message" => "User suspended"]);
		else {
		    $GLOBALS['AUTH']->generateToken($user['users_userid'], false);
            finish(true);
        }
	}
} else finish(false, ["code" => null, "message" => "Unknown error"]);
?>

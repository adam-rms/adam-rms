<?php
require_once 'loginAjaxHead.php';

if (isset($_POST['formInput'])) {
    $input = trim(strtolower($GLOBALS['bCMS']->sanitizeString($_POST['formInput'])));
    if ($input == "") finish(false, ["code" => null, "message" => "No data specified"]);
    else {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) $DBLIB->where ("users_email", $input);
        else $DBLIB->where ("users_username", $input);
        $user = $DBLIB->getOne("users",["users.users_userid"]);
        if (!$user) finish(false, ["code" => null, "message" => "No user found with associated email address or username"]);

        if ($AUTH->forgotPassword($user['users_userid'])) {
            $bCMS->auditLog("UPDATE", "users", "INIT PASSWORD RESET AT LOGIN PAGE", $user['users_userid'],$user['users_userid']);
            finish(true, null, true);
        } else finish(true, null, false);
    }
} else finish(false, ["code" => null, "message" => "Unknown error"]);
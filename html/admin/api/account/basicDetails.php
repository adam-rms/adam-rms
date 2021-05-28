<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$array = [];
if (!isset($_POST['formData'])) die("404");
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}

if (!$AUTH->permissionCheck(5)) $array['users_userid'] = $AUTH->data['users_userid'];

$DBLIB->where("users.users_deleted", 0);
$DBLIB->where("users.users_suspended", 0);
$DBLIB->where("users.users_userid", $array['users_userid']);
$currentUserData = $DBLIB->getone("users", ["users.*"]);
if (!$currentUserData) finish(false, ["message" => "User not found error"]);

$array["users_email"] = strtolower($array["users_email"]);
$array["users_username"] = strtolower($array["users_username"]);

if ($array["users_email"] != $currentUserData["users_email"] and $AUTH->emailTaken($array["users_email"])) finish(false, ["message" => "Sorry this email is in use for another account"]);
if ($array["users_username"] != $currentUserData["users_username"] and $AUTH->usernameTaken($array["users_username"])) finish(false, ["message" => "Sorry this username is in use for another account, please pick another"]);

if ($array["users_email"] != $currentUserData["users_email"]) $array["users_emailVerified"] = 0;
else $array["users_emailVerified"] = $currentUserData["users_emailVerified"];

$DBLIB->where("users_userid",$array['users_userid']);
$result = $DBLIB->update("users", array_intersect_key( $array, array_flip( ["users_username","users_name1","users_name2","users_email","users_social_facebook","users_social_twitter","users_social_instagram","users_social_linkedin","users_social_snapchat"])),1);
if (!$result) finish(false, ["code" => "UPDATE-FAIL", "message"=> "Could not update account details"]);
else {
    if ($array["users_emailVerified"] == 0) $AUTH->verifyEmail($array['users_userid']);
    $bCMS->auditLog("EDIT-ACCOUNT", "users", json_encode($array), $AUTH->data['users_userid'],$array['users_userid']);
    finish(true);
}
